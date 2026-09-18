<?php
// if (!current_user_can('administrator')) {
//     return;  // Admin olmayan kullanıcılar erişemez
// }
if (!defined('ABSPATH')) exit;
$cf7_list = apply_filters('netgsm_contact_form_7_list', '');

$netgsm = new Netgsmsms(get_option("netgsm_user"), get_option("netgsm_pass"), get_option("netgsm_input_smstitle"));
$cevap = json_decode($netgsm->netgsm_GirisSorgula(get_option("netgsm_user"), get_option("netgsm_pass")));

$sessionid = get_current_user_id();
$session = new WP_User($sessionid);

$fps_roles = new WP_Roles();
$role_list = $fps_roles->get_names();

$auth_roles = [];
if (get_option('netgsm_auth_roles') != '') {
    $auth_roles = explode(',', get_option('netgsm_auth_roles'));
}

$auth_users = [];
if (get_option('netgsm_auth_users') != '') {
    $auth_users = explode(',', get_option('netgsm_auth_users'));
}

$netgsm_auth_roles_control = get_option('netgsm_auth_roles_control');
$netgsm_auth_users_control = get_option('netgsm_auth_users_control');

// Büyük üye sayısına sahip sitelerde tüm kullanıcıların (ve tüm usermeta'larının)
// tek seferde belleğe alınması PHP bellek limitini aşıp ayarlar sayfasında
// "kritik hata" (beyaz ekran) oluşturuyordu. Bu değişken artık yalnızca "Ayarlar"
// sekmesindeki yetkilendirme listeleri için kullanılıyor (Toplu SMS ve Gelen kutusu
// AJAX ile parça parça yüklenir). Bu nedenle tüm kullanıcılar yerine yalnızca
// yetkilendirmeyle ilgili rollere sahip kullanıcılar getirilir; ayrıca güvenlik
// amacıyla filtre ile ayarlanabilen bir üst sınır uygulanır.
$netgsm_users_limit = (int) apply_filters('netgsm_users_query_limit', 2000);
if ($netgsm_users_limit <= 0) {
    $netgsm_users_limit = 2000;
}
$netgsm_role_slugs = array_values(array_unique(array_merge(['administrator'], (array) $auth_roles)));
$users = get_users([
    'role__in' => $netgsm_role_slugs,
    'number'   => $netgsm_users_limit,
    'orderby'  => 'ID',
    'order'    => 'ASC',
]);
// Rolü yukarıdakilerden biri olmayan ama açıkça yetkilendirilmiş kullanıcılar da eklenir
// (netgsm_findUser'ın onları bulabilmesi için).
if (!empty($auth_users)) {
    $netgsm_existing_ids = wp_list_pluck($users, 'ID');
    $netgsm_missing_ids  = array_diff(array_map('intval', $auth_users), array_map('intval', $netgsm_existing_ids));
    if (!empty($netgsm_missing_ids)) {
        $users = array_merge($users, get_users([
            'include' => $netgsm_missing_ids,
            'orderby' => 'ID',
            'order'   => 'ASC',
        ]));
    }
}

//yetkilendirme ile ilgili geliştirmeler 16.07.2021
$cntrl = false;
$cntrl2 = false;

foreach ($session->roles as $k => $role) {
    if (in_array($role, ['administrator'])) {
        $cntrl = true;
    }
    if (in_array($role, $auth_roles)) {
        $cntrl2 = true;
    }
}
//yetkilendirme ile ilgili geliştirmeler 16.07.2021

