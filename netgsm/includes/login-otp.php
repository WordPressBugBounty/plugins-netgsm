<?php

if (!defined('ABSPATH')) exit;

/**
 * Müşteri girişinde (login) OTP SMS ile 2FA.
 *
 * Bilerek TÜM hook'lar dosya kökünde, koşulsuz olarak kaydediliyor —
 * netgsm_options() fonksiyonuna GÖMÜLMÜYOR. netgsm_options() sadece
 * admin_init hook'unda çalışıyor ve admin_init yalnızca admin-ajax.php,
 * admin-post.php ve admin.php içinde ateşleniyor (WordPress çekirdeğinden
 * doğrulandı) — wp-login.php bunların hiçbiri değil. Aynı deseni orada
 * kullansaydık, giriş sırasında bu kod hiç çalışmazdı.
 *
 * Kapsam: sadece "customer" rolündeki kullanıcılar (bkz. proje notları).
 * Telefon/SMS gönderimi başarısız olursa veya kullanıcının telefonu
 * kayıtlı değilse, akış SESSİZCE normal girişe düşer — kimse hesabından
 * kilitlenmez (Shopify tarafındaki kanıtlanmış aynı güvenlik prensibi).
 */

const NETGSM_LOGIN_OTP_TICKET_TTL = 5 * MINUTE_IN_SECONDS;
const NETGSM_LOGIN_OTP_RESEND_COOLDOWN = 120; // saniye
const NETGSM_LOGIN_OTP_MAX_ATTEMPTS = 3;

add_filter('authenticate', 'netgsm_login_otp_intercept', 30, 3);
function netgsm_login_otp_intercept($user, $username, $password)
{
    if (!($user instanceof WP_User)) {
        return $user; // Şifre zaten yanlış / bilgi eksik — WP kendi hata akışını yürütsün.
    }

    if (empty(get_option('netgsm_login_otp_control')) || empty(get_option('netgsm_status'))) {
        return $user;
    }

    if (!in_array('customer', (array) $user->roles, true)) {
        return $user; // Sadece müşteri rolü kapsamda.
    }

    $phone = sanitize_text_field(get_user_meta($user->ID, 'billing_phone', true));
    if ($phone === '') {
        return $user; // Telefon yok → 2FA uygulanamaz, girişi engelleme.
    }

    $sent = netgsm_login_otp_send($user, $phone);
    if (!$sent['ok']) {
        return $user; // Hiç gönderilmiş/bekleyen kod yok VE yeni gönderim de başarısız → girişi engelleme (kilitlenmeyi önle).
    }

    netgsm_login_otp_render_code_form($sent['ticket'], $phone, '');
    exit;
}

/**
 * Bir telefon için hâlâ geçerli, bekleyen bir OTP bileti var mı?
 * Varsa döndürür — bu, aynı kullanıcının kısa sürede tekrar giriş
 * denemesi yaptığı (ör. yanlış şifreyle birkaç kez denedikten sonra
 * doğru şifreyle tekrar denediği) durumda YENİ kod göndermek yerine
 * MEVCUT kodu kullanmaya devam etmemizi sağlar. Bu olmadan, hız
 * sınırına takılan "yeniden gönderim" başarısızlığı yanlışlıkla
 * 2FA'yı tamamen atlatan bir açık haline geliyordu.
 */
function netgsm_login_otp_pending_ticket(string $phone): ?string
{
    $ticket = get_transient('netgsm_login_otp_phone_' . md5($phone));
    if ($ticket === false) {
        return null;
    }
    // Bilet hâlâ gerçekten canlı mı (kod transient'i düşmüş/silinmiş olabilir)?
    if (get_transient('netgsm_login_otp_' . $ticket) === false) {
        // Yetim kalmış eşleme — hız sınırını da birlikte temizle ki
        // aşağıdaki netgsm_login_otp_clear() çağrılmamış eski bir durumda
        // takılı kalmasın.
        netgsm_login_otp_clear($phone);
        return null;
    }
    return $ticket;
}

