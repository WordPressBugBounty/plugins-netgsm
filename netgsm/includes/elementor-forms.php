<?php

if (!defined('ABSPATH')) exit;

/**
 * Elementor Pro Form Widget entegrasyonu.
 *
 * Bu dosya SADECE Elementor Pro yüklü ve etkinken (elementor_pro/init
 * hook'u ateşlendiğinde) require edilir — bkz. index.php. Bu yüzden
 * \ElementorPro\Modules\Forms\Classes\Action_Base sınıfının burada her
 * zaman mevcut olduğu garanti.
 *
 * Netgsm SMS gönderimi, CF7 entegrasyonundaki netgsm_sendSMS_oneToMany()
 * yardımcı fonksiyonunu KULLANMAZ — o fonksiyon admin_init hook'una bağlı
 * netgsm_options() içinde tanımlı olduğu için, Elementor'ın form gönderim
 * isteği aynı hook sırasını izlemezse "tanımsız fonksiyon" hatası riski
 * taşır. Bunun yerine, her zaman koşulsuz yüklenen Netgsmsms ve
 * ReplaceFunction sınıfları burada doğrudan kullanılıyor.
 */
class Netgsm_Elementor_Form_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base
{
    public function get_name()
    {
        return 'netgsm_sms';
    }

    public function get_label()
    {
        return __('Netgsm SMS Gönder', 'netgsm');
    }

    /**
     * Form gönderildiğinde Elementor Pro tarafından çağrılır.
     *
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
     */
    public function run($record, $ajax_handler)
    {
        $netgsm_status = sanitize_text_field(get_option('netgsm_status'));
        if (empty($netgsm_status)) {
            return;
        }

        $settings = $record->get('form_settings');

        // Elementor'daki her form alanının değerini [field_id] => değer
        // eşlemesine çeviriyoruz; ReplaceFunction::netgsm_cf7_replace_all_var()
        // CF7 entegrasyonunda kullandığımız aynı [etiket] söz dizimini
        // burada da kullanmamızı sağlıyor.
        $raw_fields = $record->get('fields');
        $fields = [];
        if (is_array($raw_fields)) {
            foreach ($raw_fields as $field_id => $field) {
                $fields[$field_id] = isset($field['value']) ? $field['value'] : '';
            }
        }

        $replace = new ReplaceFunction();

        // Müşteriye (formu dolduran kişiye) SMS
        if (
            !empty($settings['netgsm_customer_enabled'])
            && !empty($settings['netgsm_customer_phone_field'])
        ) {
            $phone_field_id = $settings['netgsm_customer_phone_field'];
            $phone = isset($fields[$phone_field_id]) ? $fields[$phone_field_id] : '';
            $template = isset($settings['netgsm_customer_message']) ? $settings['netgsm_customer_message'] : '';

            if ($phone !== '' && $template !== '') {
                $this->send($fields, $phone, $template, $replace);
            }
        }

        // Belirlenen (yönetici) numaralara SMS
        if (
            !empty($settings['netgsm_admin_enabled'])
            && !empty($settings['netgsm_admin_phone'])
        ) {
            $template = isset($settings['netgsm_admin_message']) ? $settings['netgsm_admin_message'] : '';

            if ($template !== '') {
                $this->send($fields, $settings['netgsm_admin_phone'], $template, $replace);
            }
        }
    }

    private function send($fields, $phone, $template, ReplaceFunction $replace)
    {
        $message = $replace->netgsm_cf7_replace_all_var($fields, $template);
        $message = $replace->netgsm_replace_date($message);
        $message = sanitize_textarea_field(wp_unslash($message));
        $message = strip_tags($message);

        if ($message === '') {
            return;
        }

        $netgsm = new Netgsmsms(
            sanitize_text_field(get_option('netgsm_user')),
            sanitize_text_field(get_option('netgsm_pass')),
            sanitize_text_field(get_option('netgsm_input_smstitle')),
            sanitize_text_field(get_option('netgsm_trChar'))
        );

        $netgsm->sendSMS($replace->netgsm_spaceTrim($phone), $message);
    }

    /**
     * Elementor form widget'ının "Actions After Submit" sekmesinde,
     * bu action seçildiğinde açılan ayar panelini oluşturur.
     *
     * @param \Elementor\Widget_Base $widget
     */
    public function register_settings_section($widget)
    {
        $widget->start_controls_section(
            'netgsm_sms_section',
            [
                'label' => __('Netgsm SMS', 'netgsm'),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'netgsm_customer_enabled',
            [
                'label' => __('Müşteriye SMS gönder', 'netgsm'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $widget->add_control(
            'netgsm_customer_phone_field',
            [
                'label' => __('Telefon alanı (Field ID)', 'netgsm'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Örn: field_telefon', 'netgsm'),
                'description' => __('Bu formdaki telefon alanının Field ID değerini girin. Field ID, formun "İçerik" sekmesinde ilgili alana tıklayınca "Gelişmiş" bölümünde görünür.', 'netgsm'),
                'condition' => [
                    'netgsm_customer_enabled' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'netgsm_customer_message',
            [
                'label' => __('Müşteri mesajı', 'netgsm'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'placeholder' => __('Örn: Sayın [name], mesajınız tarafımıza ulaşmıştır.', 'netgsm'),
                'description' => __('Form alanlarındaki değerleri [field_id] şeklinde kullanabilirsiniz (Field ID neyse köşeli parantez içine onu yazın).', 'netgsm'),
                'condition' => [
                    'netgsm_customer_enabled' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'netgsm_admin_enabled',
            [
                'label' => __('Belirlenen numaralara SMS gönder', 'netgsm'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $widget->add_control(
            'netgsm_admin_phone',
            [
                'label' => __('Numaralar (virgülle ayırın)', 'netgsm'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('05xxXXXxxXX,05xxXXXxxXX', 'netgsm'),
                'condition' => [
                    'netgsm_admin_enabled' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'netgsm_admin_message',
            [
                'label' => __('Yönetici mesajı', 'netgsm'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'placeholder' => __('Örn: Yeni form gönderimi: [name] - [email]', 'netgsm'),
                'condition' => [
                    'netgsm_admin_enabled' => 'yes',
                ],
            ]
        );

        $widget->end_controls_section();
    }

    /**
     * Şablon/JSON dışa aktarımında Netgsm'e özel ayarları temizler
     * (Elementor'ın resmi Action_Base sözleşmesinin bir parçası).
     */
    public function on_export($element)
    {
        unset(
            $element['netgsm_customer_enabled'],
            $element['netgsm_customer_phone_field'],
            $element['netgsm_customer_message'],
            $element['netgsm_admin_enabled'],
            $element['netgsm_admin_phone'],
            $element['netgsm_admin_message']
        );

        return $element;
    }
}