if ($cntrl || ($cntrl2 && $netgsm_auth_roles_control == 1)) {


    //$netgsm_auth_users_control 0 ise bu özellik kapalıdır ve user bazlı yetki kontrolüne gerek yoktur..
?>
    <br>
    <div class="vx-shell">
        <div class="vx-page">
            <div class="vx-panel">
                <div class="vx-topbar">
                    <a href="https://www.netgsm.com.tr/" alt="Yeni nesil telekom operatörü" target="_blank" class="vx-topbar-logo">
                        <img src="<?= esc_url(plugins_url('lib/image/netgsm-logo-dark.png', dirname(__FILE__))) ?>" alt="Netgsm">
                    </a>

                    <div class="vx-topbar-right">
                        <div <?php if ($cevap->href != "") { ?>onclick="window.open('<?php echo esc_url($cevap->href); ?>','_blank');" <?php } ?> class="vx-badge<?php echo ($cevap->href != "") ? ' is-clickable' : ''; ?>" id="bakiye" <?php if ($cevap->href != "") { ?>style="cursor:pointer"<?php } ?>>
                            <?php
                            // Dolu olan parcalari topla, aralarina yalnizca gercekten iki parca varsa
                            // ayirici koy (aksi halde basta bosta bir "·" kaliyordu).
                            $ngsm_badge_parts = array();
                            if (!empty($cevap->mesaj)) {
                                $ngsm_badge_parts[] = "<i class='fa " . esc_attr($cevap->icon) . "'></i> " . rtrim(wp_kses_post($cevap->mesaj), ':');
                            }
                            if (!empty($cevap->mesajPaket)) {
                                $ngsm_badge_parts[] = trim(rtrim(wp_kses_post($cevap->mesajPaket), ':'));
                            }
                            if (!empty($cevap->mesajKredi)) {
                                $ngsm_badge_parts[] = trim(rtrim(wp_kses_post($cevap->mesajKredi), ':'));
                            }
                            echo implode(' · ', $ngsm_badge_parts);
                            ?>
                        </div>
                        <button class="vx-btn vx-btn-outline-error vx-btn-sm" id="ngsm-logout-btn" type="button" onclick="logout()" style="display:<?php echo ($cevap->btnkontrol == "enabled") ? '' : 'none'; ?>;"><i class="fa fa-sign-out"></i> Çıkış</button>
                    </div>
                </div>

                <div class="vx-body">
                    <form action="options.php" method="post" id="form-module" class="form-horizontal" name="form-module" style="width:100%; display:flex;">
                        <?php settings_fields('netgsmoptions'); ?>
                        <?php do_settings_sections('netgsmoptions'); ?>
                        <input type="hidden" name="sayfayi_yenile" id="sayfayi_yenile" value="0">

                        <div class="vx-aside">
                            <div class="vx-aside-inner">
                                <div class="vx-aside-label">Netgsm SMS</div>
                                <ul class="vx-nav" id="language">
                                    <li><a href="#login" data-toggle="tab" class="vx-nav-link"><i class="ti ti-login"></i> Giriş</a></li>
                                    <li><a href="#sms" data-toggle="tab" class="vx-nav-link"><i class="ti ti-shopping-cart"></i> WooCommerce SMS</a></li>
                                    <li><a href="#tf2sms" data-toggle="tab" class="vx-nav-link"><i class="ti ti-user-check"></i> Üyelik Doğrulama</a></li>
                                    <li><a href="#bulksms" data-toggle="tab" class="vx-nav-link"><i class="ti ti-messages"></i> Toplu SMS</a></li>
                                    <li><a href="#privatesms" data-toggle="tab" class="vx-nav-link"><i class="ti ti-message-plus"></i> Özel SMS</a></li>
                                    <li><a href="#cf7sms" data-toggle="tab" class="vx-nav-link"><i class="ti ti-forms"></i> Contact Form7 SMS</a></li>
                                    <li><a href="#iys" data-toggle="tab" class="vx-nav-link"><i class="ti ti-shield-check"></i> İYS</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="vx-content">
                            <div class="vx-content-pad">
                                <div class="tab-content">
                            <div class="tab-pane" id="login">
                                <?php
                                $netgsm_all_smstitle   = $netgsm->getSmsBaslik();
                                $netgsm_input_smstitle = esc_html(get_option("netgsm_input_smstitle"));
                                $netgsm_trChar         = esc_html(get_option("netgsm_trChar"));
                                if ($cevap->btnkontrol != "enabled") { $netgsm_trChar = 0; }
                                $netgsm_iys_control       = esc_html(get_option("netgsm_iys_control"));
                                $netgsm_brandcode_control = esc_html(get_option("netgsm_brandcode_control"));
                                $netgsm_status = esc_html(get_option("netgsm_status"));
                                if ($cevap->btnkontrol != "enabled") { $netgsm_status = 0; }
                                $ngsm_connected = ($cevap->btnkontrol == "enabled");
                                ?>
                                <div class="vx-page-header">
                                    <div class="vx-page-header-icon"><i class="ti ti-login"></i></div>
                                    <div class="vx-page-header-text">
                                        <h2 class="vx-page-header-title">Giriş</h2>
                                        <p class="vx-page-header-subtitle">Netgsm hesabınız ve varsayılan SMS ayarları</p>
                                    </div>
                                </div>

                                <div class="vx-screen" style="max-width:640px;">

                                    <div id="ngsm-connect-strip" style="display:<?php echo $ngsm_connected ? 'flex' : 'none'; ?>;align-items:center;gap:14px;padding:14px 18px;border-radius:var(--radius-lg);background:var(--opacity-color-success-success-8);margin-bottom:32px;">
                                        <div style="width:40px;height:40px;border-radius:50%;background:var(--color-palette-success-main);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="ti ti-circle-check" style="font-size:22px;"></i>
                                        </div>
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:15px;font-weight:700;color:var(--text-primary);">Hesabınız bağlı ve doğrulandı</div>
                                            <div id="ngsm-connect-user" style="font-size:13px;color:var(--text-secondary);"><?= esc_html(get_option('netgsm_user')) ?> · Netgsm API bağlantısı aktif</div>
                                        </div>
                                        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:500px;background:var(--color-palette-success-main);color:#fff;font-size:13px;font-weight:600;white-space:nowrap;">
                                            <i class="ti ti-plug-connected"></i> Bağlı
                                        </span>
                                    </div>

                                    <div class="vx-secbox">
                                        <div class="vx-sec-header">
                                            <i class="ti ti-key vx-sec-icon"></i>
                                            <h3>Hesap Bilgileri</h3>
                                        </div>
                                        <p class="vx-sec-desc">netgsm.com.tr giriş bilgilerinizle aynıdır. Değiştirdikten sonra bağlantıyı doğrulayın.</p>
                                        <hr class="vx-sec-rule">
                                        <div class="vx-grid-2" style="margin-top:16px;">
                                            <div class="vx-field">
                                                <label for="netgsm_user">Kullanıcı Adı</label>
                                                <input type="text" name="netgsm_user" id="netgsm_user" class="vx-input" placeholder="Kullanıcı Adı" value="<?php echo esc_attr(get_option("netgsm_user")); ?>" onkeypress="return RestrictSpace()">
                                                <span class="vx-help">Netgsm abone numaranız veya kullanıcı adınız.</span>
                                            </div>
                                            <div class="vx-field">
                                                <label for="netgsm_pass">Şifre</label>
                                                <input type="password" name="netgsm_pass" id="netgsm_pass" class="vx-input" placeholder="Şifre" value="<?php echo esc_attr(get_option("netgsm_pass")); ?>">
                                                <span class="vx-help">Bilgilerinizi kimseyle paylaşmıyoruz.</span>
                                            </div>
                                        </div>
                                        <div style="display:flex;justify-content:flex-end;margin-top:22px;">
                                            <button type="button" class="vx-btn vx-btn-outline" id="login_save" name="login_save" onclick="verifyAccountAjax();"><i class="ti ti-user-check"></i> Hesabımı Doğrula</button>
                                        </div>
                                    </div>

                                    <div class="vx-secbox" style="margin-top:36px;">
                                        <div class="vx-sec-header">
                                            <i class="ti ti-adjustments vx-sec-icon"></i>
                                            <h3>SMS Ayarları</h3>
                                        </div>
                                        <p class="vx-sec-desc">Gönderilecek tüm mesajlar için varsayılan değerler.</p>
                                        <hr class="vx-sec-rule">
                                        <div class="vx-grid-2" style="margin-top:16px;row-gap:18px;">
                                            <div class="vx-field">
                                                <label for="netgsm_input_smstitle">SMS Başlığı</label>
                                                <select name="netgsm_input_smstitle" id="netgsm_input_smstitle" class="vx-select">
                                                    <option value="0">Sms Başlığı Seçiniz</option>
                                                    <?php
                                                    if (isset($netgsm_input_smstitle) && $netgsm_input_smstitle != "" && is_array($netgsm_all_smstitle)) {
                                                        foreach ($netgsm_all_smstitle as $title) {
                                                            if ($title != '') { ?>
                                                                <option value="<?php echo esc_attr($title); ?>" <?php echo ($title == $netgsm_input_smstitle) ? 'selected' : ''; ?>><?= esc_html($title); ?></option>
                                                    <?php   }
                                                        }
                                                    } ?>
                                                </select>
                                                <span class="vx-help">Onaylı gönderici başlığınız.</span>
                                            </div>
                                            <div class="vx-field">
                                                <label for="input-trChar">Türkçe Karakter</label>
                                                <select name="netgsm_trChar" id="input-trChar" class="vx-select">
                                                    <?php if ($netgsm_trChar) { ?>
                                                        <option value="1" selected>Açık, Türkçe karakterler gönderilsin.</option>
                                                        <option value="0">Kapalı, Türkçe karakterler gönderilmesin.</option>
                                                    <?php } else { ?>
                                                        <option value="1">Açık, Türkçe karakterler gönderilsin.</option>
                                                        <option value="0" selected>Kapalı, Türkçe karakterler gönderilmesin.</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="vx-field">
                                                <label for="input-iysControl">Mesaj İçerik Türü</label>
                                                <select name="netgsm_iys_control" id="input-iysControl" class="vx-select" onchange="kontrolEt(this)">
                                                    <option value="" <?php echo empty($netgsm_iys_control) ? 'selected' : ''; ?>>Mesaj içerik türü seçiniz</option>
                                                    <option value="1" <?php echo $netgsm_iys_control == '1' ? 'selected' : ''; ?>>Kampanya, tanıtım, kutlama vb. (İYS'ye bireysel kayıtlı alıcılarınıza gönderilir.)</option>
                                                    <option value="2" <?php echo $netgsm_iys_control == '2' ? 'selected' : ''; ?>>Kampanya, tanıtım, kutlama vb. (İYS'ye tacir kayıtlı alıcılarınıza gönderilir.)</option>
                                                    <option value="3" <?php echo $netgsm_iys_control == '3' ? 'selected' : ''; ?>>Bilgilendirme, kargo, şifre vb. (İYS'den sorgulanmaz.)</option>
                                                </select>
                                                <p id="iys_uyari_mesaji" class="vx-help" style="display:none;color:var(--color-palette-error-main);"></p>
                                            </div>
                                            <div class="vx-field">
                                                <label for="input-status">Eklenti Durumu</label>
                                                <select name="netgsm_status" id="input-status" class="vx-select">
                                                    <?php if ($netgsm_status) { ?>
                                                        <option value="1" selected>Açık</option>
                                                        <option value="0">Kapalı</option>
                                                    <?php } else { ?>
                                                        <option value="1">Açık</option>
                                                        <option value="0" selected>Kapalı</option>
                                                    <?php } ?>
                                                </select>
                                                <?php if (!$netgsm_status) : ?><span class="vx-help">Kapalıyken hiçbir otomatik SMS gönderilmez.</span><?php endif; ?>
                                            </div>
                                        </div>
                                        <div style="display:flex;justify-content:flex-end;margin-top:22px;">
                                            <button type="button" class="vx-btn vx-btn-primary" id="login_save2" name="login_save2" onclick="saveSmsSettingsAjax();"><i class="ti ti-device-floppy"></i> SMS Ayarlarını Kaydet</button>
                                        </div>
                                    </div>

                                    <?php
                                    // Yetkilendirme karti — eskiden ayri "Ayarlar" sekmesindeydi (pages/settings.php).
                                    // Alan adlari (netgsm_auth_roles / netgsm_auth_users / *_control) ve transfer
                                    // listesi JS mantigi birebir korundu; yalnizca gorunum yenilendi.
                                    // Yalnizca administrator gorebilir: yetkilendirilmis ama admin olmayan
                                    // kullanicilarin yetki listelerini degistirmesi engellenmeli.
                                    if (current_user_can('administrator')) :
                                        $ngsm_roles_on = (esc_attr(get_option('netgsm_auth_roles_control')) == 1);
                                        $ngsm_users_on = (esc_attr(get_option('netgsm_auth_users_control')) == 1);
                                        $ngsm_zero_on  = (get_option('netgsm_phonenumber_zero1') == 1);
                                        $ngsm_lic_on   = (get_option('netgsm_licence_key_to_meta') == 1);
                                    ?>
                                    <div class="vx-secbox" style="margin-top:36px;">
                                        <div class="vx-sec-header vx-sec-header-toggle">
                                            <div style="display:flex;align-items:center;gap:9px;">
                                                <i class="ti ti-shield-lock vx-sec-icon"></i>
                                                <h3>Yetkilendirme</h3>
                                            </div>
                                            <button type="button" class="vx-gear-btn" id="ngsm_auth_eye" onclick="ngsmToggleAuthCard()" title="Yetkilendirme ayarlarını göster/gizle">
                                                <i class="ti ti-eye-off" id="ngsm_auth_eye_icon"></i>
                                            </button>
                                        </div>
                                        <p class="vx-sec-desc">Eklentiye hangi rol ve kullanıcıların erişebileceğini yönetin. Administrator (Yönetici) rolü her durumda tam yetkilidir.</p>
                                        <div id="ngsm_auth_body" style="display:none;">
                                        <hr class="vx-sec-rule">

                                        <!-- Tam Yetkili Roller -->
                                        <div class="vx-wc-item">
                                            <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                                                <div class="vx-toggle-row-left">
                                                    <i class="ti ti-users-group"></i>
                                                    <div>
                                                        <p class="vx-toggle-row-label">Tam Yetkili Roller</p>
                                                        <p class="vx-toggle-row-desc">Kapalıyken seçilen roller dikkate alınmaz.</p>
                                                    </div>
                                                </div>
                                                <label class="vx-switch">
                                                    <input name="netgsm_auth_roles_control" id="switch_netgsm_roles" type="checkbox" value="1" onchange="netgsm_field_onoff_custom('netgsm_roles')" <?php checked($ngsm_roles_on); ?>>
                                                    <span class="vx-switch-track"></span>
                                                </label>
                                            </div>
                                            <div class="vx-sec-reveal" id="field_netgsm_roles" style="<?php echo $ngsm_roles_on ? '' : 'display:none;'; ?>">
                                                <input type="hidden" id="netgsm_auth_roles_text" name="netgsm_auth_roles" value="<?php echo esc_attr(get_option('netgsm_auth_roles')); ?>">
                                                <div class="vx-transfer">
                                                    <div>
                                                        <div class="vx-transfer-label">Rol Listesi</div>
                                                        <select multiple id="netgsm_auth_roles" class="vx-transfer-list" size="6">
                                                            <?php foreach ($role_list as $key => $role) {
                                                                if ($key == 'administrator' || in_array($key, $auth_roles)) { continue; } ?>
                                                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($role); ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="vx-transfer-mid"><i class="ti ti-arrows-exchange"></i></div>
                                                    <div>
                                                        <div class="vx-transfer-label">İzin Verilen Roller</div>
                                                        <select multiple id="netgsm_auth_roles_selected" class="vx-transfer-list" size="6">
                                                            <?php foreach ($auth_roles as $key => $role) {
                                                                if ($role == 'administrator') { continue; } ?>
                                                                <option value="<?php echo esc_attr($role); ?>"><?php echo esc_html($role_list[$role]); ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="vx-transfer-actions">
                                                    <button type="button" class="vx-btn vx-btn-primary vx-btn-sm" id="netgsm_add_role_btn">İzin Ver <i class="ti ti-arrow-right"></i></button>
                                                    <button type="button" class="vx-btn vx-btn-outline-error vx-btn-sm" id="netgsm_delete_role_btn"><i class="ti ti-arrow-left"></i> Kaldır</button>
                                                </div>
                                                <div class="vx-note vx-note-neutral">
                                                    <ul>
                                                        <li><b>Administrator (Yönetici)</b> rolü her durumda <b>tam yetkilidir</b>.</li>
                                                        <li><b>CTRL</b> tuşu ile birden fazla rol seçebilirsiniz.</li>
                                                        <li>Ekleme/çıkarma sonrası değişiklikleri kaydetmelisiniz.</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Yetkili Kullanıcılar -->
                                        <div class="vx-wc-item">
                                            <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                                                <div class="vx-toggle-row-left">
                                                    <i class="ti ti-user-shield"></i>
                                                    <div>
                                                        <p class="vx-toggle-row-label">Yetkili Kullanıcılar</p>
                                                        <p class="vx-toggle-row-desc">Yalnızca izin verilen rollerdeki kullanıcılar seçilebilir.</p>
                                                    </div>
                                                </div>
                                                <label class="vx-switch">
                                                    <input name="netgsm_auth_users_control" id="switch_netgsm_users" type="checkbox" value="1" onchange="netgsm_field_onoff_custom('netgsm_users')" <?php checked($ngsm_users_on); ?>>
                                                    <span class="vx-switch-track"></span>
                                                </label>
                                            </div>
                                            <div class="vx-sec-reveal" id="field_netgsm_users" style="<?php echo $ngsm_users_on ? '' : 'display:none;'; ?>">
                                                <input type="hidden" id="netgsm_auth_users_text" name="netgsm_auth_users" value="<?php echo esc_attr(get_option('netgsm_auth_users')); ?>">
                                                <div class="vx-transfer">
                                                    <div>
                                                        <div class="vx-transfer-label">Kullanıcı Listesi</div>
                                                        <select multiple id="netgsm_auth_users" class="vx-transfer-list" size="6">
                                                            <?php foreach ($users as $key => $user) {
                                                                if (
                                                                    in_array($user->roles[0], ['administrator'])
                                                                    || (in_array($user->roles[0], $auth_roles) && $netgsm_auth_roles_control == 1)
                                                                ) {
                                                                    if ($user->user_login == 'admin' || in_array($user->ID, $auth_users)) { continue; } ?>
                                                                    <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name) . ' (' . esc_html($role_list[$user->roles[0]]) . ')'; ?></option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                    <div class="vx-transfer-mid"><i class="ti ti-arrows-exchange"></i></div>
                                                    <div>
                                                        <div class="vx-transfer-label">Yetki Verilen Kullanıcılar</div>
                                                        <select multiple id="netgsm_auth_users_selected" class="vx-transfer-list" size="6">
                                                            <?php foreach ($auth_users as $key => $userID) {
                                                                // netgsm_findUser() eskiden settings.php'de tanimliydi; o dosya
                                                                // artik dahil edilmedigi icin arama burada satir ici yapiliyor.
                                                                $ngsm_u = false;
                                                                foreach ($users as $u) { if ($u->ID == $userID) { $ngsm_u = $u; break; } }
                                                                if (!$ngsm_u || empty($ngsm_u->roles)) { continue; }
                                                                if (in_array($ngsm_u->roles[0], ['administrator']) || (in_array($ngsm_u->roles[0], $auth_roles) && $netgsm_auth_roles_control == 1)) {
                                                                    if ($ngsm_u->user_login == 'admin') { continue; } ?>
                                                                    <option value="<?php echo esc_attr($userID); ?>"><?php echo esc_html($ngsm_u->display_name) . ' (' . esc_html($role_list[$ngsm_u->roles[0]]) . ')'; ?></option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="vx-transfer-actions">
                                                    <button type="button" class="vx-btn vx-btn-primary vx-btn-sm" id="netgsm_add_user_btn">İzin Ver <i class="ti ti-arrow-right"></i></button>
                                                    <button type="button" class="vx-btn vx-btn-outline-error vx-btn-sm" id="netgsm_delete_user_btn"><i class="ti ti-arrow-left"></i> Kaldır</button>
                                                </div>
                                                <div class="vx-note vx-note-neutral">
                                                    <ul>
                                                        <li><b>admin</b> kullanıcısı, administrator olduğu sürece her durumda <b>tam yetkilidir</b>.</li>
                                                        <li>Ekleme/çıkarma sonrası değişiklikleri kaydetmelisiniz.</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Telefon alanında 0 zorunluluğu -->
                                        <div class="vx-wc-item">
                                            <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                                                <div class="vx-toggle-row-left">
                                                    <i class="ti ti-phone-check"></i>
                                                    <div>
                                                        <p class="vx-toggle-row-label">Yeni üyeliklerde telefon alanında 0 zorunluluğu</p>
                                                        <p class="vx-toggle-row-desc">Telefon numarasının başında 0 girilmesi zorunlu olur.</p>
                                                    </div>
                                                </div>
                                                <label class="vx-switch">
                                                    <input name="netgsm_phonenumber_zero1" id="netgsm_switch145" type="checkbox" value="1" onclick="netgsm_field_onoff('145')" <?php checked($ngsm_zero_on); ?>>
                                                    <span class="vx-switch-track"></span>
                                                </label>
                                            </div>
                                            <div class="vx-sec-reveal" id="netgsm_field145" style="<?php echo $ngsm_zero_on ? '' : 'display:none;'; ?>">
                                                <p class="vx-inline-warning" style="margin:0;">Yeni üyelik özelliklerinde eklenen telefon alanlarında, telefon numarasının başında 0 (sıfır) girilmesi zorunlu olur.</p>
                                            </div>
                                        </div>

                                        <!-- Licence Key -->
                                        <div class="vx-wc-item">
                                            <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                                                <div class="vx-toggle-row-left">
                                                    <i class="ti ti-key"></i>
                                                    <div>
                                                        <p class="vx-toggle-row-label">Licence Key bilgisini siparişin meta anahtarına kaydet</p>
                                                        <p class="vx-toggle-row-desc">Licence Manager eklentisiyle oluşturulan anahtarlar siparişe eklenir.</p>
                                                    </div>
                                                </div>
                                                <label class="vx-switch">
                                                    <input name="netgsm_licence_key_to_meta" id="netgsm_switch146" type="checkbox" value="1" onclick="netgsm_field_onoff('146')" <?php checked($ngsm_lic_on); ?>>
                                                    <span class="vx-switch-track"></span>
                                                </label>
                                            </div>
                                            <div class="vx-sec-reveal" id="netgsm_field146" style="<?php echo $ngsm_lic_on ? '' : 'display:none;'; ?>">
                                                <p class="vx-inline-warning" style="margin:0;">Licence Manager eklentisi ile oluşturulan lisans anahtarları, siparişin meta anahtarlarına eklenir.</p>
                                            </div>
                                        </div>

                                        <div class="vx-note vx-note-error" style="margin-top:18px;">
                                            Rollerin yetki kapasiteleri Netgsm eklentisi üzerinden ayarlanamamaktadır. Bunun için WordPress rol yetkilerini yönetme konusunu araştırabilirsiniz.
                                        </div>

                                        <div style="display:flex;justify-content:flex-end;margin-top:22px;">
                                            <button type="submit" class="vx-btn vx-btn-primary" id="login_save6" name="login_save6" onclick="login();"><i class="ti ti-device-floppy"></i> Yetkilendirmeyi Kaydet</button>
                                        </div>
                                        </div><!-- /#ngsm_auth_body -->
                                    </div>
                                    <?php endif; ?>

                                </div>
                                <script>
                                    // Yetkilendirme kartini goz ikonuyla ac/kapa. Yalnizca gorunumu etkiler,
                                    // hicbir ayar degeri degistirmez; varsayilan kapali.
                                    function ngsmToggleAuthCard() {
                                        var body = document.getElementById('ngsm_auth_body');
                                        var icon = document.getElementById('ngsm_auth_eye_icon');
                                        if (!body || !icon) { return; }
                                        var acik = body.style.display !== 'none';
                                        body.style.display = acik ? 'none' : '';
                                        icon.className = acik ? 'ti ti-eye-off' : 'ti ti-eye';
                                    }

                                    // Yetkilendirme transfer listeleri — eskiden pages/settings.php icindeydi.
                                    // Secili ogeleri iki liste arasinda tasir ve gizli alani (virgulle
                                    // ayrilmis deger) gunceller; kaydetme bu gizli alan uzerinden yurur.
                                    function ngsmTransfer(fromId, toId, hiddenId) {
                                        var $from = jQuery('#' + fromId), $to = jQuery('#' + toId);
                                        if ($from.find('option:selected').length === 0) { return; }
                                        $from.find('option:selected').removeAttr('selected').appendTo($to);
                                        var values = [];
                                        $to.find('option').each(function () { values.push(jQuery(this).val()); });
                                        jQuery('#' + hiddenId).val(values.join(','));
                                    }

                                    jQuery(function () {
                                        jQuery('#netgsm_add_role_btn').on('click', function () {
                                            ngsmTransfer('netgsm_auth_roles', 'netgsm_auth_roles_selected', 'netgsm_auth_roles_text');
                                        });
                                        jQuery('#netgsm_delete_role_btn').on('click', function () {
                                            ngsmTransfer('netgsm_auth_roles_selected', 'netgsm_auth_roles', 'netgsm_auth_roles_text');
                                        });
                                        jQuery('#netgsm_add_user_btn').on('click', function () {
                                            ngsmTransfer('netgsm_auth_users', 'netgsm_auth_users_selected', 'netgsm_auth_users_text');
                                        });
                                        jQuery('#netgsm_delete_user_btn').on('click', function () {
                                            ngsmTransfer('netgsm_auth_users_selected', 'netgsm_auth_users', 'netgsm_auth_users_text');
                                        });
                                    });

                                    function ngsmBuildBadgeHtml(cevap) {
                                        var parts = [];
                                        if (cevap.mesaj) parts.push('<i class="fa ' + cevap.icon + '"></i> ' + String(cevap.mesaj).replace(/:$/, ''));
                                        if (cevap.mesajPaket) parts.push(String(cevap.mesajPaket).replace(/:$/, ''));
                                        if (cevap.mesajKredi) parts.push(String(cevap.mesajKredi).replace(/:$/, ''));
                                        return parts.join(' · ');
                                    }

                                    function verifyAccountAjax() {
                                        var btn = document.getElementById('login_save');
                                        var user = document.getElementById('netgsm_user').value;
                                        var pass = document.getElementById('netgsm_pass').value;
                                        var original = btn.innerHTML;
                                        btn.disabled = true;
                                        btn.innerHTML = 'Doğrulanıyor...';

                                        jQuery.post(ajaxurl, {
                                            action: 'netgsm_verify_account',
                                            _wpnonce: '<?php echo esc_js(wp_create_nonce('netgsm_verify_account')); ?>',
                                            netgsm_user: user,
                                            netgsm_pass: pass
                                        }).done(function (res) {
                                            btn.disabled = false;
                                            btn.innerHTML = original;
                                            if (!res || !res.success) {
                                                swal('Hata', 'İstek başarısız oldu.', 'error');
                                                return;
                                            }
                                            var cevap = res.data;
                                            var connected = (cevap.btnkontrol === 'enabled');

                                            jQuery('#bakiye').html(ngsmBuildBadgeHtml(cevap));

                                            var logoutBtn = document.getElementById('ngsm-logout-btn');
                                            if (logoutBtn) logoutBtn.style.display = connected ? '' : 'none';

                                            var strip = document.getElementById('ngsm-connect-strip');
                                            if (strip) {
                                                strip.style.display = connected ? 'flex' : 'none';
                                                if (connected) {
                                                    jQuery('#ngsm-connect-user').text(user + ' · Netgsm API bağlantısı aktif');
                                                }
                                            }

                                            var msg = (cevap.mesaj || '').replace(/:$/, '');
                                            if (connected) {
                                                swal('Doğrulandı', msg || 'Hesap bağlantısı doğrulandı.', 'success');
                                            } else {
                                                swal('Doğrulanamadı', msg || 'Bilgileri kontrol edin.', 'error');
                                            }
                                        }).fail(function () {
                                            btn.disabled = false;
                                            btn.innerHTML = original;
                                            swal('Hata', 'Sunucuya ulaşılamadı.', 'error');
                                        });
                                    }

                                    function saveSmsSettingsAjax() {
                                        var btn = document.getElementById('login_save2');
                                        var original = btn.innerHTML;
                                        btn.disabled = true;
                                        btn.innerHTML = 'Kaydediliyor...';

                                        jQuery.post(ajaxurl, {
                                            action: 'netgsm_save_sms_settings',
                                            _wpnonce: '<?php echo esc_js(wp_create_nonce('netgsm_save_sms_settings')); ?>',
                                            netgsm_input_smstitle: document.getElementById('netgsm_input_smstitle').value,
                                            netgsm_trChar: document.getElementById('input-trChar').value,
                                            netgsm_iys_control: document.getElementById('input-iysControl').value,
                                            netgsm_status: document.getElementById('input-status').value
                                        }).done(function (res) {
                                            btn.disabled = false;
                                            btn.innerHTML = original;
                                            if (!res || !res.success) {
                                                swal('Hata', (res && res.data && res.data.mesaj) || 'Kaydetme başarısız oldu.', 'error');
                                                return;
                                            }
                                            swal('Kaydedildi', res.data.mesaj || 'SMS ayarları kaydedildi.', 'success');
                                        }).fail(function () {
                                            btn.disabled = false;
                                            btn.innerHTML = original;
                                            swal('Hata', 'Sunucuya ulaşılamadı.', 'error');
                                        });
                                    }

                                    function kontrolEt(selectedElement) {
                                        const netgsm_brandcode_control = <?php echo (int) ($netgsm_brandcode_control ?? 0); ?>;
                                        const netgsm_iys_control = "<?php echo esc_js(trim($netgsm_iys_control)); ?>";
                                        const uyari = document.getElementById("iys_uyari_mesaji");
                                        const selectedValue = selectedElement.value;
                                        if (netgsm_brandcode_control == 0 && (selectedValue == "1" || selectedValue == "2")) {
                                            uyari.innerText = "Marka kodunuz olmadığı için bu mesaj türünü seçemezsiniz. IYS bölümünden brandcode ekleyiniz.";
                                            uyari.style.display = "block";
                                            selectedElement.value = netgsm_iys_control;
                                        } else {
                                            uyari.innerText = "";
                                            uyari.style.display = "none";
                                        }
                                    }
                                </script>
                            </div>
                            <div class="tab-pane container-fluid" id="sms">
                                <div class="vx-page-header">
                                    <div class="vx-page-header-icon"><i class="ti ti-bell"></i></div>
                                    <div class="vx-page-header-text">
                                        <h2 class="vx-page-header-title">WooCommerce SMS</h2>
                                        <p class="vx-page-header-subtitle">Sipariş ve üyelik olaylarında otomatik SMS</p>
                                    </div>
                                </div>
                                <?php
                                // Bu 10 bildirimin çoğu aynı şekle sahip: switch + (opsiyonel telefon alanı) +
                                // mesaj textarea'sı + değişken chip listesi + "Ek Ayarlar" dişli ikonu. Tekrarı
                                // önlemek için ortak bir render fonksiyonu kullanılıyor. Sipariş durumu (id 5)
                                // ve sepette unutulan ürün (id 13) farklı alanlara sahip oldukları için ayrı
                                // yazılıyor. Her alanın name/id/onclick'i orijinaliyle birebir aynı — chip
                                // doldurma (field1..field23 + varfill), toggle (netgsm_field_onoff) ve "Ek
                                // Ayarlar" modalı (settingOpen) JS mekanizmalarının hiçbiri değişmedi.
                                function ngsm_render_wc_toggle($n, $icon, $label, $desc, $controlKey, $phoneName, $textName, $jsonName, $conf, $placeholder)
                                {
                                    $checked = (get_option($controlKey) == 1);
                                    $hasJson = (esc_textarea(get_option($jsonName)) != '');
                                ?>
                                    <div class="vx-wc-item">
                                        <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                                            <div class="vx-toggle-row-left">
                                                <i class="ti ti-<?= esc_attr($icon) ?>"></i>
                                                <div>
                                                    <p class="vx-toggle-row-label"><?= esc_html($label) ?></p>
                                                    <p class="vx-toggle-row-desc"><?= esc_html($desc) ?></p>
                                                </div>
                                            </div>
                                            <label class="vx-switch">
                                                <input type="checkbox" name="<?= esc_attr($controlKey) ?>" id="netgsm_switch<?= esc_attr($n) ?>" onchange="netgsm_field_onoff(<?= (int) $n ?>)" value="1" <?php checked($checked); ?>>
                                                <span class="vx-switch-track"></span>
                                            </label>
                                        </div>
                                        <div class="vx-wc-reveal" id="netgsm_field<?= esc_attr($n) ?>" style="<?php echo $checked ? '' : 'display:none;'; ?>">
                                            <?php if ($phoneName) : ?>
                                                <input name="<?= esc_attr($phoneName) ?>" id="<?= esc_attr($phoneName) ?>" type="text" class="vx-input" placeholder="Sms gönderilecek numaraları giriniz. Örn: 05xxXXXxxXX,05xxXXXxxXX" value="<?= esc_attr(get_option($phoneName)) ?>">
                                            <?php endif; ?>
                                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                                <textarea name="<?= esc_attr($textName) ?>" id="netgsm_textarea<?= esc_attr($n) ?>" rows="2" class="vx-textarea" style="flex:1;" placeholder="<?= esc_attr($placeholder) ?>"><?= esc_textarea(get_option($textName)) ?></textarea>
                                                <button type="button" class="vx-gear-btn" onclick="settingOpen('<?= esc_attr($conf) ?>')" title="Ek Ayarlar">
                                                    <i class="ti ti-adjustments-horizontal" id="<?= esc_attr($conf) ?>_color" style="color:<?php echo $hasJson ? '#17A2B8' : 'var(--text-disabled)'; ?>;"></i>
                                                </button>
                                                <input type="hidden" id="<?= esc_attr($jsonName) ?>" name="<?= esc_attr($jsonName) ?>" value="<?= esc_attr(get_option($jsonName)) ?>">
                                            </div>
                                            <p id="netgsm_tags_text<?= esc_attr($n) ?>" class="vx-chip-list">Kullanabileceğiniz değişkenler:</p>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                <div class="vx-screen">
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header">
                                            <i class="ti ti-bell vx-sec-icon"></i>
                                            <h3>Otomatik SMS Bildirimleri</h3>
                                        </div>
                                        <p class="vx-sec-desc">Sipariş ve üyelik olaylarında hangi durumlarda otomatik SMS gönderileceğini seçin.</p>
                                        <hr class="vx-sec-rule">
                                        <?php
                                        ngsm_render_wc_toggle(1, 'user-plus', 'Yeni üye olunca belirlenen numaralara SMS', 'Yeni üye kaydında belirlediğiniz numaralara bildirim gider.', 'netgsm_newuser_to_admin_control', 'netgsm_newuser_to_admin_no', 'netgsm_newuser_to_admin_text', 'netgsm_newuser_to_admin_json', 'conf1', 'Örnek : Sayın yetkili, [uye_adi] [uye_soyadi] kullanıcı sisteme kaydoldu. Bilgileri : tel : [uye_telefonu] eposta: [uye_epostasi]');
                                        ngsm_render_wc_toggle(2, 'user-check', 'Yeni üye olunca müşteriye SMS', 'Kaydolan müşteriye hoş geldin mesajı gönderilir.', 'netgsm_newuser_to_customer_control', null, 'netgsm_newuser_to_customer_text', 'netgsm_newuser_to_customer_json', 'conf2', "Örnek :Sayın [uye_adi] [uye_soyadi], sitemize hoşgeldiniz! [uye_telefonu] telefon numarası ve [uye_epostasi] ile kayıt oldunuz. Keyifli Alışverişler !");
                                        ?>

                                <script>
                                    var settings = {
                                        'conf1': {
                                            'name': 'netgsm_newuser_to_admin_json',
                                            'settings': {
                                                'source': 'no',
                                                'timecondition': 'yes',
                                                'otherAction': 'no'
                                            }
                                        },
                                        'conf2': {
                                            'name': 'netgsm_newuser_to_customer_json',
                                            'settings': {
                                                'source': 'no',
                                                'timecondition': 'yes',
                                                'otherAction': 'no'
                                            }
                                        },
                                        'conf3': {
                                            'name': 'netgsm_neworder_to_admin_json',
                                            'settings': {
                                                'source': 'no',
                                                'timecondition': 'yes',
                                                'otherAction': 'yes',
                                                'addOrderAdminPanel': 'yes'
                                            }
                                        },
                                        'conf4': {
                                            'name': 'netgsm_neworder_to_customer_json',
                                            'settings': {
                                                'source': 'yes',
                                                'timecondition': 'yes',
                                                'otherAction': 'yes',
                                                'addOrderAdminPanel': 'yes'
                                            }
                                        },
                                        'conf5': {
                                            'name': 'netgsm_order_refund_to_admin_json',
                                            'settings': {
                                                'source': 'no',
                                                'timecondition': 'yes',
                                                'otherAction': 'no'
                                            }
                                        },
                                        'conf6': {
                                            'name': 'netgsm_product_waitlist1_json',
                                            'settings': {
                                                'source': 'yes',
                                                'timecondition': 'yes',
                                                'otherAction': 'no'
                                            }
                                        },
                                        'conf11': {
                                            'name': 'netgsm_newnote1_to_customer_json',
                                            'settings': {
                                                'source': 'yes',
                                                'timecondition': 'yes',
                                                'otherAction': 'no'
                                            }
                                        },
                                        'conf12': {
                                            'name': 'netgsm_newnote2_to_customer_json',
                                            'settings': {
                                                'source': 'yes',
                                                'timecondition': 'yes',
                                                'otherAction': 'no'
                                            }
                                        },
                                        'conf13': {
                                            'name': 'netgsm_abandoned_cart_to_admin_json',
                                            'settings': {
                                                'source': 'yes',
                                                'timecondition': 'yes',
                                                'otherAction': 'no'
                                            }
                                        },
                                        <?php if (function_exists('wc_get_order_statuses')) {
                                            $order_statuses = wc_get_order_statuses();
                                            $arraykeys = array_keys($order_statuses);
                                            foreach ($arraykeys as $item) { ?> '<?= esc_html($item) ?>': {
                                                    'name': 'netgsm_order_status_text_<?= esc_html($item) ?>_json',
                                                    'settings': {
                                                        'source': 'yes',
                                                        'timecondition': 'yes',
                                                        'otherAction': 'no'
                                                    }
                                                },
                                        <?php }
                                        } ?>
                                    };

                                    var settings2 = {
                                        'source2': {
                                            'type': 'checkbox',
                                            'ids': [
                                                '_source_billing_phone',
                                                '_source_address_phone'
                                            ]
                                        },
                                        'source': {
                                            'type': 'text',
                                            'ids': [
                                                '_custom_phone_key'
                                            ]
                                        },
                                        'timecondition': {
                                            'type': 'text',
                                            'ids': [
                                                '_timecondition'
                                            ]
                                        },
                                        'otherAction': {
                                            'type': 'text',
                                            'ids': [
                                                '_otherAction'
                                            ]
                                        },
                                        'addOrderAdminPanel': {
                                            'type': 'checkbox',
                                            'ids': [
                                                '_addOrderAdminPanel'
                                            ]
                                        }
                                    };

                                    function settingOpen(conf) {
                                        var settingHtml = {
                                            'source2': '<hr><div class="col-md-12"><div class="col-md-3"><label for=""><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="SMSin hangi telefon numarası kaynaklarına gönderileceğini belirleyebilirsiniz."></i> Gönderilecek Kaynak: </label></div><div class="col-md-7"><input type="checkbox" name="" id="' + conf + settings2["source"].ids[0] + '" class="checkbox-fix" value="off"> Fatura telefon numarasına gönder<br><input type="checkbox" name="" id="' + conf + settings2["source"].ids[1] + '" class="checkbox-fix" value="off"> Adres telefon numarasına gönder</div></div>',
                                            'source': '<div class="col-md-12"><div class="col-md-3"><label for=""><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Gönderilmesini istediğiniz özel bir telefon anahtarı varsa bu anahtardaki telefon numarasına SMS gönderebilirsiniz. Örn: billing_phone, shipping_phone, musteri_tel vs. "></i> SMS gönderilecek telefon numarası anahtarı: </label></div><div class="col-md-8"><input type="text" name="" id="' + conf + settings2["source"].ids[0] + '"  class="form-control" placeholder="Gönderilmesini istediğiniz özel bir telefon anahtarı varsa bu anahtardaki telefon numarasına SMS gönderebilirsiniz. Örn: billing_phone, shipping_phone, musteri_tel vs. " value="billing_phone"></div></div>',
                                            'timecondition': '<hr><div class="col-md-12"><div class="col-md-3"><label for=""><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="SMSin ne kadar zaman sonra gönderileceğini dakika cinsinden belirleyebilirsiniz. Zaman ayarlarınızı kontrol edin. Bu sayfa yüklendiğinde saat : <?= esc_html(date('H:i:s', current_time('timestamp'))) ?>"></i> Zamanla: </label></div><div class="col-md-8"><input type="number" name="" id="' + conf + settings2["timecondition"].ids[0] + '"  class="form-control" placeholder="Kaç dakika sonra gönderilsin istiyorsunuz? "></div></div>',
                                            'otherAction': '<hr><div class="col-md-12" style="display: none;"><div class="col-md-3"><label for=""><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Farklı bir actionda çalıştırmak için kanca ismi girin. Gizli özelliktir."></i> Ek kanca girişi: </label></div><div class="col-md-8"><input type="text" name="" id="' + conf + settings2["otherAction"].ids[0] + '"  class="form-control" placeholder="Farklı kancada çalıştırmak için kanca ismi girin"></div></div>',
                                            'addOrderAdminPanel': '<hr><div class="col-md-12"><div class="col-md-3"><label for=""><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="Admin panelden eklenen siparişlerdede SMS gönderilmesini seçebilirsiniz."></i> Admin panelden eklenen siparişte de gönder: </label></div><div class="col-md-7"><input type="checkbox" name="" id="' + conf + settings2["addOrderAdminPanel"].ids[0] + '" class="checkbox-fix" value="off"> SMS Gönder</div></div>',
                                        };

                                        if (settings[conf]) {
                                            var setting = settings[conf];
                                            // var data = JSON.parse(jQuery('#'+setting.name).val());
                                            modalCleaner();
                                            jQuery('#modal-save').attr('conf', conf);

                                            Object.keys(settings2).forEach(function(item) {
                                                if (setting.settings[item] == 'yes') { //Gönderilecek kaynak ayarı
                                                    jQuery('#row_' + item).html(settingHtml[item]);
                                                }
                                            });

                                            try {
                                                var data = JSON.parse(jQuery('#' + settings[conf].name).val());
                                            } catch (e) {
                                                var data = [];
                                            }

                                            Object.keys(settings2).forEach(function(item) {
                                                var types = settings2[item];
                                                Object.keys(types.ids).forEach(function(ids) {
                                                    var id = types.ids[ids];
                                                    if (types.type == 'checkbox') {
                                                        if (data[id] == true) {
                                                            jQuery('#' + conf + id).prop("checked", true);
                                                        } else {
                                                            jQuery('#' + conf + id).prop("checked", false);
                                                        }
                                                    } else {
                                                        if (data[id] != undefined && data[id] != '') {
                                                            jQuery('#' + conf + id).val(data[id]);
                                                        }
                                                    }
                                                });
                                            });
                                        }
                                        jQuery('#settingModal').modal('show');
                                        jQuery('[data-toggle="tooltip"]').tooltip();
                                    }

                                    function modalCleaner() {
                                        Object.keys(settings2).forEach(function(item) {
                                            jQuery('#row_' + item).html('');
                                        });
                                    }
                                </script>

                                <div class="modal inmodal fade" id="settingModal" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top: 20px">
                                    <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                                                </button>
                                                <h4 class="modal-title">
                                                    <span id="modalTitle"><i class="fa fa-cogs"></i> Ek Ayarlar</span>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row" id="row_source">
                                                </div>
                                                <div class="row" id="row_custom_phone_key">
                                                </div>
                                                <div class="row" id="row_timecondition">
                                                </div>
                                                <div class="row" id="row_addOrderAdminPanel">
                                                </div>
                                                <div class="row" id="row_otherAction">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-white" data-dismiss="modal" id="modal-close">Kapat
                                                </button>
                                                <button type="button" class="btn btn-primary" id="modal-save" conf="">Kaydet
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    jQuery('#modal-save').click(function() {
                                        var conf = jQuery(this).attr('conf');
                                        var data = {};
                                        Object.keys(settings2).forEach(function(item) {
                                            var types = settings2[item];
                                            if (settings[conf].settings[item] == 'yes') {
                                                Object.keys(types.ids).forEach(function(ids) {
                                                    var id = types.ids[ids];
                                                    if (types.type == 'checkbox') {
                                                        data[id] = jQuery('#' + conf + id).is(':checked');
                                                    } else {
                                                        data[id] = jQuery('#' + conf + id).val();
                                                    }
                                                });
                                            }
                                        });

                                        if (JSON.stringify(data) != '') {
                                            jQuery('#' + conf + '_color').css('color', '#17A2B8')
                                        }

                                        jQuery('#' + settings[conf].name).val(JSON.stringify(data));
                                        jQuery('#settingModal').modal('hide');
                                    })
                                </script>


                                <?php
                                ngsm_render_wc_toggle(3, 'shopping-cart', 'Yeni sipariş geldiğinde belirlenen numaralara SMS', 'Yeni sipariş bildirimi ekibinize iletilir.', 'netgsm_neworder_to_admin_control', 'netgsm_neworder_to_admin_no', 'netgsm_neworder_to_admin_text', 'netgsm_neworder_to_admin_json', 'conf3', "Örnek : Sayın Yönetici, [siparis_no] no'lu bir sipariş aldınız. Ürün bilgileri : [urun_adlari]-[urun_kodlari]-[urun_adetleri]");
                                ngsm_render_wc_toggle(4, 'receipt', "Yeni sipariş geldiğinde müşteriye bilgilendirme SMS'i", "Müşteriye 'siparişiniz alındı' bilgisi gönderilir.", 'netgsm_neworder_to_customer_control', null, 'netgsm_neworder_to_customer_text', 'netgsm_neworder_to_customer_json', 'conf4', "Örnek : [siparis_no]' nolu siparişiniz başarıyla oluşturulmuştur.");
                                ?>



                                <?php
                                if (function_exists('wc_get_order_statuses')) {
                                    $order_statuses = wc_get_order_statuses();

                                    $actives = [];
                                    foreach ($order_statuses as $key => $order_status) {
                                        if (esc_textarea(get_option('netgsm_order_status_text_' . $key)) != '') {
                                            array_push($actives, $order_status);
                                        }
                                    }
                                }
                                ?>

                                <div class="vx-wc-item">
                                    <?php $ngsm_os5_checked = (get_option('netgsm_orderstatus_change_customer_control') == 1); ?>
                                    <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                                        <div class="vx-toggle-row-left">
                                            <i class="ti ti-refresh"></i>
                                            <div>
                                                <p class="vx-toggle-row-label">Sipariş durumu değiştiğinde müşteriye SMS</p>
                                                <p class="vx-toggle-row-desc">Durum her değiştiğinde müşteri otomatik bilgilendirilir.</p>
                                            </div>
                                        </div>
                                        <label class="vx-switch">
                                            <input name="netgsm_orderstatus_change_customer_control" id="netgsm_switch5" type="checkbox" onchange="netgsm_field_onoff(5)" value="1" <?php checked($ngsm_os5_checked); ?>>
                                            <span class="vx-switch-track"></span>
                                        </label>
                                    </div>
                                    <div class="vx-wc-reveal" id="netgsm_field5" style="<?php echo $ngsm_os5_checked ? '' : 'display:none;'; ?>">
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <?php if (isset($actives[0])) : ?>
                                                <span data-toggle="tooltip" data-placement="right" data-html="true" title="<b style='color: #2ECC71'>Aktif durumlar : </b><hr> <?= esc_attr(implode('<br>', $actives)) ?>" style="color:var(--color-palette-success-main);font-size:18px;display:inline-flex;">
                                                    <i class="ti ti-square-check"></i>
                                                </span>
                                            <?php else : ?>
                                                <span data-toggle="tooltip" data-placement="right" data-html="true" title="Hiçbir durum aktifleştirilmemiş." style="color:var(--color-palette-error-main);font-size:18px;display:inline-flex;">
                                                    <i class="ti ti-circle-x"></i>
                                                </span>
                                            <?php endif; ?>
                                            <select id="order_status" onchange="order_status_change(this.value)" class="vx-select" style="max-width:320px;">
                                                <option value="" selected>Sipariş Durumu Seçiniz</option>
                                                <?php if (function_exists('wc_get_order_statuses')) {
                                                    $order_statuses = wc_get_order_statuses();
                                                    $arraykeys = array_keys($order_statuses);
                                                    foreach ($arraykeys as $item) { ?>
                                                        <option value="<?= esc_attr($item) ?>"><?= esc_html($order_statuses[$item]) ?></option>
                                                <?php }
                                                } ?>
                                            </select>
                                            <button type="button" class="vx-gear-btn" id="settings-btn-changed" onclick="" title="Ek Ayarlar">
                                                <i class="ti ti-adjustments-horizontal" id="setting-btn_color" style="color:var(--text-disabled);"></i>
                                            </button>
                                        </div>
                                        <span id="activeStatus" data=""></span>
                                        <?php if (isset($arraykeys)) {
                                            foreach ($arraykeys as $item) {
                                                $order_status_text = array($item => 'netgsm_order_status_text_' . $item);
                                        ?>
                                                <textarea style="display: none;" name="netgsm_order_status_text_<?= esc_attr($item) ?>" id="netgsm_order_status_text_<?= esc_attr($item) ?>" class="vx-textarea order_status_text" rows="2" placeholder="Örnek : Sayın [uye_adi] [uye_soyadi], [siparis_no] numaralı siparişinizin kargo durumu ... olarak değiştirilmiştir."><?= esc_textarea(get_option($order_status_text[$item])); ?></textarea>
                                                <input type="hidden" id="netgsm_order_status_text_<?= esc_attr($item) ?>_json" name="netgsm_order_status_text_<?= esc_attr($item) ?>_json" value="<?= esc_attr(get_option("netgsm_order_status_text_" . $item . "_json")) ?>">
                                        <?php }
                                        } ?>
                                        <p id="netgsm_tags_text5" class="vx-chip-list">Kullanabileceğiniz değişkenler:
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'siparis_no')">[siparis_no]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_adi')">[uye_adi]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_soyadi')">[uye_soyadi]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_telefonu')">[uye_telefonu]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_epostasi')">[uye_epostasi]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'kullanici_adi')">[kullanici_adi]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'tarih')">[tarih]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'saat')">[saat]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'kargo_firmasi')">[kargo_firmasi]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'takip_kodu')">[takip_kodu]</mark>
                                            <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'siparis_tutar')">[siparis_tutar]</mark>
                                        </p>
                                    </div>
                                </div>


                                <?php
                                ngsm_render_wc_toggle(11, 'note', 'Siparişe özel not eklendiğinde müşteriye SMS', 'Eklenen özel not müşteriye SMS ile iletilir.', 'netgsm_newnote1_to_customer_control', null, 'netgsm_newnote1_to_customer_text', 'netgsm_newnote1_to_customer_json', 'conf11', "Örnek : [siparis_no]' nolu siparişinize yeni not eklendi : [not]");
                                ngsm_render_wc_toggle(12, 'message-2', 'Siparişe müşteri notu eklendiğinde müşteriye SMS', 'Müşteri notu eklendiğinde müşteriye bildirim gider.', 'netgsm_newnote2_to_customer_control', null, 'netgsm_newnote2_to_customer_text', 'netgsm_newnote2_to_customer_json', 'conf12', "Örnek : [siparis_no]' nolu siparişinize yeni not eklendi : [not]");
                                ?>

                                <?php
                                ngsm_render_wc_toggle(6, 'ban', 'Sipariş iptal edildiğinde bilgilendir', 'İptalde belirlediğiniz numaraya SMS gönderilir.', 'netgsm_order_refund_to_admin_control', 'netgsm_order_refund_to_admin_no', 'netgsm_order_refund_to_admin_text', 'netgsm_order_refund_to_admin_json', 'conf5', "Sayın yönetici, [uye_adi][uye_soyadi] kullanıcısı, [urun] ürününü '[iade_nedeni]' nedeninden dolayı iptal etmiştir.");
                                ngsm_render_wc_toggle(8, 'package', 'Ürün stoğa girince bekleme listesine SMS (WC Waitlist)', 'Stoğa giren ürün için bekleyenler bilgilendirilir.', 'netgsm_product_waitlist1_control', null, 'netgsm_product_waitlist1_text', 'netgsm_product_waitlist1_json', 'conf6', 'Sayın [uye_adi][uye_soyadi], [urun_adi] ürünü tekrar stoğa girmiştir. Bilginize.');
                                ?>

                                <?php $ngsm_ac13_checked = (get_option('netgsm_abandoned_card_sms_admin_control') == 1); ?>
                                <div class="vx-wc-item">
                                    <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                                        <div class="vx-toggle-row-left">
                                            <i class="ti ti-shopping-cart-off"></i>
                                            <div>
                                                <p class="vx-toggle-row-label">Sepette ürün unutulduğunda müşteriye SMS</p>
                                                <p class="vx-toggle-row-desc">Sepette unutulan ürün için hatırlatma gönderilir.</p>
                                            </div>
                                        </div>
                                        <label class="vx-switch">
                                            <input name="netgsm_abandoned_card_sms_admin_control" id="netgsm_switch13" type="checkbox" onchange="netgsm_field_onoff(13)" value="1" <?php checked($ngsm_ac13_checked); ?>>
                                            <span class="vx-switch-track"></span>
                                        </label>
                                    </div>
                                    <div class="vx-wc-reveal" id="netgsm_field13" style="<?php echo $ngsm_ac13_checked ? '' : 'display:none;'; ?>">
                                        <div class="vx-grid-2">
                                            <div class="vx-field">
                                                <label for="netgsm_abandoned_cart_periyod">Sepette bekleme süresi (saat)</label>
                                                <input name="netgsm_abandoned_cart_periyod" id="netgsm_abandoned_cart_periyod" type="number" class="vx-input" placeholder="Örn: 5 (varsayılan 24 saat)" value="<?= esc_attr(get_option("netgsm_abandoned_cart_periyod")) ?>">
                                            </div>
                                            <div class="vx-field">
                                                <label for="netgsm_abandoned_cart_smslimit">Periyotta gönderilecek toplam SMS limiti</label>
                                                <input name="netgsm_abandoned_cart_smslimit" id="netgsm_abandoned_cart_smslimit" type="number" class="vx-input" placeholder="Örn: 1" value="<?= esc_attr(get_option("netgsm_abandoned_cart_smslimit")) ?>">
                                            </div>
                                        </div>
                                        <textarea name="netgsm_abandoned_cart_to_admin_text" id="netgsm_textarea13" rows="2" class="vx-textarea" placeholder="Merhaba [uye_adi][uye_soyadi] Sepetinizde ürün kaldı! Fırsat bitmeden hemen satın alın. Stoklar hızla tükeniyor!"><?= esc_textarea(get_option("netgsm_abandoned_cart_to_admin_text")) ?></textarea>
                                        <input type="hidden" id="netgsm_abandoned_cart_to_admin_json" name="netgsm_abandoned_cart_to_admin_json" value="<?= esc_attr(get_option("netgsm_abandoned_cart_to_admin_json")) ?>">
                                        <p id="netgsm_tags_text13" class="vx-chip-list">Kullanabileceğiniz değişkenler:</p>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex;justify-content:flex-end;margin-top:22px;">
                                <button type="submit" class="vx-btn vx-btn-primary" id="login_save3" name="login_save3" onclick="login();"><i class="ti ti-device-floppy"></i> Değişiklikleri Kaydet</button>
                            </div>
                            </div>
                            </div>

                            <div class="tab-pane container-fluid" id="tf2sms">
                                <div class="vx-page-header">
                                    <div class="vx-page-header-icon"><i class="ti ti-user-check"></i></div>
                                    <div class="vx-page-header-text">
                                        <h2 class="vx-page-header-title">Üyelik Doğrulama</h2>
                                        <p class="vx-page-header-subtitle">OTP ve 2FA ile güvenli doğrulama</p>
                                    </div>
                                </div>
                                <div class="vx-screen">
                                    <div class="vx-note vx-note-neutral" style="margin-bottom:20px;">
                                        <p style="font-weight:700;color:var(--text-primary);margin:0 0 8px;">Bu özelliği test ettikten sonra kullanmaya başlayın</p>
                                        <ul>
                                            <li>Bu özelliği kullanabilmeniz için OTP SMS paketinizin olması gereklidir. <a href="https://portal.netgsm.com.tr/" target="_blank">portal.netgsm.com.tr</a> adresinden paket satın alabilirsiniz.</li>
                                            <li>Bu özellikler WooCommerce e-ticaret eklentisi yüklü ve etkin olduğunda çalışır. WooCommerce kayıt olma sayfasında geçerlidir.</li>
                                            <li>Kayıtlı telefon numarasını engelleme seçeneğindeki metin uyarı olarak gösterilecektir; SMS gönderimi için değildir.</li>
                                            <li>OTP SMS kuralları için <a href="https://www.netgsm.com.tr/dokuman/#otp-sms" target="_blank">netgsm.com.tr/dokuman/#otp-sms</a> adresini ziyaret edebilirsiniz.</li>
                                            <li>Satır atlamak için <b>\n</b> kullanabilirsiniz.</li>
                                        </ul>
                                    </div>

                                    <?php $ngsm_otp_reg = (get_option('netgsm_tf2_auth_register_control') == 1); ?>
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header vx-sec-header-toggle">
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <i class="ti ti-user-plus vx-sec-icon"></i>
                                                <h3>Yeni üye kaydında OTP SMS ile doğrula</h3>
                                                <span class="vx-tip"><i class="ti ti-info-circle"></i><span class="vx-tip-bubble">OTP SMS paketinden ücretlendirilir. OTP SMS paketiniz olduğuna emin olun.</span></span>
                                            </div>
                                            <label class="vx-switch">
                                                <input name="netgsm_tf2_auth_register_control" id="netgsm_switch9" type="checkbox" onchange="netgsm_field_onoff(9)" value="1" <?php checked($ngsm_otp_reg); ?>>
                                                <span class="vx-switch-track"></span>
                                            </label>
                                        </div>
                                        <p class="vx-sec-desc" style="margin-left:28px;">Yeni üye olurken telefona doğrulama kodu gönderilir.</p>
                                        <div id="netgsm_field9" class="vx-sec-reveal" style="<?php echo $ngsm_otp_reg ? '' : 'display:none;'; ?>">
                                            <div class="vx-field">
                                                <label for="netgsm_textarea9">Mesaj şablonu</label>
                                                <textarea name="netgsm_tf2_auth_register_text" id="netgsm_textarea9" rows="2" class="vx-textarea" maxlength="140" placeholder="Tek seferlik doğrulama kodunuz : [kod]&#10;*OTP SMS tek boy gönderilebilir.&#10;*Metin taslağı 140 karakter ile sınırlandırılmıştır."><?= esc_textarea(get_option("netgsm_tf2_auth_register_text")) ?></textarea>
                                            </div>
                                            <p class="vx-chip-caption">Değişken eklemek için tıklayın:</p>
                                            <p id="netgsm_tags_text9" class="vx-chip-list"></p>
                                            <div class="vx-field" style="max-width:320px;margin-top:8px;">
                                                <label for="netgsm_reg_diff">Kod geçerlilik süresi (sn)</label>
                                                <input type="number" name="netgsm_tf2_auth_register_diff" id="netgsm_reg_diff" class="vx-input" placeholder="örn: 120" value="<?= esc_attr(get_option("netgsm_tf2_auth_register_diff")) ?>">
                                                <span class="vx-help">Bu süre boyunca aynı numaraya tekrar kod gönderilmez. (varsayılan 180sn.)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php $ngsm_otp_phoneonly = (get_option('netgsm_tf2_name_optional') == 1); ?>
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header vx-sec-header-toggle">
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <i class="ti ti-phone vx-sec-icon"></i>
                                                <h3>OTP doğrulamada sadece telefon numarası iste</h3>
                                                <span class="vx-tip"><i class="ti ti-info-circle"></i><span class="vx-tip-bubble">Açıldığında kod gönderebilmek için yalnızca telefon numarası yeterli olur; ad, soyad ve e-posta zorunlu tutulmaz.</span></span>
                                            </div>
                                            <label class="vx-switch">
                                                <input name="netgsm_tf2_name_optional" id="netgsm_switch23" type="checkbox" value="1" <?php checked($ngsm_otp_phoneonly); ?>>
                                                <span class="vx-switch-track"></span>
                                            </label>
                                        </div>
                                        <p class="vx-sec-desc" style="margin-left:28px;">OTP doğrulamada ad/soyad zorunlu tutulmaz.</p>
                                        <p class="vx-help" style="margin:12px 0 0;">Bu seçenek açıkken müşteri OTP kodunu almak için yalnızca telefon numarasını girer; ad, soyad ve e-posta alanları zorunlu tutulmaz. Doğrulama yine telefona gelen kod ile yapılır. SMS metninde [ad]/[soyad] değişkenleri kullanıyorsanız bu alanlar boş görünebilir.</p>
                                    </div>

                                    <?php $ngsm_otp_cod = (get_option('netgsm_tf2_cash_on_delivery_control') == 1); ?>
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header vx-sec-header-toggle">
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <i class="ti ti-cash vx-sec-icon"></i>
                                                <h3>Kapıda ödemede OTP SMS ile doğrula</h3>
                                                <span class="vx-tip"><i class="ti ti-info-circle"></i><span class="vx-tip-bubble">OTP SMS paketinden ücretlendirilir. OTP SMS paketiniz olduğuna emin olun.</span></span>
                                            </div>
                                            <label class="vx-switch">
                                                <input name="netgsm_tf2_cash_on_delivery_control" id="netgsm_switch22" type="checkbox" onchange="netgsm_field_onoff(22)" value="1" <?php checked($ngsm_otp_cod); ?>>
                                                <span class="vx-switch-track"></span>
                                            </label>
                                        </div>
                                        <p class="vx-sec-desc" style="margin-left:28px;">Kapıda ödeme işleminde OTP SMS ile doğrulama yapılır.</p>
                                        <div id="netgsm_field22" class="vx-sec-reveal" style="<?php echo $ngsm_otp_cod ? '' : 'display:none;'; ?>">
                                            <div class="vx-field">
                                                <label for="netgsm_textarea22">Mesaj şablonu</label>
                                                <textarea name="netgsm_tf2_cash_on_delivery_text" id="netgsm_textarea22" rows="2" class="vx-textarea" maxlength="140" placeholder="Tek seferlik doğrulama kodunuz : [kod]&#10;*OTP SMS tek boy gönderilebilir.&#10;*Metin taslağı 140 karakter ile sınırlandırılmıştır."><?= esc_textarea(get_option("netgsm_tf2_cash_on_delivery_text")) ?></textarea>
                                            </div>
                                            <p class="vx-chip-caption">Değişken eklemek için tıklayın:</p>
                                            <p id="netgsm_tags_text22" class="vx-chip-list"></p>
                                            <div class="vx-field" style="max-width:320px;margin-top:8px;">
                                                <label for="netgsm_cod_diff">Kod geçerlilik süresi (sn)</label>
                                                <input type="number" name="netgsm_tf2_cash_on_delivery_diff" id="netgsm_cod_diff" class="vx-input" placeholder="örn: 120" value="<?= esc_attr(get_option("netgsm_tf2_cash_on_delivery_diff")) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <?php $ngsm_otp_block = (get_option('netgsm_tf2_auth_register_phone_control') == 1); ?>
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header vx-sec-header-toggle">
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <i class="ti ti-user-x vx-sec-icon"></i>
                                                <h3>Kayıtlı telefon numarası ile yeni üyeliği engelle</h3>
                                            </div>
                                            <label class="vx-switch">
                                                <input name="netgsm_tf2_auth_register_phone_control" id="netgsm_switch10" type="checkbox" onchange="netgsm_field_onoff(10)" value="1" <?php checked($ngsm_otp_block); ?>>
                                                <span class="vx-switch-track"></span>
                                            </label>
                                        </div>
                                        <p class="vx-sec-desc" style="margin-left:28px;">Aynı telefon numarasıyla ikinci üyelik açılmasını önler.</p>
                                        <div id="netgsm_field10" class="vx-sec-reveal" style="<?php echo $ngsm_otp_block ? '' : 'display:none;'; ?>">
                                            <div class="vx-field">
                                                <label for="netgsm_textarea10">Uyarı metni</label>
                                                <textarea name="netgsm_tf2_auth_register_phone_warning_text" id="netgsm_textarea10" rows="2" class="vx-textarea" placeholder="[telefon_no] numarası ile zaten üyeliğiniz mevcut."><?= esc_textarea(get_option("netgsm_tf2_auth_register_phone_warning_text")) ?></textarea>
                                            </div>
                                            <p class="vx-chip-caption">Uyarı metnidir, bu seçenekte SMS gönderilmez. Değişken eklemek için tıklayın:</p>
                                            <p id="netgsm_tags_text10" class="vx-chip-list"></p>
                                        </div>
                                    </div>

                                    <?php $ngsm_otp_fa2 = (get_option('netgsm_login_otp_control') == 1); ?>
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header vx-sec-header-toggle">
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <i class="ti ti-shield-lock vx-sec-icon"></i>
                                                <h3>Girişte OTP SMS ile 2FA doğrulaması</h3>
                                                <span class="vx-tip"><i class="ti ti-info-circle"></i><span class="vx-tip-bubble">OTP SMS paketinden ücretlendirilir. Sadece "customer" rolündeki kullanıcılar için geçerlidir, site yöneticileri etkilenmez.</span></span>
                                            </div>
                                            <label class="vx-switch">
                                                <input name="netgsm_login_otp_control" id="netgsm_switch24" type="checkbox" onchange="netgsm_field_onoff(24)" value="1" <?php checked($ngsm_otp_fa2); ?>>
                                                <span class="vx-switch-track"></span>
                                            </label>
                                        </div>
                                        <p class="vx-sec-desc" style="margin-left:28px;">Müşteri girişinde (login) ek güvenlik için OTP SMS istenir.</p>
                                        <div id="netgsm_field24" class="vx-sec-reveal" style="<?php echo $ngsm_otp_fa2 ? '' : 'display:none;'; ?>">
                                            <div class="vx-field">
                                                <label for="netgsm_textarea24">Mesaj şablonu</label>
                                                <textarea name="netgsm_login_otp_text" id="netgsm_textarea24" rows="2" class="vx-textarea" maxlength="140" placeholder="Giriş doğrulama kodunuz : [kod]"><?= esc_textarea(get_option("netgsm_login_otp_text")) ?></textarea>
                                            </div>
                                            <p id="netgsm_tags_text24" class="vx-help">Kullanabileceğiniz değişkenler: [kod] [telefon_no] [ad] [soyad] [mail] [referans_no]</p>
                                            <div class="vx-field" style="max-width:320px;margin-top:8px;">
                                                <label for="netgsm_fa2_diff">Kod geçerlilik süresi (sn)</label>
                                                <input type="number" name="netgsm_login_otp_diff" id="netgsm_fa2_diff" class="vx-input" placeholder="örn: 180" value="<?= esc_attr(get_option("netgsm_login_otp_diff")) ?>">
                                                <span class="vx-help">Müşterinin telefonuna kayıtlı numara (fatura telefonu) yoksa veya SMS gönderilemezse, güvenlik amacıyla giriş normal şekilde (2FA'sız) tamamlanır — kimse hesabından kilitlenmez.</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                                        <button type="submit" class="vx-btn vx-btn-primary" id="login_save4" name="login_save4" onclick="login();"><i class="ti ti-device-floppy"></i> Değişiklikleri Kaydet</button>
                                    </div>
                                </div>
                            </div>
                            <?php include 'contactform7.php' ?>
                            <?php include 'iys.php' ?>
                            <div class="tab-pane container-fluid" id="privatesms">
                                <div class="vx-page-header">
                                    <div class="vx-page-header-icon"><i class="ti ti-send"></i></div>
                                    <div class="vx-page-header-text">
                                        <h2 class="vx-page-header-title">Özel SMS</h2>
                                        <p class="vx-page-header-subtitle">Belirli numaralara tek seferlik gönderim</p>
                                    </div>
                                </div>
                                <div class="vx-screen">
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header">
                                            <i class="ti ti-send vx-sec-icon"></i>
                                            <h3>Tek seferlik SMS gönder</h3>
                                        </div>
                                        <p class="vx-sec-desc">Belirli numaralara anlık mesaj gönderin.</p>
                                        <hr class="vx-sec-rule">
                                        <div class="vx-grid-2" style="margin-top:16px;">
                                            <div class="vx-field">
                                                <label for="private_phone">Telefon No</label>
                                                <input type="text" name="private_phone" id="private_phone" class="vx-input" placeholder="Birden fazla numara için virgül (,) kullanın">
                                            </div>
                                            <div class="vx-field">
                                                <label for="netgsm_content_type">Mesaj İçerik Türü</label>
                                                <select name="netgsm_content_type" id="netgsm_content_type" class="vx-select">
                                                    <option value="">Mesaj içerik türü seçiniz</option>
                                                    <option value="11">Kampanya, tanıtım, kutlama vb. (İYS'ye bireysel kayıtlı alıcılarınıza gönderilir.)</option>
                                                    <option value="12">Kampanya, tanıtım, kutlama vb. (İYS'ye tacir kayıtlı alıcılarınıza gönderilir.)</option>
                                                    <option value="0">Bilgilendirme, kargo, şifre vb. (İYS'den sorgulanmaz.)</option>
                                                </select>
                                                <span class="vx-help">Hesabınıza tanımlı marka kodunuz yoksa Bilgilendirme/kargo/şifre türü seçilmelidir.</span>
                                            </div>
                                        </div>
                                        <div class="vx-field" style="margin-top:18px;">
                                            <label for="private_text">Mesaj</label>
                                            <textarea name="private_text" id="private_text" rows="4" class="vx-textarea" placeholder="Göndermek istediğiniz mesaj içeriğini girin."></textarea>
                                        </div>
                                        <div style="display:flex;justify-content:flex-end;margin-top:22px;">
                                            <button type="button" class="vx-btn vx-btn-primary" onclick="privatesmsSend()" name="sendSMS" id="sendSMS"><i class="ti ti-send"></i> SMS Gönder</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane container-fluid" id="bulksms">
                                <div class="vx-page-header">
                                    <div class="vx-page-header-icon"><i class="ti ti-messages"></i></div>
                                    <div class="vx-page-header-text">
                                        <h2 class="vx-page-header-title">Toplu SMS</h2>
                                        <p class="vx-page-header-subtitle">Kayıtlı kullanıcılara toplu gönderim</p>
                                    </div>
                                </div>
                                <div class="vx-screen">
                                    <?php
                                    $netgsm_contact_meta_key = 'billing_phone';
                                    if (esc_html(get_option("netgsm_contact_meta_key")) != '') {
                                        $netgsm_contact_meta_key = esc_html(get_option("netgsm_contact_meta_key"));
                                    }
                                    ?>
                                    <div class="vx-secbox">
                                        <div class="vx-sec-header">
                                            <i class="ti ti-settings vx-sec-icon"></i>
                                            <h3>Telefon alanı ayarı</h3>
                                        </div>
                                        <p class="vx-sec-desc">Toplu gönderimde numaraların okunacağı meta anahtarını belirleyin.</p>
                                        <hr class="vx-sec-rule">
                                        <div style="display:flex;align-items:flex-end;gap:12px;margin-top:16px;flex-wrap:wrap;">
                                            <div class="vx-field" style="max-width:320px;">
                                                <label for="netgsm_contact_meta_key">Telefon Meta Anahtarı</label>
                                                <input type="text" value="<?= esc_attr($netgsm_contact_meta_key) ?>" class="vx-input vx-input-sm" name="netgsm_contact_meta_key" id="netgsm_contact_meta_key" placeholder="Telefon meta anahtarı">
                                                <span class="vx-help">Varsayılan WooCommerce telefon anahtarı billing_phone kullanılır.</span>
                                            </div>
                                            <button type="button" class="vx-btn vx-btn-primary" id="netgsm_meta_key_save" onclick="saveContactMetaKeyAjax();"><i class="ti ti-device-floppy"></i> Kaydet</button>
                                        </div>
                                    </div>

                                    <div class="vx-secbox" style="margin-top:16px;">
                                        <div class="vx-sec-header" style="justify-content:space-between;">
                                            <div>
                                                <div style="display:flex;align-items:center;gap:9px;">
                                                    <i class="ti ti-users vx-sec-icon"></i>
                                                    <h3>Kullanıcılar</h3>
                                                </div>
                                                <p class="vx-sec-desc">Listeden seçtiğiniz kayıtlı kullanıcılara toplu SMS gönderin.</p>
                                            </div>
                                            <button type="button" class="vx-btn vx-btn-primary" onclick="netgsm_sendSMS_bulkTab('')"><i class="ti ti-send"></i> SMS Gönder</button>
                                        </div>
                                        <?php
                                        // Kullanıcı listesi, tüm kullanıcıları belleğe/HTML'e basmak yerine
                                        // sunucu taraflı sayfalama (AJAX) ile parça parça yüklenir.
                                        // Bkz: wp_ajax_netgsm_users_datatable (index.php)
                                        ?>
                                        <div class="vx-bstable-wrap" style="margin-top:16px;">
                                            <table
                                                id="table" name="table"
                                                class="table table-bordered table-striped dataTable no-footer"
                                                data-toggle="table"
                                                data-pagination="true"
                                                data-side-pagination="server"
                                                data-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                                                data-method="post"
                                                <?php // bootstrap-table varsayilan olarak JSON govde gonderir; PHP JSON govdeyi
                                                      // $_POST'a koymadigi icin admin-ajax.php "action" parametresini goremeyip
                                                      // 400 donuyordu. Form-encoded gonderim bunu cozer. ?>
                                                data-content-type="application/x-www-form-urlencoded"
                                                data-query-params="netgsmUsersQueryParams"
                                                data-unique-id="userid"
                                                data-maintain-meta-data="true"
                                                data-search="true" data-search-align="left"
                                                data-pagination-v-align="bottom"
                                                data-click-to-select="true"
                                                data-page-size="25"
                                                data-page-list="[10, 25, 50, 100, 150, 200]">
                                                <thead>
                                                    <tr>
                                                        <th data-checkbox="true"></th>
                                                        <th data-field="userid" data-visible="false">userid</th>
                                                        <th data-field="username" data-formatter="netgsmUserUsername">Kullanıcı adı</th>
                                                        <th data-field="name">İsim</th>
                                                        <th data-field="email" data-formatter="netgsmUserEmail">E-posta</th>
                                                        <th data-field="phone" data-formatter="netgsmUserPhone" class="manage-column column-phone">Telefon</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    function saveContactMetaKeyAjax() {
                                        var btn = document.getElementById('netgsm_meta_key_save');
                                        var original = btn.innerHTML;
                                        btn.disabled = true;
                                        btn.innerHTML = 'Kaydediliyor...';
                                        jQuery.post(ajaxurl, {
                                            action: 'netgsm_save_contact_meta_key',
                                            _wpnonce: '<?php echo esc_js(wp_create_nonce('netgsm_save_contact_meta_key')); ?>',
                                            netgsm_contact_meta_key: document.getElementById('netgsm_contact_meta_key').value
                                        }).done(function (res) {
                                            btn.disabled = false;
                                            btn.innerHTML = original;
                                            if (!res || !res.success) {
                                                swal('Hata', 'Kaydetme başarısız oldu.', 'error');
                                                return;
                                            }
                                            swal('Kaydedildi', res.data.mesaj || 'Telefon meta anahtarı kaydedildi.', 'success');
                                        }).fail(function () {
                                            btn.disabled = false;
                                            btn.innerHTML = original;
                                            swal('Hata', 'Sunucuya ulaşılamadı.', 'error');
                                        });
                                    }

                                    // bootstrap-table sunucu taraflı sayfalama parametreleri (limit/offset/search)
                                    // WordPress admin-ajax'a action + nonce ile gönderilir.
                                    function netgsmUsersQueryParams(params) {
                                        params.action = 'netgsm_users_datatable';
                                        params._wpnonce = '<?php echo esc_attr(wp_create_nonce('netgsm_users_datatable')); ?>';
                                        return params;
                                    }
                                    function netgsmUserUsername(value, row) {
                                        return '<img alt="" class="avatar avatar-32 photo" width="32" height="32" src="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=32&d=mm&r=g"> ' +
                                            '<strong><a href="user-edit.php?user_id=' + encodeURIComponent(row.userid) + '" target="_blank">' + (value || '') + '</a></strong>';
                                    }
                                    function netgsmUserEmail(value) {
                                        if (!value) return '';
                                        return '<a href="mailto:' + value + '">' + value + '</a>';
                                    }
                                    function netgsmUserPhone(value, row) {
                                        return (value || '') +
                                            '<div class="row-actions"><span class="view">' +
                                            '<a href="javascript:void(0);" onclick="netgsm_sendSMS_bulkTab(' + row.userid + ')">Sms Gönder</a>' +
                                            '</span></div>';
                                    }
                                </script>
                            </div>
                            <?php /* Gelen SMS sekmesi admin panel redesign kapsamında menüden kaldırıldı (2026-09).
                                     Kod silinmedi, ileride geri eklenebilir. */ ?>
                            <?php if (false) : ?>
                            <div class="tab-pane container-fluid" id="inbox">
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table data-pagination="true" id="table" name="table" class="table table-bordered table-striped dataTable no-footer" data-search="true" data-search-align="left" data-pagination-v-align="bottom" data-click-to-select="true" data-toggle="table" data-page-list="[10, 25, 50, 100, 150, 200]">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="false">#</th>
                                                    <th>Kullanıcı adı</th>
                                                    <th>İsim</th>
                                                    <th>E posta</th>
                                                    <th>Telefon</th>
                                                    <th>Mesaj</th>
                                                    <th>Tarih</th>
                                                    <th>Saat</th>
                                                    <th>İşlemler</th>

                                                </tr>
                                            </thead>
                                            <tbody id="the-list" data-wp-lists="list:user">
                                                <?php
                                                $inboxData = ($netgsm->inbox());
                                                if (isset($inboxData['status']) && !empty($inboxData['status']) && $inboxData['status'] == 200) {
                                                    // Gelen kutusundaki numaraları tek sorguda kullanıcılara eşle
                                                    // (tüm kullanıcıları belleğe almadan).
                                                    $inboxPhones = array();
                                                    foreach ($inboxData as $__row) {
                                                        if (is_array($__row) && isset($__row['phone']) && $__row['phone'] !== '') {
                                                            $inboxPhones[] = $__row['phone'];
                                                        }
                                                    }
                                                    $inboxPhoneMap = $netgsm->phonesToUserMap($inboxPhones);
                                                    foreach ($inboxData as $data) {
                                                        if (isset($data['phone']) && !empty($data['phone'])) {
                                                            $userinfo = $inboxPhoneMap[ltrim($data['phone'], '0')] ?? null; ?>

                                                            <tr id="user-<?php echo esc_attr($data['phone']); ?>">

                                                                <td><?php echo esc_html($userinfo->ID); ?></td>

                                                                <td class="username column-username has-row-actions column-primary" data-colname="Kullanıcı adı">
                                                                    <img alt="" src="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=32&amp;d=mm&amp;r=g" srcset="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32">
                                                                    <?php
                                                                    if (isset($userinfo) && !empty($userinfo) && $userinfo != 0) {
                                                                        $escaped_user_id = esc_attr($userinfo->ID);
                                                                        $escaped_user_login = esc_html($userinfo->user_login);
                                                                        echo '<strong><a href="user-edit.php?user_id=' . esc_url($escaped_user_id) . '" target="_blank">' . esc_html($escaped_user_login) . '</a></strong><br>';
                                                                    } else {
                                                                        echo '<strong>Kayıtlı Değil</strong><br>';
                                                                    }

                                                                    ?>


                                                                </td>
                                                                <td>
                                                                    <?php if (!empty($userinfo->first_name)) {
                                                                        echo esc_html($userinfo->first_name) . " " . esc_html($userinfo->last_name);
                                                                    } else {
                                                                        print '—';
                                                                    } ?>
                                                                </td>
                                                                <td data-colname="E-posta">
                                                                    <?php if (isset($userinfo->user_email) && !empty($userinfo->user_email)) { ?>
                                                                        <a href="mailto:<?= esc_url($userinfo->user_email) ?>"><?= esc_html($userinfo->user_email) ?></a>
                                                                    <?php } ?>
                                                                </td>
                                                                <td><?= esc_html($data['phone']) ?></td>

                                                                <td>
                                                                    <strong><?= esc_html(iconv("ISO-8859-9", "UTF-8", $data['message'])); ?></strong>
                                                                </td>

                                                                <?php $time = explode(' ', $data['time']); ?>
                                                                <td><?= esc_html($time[0]) ?></td>
                                                                <td><?= esc_html($time[1]) ?></td>
                                                                <td class="text-center">
                                                                    <a href="javascript:void(0)" class="btn btn-warning btn-sm" onclick="<?php if (!empty($userinfo->ID)) { ?>netgsm_sendSMS_bulkTab(<?= esc_js($userinfo->ID) ?>)<?php } else { ?>sendSMSglobal('<?= esc_js($data['phone']) ?>'); <?php } ?>"><i class="fa fa-commenting-o"></i> Cevapla</a>
                                                                </td>
                                                            </tr>
                                                <?php }
                                                    }
                                                } else {
                                                    echo '<div class="alert alert-warning">' . esc_html($inboxData['message']) . '</div>';
                                                } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php endif; /* /Gelen SMS — kapsam dışı */ ?>
                            <?php /* Gelen Çağrılar, Netasistan, Ayarlar — admin panel redesign kapsamında
                                     menüden kaldırıldı (2026-09). Dosyalar silinmedi, ileride geri eklenebilir. */ ?>
                            <?php // include 'voip.php'; ?>
                            <?php // include 'asistan.php'; ?>
                            <?php // include 'settings.php'; ?>

                                </div><!-- /.tab-content -->
                            </div><!-- /.vx-content-pad -->
                        </div><!-- /.vx-content -->
                    </form>
                    </div><!-- /.vx-body -->
                    <div class="vx-panel-footer" style="padding:14px 24px;border-top:1px solid var(--divider);font-size:13px;color:var(--text-secondary);display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                        <span>version: <a href="https://wordpress.org/plugins/netgsm/" target="_blank"><?= esc_html(plugin_name_get_version()) ?></a> | <a href="https://www.netgsm.com.tr" target="_blank">Netgsm</a></span>
                        <span>WordPress ile oluşturuldu.</span>
                    </div>
                </div><!-- /.vx-panel -->
            </div><!-- /.vx-page -->
        </div><!-- /.vx-shell -->
    <div id="loadingmessage" style="display:none;margin: 0px; padding: 0px; position: fixed; right: 0px; top: 0px; width: 100%; height: 100%; background-color: rgb(102, 102, 102); z-index: 999999; opacity: 0.8;">
        <div style="color: white; position: absolute; top: 50%; left: 50%;transform: translate(-50%, -50%); display: inline-block;">
            <div class="text-center">
                <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
                <br>
                <span style="color: white;" id="loadMesage">Aktarılıyor, bekleyin...</span>
            </div>
        </div>
    </div>
    <script>
        jQuery('[data-toggle="tooltip"]').tooltip();

        function showLoadingMessage(message) {
            jQuery('#loadMesage').html(message);
            jQuery('#loadingmessage').show();
        }

        function hideLoadingMessage() {
            jQuery('#loadMesage').html('');
            jQuery('#loadingmessage').hide();
        }
    </script>
    <script>
        function RestrictSpace() {
            if (event.keyCode == 32) {
                return false;
            }
        }

        jQuery("#language a:first").tab("show");

        function login() {
            jQuery('#sayfayi_yenile').val(1);
        }

        function logout() {
            swal({
                title: 'Emin misiniz?',
                text: "Çıkış yapılacak, onaylıyor musunuz?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'Hayır',
                buttonsStyling: true,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    jQuery('#netgsm_user').val('');
                    jQuery('#netgsm_pass').val('');
                    jQuery('#input-status').val(0);
                    jQuery('#login_save').click();

                }
            })
        }

        function cf7_form_change(id, tip, activestatus) {
            jQuery('.cf7_list_text_success_' + tip).hide('slow');
            jQuery('#netgsm_cf7_list_text_success_' + tip + '_' + id).show('slow');
            jQuery('#netgsm_cf7_list_tags_success_' + tip + '_' + id).show('slow');
            jQuery('#activeStatus_cf7_' + tip).attr('data', id);
        }

        function cf7_form_change2(id, tip, activestatus) {
            jQuery('.cf7_list_' + tip).hide('slow');
            jQuery('#netgsm_cf7_list_' + tip + '_' + id).show('slow');
            jQuery('#netgsm_cf7_list_' + tip + '_other_' + '_' + id).show('slow');
            jQuery('#' + activestatus).attr('data', id);
            jQuery('#activeStatus_cf7_other_' + tip).attr('data', id);
        }



        function order_status_change(id) {
            jQuery('.order_status_text').hide('slow');
            jQuery('#netgsm_order_status_text_' + id).show('slow');
            jQuery('#activeStatus').attr('data', id);
            jQuery('#settings-btn-changed').attr('onclick', "settingOpen('" + id + "')");

            if (jQuery('#netgsm_order_status_text_' + id + '_json').val() != '') {
                jQuery('#setting-btn_color').css('color', '#17A2B8');
            } else {
                jQuery('#setting-btn_color').css('color', '#2B2B2B');
            }
        }

        function netgsm_field_onoff(id) {
            var switchstatus = document.getElementById('netgsm_switch' + id).checked;
            var field = document.getElementById('netgsm_field' + id);
            if (switchstatus) {
                jQuery('#netgsm_field' + id).show('fast')
            } else {
                jQuery('#netgsm_field' + id).hide('fast');
            }
        }

        function netgsm_field_onoff_custom(id) {
            var switchstatus = document.getElementById('switch_' + id).checked;
            if (switchstatus) {
                jQuery('#field_' + id).show('fast');
            } else {
                jQuery('#field_' + id).hide('fast');
            }
        }

        var field1 = ['uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'tarih', 'saat'];
        var field2 = ['uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'tarih', 'saat'];
        var field3 = ['siparis_no', 'toplam_tutar', 'uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'urun_bilgileri', 'urun_kdv', 'urun_adi', 'tarih', 'saat'];
        var field4 = ['siparis_no', 'toplam_tutar', 'uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'urun_bilgileri', 'urun_kdv', 'urun_adi', 'tarih', 'saat'];
        var field5 = ['siparis_no', 'uye_adi', 'uye_soyadi', 'aciklama'];
        var field6 = ['siparis_no', 'uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'tarih', 'saat'];
        var field7 = [''];
        var field8 = ['uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'urun_kodu', 'urun_adi', 'stok_miktari', 'tarih', 'saat', 'urun_bilgileri'];
        var field9 = ['kod', 'telefon_no', 'ad', 'soyad', 'mail', 'referans_no', 'tarih', 'saat'];
        var field10 = ['telefon_no', 'ad', 'soyad', 'mail', 'tarih', 'saat'];
        var field11 = ['siparis_no', 'not', 'uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'siparis_toplamtutar', 'tarih', 'saat'];
        var field12 = ['siparis_no', 'not', 'uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'siparis_toplamtutar', 'tarih', 'saat'];
        var field13 = ['uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi'];
        var field15 = [''];
        var field16 = [''];
        var field17 = [''];
        var field18 = [''];
        var field19 = [''];
        var field20 = [''];
        var field21 = [''];
        var field22 = ['kod', 'telefon_no', 'ad', 'soyad', 'mail'];
        var field23 = [''];
        for (var x = 1; x <= 22; x++) {
            if (x != 5) { //değişkeni olmayan idler
                var field = window['field' + x];
                if (field) {
                    for (var i = 0; i < field.length; i++) {
                        var textarea = document.getElementById('netgsm_tags_text' + x);
                        var mark = '<mark onclick="varfill(' + "'netgsm_textarea" + x + "','" + field[i] + "');" + '">[' + field[i] + ']</mark> ';
                        if (textarea) {
                            if (textarea.innerHTML) {
                                textarea.innerHTML += mark;
                            } else {
                                textarea.innerHTML = mark;
                            }
                        }
                    }
                }
            }
        }

        function varfill(input, degisken) {
            var textarea = document.getElementById(input);
            if (jQuery('#' + input).is(":visible")) {
                var start = textarea.selectionStart;
                var end = textarea.selectionEnd;
                var finText = textarea.value.substring(0, start) + '[' + degisken + ']' + textarea.value.substring(end);
                textarea.value = finText;
                textarea.focus();
                textarea.selectionEnd = end + (degisken.length + 2);
            }
        }
    </script>

<?php

} //login kontrol
else {
    $text = ['Administrator(Yönetici)'];
    if ($netgsm_auth_roles_control == 1) {
        foreach ($auth_roles as $auth_role) {
            array_push($text, $role_list[$auth_role]);
        }
    }
?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <h1>Netgsm eklentisine sadece <?php print esc_html(implode($text, ', ')); ?> rollerine sahip kullanıcılar erişebilir. </h1>
                </div>
                <div class="alert alert-info">
                    <h2><b><?php print esc_html($role_list[$session->roles[0]]) ?></b> rolüne sahip bu kullanıcı için, Yönetici hesabı ile giriş yapıp; Netgsm eklentisi > Ayarlar sekmesinden izin verebilirsiniz.</h2>




                </div>
            </div>
        </div>
    </div>

<?php

}
?>