/**
 * Bir telefon için tüm OTP durumunu (bilet + telefon eşlemesi + hız sınırı)
 * birlikte temizler. Bilet her geçersiz kılındığında (başarılı giriş,
 * çok fazla yanlış deneme) bunun çağrılması ZORUNLU — aksi halde hız
 * sınırı bilet öldükten sonra da yaşamaya devam eder ve bir sonraki
 * girişte "gönderilemedi" sanılıp 2FA'nın tamamen atlanmasına yol açar
 * (canlıda tam olarak bu hata yaşandı ve düzeltildi).
 */
function netgsm_login_otp_clear(string $phone, ?string $ticket = null): void
{
    if ($ticket !== null) {
        delete_transient('netgsm_login_otp_' . $ticket);
    }
    delete_transient('netgsm_login_otp_phone_' . md5($phone));
    delete_transient('netgsm_login_otp_rate_' . md5($phone));
}

/**
 * OTP üretir, gönderir, transient'e yazar.
 *
 * Bekleyen geçerli bir kod varsa (ör. art arda giriş denemeleri), YENİ SMS
 * göndermek yerine onu yeniden kullanır — hız sınırı asla 2FA'yı atlatmaz,
 * sadece gereksiz SMS gönderimini engeller. $force=true yalnızca kullanıcı
 * açıkça "Kodu Tekrar Gönder"e bastığında kullanılır.
 *
 * @return array{ok: bool, ticket?: string, wait?: int}
 */
function netgsm_login_otp_send(WP_User $user, string $phone, bool $force = false): array
{
    if (!$force) {
        $pending = netgsm_login_otp_pending_ticket($phone);
        if ($pending !== null) {
            return ['ok' => true, 'ticket' => $pending, 'reused' => true];
        }
    }

    $rate_key = 'netgsm_login_otp_rate_' . md5($phone);
    $last_sent = get_transient($rate_key);
    if ($last_sent !== false) {
        // Bekleyen geçerli kod yok (yukarıda elendi) ama hâlâ soğuma
        // süresindeyiz — bu sadece kod süresi admin tarafından hız
        // sınırından kısa ayarlandıysa oluşabilecek nadir bir durum.
        return ['ok' => false, 'wait' => NETGSM_LOGIN_OTP_RESEND_COOLDOWN - (time() - (int) $last_sent)];
    }

    $code = (string) random_int(100000, 999999);
    $refno = substr(md5(uniqid('', true)), 0, 5);

    $template = sanitize_textarea_field(wp_unslash(get_option('netgsm_login_otp_text')));
    if ($template === '') {
        $template = 'Giriş doğrulama kodunuz: [kod]';
    }

    $replace = new ReplaceFunction();
    $message = $replace->netgsm_replace_twofactorauth_text([
        'otpcode'    => $code,
        'phone'      => $phone,
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'user_email' => $user->user_email,
        'refno'      => $refno,
        'message'    => $template,
    ]);
    $message = strip_tags($message);

    $netgsm = new Netgsmsms(
        sanitize_text_field(get_option('netgsm_user')),
        sanitize_text_field(get_option('netgsm_pass')),
        sanitize_text_field(get_option('netgsm_input_smstitle')),
        sanitize_text_field(get_option('netgsm_trChar'))
    );
    $result = $netgsm->sendOTPSMS($phone, $message);

    if (!isset($result['kod']) || $result['kod'] != '00') {
        return ['ok' => false];
    }

    $diff = (int) get_option('netgsm_login_otp_diff');
    if ($diff <= 0) {
        $diff = 180;
    }

    $ttl = min($diff, NETGSM_LOGIN_OTP_TICKET_TTL);
    $ticket = wp_generate_password(32, false, false);
    set_transient('netgsm_login_otp_' . $ticket, [
        'user_id'  => $user->ID,
        'code'     => $code,
        'attempts' => 0,
        'phone'    => $phone,
    ], $ttl);
    // Telefon → bilet eşlemesi: art arda giriş denemelerinde aynı kodu
    // bulup yeniden kullanabilmek için (bkz. netgsm_login_otp_pending_ticket).
    set_transient('netgsm_login_otp_phone_' . md5($phone), $ticket, $ttl);

    set_transient($rate_key, time(), NETGSM_LOGIN_OTP_RESEND_COOLDOWN);

    return ['ok' => true, 'ticket' => $ticket];
}

/**
 * Kod giriş ekranını doğrudan render edip çıkar — wp-login.php'nin normal
 * akışına hiç girmeden, kendi minimal sayfamızı basıyoruz.
 */
function netgsm_login_otp_render_code_form(string $ticket, string $phone, string $error)
{
    $masked = substr($phone, 0, 3) . str_repeat('•', max(0, strlen($phone) - 5)) . substr($phone, -2);
    $action_url = esc_url(admin_url('admin-post.php'));
    $resend_url = esc_url(admin_url('admin-post.php'));

    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php esc_html_e('Giriş Doğrulama', 'netgsm'); ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 2rem; max-width: 360px; width: 90%; text-align: center; }
        h1 { font-size: 1.1rem; margin: 0 0 0.5rem; }
        p { color: #555; font-size: 0.9rem; }
        input[type=text] { width: 100%; box-sizing: border-box; font-size: 1.25rem; letter-spacing: 0.3em; text-align: center; padding: 0.75rem; margin: 1rem 0; border: 1px solid #ccc; border-radius: 6px; }
        button { width: 100%; padding: 0.75rem; background: #111; color: #fff; border: 0; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .error { color: #c0392b; font-size: 0.85rem; margin-top: 0.5rem; }
        .resend { margin-top: 1rem; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1><?php esc_html_e('Giriş Doğrulama', 'netgsm'); ?></h1>
        <p><?php echo esc_html(sprintf(__('%s numaralı telefonunuza gönderilen kodu girin.', 'netgsm'), $masked)); ?></p>
        <form method="post" action="<?php echo $action_url; ?>">
            <input type="hidden" name="action" value="netgsm_verify_login_otp">
            <input type="hidden" name="ticket" value="<?php echo esc_attr($ticket); ?>">
            <?php wp_nonce_field('netgsm_login_otp_' . $ticket, 'netgsm_login_otp_nonce'); ?>
            <input type="text" name="code" inputmode="numeric" maxlength="6" autofocus required>
            <?php if ($error !== '') : ?>
                <p class="error"><?php echo esc_html($error); ?></p>
            <?php endif; ?>
            <button type="submit"><?php esc_html_e('Doğrula ve Giriş Yap', 'netgsm'); ?></button>
        </form>
        <form method="post" action="<?php echo $resend_url; ?>" class="resend">
            <input type="hidden" name="action" value="netgsm_resend_login_otp">
            <input type="hidden" name="ticket" value="<?php echo esc_attr($ticket); ?>">
            <?php wp_nonce_field('netgsm_login_otp_' . $ticket, 'netgsm_login_otp_nonce'); ?>
            <button type="submit" style="background:transparent;color:#555;border:1px solid #ccc;"><?php esc_html_e('Kodu Tekrar Gönder', 'netgsm'); ?></button>
        </form>
    </div>
</body>
</html>
<?php
}

add_action('admin_post_netgsm_verify_login_otp', 'netgsm_verify_login_otp_handler');
add_action('admin_post_nopriv_netgsm_verify_login_otp', 'netgsm_verify_login_otp_handler');
function netgsm_verify_login_otp_handler()
{
    $ticket = isset($_POST['ticket']) ? sanitize_text_field($_POST['ticket']) : '';
    $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';

    if ($ticket === '' || !isset($_POST['netgsm_login_otp_nonce']) || !wp_verify_nonce($_POST['netgsm_login_otp_nonce'], 'netgsm_login_otp_' . $ticket)) {
        wp_die(esc_html__('Geçersiz veya süresi dolmuş istek. Lütfen tekrar giriş yapmayı deneyin.', 'netgsm'), '', ['response' => 400]);
    }

    $key = 'netgsm_login_otp_' . $ticket;
    $data = get_transient($key);

    if ($data === false) {
        wp_die(esc_html__('Doğrulama kodunun süresi doldu. Lütfen tekrar giriş yapmayı deneyin.', 'netgsm'), '', ['response' => 400]);
    }

    if ($data['attempts'] >= NETGSM_LOGIN_OTP_MAX_ATTEMPTS) {
        // Bilet İLE hız sınırını birlikte temizle — sadece bileti silip hız
        // sınırını bırakırsak, kullanıcı tekrar giriş denediğinde yeni kod
        // gönderilemez ve akış yanlışlıkla 2FA'sız girişe düşer.
        netgsm_login_otp_clear($data['phone'], $ticket);
        wp_die(esc_html__('Çok fazla hatalı deneme. Lütfen tekrar giriş yapmayı deneyin.', 'netgsm'), '', ['response' => 400]);
    }

    if (!hash_equals($data['code'], $code)) {
        $data['attempts']++;
        set_transient($key, $data, NETGSM_LOGIN_OTP_TICKET_TTL);
        netgsm_login_otp_render_code_form($ticket, $data['phone'], __('Doğrulama kodunu yanlış girdiniz.', 'netgsm'));
        exit;
    }

    netgsm_login_otp_clear($data['phone'], $ticket);

    $user = get_user_by('id', $data['user_id']);
    if (!$user) {
        wp_die(esc_html__('Kullanıcı bulunamadı.', 'netgsm'), '', ['response' => 400]);
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
    do_action('wp_login', $user->user_login, $user);

    $redirect = (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '') ?: home_url('/');
    wp_safe_redirect($redirect);
    exit;
}

add_action('admin_post_netgsm_resend_login_otp', 'netgsm_resend_login_otp_handler');
add_action('admin_post_nopriv_netgsm_resend_login_otp', 'netgsm_resend_login_otp_handler');
function netgsm_resend_login_otp_handler()
{
    $ticket = isset($_POST['ticket']) ? sanitize_text_field($_POST['ticket']) : '';

    if ($ticket === '' || !isset($_POST['netgsm_login_otp_nonce']) || !wp_verify_nonce($_POST['netgsm_login_otp_nonce'], 'netgsm_login_otp_' . $ticket)) {
        wp_die(esc_html__('Geçersiz veya süresi dolmuş istek. Lütfen tekrar giriş yapmayı deneyin.', 'netgsm'), '', ['response' => 400]);
    }

    $key = 'netgsm_login_otp_' . $ticket;
    $data = get_transient($key);
    if ($data === false) {
        wp_die(esc_html__('Doğrulama kodunun süresi doldu. Lütfen tekrar giriş yapmayı deneyin.', 'netgsm'), '', ['response' => 400]);
    }

    $user = get_user_by('id', $data['user_id']);
    if (!$user) {
        wp_die(esc_html__('Kullanıcı bulunamadı.', 'netgsm'), '', ['response' => 400]);
    }

    // force=true: kullanıcı açıkça yeni kod istedi, mevcut bekleyen kodu
    // yeniden kullanmak yerine gerçekten yeni bir SMS gönder.
    $sent = netgsm_login_otp_send($user, $data['phone'], true);
    if (!$sent['ok']) {
        $wait = isset($sent['wait']) && $sent['wait'] > 0 ? $sent['wait'] : NETGSM_LOGIN_OTP_RESEND_COOLDOWN;
        netgsm_login_otp_render_code_form($ticket, $data['phone'], sprintf(__('Yeni kod için %d saniye beklemelisiniz.', 'netgsm'), $wait));
        exit;
    }

    // Eski bileti geçersiz kılıp yenisiyle devam et.
    delete_transient($key);
    netgsm_login_otp_render_code_form($sent['ticket'], $data['phone'], '');
    exit;
}
