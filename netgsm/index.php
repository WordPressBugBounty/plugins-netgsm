<?php
/*
Plugin Name: Netgsm
Plugin URI: https://wordpress.org/plugins/netgsm/
Description: Netgsm hesabınız ile Woocommerce müşterileriniz yeni sipariş verdiğinde, yeni kayıt olan müşterileriniz olduğunda ve toplu smslerde kişiye özel ve yöneticilere sms gönderebileceğiniz bir eklentidir. Bunun yanısıra kişiye özel toplu ve özel sms gönderebilir, Gelen kutunuzdaki smsleri anında cevaplaya bilirsiniz. Yeni kayıt olan müşterileriniz netgsm rehberine ekleyebilir, siparişlerin durumları değiştiğinde kargo takip kodu gibi bilgileri müşterilerinize otomatik olarak gönderebilirsiniz. Ayrıca Contact Form 7 formlarınızda sms gönderimi sağlayabilirsiniz.
Author: Netgsm
Author URI: www.netgsm.com.tr
Version: 2.9.29

*/

/**
 * Copyright (c) 2018 NETGSM Tüm hakları saklıdır.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 *
 *
 * NETGSM - Yeni Nesil Telekom Operatörü - www.netgsm.com.tr
 * NETGSM - Toplu SMS - Başlıklı SMS - Sabit Telefon - Sanal Santral - 0850li Numara
 * **********************************************************************
 */
    if ( !defined( 'ABSPATH' ) ) exit;

    define( 'PLUGIN_CLASS_PATH', dirname(__FILE__). '/includes' );
    require_once PLUGIN_CLASS_PATH.'/netgsmsms.php';
    require_once PLUGIN_CLASS_PATH.'/replacefunction.php';

    @ini_set('log_errors','On');
    @ini_set('display_errors','Off');
    @ini_set('error_log','phperrors.log'); // path to server-writable log file

    add_action("admin_menu", "netgsm_addMenuu");
    function netgsm_addMenuu()
    {
        add_menu_page("NETGSM - Yeni Nesil Telekom Operatörü - www.netgsm.com.tr", "Netgsm",'edit_pages', "netgsm-wp-plugin", "netgsm_index",plugins_url( 'lib/image/netgsm.png', __FILE__ ));
    }


    
function plugin_name_get_version() {
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

    function netgsm_index()
    {
        
        require_once ('pages/index.php');
    }

    add_action( 'admin_enqueue_scripts', 'netgsm_loadcustomadminstyle' );
    function netgsm_loadcustomadminstyle($hook)
    {
        if($hook!= 'toplevel_page_netgsm-wp-plugin') {
            return;
        }

        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'bootstrap',      $plugin_url . 'lib/css/bootstrap.css' );
        wp_enqueue_style( 'font-awesome',   $plugin_url . '/lib/fonts/css/font-awesome.min.css' );
        wp_enqueue_style( 'style',         $plugin_url . 'lib/css/style.css' );
        wp_enqueue_style( 'sweetalert2',    $plugin_url . 'lib/js/sweetalert2/dist/sweetalert2.css' );
        wp_enqueue_style( 'dataTables',    $plugin_url . 'lib/css/bootstrap-table.min.css' );
    }

    add_action( 'admin_enqueue_scripts','netgsm_script');
    function netgsm_script()
    {
        //bootstrap-js YAPMALIYIZ
       wp_register_script('bootstrapminjs', plugins_url('bootstrap.min.js', dirname(__FILE__).'/lib/js/1/'));
       wp_enqueue_script('bootstrapminjs');

       wp_register_script('sweet2', plugins_url('sweetalert2.all.js', dirname(__FILE__).'/lib/js/sweetalert2/dist/1/'));
       wp_enqueue_script('sweet2');

        wp_register_script('table', plugins_url('bootstrap-table.min.js', dirname(__FILE__).'/lib/js/1/'));
        wp_enqueue_script('table');
    }

    function my_settings_sanitize($input){
        $replace = new ReplaceFunction();
        $input = $replace->netgsm_spaceTrim($input);
        return $input;
    }

    add_action('admin_init', 'netgsm_options');
    function netgsm_options()
    {
        register_setting('netgsmoptions', 'netgsm_user', 'my_settings_sanitize');
        register_setting('netgsmoptions', 'netgsm_pass');
        register_setting('netgsmoptions', 'netgsm_input_smstitle');

        register_setting('netgsmoptions', 'netgsm_newuser_to_admin_control');
        register_setting('netgsmoptions', 'netgsm_newuser_to_admin_no');
        register_setting('netgsmoptions', 'netgsm_newuser_to_admin_text');
        register_setting('netgsmoptions', 'netgsm_newuser_to_customer_control');
        register_setting('netgsmoptions', 'netgsm_newuser_to_customer_text');

        register_setting('netgsmoptions', 'netgsm_neworder_to_admin_control');
        register_setting('netgsmoptions', 'netgsm_neworder_to_admin_no');
        register_setting('netgsmoptions', 'netgsm_neworder_to_admin_text');
        register_setting('netgsmoptions', 'netgsm_neworder_to_customer_control');
        register_setting('netgsmoptions', 'netgsm_neworder_to_customer_text');

        register_setting('netgsmoptions', 'netgsm_newnote1_to_customer_control');
        register_setting('netgsmoptions', 'netgsm_newnote1_to_customer_text');

        register_setting('netgsmoptions', 'netgsm_newnote2_to_customer_control');
        register_setting('netgsmoptions', 'netgsm_newnote2_to_customer_text');

        register_setting('netgsmoptions', 'netgsm_order_refund_to_admin_control');
        register_setting('netgsmoptions', 'netgsm_order_refund_to_admin_no');
        register_setting('netgsmoptions', 'netgsm_order_refund_to_admin_text');


        register_setting('netgsmoptions', 'netgsm_product_waitlist1_control');
        register_setting('netgsmoptions', 'netgsm_product_waitlist1_text');



        register_setting('netgsmoptions', 'netgsm_rehber_control');
        register_setting('netgsmoptions', 'netgsm_rehber_groupname');

        register_setting('netgsmoptions', 'netgsm_orderstatus_change_customer_control');

        register_setting('netgsmoptions', 'netgsm_status');
        register_setting('netgsmoptions', 'netgsm_trChar');

        register_setting('netgsmoptions', 'netgsm_order_status_query_control');
        register_setting('netgsmoptions', 'netgsm_order_status_query_prefix');
        register_setting('netgsmoptions', 'netgsm_order_status_query_text');
        register_setting('netgsmoptions', 'netgsm_order_status_query_error_text');
        register_setting('netgsmoptions', 'netgsm_order_status_query_token');
        register_setting('netgsmoptions', 'netgsm_order_status_query_link');

        //JSON settings
        register_setting('netgsmoptions', 'netgsm_newuser_to_admin_json');
        register_setting('netgsmoptions', 'netgsm_newuser_to_customer_json');
        register_setting('netgsmoptions', 'netgsm_newnote1_to_customer_json');
        register_setting('netgsmoptions', 'netgsm_newnote2_to_customer_json');
        register_setting('netgsmoptions', 'netgsm_neworder_to_admin_json');
        register_setting('netgsmoptions', 'netgsm_neworder_to_customer_json');
        register_setting('netgsmoptions', 'netgsm_order_refund_to_admin_json');
        register_setting('netgsmoptions', 'netgsm_product_waitlist1_json');

        //OTP SMS
        register_setting('netgsmoptions', 'netgsm_tf2_auth_register_control');
        register_setting('netgsmoptions', 'netgsm_tf2_auth_register_text');
        register_setting('netgsmoptions', 'netgsm_tf2_auth_register_diff');

        //otp duplicate control
        register_setting('netgsmoptions', 'netgsm_tf2_auth_register_phone_control');
        register_setting('netgsmoptions', 'netgsm_tf2_auth_register_phone_warning_text');

        //contacts meta
        register_setting('netgsmoptions', 'netgsm_contact_meta_key');

        //roles
        register_setting('netgsmoptions', 'netgsm_auth_roles');
        register_setting('netgsmoptions', 'netgsm_auth_users');
        register_setting('netgsmoptions', 'netgsm_auth_roles_control');
        register_setting('netgsmoptions', 'netgsm_auth_users_control');

        register_setting('netgsmoptions', 'netgsm_phonenumber_zero1');
        register_setting('netgsmoptions', 'netgsm_licence_key_to_meta');

        //ÇIKIŞ
        register_setting('netgsmoptionslogout', 'netgsm_user');
        register_setting('netgsmoptionslogout', 'netgsm_pass');

        //İYS
        register_setting('netgsmoptions', 'netgsm_iys_check_text');
        register_setting('netgsmoptions', 'netgsm_iys_check_control');

        register_setting('netgsmoptions', 'netgsm_brandcode_control');
        register_setting('netgsmoptions', 'netgsm_brandcode_text');
        register_setting('netgsmoptions', 'netgsm_recipient_type');

        register_setting('netgsmoptions', 'netgsm_message');
        register_setting('netgsmoptions', 'netgsm_call');
        register_setting('netgsmoptions', 'netgsm_email');

        register_setting('netgsmoptions', 'netgsm_iys_meta_key');


        //ASİSTAN

        register_setting('netgsmoptions', 'netgsm_asistan');

        register_setting('netgsmoptions', 'netgsm_asistan_message');
        register_setting('netgsmoptions', 'netgsm_asistan_messagenumber');

        register_setting('netgsmoptions', 'netgsm_asistan_call');
        register_setting('netgsmoptions', 'netgsm_asistan_callnumber');

        register_setting('netgsmoptions', 'netgsm_asistan_email');
        register_setting('netgsmoptions', 'netgsm_asistan_emailaddress');

        register_setting('netgsmoptions', 'netgsm_asistan_whatsapp');
        register_setting('netgsmoptions', 'netgsm_asistan_whatsappnumber');


        register_setting('netgsmoptions', 'netgsm_asistan_netasistan');
        register_setting('netgsmoptions', 'netgsm_netasistan_appkey');
        register_setting('netgsmoptions', 'netgsm_netasistan_userkey');
        register_setting('netgsmoptions', 'netgsm_netasistan_etiket');


        register_setting('netgsmoptions', 'netgsm_netasistan_token');
        register_setting('netgsmoptions', 'netgsm_netasistan_tokendate');



        if(function_exists('wc_get_order_statuses')) {
            $order_statuses = wc_get_order_statuses();
            $arraykeys = array_keys($order_statuses);
            foreach ($arraykeys as $arraykey) {
                register_setting('netgsmoptions','netgsm_order_status_text_'.$arraykey);
                register_setting('netgsmoptions','netgsm_order_status_text_'.$arraykey.'_json');
            }
        }

        register_setting('netgsmoptions','netgsm_cf7_success_customer_control');
        register_setting('netgsmoptions','netgsm_cf7_success_admin_control');
        register_setting('netgsmoptions','netgsm_cf7_contact_control');

        register_setting('netgsmoptions','netgsm_cf7_to_admin_no');
        $cf7_list = apply_filters( 'netgsm_contact_form_7_list', '');
        foreach ($cf7_list as $item) {
            register_setting('netgsmoptions','netgsm_cf7_list_text_success_customer_'.$item->ID);
            register_setting('netgsmoptions','netgsm_cf7_list_text_success_admin_'.$item->ID);
            register_setting('netgsmoptions','netgsm_cf7_list_contact_'.$item->ID);
            register_setting('netgsmoptions','netgsm_cf7_list_contact_firstname_'.$item->ID);
            register_setting('netgsmoptions','netgsm_cf7_list_contact_lastname_'.$item->ID);
            register_setting('netgsmoptions','netgsm_cf7_list_contact_other_'.$item->ID);
            register_setting('netgsmoptions','netgsm_cf7_list_text_error_'.$item->ID);
        }
    }

    
    function netgsm_getCustomSetting($key, $search){
        $settings  = esc_html(get_option($key));
        if ($settings != ''){
            $jsonData = stripslashes(html_entity_decode($settings));
            $object = json_decode($jsonData, true);
            if (isset($object[$search])){
                return $object[$search];
            } else {
                return '';
            }
        }
        return $settings;
    }

    add_action( 'admin_footer', 'netgsm_ajaxRequest' );
    function netgsm_ajaxRequest() { ?>
        <script type="text/javascript" >
            function privatesmsSend(number="") {
                var phone = document.getElementById('private_phone').value;
                var message = document.getElementById('private_text').value;
                var content_type =document.getElementById('netgsm_content_type').selectedIndex;
                var filter;

                if (phone =="" || message =="" || content_type=="0")
                {
                    swal('Mesaj göndermek için lütfen gerekli alanları doldurun.');
                    return;
                }
                if (content_type == "1"){
                    filter = "11"
                }else if (content_type == "2"){
                    filter = "12"
                }else if (content_type == "3"){
                    filter = "0"
                }
                document.getElementById('sendSMS').disabled = true;
                var data = {
                    'action': 'netgsm_sendsms',
                    'phone': phone,
                    'message':message,
                    'filter' : filter,
                };

                jQuery.post(ajaxurl, data, function(response) {


                   var obje = JSON.parse(response);
                        if(obje.durum==1){
                            document.getElementById('private_phone').value="";
                            document.getElementById('private_text').value="";
                            swal({
                                title: "BAŞARILI!",
                                html: obje.mesaj+ '<br><br><b>' + phone + '</b> numarasına ' + '"<b>'+message+'</b>" gönderildi.',
                                type: 'success'
                            });
                        }
                        else{
                            swal({
                                title: "HATA! Kod : "+ obje.kod,
                                text: obje.mesaj,
                                type: 'error'
                            });
                        }
                    document.getElementById('sendSMS').disabled = false;
                });
            }
            function netgsm_sendSMS_bulkTab(id="") {
                document.getElementById('bulkSMSbtn').disabled = true;
                var users = [];
                var numberstext;
                if (id!=""){
                    users = id; numberstext="numarasına";
                }
                else {
                    var table = jQuery('#table');
                    var sonuc = table.bootstrapTable('getAllSelections');
                    for (var i=0 ; i<sonuc.length ; i++)
                    {
                        users += sonuc[i][1]+",";
                    }
                    numberstext="numaralarına";
                }
                var mark='<mark class="bulkmark" onclick="varfill2(this.innerHTML);">';
                var variables = mark+'[uye_adi]</mark> &nbsp'+mark+'[uye_soyadi]</mark> &nbsp'+mark+'[uye_telefonu]</mark> &nbsp'+mark+'[uye_epostasi]</mark> &nbsp'+mark+'[kullanici_adi]</mark>&nbsp'+mark+'[tarih]</mark>&nbsp'+mark+'[saat]</mark>';
                if(users!="") {

                    swal({
                        title: "Mesaj",
                        input: 'textarea',
                        inputPlaceholder:'Mesaj İçeriğini buraya giriniz.',
                        html:
                            '<select id="swal-input2" name="swal-input2" class="swal2-select" style="border-color: #d9d9d9 " >' +
                                '<option style="color: #2c3338" value="0" > Mesaj içerik türü seçiniz</option>'+
                                '<option style="color: #2c3338" value="1"> Kampanya, tanıtım, kutlama vb. (İYS\'ye bireysel kayıtlı alıcılarınıza gönderilir.) </option>'+
                                '<option style="color: #2c3338" value="1"> Kampanya, tanıtım, kutlama vb. (İYS\'ye tacir kayıtlı alıcılarınıza gönderilir.) </option>'+
                                '<option style="color: #2c3338" value="2"> Bilgilendirme, kargo, şifre vb. (İYS\'den sorgulanmaz.)</option>'+
                            '</select>'+
                            '<i style="font-size: small; color: #555">*Tanımlı marka kodunuz bulunmuyorsa Bilgilendirme, kargo, şifre vb. (İYS\'den sorgulanmaz.) seçilmelidir.</i>'
                        ,
                        confirmButtonText : 'Gönder',
                        cancelButtonText : 'İptal',
                        confirmButtonColor : '#2ECC71',
                        cancelButtonColor : '#E74C3C',
                        width: 650,
                        showCancelButton: true,
                        showLoaderOnConfirm: true,
                        footer: '<i class="fa fa-angle-double-right"></i> Kullanabileceğiniz Değişkenler : ' + variables,
                        preConfirm: function (text) {
                            return new Promise(function (resolve, reject) {
                                if (text) {
                                    var message = text;
                                    var content_type = document.getElementById('swal-input2').selectedIndex;
                                    var filter ='';
                                    if (content_type=='0'){
                                        swal({
                                            title: "Mesaj içerik türü seçilmedi.",
                                            text: "Lütfen sms göndermek için mesaj içerik türü seçiniz.",
                                            type: 'error',
                                        });
                                    }else{
                                        if(content_type=='1'){
                                            filter = '11';
                                        }else if(content_type=='2'){
                                            filter = '12';
                                        }
                                        else{
                                            filter = '0';
                                        }
                                        var data = {
                                            'action': 'netgsm_sendSMS_bulkTab',
                                            'users': users,
                                            'message':message,
                                            'filter': filter,
                                        };
                                        jQuery.post(ajaxurl, data, function (response) {
                                            var obje = JSON.parse(response);

                                            if (obje.durum == 1) {
                                                swal({
                                                    title: "BAŞARILI!",
                                                    html: obje.mesaj+ '<br><br><b>' + obje.phones + '</b> '+numberstext+' "<b>'+message+'</b>" gönderildi.',
                                                    type: 'success'
                                                });
                                            }
                                            else{
                                                swal({
                                                    title: "HATA! Kod : "+ obje.kod,
                                                    text: obje.mesaj,
                                                    type: 'error'
                                                });
                                            }
                                        });
                                    }
                                } else {
                                    swal({
                                        title: "Mesaj içeriğini boş bıraktınız.",
                                        text: "Lütfen sms göndermek için birşeyler yazın.",
                                        type: 'error',
                                    });
                                }
                                document.getElementById('bulkSMSbtn').disabled = false;
                            })
                        }
                    })
                }
                else {
                    swal('Mesaj göndermek için müşteri seçmelisiniz.');
                    return;
                }
            }
            function sendSMSglobal(phone) {
                if(!jQuery.isNumeric(phone)) {
                    swal({
                        title: "Uyarı!",
                        text: 'Sadece telefon numaralarına SMS gönderilebilir.',
                        type: 'error'
                    });
                    return false;

                }
                if(phone!="") {
                    swal({
                        title: "Mesaj",
                        input: 'textarea',
                        inputPlaceholder:'Mesaj İçeriğini buraya giriniz.',
                        confirmButtonText : 'Gönder',
                        cancelButtonText : 'İptal',
                        confirmButtonColor : '#2ECC71',
                        cancelButtonColor : '#E74C3C',
                        width: 650,
                        showCancelButton: true,
                        showLoaderOnConfirm: true,
                        footer: 'Kayıtlı olmayan bir numaraya sms atıyorsunuz.',
                        preConfirm: function (text) {
                            return new Promise(function (resolve, reject) {
                                if (text) {
                                    var message = text;
                                    var data = {
                                        'action': 'netgsm_sendsms',
                                        'phone': phone,
                                        'message':message
                                    };
                                    jQuery.post(ajaxurl, data, function (response) {
                                        var obje = JSON.parse(response);

                                        if (obje.durum == 1) {
                                            swal({
                                                title: "BAŞARILI!",
                                                html: obje.mesaj+ '<br><br><b>' + phone + '</b> numarasına ' + '"<b>'+message+'</b>" gönderildi.',
                                                type: 'success'
                                            });
                                        }
                                        else{
                                            swal({
                                                title: "HATA! Kod : "+ obje.kod,
                                                text: obje.mesaj,
                                                type: 'error'
                                            });
                                        }
                                    });
                                } else {
                                    swal({
                                        title: "Mesaj içeriğini boş bıraktınız.",
                                        text: "Lütfen sms göndermek için birşeyler yazın.",
                                        type: 'error',
                                    });
                                }
                                document.getElementById('bulkSMSbtn').disabled = false;
                            })
                        }
                    })
                }
                else {
                    swal('Mesaj göndermek için müşteri seçmelisiniz.');
                    return;
                }
            }
            function varfill2(degisken) {
                var textarea = document.getElementsByClassName('swal2-textarea')[0];
                var start = textarea.selectionStart;
                var end = textarea.selectionEnd;
                var finText = textarea.value.substring(0, start) + degisken + textarea.value.substring(end);
                textarea.value = finText;
                textarea.focus();
                textarea.selectionEnd= end + (degisken.length);
            }



            var xhr = '';
            var m = 0; var errorControl = 0;
            jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = jQuery(e.target).attr("href") // activated tab
                if (target == '#voip'){
                    var type = 'netsantral';
                    var date = new Date();
                    if(m == 0 || date.getMinutes() - m > 2 ){
                        showLoadingMessage('Görüşme kayıtları getiriliyor...');
                        var data = {
                            'action': 'netgsm_getNetsantral_Report',
                            'type': type
                        };
                        xhr = jQuery.post(ajaxurl, data, function (response) {
                            var response = JSON.parse(response);
                            if(response["status"] == 'success'){
                                jQuery('#santralTable').html(response["message"]);
                                jQuery('#santralTab').html(response["type"]);
                                jQuery('#santralInfo').html(response["info"] + ' <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="left" title="Maximum 250 kayıt gösterilir. Daha fazla görüntülemek için Netgsm Webportal\'ı ziyaret edin."></i>');
                                m = date.getMinutes();
                            } else {
                                m = 0;
                                swal({
                                    title: "Başarısız",
                                    text: response['info'],
                                    type: 'error'
                                });
                                jQuery('#santralInfo').html(response["info"]+' Lütfen daha sonra tekrar deneyin.');
                            }
                            jQuery('[data-toggle="tooltip"]').tooltip();
                            hideLoadingMessage();
                        });
                    }
                }
            });

            jQuery(document).keyup(function(e) {
                if (e.keyCode == 27) {
                    hideLoadingMessage();
                    errorControl = 1;
                    xhr.abort();
                }
            });


            jQuery('#contactsMove').click(function () {
                var groupName = jQuery('#contactsBulk').val();
                if(groupName == ''){
                    swal({
                        title: "BAŞARISIZ!",
                        html: 'Aktarım için grup ismi girilmelidir.',
                        type: 'error',
                    });
                    return false;
                }
                showLoadingMessage('Kayıtlar aktarılıyor...');
                var data = {
                    'action': 'netgsm_addtoGroup',
                    'copy' :1,
                    'groupName':groupName
                };
                jQuery.post(ajaxurl, data, function (response) {
                    var response = JSON.parse(response);
                    if (response['result']=='success') {
                        swal({
                            title: "BAŞARILI!",
                            html: response['resultmsg'],
                            type: response["result"],
                        });
                    }
                    else
                    {
                        swal({
                            title: "HATA!",
                            text:  response['resultmsg'],
                            type: response["result"],
                        });
                    }
                    hideLoadingMessage();
                });

            })


        </script> <?php
    }

    add_action( 'wp_ajax_netgsm_addtoGroup', 'netgsm_addtoGroup' );
    function netgsm_addtoGroup()
    {
        $users = get_users();

        $phone_meta = 'billing_phone';
        if (esc_html(get_option("netgsm_contact_meta_key") != '')){
            $phone_meta = esc_html(get_option("netgsm_contact_meta_key"));
        }

        $contacts = []; $tmp = [];
        foreach ($users as $result) {
            $tmp['telephone'] = sanitize_text_field(get_user_meta($result->ID, $phone_meta, true));
            if ($tmp['telephone'] == ''){
                continue;
            }
            
            $tmp['firstname'] = sanitize_text_field($result->first_name);
            $tmp['lastname'] = sanitize_text_field($result->last_name);

            array_push($contacts, $tmp);
        }

        $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));

                $groupName = 'Wordpress';

                // Check if 'groupName' is set in POST request
                if (isset($_POST['groupName'])) {
                    // Sanitize the input to remove harmful characters
                    $groupName = sanitize_text_field($_POST['groupName']);
                }


                // İşlem gerçekleştir
                $moving = $netgsm->setContact($contacts, $groupName);

                // JSON çıktısı verirken HTML etiketlerinden kaçının
                $response = [
                    'result' => 'success',
                    'resultmsg' => sprintf(
                        'Toplam <b>%d</b> kullanıcı <b>%s</b> grubuna aktarıldı.',
                        count($contacts),
                        esc_html($groupName)
                    )
                ];

                echo json_encode($response);
                wp_die();
            }

    add_action( 'wp_ajax_netgsm_getNetsantral_Report', 'netgsm_getNetsantral_Report' );
    function netgsm_getNetsantral_Report()
            {
                $json   = array();
                $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));
                $json = $netgsm->getVoipReport();
                $type = 'NETSANTRAL';
                $info = '';
                $table = '<table data-pagination="true" id="table_voip" name="table_voip" class="table table-bordered table-striped dataTable no-footer"
                      data-search="true" data-search-align="left"
                      data-pagination-v-align="bottom"
                      data-click-to-select="true"
                      data-toggle="table"
                      data-page-list="[10, 25, 50, 100, 150, 200]"> <thead><th>#</th><th>Tarih</th><th>Arayan</th><th>Aranan</th><th>Süre</th><th>Yön</th><th>İşlemler</th></thead><tbody>';
                $object = json_decode($json);
                $users = get_users();
                if (is_object($object) && isset($object->success)) {
                    foreach ($object->data as $key => $data) {
                        if ($key >= 250) {
                            break;
                        }
                        $table .= '<tr>';
                        $table .= '<td><a href="https://www.netgsm.com.tr/webportal/netsantral/cdr/gorusme_detayi/' . $data->call_id . '" target="_blank">' . $data->call_id . '</a></td>';
                        $table .= '<td>' . $data->date . '</td>';

                        $customer = $netgsm->phoneToUser(ltrim($data->caller_number, '0'), $users);
                        //echo json_encode(['status'=>'success', 'message'=>$customer, 'type'=>$type, 'info'=>$info]);
                        //wp_die();
                        $c_id = 0;
                        $c_name = '';
                        if ($customer != null) {
                            $c_id = $customer->ID;
                            if (isset($customer->first_name) && $customer->first_name != '') {
                                $c_name = $customer->first_name . ' ' . $customer->last_name;
                            } else {
                                $c_name = $customer->user_login;
                            }
                        }

                        if ($c_name == ' ' || $c_name == '') {
                            $table .= '<td class="username column-username has-row-actions column-primary" data-colname="Kullanıcı adı">
<img alt="" src="http://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=32&amp;d=mm&amp;r=g" srcset="http://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32" data-toggle="tooltip" data-placement="top" title="Kayıtlı değil"><span data-toggle="tooltip" data-placement="top" title="Kayıtlı değil">' . $data->caller_number . '</span></td>';
                        } else {
                            $table .= '<td class="username column-username has-row-actions column-primary" data-colname="Kullanıcı adı">
<img alt="" src="http://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=32&amp;d=mm&amp;r=g" srcset="http://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32" data-toggle="tooltip" data-placement="top" title="' . $c_name . '">
<a href="user-edit.php?user_id=' . $c_id . '" target="_blank" data-toggle="tooltip" data-placement="top" title="' . $data->caller_number . '">' . $c_name . '</a></td>';
                        }

                        $table .= '<td>' . $data->dial_number . '</td>';
                        $table .= '<td>' . $netgsm->timeconvert($data->time) . '</td>';

                        $direction = '';
                        switch ($data->direction) {
                            case 0: //giden
                                $direction = '<i class="fa fa-arrow-circle-o-up" style="color: #3498DB;"></i> Giden';
                                break;
                            case 1:
                                $direction = '<i class="fa fa-arrow-circle-down" style="color: #2ECC71;"></i> Gelen';
                                break;
                            case 2:
                                $direction = '<i class="fa fa-arrow-circle-down" style="color: #E74C3C;"></i> Gelen Cevapsız';
                                break;
                            case 3:
                                $direction = '<i class="fa fa-arrow-circle-up" style="color: #E74C3C;"></i> Giden Cevapsız';
                                break;
                            case 4:
                                break;
                                $direction = '<i class="fa fa-arrow-circle-left" style="color: #2C3E50;"></i> <i class="fa fa-arrow-circle-right" style="color: #2C3E50;"></i> İç Arama';
                            case 5:
                                break;
                                $direction = '<i class="fa fa-arrow-circle-left" style="color: #E74C3C;"></i> <i class="fa fa-arrow-circle-right" style="color: #E74C3C;"></i> İç Cevapsız Arama';
                            default:
                                $direction = 'Bilinmiyor';
                                break;
                        }
                        $table .= '<td>' . $direction . '</td>';
                        if (strlen($data->caller_number) == 11) {
                            if ($c_id != '') {
                                $table .= '<td><a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="netgsm_sendSMS_bulkTab(\'' . $c_id . '\',\'' . $data->caller_number . '\'); ">Arayana SMS Gönder</a></td>';
                            } else {
                                $table .= '<td><a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="sendSMSglobal(\'' . $data->caller_number . '\'); ">Arayana SMS Gönder</a></td>';
                            }
                        } else {
                            $table .= '<td></td>';
                        }
                        $table .= '</tr>';
                    }
                    $table .= '</tbody></table>';
                    $message = $table;
                    $status = 'success';
                    $type = 'NETSANTRAL';
                    $info = $object->success->message;
                } else {
                    if (is_object($object) && isset($object->error)) {
                        if (in_array($object->error->code, [30, 80])) {
                            $json = $netgsm->getPhoneReport();

                            $table = '<table  data-pagination="true" id="table" name="table" class="table table-bordered table-striped dataTable no-footer"
                         data-search="true" data-search-align="left"
                         data-pagination-v-align="bottom"
                         data-click-to-select="true"
                         data-toggle="table"
                         data-page-list="[10, 25, 50, 100, 150, 200]"><thead><th>#</th><th>Tarih</th><th>Arayan</th><th>Süre</th><th>Yön</th><th>İşlemler</th></thead><tbody>';
                            $object = json_decode($json);
                            if (is_object($object) && isset($object->success)) {
                                foreach ($object->data as $key => $data) {
                                    if ($key >= 250) {
                                        break;
                                    }
                                    $table .= '<tr>';
                                    $table .= '<td>#</td>';
                                    $table .= '<td>' . $data->date . '</td>';

                                    $customer = $netgsm->phoneToUser(ltrim($data->caller_number, '0'), $users);
                                    $c_id = 0;
                                    $c_name = '';
                                    if ($customer != null) {
                                        $c_id = $customer->ID;
                                        if (isset($customer->first_name) && $customer->first_name != '') {
                                            $c_name = $customer->first_name . ' ' . $customer->last_name;
                                        } else {
                                            $c_name = $customer->user_login;
                                        }
                                    }

                                    if ($c_name == ' ' || $c_name == '') {
                                        $table .= '<td class="username column-username has-row-actions column-primary" data-colname="Kullanıcı adı">
<img alt="" src="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=32&amp;d=mm&amp;r=g" srcset="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32" data-toggle="tooltip" data-placement="top" title="Kayıtlı değil"><span data-toggle="tooltip" data-placement="top" title="Kayıtlı değil">' . $data->caller_number . '</span></td>';
                                    } else {
                                        $table .= '<td class="username column-username has-row-actions column-primary" data-colname="Kullanıcı adı">
<img alt="" src="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=32&amp;d=mm&amp;r=g" srcset="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32" data-toggle="tooltip" data-placement="top" title="' . $c_name . '">
<a href="user-edit.php?user_id=' . $c_id . '" target="_blank" data-toggle="tooltip" data-placement="top" title="' . $data->caller_number . '">' . $c_name . '</a></td>';
                                    }

                                    $table .= '<td>' . $netgsm->timeconvert($data->time) . '</td>';

                                    $direction = '';
                                    switch ($data->direction) {
                                        case 1:
                                            $direction = '<i class="fa fa-arrow-circle-down" style="color: #2ECC71;"></i> Gelen';
                                            break;
                                        case 2: //giden
                                            $direction = '<i class="fa fa-arrow-circle-o-up" style="color: #3498DB;"></i> Giden';
                                            break;
                                        case 3:
                                            $direction = '<i class="fa fa-arrow-circle-down" style="color: #E74C3C;"></i> Cevapsız';
                                            break;
                                        default:
                                            $direction = 'Bilinmiyor';
                                            break;
                                    }
                                    $table .= '<td>' . $direction . '</td>';
                                    if (in_array(strlen($data->caller_number), [10, 11])) {
                                        if ($c_id != '') {
                                            $table .= '<td><a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="netgsm_sendSMS_bulkTab(\'' . $c_id . '\',\'' . $data->caller_number . '\'); ">Arayana SMS Gönder</a></td>';
                                        } else {
                                            $table .= '<td><a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="sendSMSglobal(\'' . $data->caller_number . '\'); ">Arayana SMS Gönder</a></td>';
                                        }
                                    } else {
                                        $table .= '<td></td>';
                                    }
                                    $table .= '</tr>';
                                }
                                $table .= '</tbody></table>';
                                $message = $table;
                                $status = 'success';
                                $type = 'SES';
                                $info = $object->success->message;
                            } else {
                                if (is_object($object) && isset($object->error)) {
                                    $status = 'error';
                                    $message = $object->error->message;
                                    $info = $object->error->message;
                                } else {
                                    $message = 'Bilinmeyen Hata oluştu.';
                                    $info = 'Bilinmeyen Hata oluştu.';
                                    $status = 'error';
                                }
                            }
                        } else {
                            $status = 'error';
                            $message = $object->error->message;
                            $info = $object->error->message;
                        }
                    } else {
                        $message = 'Bilinmeyen Hata oluştu.';
                        $info = 'Bilinmeyen Hata oluştu.';
                        $status = 'error';
                    }
                }
                echo json_encode(['status' => $status, 'message' => $message, 'type' => $type, 'info' => $info]);
                wp_die();
            }

    add_filter( 'netgsm_contact_form_7_list', 'netgsm_wpcf7_form_list' );
    function netgsm_wpcf7_form_list($arg='') {
        $list = get_posts(array(
            'post_type'     => 'wpcf7_contact_form',
            'numberposts'   => -1
        ));

        return  $list;
    }

    add_action( 'wp_ajax_netgsm_sendSMS_bulkTab', 'netgsm_sendSMS_bulkTab' );
    function netgsm_sendSMS_bulkTab() {
        $phones = "";
        $json   = array();
        $phone_meta = 'billing_phone'; // Varsayılan değer

        $contact_meta_key = sanitize_text_field(get_option("netgsm_contact_meta_key"));

        // Check if the option is not empty
        if (!empty($contact_meta_key)) {
            $phone_meta = $contact_meta_key;
        }
        if(isset($_POST['users']) && isset($_POST['message'])) {
            $users      = explode(',',rtrim(sanitize_text_field($_POST['users']),','));
            $bulkBody   = "";
            $replace    = new ReplaceFunction();
            foreach ($users as $userID) {
                $phones    .= $replace->netgsm_spaceTrim(get_user_meta( $userID, $phone_meta, true )).",";
                $userinfo   = get_userdata($userID);
                $data       = array('first_name'=> $userinfo->first_name,   'last_name'=> $userinfo->last_name,
                                    'user_login'=> $userinfo->user_login,   'phone'=>get_user_meta($userID, $phone_meta, true),
                                    'user_email' =>$userinfo->user_email,   'message'=> wp_unslash($_POST['message']),
                                    'filter'=> wp_unslash($_POST['filter'])
                    );
                $message = $replace->netgsm_replace_bulksms($data);
                $message = $replace->netgsm_replace_date($message);
                $bulkBody  .= '<mp><msg><![CDATA[' . $message . ']]></msg><no>' . $data['phone'] . '</no></mp>';
            }
            $phones = rtrim($phones,',');
            $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));
            $json = $netgsm->sendBulkSMS($bulkBody,$data['filter']);
            $json['phones'] = $phones;
        }
        else {
            $json['durum'] = '0';
            $json['mesaj'] = 'Sms gönderimi başarısız.';
        }
        echo json_encode($json);
        wp_die();
    }

    add_action( 'wp_ajax_netgsm_sendsms', 'netgsm_sendsms' );
    function netgsm_sendsms() {

        $json = array();
        $replace = new ReplaceFunction();
        if(isset($_POST['phone']) && isset($_POST['message'])){
            $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));
            $json = $netgsm->sendSMS(sanitize_text_field($replace->netgsm_spaceTrim($_POST['phone'])), wp_unslash($_POST['message']),$_POST['filter']);
            //netgsm_setData($_POST['phone'], get_option('netgsm_input_smstitle'), get_option("netgsm_trChar"), $_POST['message'], 'ÖzelSMS', date('Y-m-d H:i:s'), $json['gorevid'], 0 );
        }
        else {
            $json['durum'] = '0';
            $json['mesaj'] = 'Sms gönderimi başarısız.';
        }
        echo json_encode($json);
        wp_die();
    }
    add_action('wp_ajax_nopriv_netgsm_netasistanticket', 'netgsm_netasistanticket');
    add_action( 'wp_ajax_netgsm_netasistanticket', 'netgsm_netasistanticket' );
    function netgsm_netasistanticket(){
        $json = array();
        $replace = new ReplaceFunction();
        date_default_timezone_set('Europe/Istanbul');
        if(isset($_POST['netasistan_name']) && isset($_POST['netasistan_lastname']) && isset($_POST['netasistan_number']) && isset($_POST['netasistan_header']) && isset($_POST['netasistan_content']) && isset($_POST['netasistan_email'])) {
            if (sanitize_text_field(get_option('netgsm_netasistan_appkey')) != "" && sanitize_text_field(get_option('netgsm_netasistan_etiket')) != "" && sanitize_text_field(get_option('netgsm_netasistan_userkey')) != "") {
                $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));
                if (sanitize_text_field(get_option('netgsm_netasistan_token')) != "" && sanitize_text_field(get_option('netgsm_netasistan_tokendate')) != "") {
                    if (strtotime(get_option('netgsm_netasistan_tokendate'))>strtotime(date('d-m-Y H:i:s'))){
                        $tokenvalue = sanitize_text_field(get_option('netgsm_netasistan_token'));
                    }else {
                        $netasistan_token = $netgsm->netasistan_yenitoken(sanitize_text_field(get_option('netgsm_netasistan_appkey')), sanitize_text_field(get_option('netgsm_netasistan_userkey')));
                        $token = json_decode($netasistan_token['body'], true);
                        update_option('netgsm_netasistan_token', $token['data']['access_token'],false);
                        update_option('netgsm_netasistan_tokendate', $token['data']['expired_at'],false);
                        $tokenvalue = $token['data']['access_token'];
                    }
                }else {
                    $netasistan_token = $netgsm->netasistan_yenitoken(sanitize_text_field(get_option('netgsm_netasistan_appkey')), sanitize_text_field(get_option('netgsm_netasistan_userkey')));
                    $token = json_decode($netasistan_token['body'], true);
                    update_option('netgsm_netasistan_token', $token['data']['access_token']);
                    update_option('netgsm_netasistan_tokendate', $token['data']['expired_at']);
                    $tokenvalue = $token['data']['access_token'];
                }
                if (isset($tokenvalue)) {
                    $netasistan_etiketler = sanitize_text_field(get_option('netgsm_netasistan_etiket'));
                    $netasistan_etiketler = explode(",", $netasistan_etiketler);
                     $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));                    $json = $netgsm->netasistan_ticket(sanitize_text_field($_POST['netasistan_name']), sanitize_text_field($_POST['netasistan_lastname']), sanitize_text_field($replace->netgsm_spaceTrim(netasistan_phonecontrol($_POST['netasistan_number']))), sanitize_text_field($_POST['netasistan_email']), wp_unslash($_POST['netasistan_header']), wp_unslash(sanitize_text_field($_POST['netasistan_content'])), $netasistan_etiketler, $tokenvalue);
                    echo esc_html($json['body']);
                }else{
                    $json = array(
                        'code' => '0',
                        'mesaj' => 'Kayıt oluşturulamadı.'
                    );
                    echo json_encode($json);
                }
            }else{
                $json = array(
                    'code' => '0',
                    'mesaj' => 'Kayıt oluşturulamadı.'
                );
                echo json_encode($json);
            }
        }else{
            $json = array(
                'code' => '0',
                'mesaj' => 'Kayıt oluşturulamadı.'
            );
            echo json_encode($json);
        }
        wp_die();
    }

    function netgsm_newcustomer_control()
    {
        $newuser1       = esc_html(get_option("netgsm_newuser_to_admin_control"));
        $newuser2       = esc_html(get_option("netgsm_newuser_to_customer_control"));
        $newuser3       = esc_html(get_option("netgsm_rehber_control"));
        $newuser4       = esc_html(get_option("netgsm_tf2_auth_register_control"));
        $newuser5       = esc_html(get_option("netgsm_iys_check_control"));
        $control        = 0;
        if(isset($newuser1) && !empty($newuser1) && $newuser1==1) {
            $control=1;
        }
        elseif(isset($newuser2) && !empty($newuser2) && $newuser2==1) {
            $control=2;
        }
        elseif(isset($newuser3) && !empty($newuser3) && $newuser3==1) {
            $control=3;
        }
        elseif(isset($newuser4) && !empty($newuser4) && $newuser4==1) {
            $control = 4;
        }
        elseif(isset($newuser5) && !empty($newuser5) && $newuser5==1) {
            $control = 5;
        }
        return $control;
    }

    add_action( 'woocommerce_register_form', 'netgsm_custom_register_form' );
    function netgsm_custom_register_form() {
        $netgsm_status  = esc_html(get_option("netgsm_status"));
        $netgsm_phonenumber_zero  = esc_html(get_option("netgsm_phonenumber_zero1"));
        $control        = netgsm_newcustomer_control();
        $otpregister_control = esc_html(get_option("netgsm_tf2_auth_register_control"));
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 && ($control!=0 || $otpregister_control ==1))
        {
            ?><p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="first_name"><?php esc_html_e('Adınız', 'mydomain') ?><span
                        class="required">*</span><br/>
                <input type="text" name="first_name" id="first_name" class="input-text form-control"
                       value="<?php echo esc_html_e(@$_POST['first_name'])?>" required/></label>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="last_name"><?php esc_html_e('Soyadınız', 'mydomain') ?><span
                        class="required">*</span><br/>
                <input type="text" name="last_name" id="last_name" class="input-text form-control"
                       value="<?php echo esc_html_e(@$_POST['last_name'])?>" required/></label>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="billing_phone"><?php esc_html_e('Cep Telefonu', 'mydomain') ?><span
                        class="required">*</span><br/>
                <input type="tel" name="billing_phone" id="billing_phone" class="input-text form-control"
                       value="<?php if($netgsm_phonenumber_zero==1){echo '0';}?><?php echo esc_html_e(@$_POST['billing_phone'])?>" required/>
                <?php
                $netgsm_status  = esc_html(get_option("netgsm_status"));
                if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 ) {

                    if ($otpregister_control == 1) {
                        ?>
                        <input type="button" name="sendCode" id="sendCode" class="input-text" onclick="sendtf2Code(jQuery('#billing_phone').val());"
                               value="Doğrulama kodu gönder"/>
                        <?php
                    }
                }
                ?>

            </label>
            </p>
            <?php
            $netgsm_status  = esc_html(get_option("netgsm_status"));
            if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 ) {
                $otpregister_control = esc_html(get_option("netgsm_tf2_auth_register_control"));
                if ($otpregister_control == 1) {
                    ?>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="tf2Code"><?php esc_html_e('Doğrulama Kodu:', 'mydomain') ?><span
                                    class="required">*</span><br/>
                            <input type="text" name="tf2Code" id="tf2Code" class="input-text form-control"
                                   value="" required/>
                            <label for=""><i><small id="tf2Codealert"></small></i></label>
                        </label>

                    </p>
                    <?php
                }

                $iys_status = esc_html(get_option("netgsm_iys_check_control"));
                if ($iys_status == 1) {
                    ?>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="checkbox" name="netgsm_iys" id="netgsm_iys" value="1"/> <?php echo esc_html(get_option("netgsm_iys_check_text")); ?>

                    </p>
                    <?php
                }
            }
        }
    }

    add_action( 'woocommerce_register_form', 'netgsm_ajaxRegister' );
    function netgsm_ajaxRegister() {
        $netgsm_phonenumber_zero  = esc_html(get_option("netgsm_phonenumber_zero1"));
        ?>
        <script type="text/javascript" >
            function sendtf2Code(phone) {
                var firstname = jQuery('#first_name').val();
                var lastname = jQuery('#last_name').val();
                var email = jQuery("input[name*='email']").val();

                var error = '';
                if(firstname == '') {
                    error += '> İsim girilmedi.\n';
                }
                if(lastname == '') {
                    error += '> Soyisim girilmedi.\n';
                }
                if(email == '') {
                    error += '> E-mail adresi girilmedi.\n';
                }
                if(phone == '') {
                    error += '> Telefon numarası girilmedi.\n';
                }

                <?php
                if ($netgsm_phonenumber_zero==1){
                    ?>
                if(phone.slice( 0,1 ) !='0'){
                    error += '> Telefon numarası 0 ile başlamalıdır.\n';
                }
                <?php
                }
                ?>

                if (error != ''){
                    alert('Aşağıdaki hatalar alındı : \n\n'+ error);
                    return false;
                }

                var data = {
                    'action': 'netgsm_sendtf2SMS',
                    'phone': phone,
                    'first_name': firstname,
                    'last_name': lastname,
                    'email':email
                };

                jQuery.post(ajaxurl, data, function(response) {
                    var endChar = response.substring(response.length-1);
                    if (endChar == '0'){
                        response = response.substring(0,(response.length-1));
                    }
                    var obje = JSON.parse(response);
                    if(obje.status == 'success'){
                        alert('Lütfen '+obje.phone+' numaralı telefonunuza gelen güvenlik kodunu giriniz.');
                        jQuery('#tf2Codealert').html('*Lütfen '+obje.phone+' numaralı telefonunuza gelen '+obje.refno+' referans numaralı güvenlik kodunu giriniz.');
                        document.getElementById("tf2Code").focus();
                        jQuery('#sendCode').prop('disabled', true);
                    } else {
                        if (obje.status == 'error' && obje.state == 1){
                            alert(obje.data + ' Lütfen site yöneticisi ile iletişime geçin.');
                        } else if(obje.status == 'error' && obje.state == 2) {
                            alert(obje.data);
                        }else if(obje.status == 'error' && obje.state == 4){
                            alert(obje.data);
                        }else if(obje.status == 'error' && obje.state == 5){
                            alert(obje.data);
                        } else {
                            alert('Bilinmeyen bir hata oluştu. GSM numarası girdiğinize emin olun. Sorunun devam etmesi halinde lütfen site yöneticisi ile iletişime geçin.');
                        }
                    }
                });
            }
            </script>
        <?php
    }

    add_action( 'wp_ajax_netgsm_sendtf2SMS', 'netgsm_sendtf2SMS' );
    function netgsm_sendtf2SMS(){
        $authKey = rand(10000, 99999);
        $refno = substr(md5(uniqid()),0,5);
        $phone = sanitize_text_field($_POST['phone']);
        ltrim($phone, '0');
        $replace = new ReplaceFunction();

        $phone_control = sanitize_text_field(get_option('netgsm_tf2_auth_register_phone_control'));
        if($phone_control == 1){
            $args = array (
                'order' => 'DESC',
                'orderby' => 'ID',
                'number'=>1,    //limit
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'billing_phone',
                        'value'   => trim($_POST['phone'], '0'),
                        'compare' => 'LIKE'
                    )
                )
            );
            $query = new WP_User_Query($args);
            $results = $query->get_results();
            if (is_array($results) && $results != null && isset($results[0])){  // kayıtlı numara var
                $phone_warning_text = sanitize_text_field(get_option('netgsm_tf2_auth_register_phone_warning_text'));
                $newVar = [];
                $oldVar = [];
                array_push($oldVar, '[telefon_no]');
                array_push($oldVar, '[ad]');
                array_push($oldVar, '[soyad]');
                array_push($oldVar, '[mail]');

                $newVar['telefon_no'] = sanitize_text_field($_POST['phone']);
                $newVar['ad'] = sanitize_text_field($_POST['first_name']);
                $newVar['soyad'] = sanitize_text_field($_POST['last_name']);
                $newVar['mail'] = sanitize_text_field($_POST['email']);

                $phone_warning_text = $replace->netgsm_replace_array($oldVar, $newVar, $phone_warning_text);
                echo json_encode(['status'=>'error','state'=>'5', 'data'=>$phone_warning_text]);
                die;
            } else {
            }
        }

        $netgsm_status  = esc_html(get_option("netgsm_status"));
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 ){
            if ($phone != '' && in_array(strlen($phone),[10,11])){
                $otpregister_control = esc_html(get_option("netgsm_tf2_auth_register_control"));
                if ($otpregister_control == 1){
                    $first_name = sanitize_text_field($_POST['first_name']);
                    $last_name = sanitize_text_field($_POST['last_name']);
                    $email = sanitize_text_field($_POST['email']);

                    $code = get_post_meta(1, $phone.'_2fa', true);

                    date_default_timezone_set('Europe/Istanbul');
                    if ($code != ''){
                        $sendTime = get_post_meta(1, $phone.'_2fa_time', true);
                        $sendTime = strtotime($sendTime);
                        $now =  time();
                        $diff = $now - $sendTime;

                        $savedDiff  = esc_html(get_option("netgsm_tf2_auth_register_diff"));
                        if ($savedDiff == '' || is_int($savedDiff)){
                            $savedDiff = 180;
                        }
                        if ($diff > $savedDiff * 60) {
                            update_post_meta( 1, $phone.'_2fa', $authKey );
                            update_post_meta( 1, $phone.'_2fa_time',  date('Y-m-d H:i:s', time()) );
                            update_post_meta( 1, $phone.'_2fa_ref',  $refno );
                        } else {
                            $savedRefNo = get_post_meta(1, $phone.'_2fa_ref', true);
                            echo json_encode(['status'=>'error','state'=>4, 'data'=>'Daha önce '.$phone.' numarasına '.$savedRefNo.' referans numaralı doğrulama kodu gönderilmiş. Lütfen gönderilmiş güvenlik kodunu girin.'.' Gönderme zamanı: '.date('d.m.Y H:i', $sendTime), 'phone'=>$phone]);
                            die;
                        }
                    } else {
                        add_post_meta(1, $phone.'_2fa', $authKey, '');
                        add_post_meta(1, $phone.'_2fa_time', date('Y-m-d H:i:s', time()));
                        add_post_meta(1, $phone.'_2fa_ref', $refno);
                    }

                    $data  = array(
                            'first_name'=> $first_name,
                            'last_name'=> $last_name,
                            'phone'=>$phone,
                            'user_email' =>$email,
                            'otpcode'=> $authKey,
                            'refno' => $refno,
                            'message'=> (sanitize_text_field(get_option("netgsm_tf2_auth_register_text")))
                    );
                    $message = $replace->netgsm_replace_twofactorauth_text($data);
                    $message = $replace->netgsm_replace_date($message);

                    $netgsm = $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));

                    $result = $netgsm->sendOTPSMS($phone, $message);

                    if ($result['kod'] == '00'){
                        echo json_encode(['status'=>'success', 'data'=>'Doğrulama gönderildi.', 'phone'=>$phone, 'refno'=>$refno]);
                    } else {
                        update_post_meta( 1, $phone.'_2fa_time',  '1970-01-01 12:12:12' );
                        echo json_encode(['status'=>'error', 'state'=>'1', 'data'=>'Doğrulama kodu gönderilemedi. '.$result->mesaj]);
                    }
                } else {
                    echo json_encode(['status'=>'error','state'=>'3', 'data'=>'OTP kontrol açık değil.']);
                }
            } else {
                echo json_encode(['status'=>'error','state'=>'2', 'data'=>'Telefon numarası hatalı']);
            }
        }
    }

    add_action('wp_enqueue_scripts', 'netgsm_admin_scripts');
    add_action('wp_ajax_nopriv_netgsm_sendtf2SMS', 'netgsm_sendtf2SMS');
    function netgsm_admin_scripts() {
        $netgsm_status  = esc_html(get_option("netgsm_status"));
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 ){
            $otpregister_control = esc_html(get_option("netgsm_tf2_auth_register_control"));
            if ($otpregister_control == 1 || get_option('netgsm_asistan')=='1'){
                wp_enqueue_script( 'script', plugins_url('ajax.js', dirname(__FILE__).'/lib/js/1/'), array('jquery'), '1.0', true );
                wp_localize_script('script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

            }
        }
    }

    add_filter( 'woocommerce_process_registration_errors', 'netgsm_custom_registration_errors', 10, 1 ); //telefon numarası girilmemişse
    function netgsm_custom_registration_errors( $validation_error) {
        if(isset($_POST['fname']) && isset($_POST['lname'])){
            return $validation_error;
        }

        $netgsm_status  = esc_html(get_option("netgsm_status"));
        $billing_phone  = sanitize_text_field($_POST['billing_phone']);
        $first_name     = sanitize_text_field($_POST['first_name']);
        $last_name      = sanitize_text_field($_POST['last_name']);
        $control        = netgsm_newcustomer_control();

        $netgsm_status  = esc_html(get_option("netgsm_status"));
        $otpstatus = false;
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 ) {
            $otpregister_control = esc_html(get_option("netgsm_tf2_auth_register_control"));
            if ($otpregister_control == 1) {
                $otpstatus = true;
            }
        }

        $tf2Code = '';
        $code = '';
        if ($otpstatus){
            $tf2Code        = sanitize_text_field($_POST['tf2Code']);
            $code = get_post_meta(1, $billing_phone.'_2fa', true);
        }
        $netgsm_phonenumber_zero  = esc_html(get_option("netgsm_phonenumber_zero1"));
        $delete = true;
        if($code != $tf2Code && $otpstatus==true ){
            $validation_error->add('tf2Code_error', __('<strong></strong>Doğrulama kodunu yanlış girdiniz!', 'mydomain'));
            $delete = false;
        } else {
            if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 && $control!=0)
            {
                if(isset($first_name) && empty($first_name) || trim($first_name) == ''){
                    $validation_error->add('first_name_error', __('<strong></strong>Adınızı giriniz.', 'mydomain'));
                    $delete = false;
                }
                if(isset($last_name) && empty($last_name) || trim($last_name) == ''){
                    $validation_error->add('last_name_error', __('<strong></strong>Soyadınızı giriniz.', 'mydomain'));
                    $delete = false;
                }
                if(isset($billing_phone) && empty($billing_phone) || trim($billing_phone) == ''){
                    $validation_error->add('billing_phone_error', __('<strong></strong>Telefon numaranızı giriniz.', 'mydomain'));
                    $delete = false;
                }
                if ($netgsm_phonenumber_zero == 1){
                    if (substr($billing_phone, 0, 1) != 0){
                        $validation_error->add('billing_phone_error', __('<strong></strong>Telefon numaranızın başında sıfır olmalıdır.', 'mydomain'));
                        $delete = false;
                    }
                }
            }
        }
        if ($delete = true){
            delete_post_meta(1, $billing_phone.'_2fa', $code);
            delete_post_meta(1, $billing_phone.'_2fa_time');
            delete_post_meta(1, $billing_phone.'_2fa_ref');
        }

        return $validation_error;
    }

    add_action( 'woocommerce_created_customer', 'netgsm_newcustomer', 10, 3 );
    function netgsm_newcustomer($customer_id){

        $newuser1       = esc_html(get_option("netgsm_newuser_to_admin_control"));
        $newuser2       = esc_html(get_option("netgsm_newuser_to_customer_control"));
        $newuser3       = esc_html(get_option("netgsm_rehber_control"));
        $control        = netgsm_newcustomer_control();
        $billing_phone  = "";
        $billing_phone  = sanitize_text_field($_POST['billing_phone']);
        $email = sanitize_text_field($_POST['email']);

        if (isset($_POST['first_name'])){
            $first_name= sanitize_text_field($_POST['first_name']);
        }else {
            $first_name= sanitize_text_field($_POST['billing_first_name']);
        }

        if (isset($_POST['last_name'])){
            $last_name      = sanitize_text_field($_POST['last_name']);
        }else {
            $last_name      = sanitize_text_field($_POST['billing_last_name']);
        }

        $netgsm_status  = esc_html(get_option("netgsm_status"));
        $replace        = new ReplaceFunction();


        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 && $control!=0){
            update_user_meta($customer_id, 'billing_phone', $billing_phone);
            update_user_meta($customer_id, 'first_name', $first_name);
            update_user_meta($customer_id, 'last_name', $last_name);



            if (get_option("netgsm_iys_check_control")=='1'){
                add_user_meta($customer_id, 'netgsm_kvkk_check', sanitize_text_field($_POST['netgsm_iys']) );

                if(substr($billing_phone , 0 , 1)=="0"){
                    $phone = "+9".$billing_phone;
                }else if(substr($billing_phone , 0 , 2)=="90"){
                    $phone = "+".$billing_phone;
                }else if(substr($billing_phone , 0 , 3)=="+90"){
                    $phone = $billing_phone;
                }else{
                    $phone = "+90".$billing_phone;
                }

                if ($_POST['netgsm_iys']==1 && (get_option('netgsm_brandcode_control')) == 1 && (get_option('netgsm_brandcode_text'))!=''){
                    $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")));
                    $recipient_type = sanitize_text_field(get_option('netgsm_recipient_type'));
                    date_default_timezone_set('Europe/Istanbul');

                    if (!empty(type_array())){

                        if(sanitize_text_field(get_option('netgsm_recipient_type'))=="1"){
                            $recipient_type = "BIREYSEL";
                            $response = $response = $netgsm->iysadd(iys_phonecontrol($billing_phone), "", date('Y-m-d H:i:s'), sanitize_text_field(get_option('netgsm_brandcode_text')), $recipient_type, type_array());
                        }else if(sanitize_text_field(get_option('netgsm_recipient_type'))=="2"){
                            $recipient_type = "TACIR";
                            $response = $response = $netgsm->iysadd(iys_phonecontrol($billing_phone), $email, date('Y-m-d H:i:s'), sanitize_text_field(get_option('netgsm_brandcode_text')), $recipient_type, type_array());
                        }else{
                            return;
                        }
                    }
                }
            }

            $custom_settings_admin = netgsm_getCustomSetting('netgsm_newuser_to_admin_json', '_timecondition');
            $custom_settings_customer = netgsm_getCustomSetting('netgsm_newuser_to_customer_json', '_timecondition');

            /*if(empty($first_name)){
                update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']), '');
            }
            if (empty($last_name)){
                update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']), '');
            }*/
            $userinfo       = get_userdata($customer_id);
            if (isset($newuser1) && !empty($newuser1) && $newuser1 == 1) {   //admine mesaj
                $phone      = esc_html(get_option('netgsm_newuser_to_admin_no'));
                $data       = array('first_name'=> $first_name,   'last_name'=> $last_name,
                                    'user_login'=> $userinfo->user_login,   'phone'=>$billing_phone, 'user_email' =>$userinfo->user_email,
                                    'message'=> (get_option('netgsm_newuser_to_admin_text')));
                $message    = $replace->netgsm_replace_newuser_to_text($data);
                $message = $replace->netgsm_replace_date($message);

                netgsm_sendSMS_oneToMany($phone, $message, ['startDate'=>$custom_settings_admin]);
            }
            if (isset($newuser2) && !empty($newuser2) && $newuser2 == 1) {   //müşteriye mesaj
                $data       = array('first_name'=> $first_name,   'last_name'=> $last_name,
                                    'user_login'=> $userinfo->user_login,   'phone'=>$billing_phone, 'user_email' =>$userinfo->user_email,
                                    'message'=> (sanitize_text_field(get_option('netgsm_newuser_to_customer_text'))));
                $message    = $replace->netgsm_replace_newuser_to_text($data);
                $message = $replace->netgsm_replace_date($message);
                netgsm_sendSMS_oneToMany($billing_phone, $message, ['startDate'=>$custom_settings_customer]);
            }
            if (isset($newuser3) && !empty($newuser3) && $newuser3 == 1) {   //rehbere kayıt
                netgsm_add_contact($customer_id);
            }
        }
    }

    add_action('lmfwc_event_post_order_license_keys','netgsm_new_licance', 10,1);
    function netgsm_new_licance($id)
    {
        $netgsm_status = esc_html(get_option("netgsm_status"));
        $netgsm_licence_key_to_meta  = esc_html(get_option("netgsm_licence_key_to_meta"));
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 && $netgsm_licence_key_to_meta == 1 ) {
            $licences = [];
            foreach ($id['licenses'] as $item) {
                $licence = apply_filters('lmfwc_decrypt', $item->getLicenseKey());
                array_push($licences, $licence);
            }
            $keys = implode(' , ', $licences);

            add_post_meta($id['orderId'], '_licence_keys', $keys, '');
        }
    }

    //payment_complete ve thankyou kancalarının çalışmaması durumlarında özel kanca çalıştır(ayarlanmalı.)
    $netgsm_new_order_custom_action = netgsm_getCustomSetting('netgsm_neworder_to_admin_json', '_otherAction');

    if ( $netgsm_new_order_custom_action != '' ){
        add_action( $netgsm_new_order_custom_action, 'netgsm_new_order_custom_action', 10,3 );
    } else {
        add_action( 'woocommerce_payment_complete', 'netgsm_new_order_send_sms', 10,1 );   //yeni siparişte sms gönder
        add_action( 'woocommerce_thankyou', 'netgsm_new_order_send_sms', 10,1 );   //yeni siparişte sms gönder
    }
    function netgsm_new_order_custom_action( $order_id, $data, $order){
        netgsm_new_order_send_sms($order_id);
    }

    add_action( 'wp_insert_post', 'netgsm_new_order_admin_panel', 10, 1 );
    function netgsm_new_order_admin_panel( $post_id ) {
        if (function_exists('wc_get_order')){
            $order = wc_get_order( $post_id );
            if (method_exists((object)$order, 'get_billing_phone') && $order->get_billing_phone() != '') {
                $custom_settings_admin = netgsm_getCustomSetting('netgsm_neworder_to_admin_json', '_addOrderAdminPanel');
                $custom_settings_customer = netgsm_getCustomSetting('netgsm_neworder_to_customer_json', '_addOrderAdminPanel');
                if($custom_settings_admin == 1){
                    netgsm_new_order_send_sms($post_id, 1);
                }
                if($custom_settings_customer == 1){
                    netgsm_new_order_send_sms($post_id, 2);
                }
            }
        }
    }

    function netgsm_new_order_send_sms($order_id, $adminpanel = 0){
        if( get_post_meta( $order_id, '_new_order_netgsm', true ) && $adminpanel==0 ){   // eğer daha önce bu sipariş zaten oluşmuşsa bu fonksiyon dursun
            return; // sipariş zaten alınmış.
        }
        add_post_meta($order_id, '_new_order_netgsm', 'yes', '');


        if(function_exists('wc_get_order')) {
            $order2 = wc_get_order( $order_id );
            $items = $order2->get_items();

            $products_info ="";
            $products_info_kdv ="";
            $prouducts_name ="";
            foreach ($items as $item) {
                $products_info .= $item->get_name()."(".$item->get_subtotal().'TLx'.$item->get_quantity()."), ";
            }
            foreach ($items as $item) {
                if ($item->get_subtotal_tax()=="0"){
                    $products_info_kdv .= $item->get_name()."(".$item->get_subtotal().'TLx'.$item->get_quantity()."), ";
                }else{
                    $products_info_kdv .= $item->get_name()."(".$item->get_subtotal().'TL+KDV x'.$item->get_quantity()."), ";
                }
            }
            foreach ($items as $item) {
                $prouducts_name.= $item->get_name();
            }
            $products_info = rtrim($products_info,' ,');
            $products_info_kdv = rtrim($products_info_kdv,' ,');
            $prouducts_name = rtrim($prouducts_name,' ,');
        }
        else{
            $products_info="";
            $prouducts_name="";
            $products_info_kdv="";
        }

        $neworder1       = esc_html(get_option("netgsm_neworder_to_admin_control"));
        $neworder2       = esc_html(get_option("netgsm_neworder_to_customer_control"));
        $control         = netgsm_neworder_control();
        $netgsm_status   = esc_html(get_option("netgsm_status"));
        $order           = new WC_Order( $order_id );
        $userinfo        = get_userdata($order->customer_id);
        $replace         = new ReplaceFunction();
        $custom_settings_admin = netgsm_getCustomSetting('netgsm_neworder_to_admin_json', '_timecondition');
        $custom_settings_customer = netgsm_getCustomSetting('netgsm_neworder_to_customer_json', '_timecondition');
        $custom_settings_customer_private_phone_key = netgsm_getCustomSetting('netgsm_neworder_to_customer_json', '_custom_phone_key');



        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1 && $control!=0) {
            if (isset($neworder1) && !empty($neworder1) && $neworder1 == 1 && (in_array($adminpanel, [0,1]))) {   //admine mesaj
                $phone      = esc_html(get_option('netgsm_neworder_to_admin_no'));
                $username = explode('@', $order->billing_email);

                $data   = array(
                            'order_id'=>$order_id,
                            'total'=>$order->total,
                            'first_name'=> $order->billing_first_name,
                            'last_name'=> $order->billing_last_name,
                            'user_login'=> $userinfo->user_login,
                            'phone'=>$order->billing_phone,
                            'user_email' =>$order->billing_email,
                            'items' => $products_info,
                            'items_kdv' => $products_info_kdv,
                            'items_name'=> $prouducts_name,
                            'message'=> (sanitize_text_field(get_option('netgsm_neworder_to_admin_text'))),
                );
                $message = $replace->netgsm_replace_neworder_to_text($data);
                $message = $replace->netgsm_replace_order_meta_datas($order, $message);
                $metadatas = get_post_meta($order_id);
                $message = $replace->netgsm_replace_order_meta_datas2($metadatas, $message);
                $message = $replace->netgsm_replace_order_add_datas($order, $message, 'data', '[data:');
                $message = $replace->netgsm_replace_date($message);
                netgsm_sendSMS_oneToMany($phone, $message, ['startDate'=>$custom_settings_admin]);
            }

            if (isset($neworder2) && !empty($neworder2) && $neworder2 == 1  && (in_array($adminpanel, [0,2]))) {   //müşteriye mesaj
                $order = wc_get_order( $order_id );



                $phone_key = 'billing_phone';
                if ($custom_settings_customer_private_phone_key != ''){
                    $phone_key = $custom_settings_customer_private_phone_key;
                }
                $sendsmsphone = '';
                if (isset($order->{$phone_key})){
                    $sendsmsphone = $order->{$phone_key};
                }
                if ($sendsmsphone == '' || !is_numeric($sendsmsphone)){
                    $sendsmsphone = $order->billing_phone;
                }
                $data   = array(
                            'order_id'=>$order_id,
                            'total'=>$order->total,
                            'first_name'=> $order->billing_first_name,
                            'last_name'=> $order->billing_last_name,
                            'user_login'=> $userinfo->user_login,
                            'phone'=> $sendsmsphone,
                            'user_email' =>$order->billing_email,
                            'items' => $products_info,
                            'items_kdv' => $products_info_kdv,
                            'items_name'=> $prouducts_name,
                            'message'=> (sanitize_text_field(get_option('netgsm_neworder_to_customer_text')))
                );
                $message    = $replace->netgsm_replace_neworder_to_text($data);
                $message = $replace->netgsm_replace_order_meta_datas($order, $message);
                $metadatas = get_post_meta($order_id);
                $message = $replace->netgsm_replace_order_meta_datas2($metadatas, $message);
                $message = $replace->netgsm_replace_order_add_datas($order, $message, 'data', '[data:');
                $message = $replace->netgsm_replace_date($message);
                netgsm_sendSMS_oneToMany($sendsmsphone, $message,['startDate'=>$custom_settings_customer]);
            }
        }
    }

                                                        //120---            cancelled---            completed
    function netgsm_order_status_changed( $this_get_id, $this_status_transition_from, $this_status_transition_to, $instance ) {
        netgsm_order_status_changed_sendSMS($this_get_id, 'netgsm_order_status_text_wc-'.$this_status_transition_to, $this_status_transition_to);

    };
    add_action( 'woocommerce_order_status_changed', 'netgsm_order_status_changed', 10, 4 );

    add_action('woocommerce_order_status_cancelled', 'netgsm_order_status_cancelled');
    function netgsm_order_status_cancelled($order_id)
    {
        $control         = esc_html(get_option("netgsm_order_refund_to_admin_control"));
        $message         = sanitize_text_field((get_option("netgsm_order_refund_to_admin_text")));
        $phones          = esc_html(get_option("netgsm_order_refund_to_admin_no"));
        $netgsm_status   = esc_html(get_option("netgsm_status"));
        $replace         = new ReplaceFunction();
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1) {
            if (isset($control) && !empty($control) && $control == 1) {
                if (isset($message) && !empty($message)) {
                    $custom_settings_cancelled_timecondition = netgsm_getCustomSetting('netgsm_order_refund_to_admin_json', '_timecondition');

                    $order           = new WC_Order( $order_id );
                    $userinfo        = get_userdata($order->customer_id);
                    $data       = array('order_id'=>$order_id, 'first_name'=> $userinfo->first_name,   'last_name'=> $userinfo->last_name,
                        'user_login'=> $userinfo->user_login,   'phone'=> $order->billing_phone, 'user_email' =>$userinfo->user_email,
                        'message'=> $message);
                    $message    = $replace->netgsm_replace_order_status_changes($data);
                    $message = $replace->netgsm_replace_order_meta_datas($order, $message);
                    $metadatas = get_post_meta($order_id);
                    $message = $replace->netgsm_replace_order_meta_datas2($metadatas, $message);
                    $message = $replace->netgsm_replace_date($message);
                    netgsm_sendSMS_oneToMany($phones, $message, ['startDate'=>$custom_settings_cancelled_timecondition]);
                }
            }
        }
       // netgsm_order_status_changed_sendSMS($order_id, 'netgsm_order_status_text_wc-cancelled');
    }

    add_filter( 'woocommerce_customer_save_address', 'netgsm_customereditaddres', 10, 2 );  //fatura adresi değişip telefon numnarası girildiyse rehbere ekler
    function netgsm_customereditaddres($user_id){
        netgsm_add_contact($user_id);
    }

    function netgsm_sendSMS_oneToMany($phone, $message, $settings=[])
    {
        $replace = new ReplaceFunction();
        $json = array();
        if(isset($phone) && isset($message) && !empty($phone) && !empty($message)){
            $netgsm = $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")), sanitize_text_field(get_option('netgsm_input_smstitle')), sanitize_text_field(get_option("netgsm_trChar")));
            if (isset($settings['startDate']) && $settings['startDate'] != '' && is_numeric(intval($settings['startDate']))){
                $netgsm->setStartDate(date('Y-m-d H:i', current_time( 'timestamp' ) + ($settings['startDate']*60)));
            }
            $json = $netgsm->sendSMS($replace->netgsm_spaceTrim($phone), $message, "0");
        }
        else {
            $json['durum'] = '0';
            $json['mesaj'] = 'Sms gönderimi başarısız.';
        }
        return json_encode($json);
    }

    function netgsm_neworder_control()
    {
        $neworder1       = esc_html(get_option("netgsm_neworder_to_admin_control"));
        $neworder2       = esc_html(get_option("netgsm_neworder_to_customer_control"));
        $control         = 0;
        if(isset($neworder1) && !empty($neworder1) && $neworder1==1) {
            $control=1;
        }
        elseif(isset($neworder2) && !empty($neworder2) && $neworder2==1) {
            $control=2;
        }
        return $control;
    }

    function netgsm_add_contact($customer_id)
    {
        $grupcontrol    = esc_html(get_option('netgsm_rehber_control'));
        $groupname      = esc_html(get_option('netgsm_rehber_groupname'));
        $userinfo       = get_userdata($customer_id);
        if(isset($grupcontrol) && !empty($grupcontrol) && $grupcontrol==1) {
            $netgsm = new Netgsmsms(sanitize_text_field(get_option("netgsm_user")), sanitize_text_field(get_option("netgsm_pass")));
            if($userinfo->first_name=="")
                $firstname = "nickname";
            else
                $firstname = $userinfo->first_name;

            if($userinfo->last_name=="")
                $lastname = $userinfo->nickname;
            else
                $lastname = $userinfo->last_name;
            $replace = new ReplaceFunction();
            $netgsm->addContact($firstname, $lastname, $replace->netgsm_spaceTrim($userinfo->billing_phone), $groupname);
        }
    }

    function netgsm_order_status_changed_sendSMS($order_id, $text, $this_status_transition_to)
    {
        $control         = esc_html(get_option("netgsm_orderstatus_change_customer_control"));
        $message         = sanitize_text_field(get_option($text));
        $netgsm_status   = esc_html(get_option("netgsm_status"));
        $replace         = new ReplaceFunction();


        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1) {
            if (isset($control) && !empty($control) && $control == 1) {
                if (isset($message) && !empty($message)) {
                    $order           = new WC_Order( $order_id );
                    $userinfo        = get_userdata($order->customer_id);
                    $trackingCode = '';
                    $trackingCompany = '';
                    foreach ($order->meta_data as $meta_datum) {
                        if ($meta_datum->key == 'kargo_takip_no'){
                            $trackingCode = $meta_datum->value;
                        }
                        if ($meta_datum->key == 'kargo_firmasi'){
                            $trackingCompany = $meta_datum->value;
                        }
                    }
                    if ((isset($userinfo->user_login) && $userinfo->user_login != '')){
                        $user_login = $userinfo->user_login;
                    } else {
                        $user_login = $order->shipping_first_name.$order->shipping_last_name;
                    }


                    $custom_settings_changed_timecondition = netgsm_getCustomSetting('netgsm_order_status_text_wc-'.$this_status_transition_to.'_json', '_timecondition');
                    $custom_settings_customer_private_phone_key = netgsm_getCustomSetting('netgsm_order_status_text_wc-'.$this_status_transition_to.'_json', '_custom_phone_key');

                    $phone_key = 'billing_phone';
                    if ($custom_settings_customer_private_phone_key != ''){
                        $phone_key = $custom_settings_customer_private_phone_key;
                    }
                    $sendsmsphone='';
                    if (isset($order->{$phone_key})){
                        $sendsmsphone = $order->{$phone_key};
                    }
                    if ($sendsmsphone == '' || !is_numeric($sendsmsphone)){
                        $sendsmsphone = $order->billing_phone;
                    }

                    $data       = array('order_id'=>$order_id, 'first_name'=> $order->shipping_first_name,   'last_name'=> $order->shipping_last_name,
                        'user_login'=> $user_login,   'phone'=> $sendsmsphone, 'user_email' =>$order->billing_email,
                        'message'=> $message, 'trackingCompany'=>$replace->netgsm_replace_shipping_company($trackingCompany), 'trackingCode'=>$trackingCode);
                    $message    = $replace->netgsm_replace_order_status_changes($data);
                    $message = $replace->netgsm_replace_order_meta_datas($order, $message);
                    $metadatas = get_post_meta($order_id);
                    $message = $replace->netgsm_replace_order_meta_datas2($metadatas, $message);
                    $message = $replace->netgsm_replace_order_add_datas($order, $message, 'data', '[data:');
                    $message = $replace->netgsm_replace_date($message);
                    netgsm_sendSMS_oneToMany($sendsmsphone, $message, ['startDate'=>$custom_settings_changed_timecondition]);
                }
            }
        }
    }

    add_action( 'woocommerce_new_order_note_data', 'netgsm_new_order_note_data', 10, 2 );
    function netgsm_new_order_note_data( $args, $args2 ) {
        //type 0  == note1 = Özel Not -- netgsm_newnote1_to_customer_control -- netgsm_newnote1_to_customer_text
        //type  1 == note2 = Müşteriye not --

        $order_id = $args['comment_post_ID'];
        $type = $args2['is_customer_note'];

        $note = $args['comment_content'];

        $status_note1 = esc_html(get_option("netgsm_newnote1_to_customer_control"));
        $status_note2 = esc_html(get_option("netgsm_newnote2_to_customer_control"));

        if (!empty($status_note1) && $status_note1==1 && $type == 0){   //özel sms
            $customermessage = esc_html(get_option("netgsm_newnote1_to_customer_text"));
            $options = 'netgsm_newnote1_to_customer_json';
            netgsm_new_order_note_sendSMS($order_id, $type, $note, $customermessage, $options);
        }

        if (!empty($status_note2) && $status_note2==1 && $type == 1){ //müşteriye sms
            $customermessage = esc_html(get_option("netgsm_newnote2_to_customer_text"));
            $options = 'netgsm_newnote2_to_customer_json';
            netgsm_new_order_note_sendSMS($order_id, $type, $note, $customermessage, $options);
        }
        return $args;
    }


function netgsm_new_order_note_sendSMS($order_id, $note_type, $note, $customermessage, $optionskey)
    {
        $netgsm_status   = esc_html(get_option("netgsm_status"));
        $replace         = new ReplaceFunction();
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1) {
            if (isset($customermessage) && !empty($customermessage)) {
                $order           = new WC_Order( $order_id );
                $userinfo        = get_userdata($order->customer_id);

                if ((isset($userinfo->user_login) && $userinfo->user_login != '')){
                    $user_login = $userinfo->user_login;
                } else {
                    $user_login = $order->shipping_first_name.$order->shipping_last_name;
                }

                $custom_settings_changed_timecondition = netgsm_getCustomSetting($optionskey, '_timecondition');
                $custom_settings_customer_private_phone_key = netgsm_getCustomSetting($optionskey, '_custom_phone_key');

                $phone_key = 'billing_phone';
                if ($custom_settings_customer_private_phone_key != ''){
                    $phone_key = $custom_settings_customer_private_phone_key;
                }
                $sendsmsphone='';
                if (isset($order->{$phone_key})){
                    $sendsmsphone = $order->{$phone_key};
                }
                if ($sendsmsphone == '' || !is_numeric($sendsmsphone)){
                    $sendsmsphone = $order->billing_phone;
                }

                $data       = array('order_id'=>$order_id, 'first_name'=> $order->billing_first_name,   'last_name'=> $order->billing_last_name,
                    'user_login'=> $user_login,   'phone'=> $sendsmsphone, 'user_email' =>$order->billing_email, 'note'=> $note,
                    'message'=> $customermessage, 'total'=>$order->total);
                $message    = $replace->netgsm_replace_add_note($data);
                $message = $replace->netgsm_replace_date($message);
                netgsm_sendSMS_oneToMany($sendsmsphone, $message, ['startDate'=>$custom_settings_changed_timecondition]);
            }
        }
    }

    function netgsm_yaz($data, $file = 'cf7dosya1.txt'){
        touch($file);
        $dosya = fopen($file, 'w');
        fwrite($dosya, print_r($data, TRUE));
        fclose($dosya);
    }


    /*
     * WAITLIST STOK İŞLEMLERİ START
     */

    /*
     * Normal ürünlerin stok değişimini dinleyerek stoğa girmesi durumunda waitlist listesine SMS gönderir.
     */
    function netgsm_product_set_stock( $product ) {
        netgsm_set_stock_trigger($product);
    };
    add_action( 'woocommerce_product_set_stock', 'netgsm_product_set_stock', 10, 1 );

    /*
     * Varyasyonlu ürünlerin stok değişimini dinleyerek stoğa girmesi durumunda waitlist listesine SMS gönderir.
     */
    function netgsm_product_set_stock_variation($product){
        netgsm_set_stock_trigger($product);
    }
    add_action( 'woocommerce_variation_set_stock', 'netgsm_product_set_stock_variation', 10, 1 );

    /*
     * Stok değişikliğini dinleyerek bekleme listesine sms gönderimini tetikleyecek fonksiyon
     */
    function netgsm_set_stock_trigger($product)
    {
        $productId  = $product->get_data()['id'];
       // $limit = wc_get_low_stock_amount( $product );
       // $new_stock = $product->get_changes()['stock_quantity'];
       // $old_stock = $product->get_data()['stock_quantity'];

        if (isset($product->get_changes()['stock_status']) && $product->get_changes()['stock_status']=='instock'){
            if (class_exists('Pie_WCWL_Waitlist')) {
                $waitlist = new Pie_WCWL_Waitlist($product);
                $users = $waitlist->waitlist;
                foreach ($users as $customerId => $date) {
                    netgsm_waitlist_send_sms($customerId, $productId);
                }
            }
        }
    }

    /*
     * waitlist listesine mail gönderildiğinde SMS de gönderir.
     */
    function netgsm_waitlist_push($customerId, $productId)
    {
        netgsm_waitlist_send_sms($customerId, $productId);
    }
    add_action( 'wcwl_mailout_send_email', 'netgsm_waitlist_push', 10, 2 );

    /*
     * Bekleme listelerine SMS gönderimi sağlayan fonksiyondur.
     */
    function netgsm_waitlist_send_sms($customerId,$productId)
    {
        $control         = esc_html(get_option("netgsm_product_waitlist1_control"));
        $message         = esc_html(get_option("netgsm_product_waitlist1_text"));;
        $netgsm_status   = esc_html(get_option("netgsm_status"));
        $replace         = new ReplaceFunction();
        $product =  wc_get_product($productId);
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1) {
            if (isset($control) && !empty($control) && $control == 1) {
                if (isset($message) && !empty($message)) {
                    $customer = new WC_Customer($customerId);
                    $product =  wc_get_product($productId);


                    $custom_settings_changed_timecondition = netgsm_getCustomSetting('netgsm_product_waitlist1_json', '_timecondition');
                    $custom_settings_customer_private_phone_key = netgsm_getCustomSetting('netgsm_product_waitlist1_json', '_custom_phone_key');

                    $phone_key = 'billing_phone';
                    if ($custom_settings_customer_private_phone_key != ''){
                        $phone_key = $custom_settings_customer_private_phone_key;
                    }

                    $sendsmsphone='';
                    if ($customer->{$phone_key} != ''){
                        $sendsmsphone = $customer->{$phone_key};
                    }

                    if ($sendsmsphone == '' || !is_numeric($sendsmsphone)){
                        $sendsmsphone = $customer->billing_phone;
                    }

                    $newVar = [];
                    $oldVar = [];
                    array_push($oldVar, '[uye_adi]');
                    array_push($oldVar, '[uye_soyadi]');
                    array_push($oldVar, '[uye_telefonu]');
                    array_push($oldVar, '[uye_epostasi]');
                    array_push($oldVar, '[kullanici_adi]');
                    array_push($oldVar, '[urun_kodu]');
                    array_push($oldVar, '[urun_adi]');
                    array_push($oldVar, '[stok_miktari]');

                    $newVar['uye_adi'] = sanitize_text_field($customer->first_name);
                    $newVar['uye_soyadi'] = sanitize_text_field($customer->last_name);
                    $newVar['uye_telefonu'] = sanitize_text_field($customer->billing_phone);
                    $newVar['uye_epostasi'] = sanitize_text_field($customer->email);
                    $newVar['kullanici_adi'] = sanitize_text_field($customer->display_name);
                    $newVar['urun_kodu'] = sanitize_text_field($product->sku);
                    $newVar['urun_adi'] = sanitize_text_field($product->name);
                    $newVar['stok_miktari'] = sanitize_text_field($product->stock);

                    $message = $replace->netgsm_replace_array($oldVar, $newVar, $message);

                    $message = $replace->netgsm_meta_data_replace($customer->data, $message, '[meta_user:');
                    $message = $replace->netgsm_meta_data_replace($product->get_data(), $message, '[meta_product:');
                    $message = $replace->netgsm_replace_date($message);
                    netgsm_sendSMS_oneToMany($sendsmsphone, $message, ['startDate'=>$custom_settings_changed_timecondition]);
                }
            }
        }
    }

    /*
     * WAITLIST STOK İŞLEMLERİ END
     */

    //contact form 7 açık seçeneklerin smsleri gönderilir. sadece başarılı yanıt aldığında gönderiyor.
    add_action( 'wpcf7_mail_sent', 'netgsm_cf7_form_submit', 10, 1 );
    function netgsm_cf7_form_submit( $contact_form ) {
        $title = $contact_form->title;
        $submission = WPCF7_Submission::get_instance();
        $phone_customer = '';
        $phone_admin = esc_html(get_option('netgsm_cf7_to_admin_no'));

        if ( $submission ) {
            $posted_data = $submission->get_posted_data();
        }
        $form_id = 0;
        if(isset($posted_data['_wpcf7']) && $posted_data['_wpcf7']>0){
            $form_id = $posted_data['_wpcf7'];
        } else {
            $form_id = $contact_form->id;
        }

        $netgsm_status   = esc_html(get_option("netgsm_status"));
        if(isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status==1) {
            if (isset($posted_data)) {
                $customer_control = esc_html(get_option('netgsm_cf7_success_customer_control'));
                if (isset($customer_control) && !empty($customer_control) && $customer_control == 1) {
                    $customer_message = (sanitize_text_field(get_option('netgsm_cf7_list_text_success_customer_' . $form_id)));
                    $phone_customer = $posted_data['telephone'];
                    if ($phone_customer != '') {
                        if ($customer_message != '') {
                            $replace = new ReplaceFunction();
                            $message = $replace->netgsm_cf7_replace_all_var($posted_data, $customer_message);
                            $message = $replace->netgsm_replace_date($message);
                            netgsm_sendSMS_oneToMany($phone_customer, $message);
                        }
                    }
                }
                $admin_control = esc_html(get_option('netgsm_cf7_success_admin_control'));
                if (isset($admin_control) && !empty($admin_control) && $admin_control == 1) {
                    $admin_message = sanitize_text_field(get_option('netgsm_cf7_list_text_success_admin_' . $form_id));
                    if ($admin_message != '') {
                        $replace = new ReplaceFunction();
                        $message = $replace->netgsm_cf7_replace_all_var($posted_data, $admin_message);
                        $message = $replace->netgsm_replace_date($message);
                        netgsm_sendSMS_oneToMany($phone_admin, $message);
                    }
                }

                $contact_control = esc_html(get_option('netgsm_cf7_contact_control'));
                if (isset($contact_control) && !empty($contact_control) && $contact_control == 1) {
                    $groupname = sanitize_text_field(get_option('netgsm_cf7_list_contact_' . $form_id));
                    if ($groupname != '') {
                        $netgsm = new Netgsmsms(get_option("netgsm_user"), get_option("netgsm_pass"));
                        $firstname_key = sanitize_text_field(get_option('netgsm_cf7_list_contact_firstname_' . $form_id));
                        $lastname_key = sanitize_text_field(get_option('netgsm_cf7_list_contact_lastname_' . $form_id));
                        $other_keys = sanitize_text_field(get_option('netgsm_cf7_list_contact_other_' . $form_id));
                        $firstname = $posted_data[$firstname_key];
                        $lastname = $posted_data[$lastname_key];
                        $phone_number = $posted_data['telephone'];

                        $others = explode(';', $other_keys);

                        $tags = [];
                        foreach ($others as $other) {
                            $item = explode(':', $other);
                            $replace = new ReplaceFunction();
                            $val = $replace->netgsm_cf7_replace_all_var($posted_data, $item[1]);
                            $key = str_replace(['[',']'], "", $item[0]);
                            $tags[$key] = $val;
                        }

                        $netgsm->addContact($firstname, $lastname, $phone_number, $groupname, $tags);
                    }
                }

            }
        }
      //  die;
        return $posted_data;
    }

    /*
     * WC API için gerekli işlemler
     */
    add_filter( 'woocommerce_rest_api_get_rest_namespaces', 'woo_custom_api' );
    function woo_custom_api( $controllers ) {
        $controllers['wc/v3']['custom'] = 'WC_REST_Custom_Controller';
        $controllers['wc/v3']['test'] = 'WC_REST_Custom_Controller';

        return $controllers;
    }


    function type_array(){

        $iys_type = array();
        if (esc_html(get_option('netgsm_message'))== 1){
            array_push($iys_type,"MESAJ");
        }
        if (sanitize_text_field(get_option('netgsm_call'))=='1'){
            array_push($iys_type,"ARAMA");
        }
        if (sanitize_text_field(get_option('netgsm_email'))=='1'){
            array_push($iys_type,"EPOSTA");
        }
        return $iys_type;
    }

    function iys_phonecontrol($phone){
        if(substr($phone , 0 , 1)=="0"){
            $phone_edit = "+9".$phone;
        }else if(substr($phone , 0 , 2)=="90"){
            $phone_edit = "+".$phone;
        }else if(substr($phone , 0 , 3)=="+90"){
            $phone_edit = $phone;
        }else{
            $phone_edit = "+90".$phone;
        }
        return $phone_edit;
    }

add_action('wp_footer', 'netgsm_connection_button');
function netgsm_connection_button() {
    if(get_option('netgsm_asistan')=='1'){
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'style1', $plugin_url . 'lib/css/style.css' );
        wp_enqueue_style( 'font-awesome', $plugin_url . 'lib/fonts/css/font-awesome.min.css' );
        wp_enqueue_style( 'all', $plugin_url . 'lib/fonts/fonts/all.css' );

        if (sanitize_text_field(get_option('netgsm_asistan_netasistan'))=='1'){

            echo '<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"> 
      <label for="connect-menu" class="netasistan-logo"><img id="netasistan-altlogo" src="' . esc_url(plugins_url("netgsm/lib/image/netasistan-alt-logo.svg", dirname(__FILE__))) . '">
      </label><input type="checkbox" id="connect-menu" checked="false"/>';

            }
            echo '
            <div id="connection-form">
            <div class="portal-table">	   
                <div class="netasistan-header" align="left">
                    <img class="netasistan-headerlogo" src="<?php echo esc_url(plugins_url("netgsm/lib/image/netasistan-header-logo.svg", dirname(__FILE__))); ?>">				
                    <p class="netasistan-headertype">Yardım merkezine hoşgeldiniz.</p>
                    <i class="fa fa-times closeicon" id="connect-close"></i>
                </div>
            </div>
        </div>
        
                    
                <div class="netasistan_itemContent" id="netasistan-buttons">';

                if (sanitize_text_field(get_option('netgsm_asistan_message')) == '1') {
                    echo '<div class="btnItem" onclick="location.href=\'sms:' . esc_attr(get_option("netgsm_asistan_messagenumber")) . '\'">
                            <h6><i class="far fa-comment-alt squareIcon mesajBg"></i>Mesaj gönderin</h6>
                          </div>';
                }
                if (sanitize_text_field(get_option('netgsm_asistan_call')) == '1') {
                    echo '<div class="btnItem" onclick="location.href=\'tel:' . esc_attr(get_option("netgsm_asistan_callnumber")) . '\'">
                            <h6><i class="fas fa-mobile-alt squareIcon aramaBg"></i> Çağrı merkezimizi arayın</h6>
                          </div>';
                }
                if (sanitize_text_field(get_option('netgsm_asistan_email')) == '1') {
                    echo '<div class="btnItem" onclick="location.href=\'mailto:' . esc_attr(get_option('netgsm_asistan_emailaddress')) . '\'">						
                            <h6><i class="far fa-envelope-open squareIcon epostaBg"></i> E-posta gönderin </h6>
                          </div>';
                }
                if (sanitize_text_field(get_option('netgsm_asistan_whatsapp')) == '1') {
                    echo '<div class="btnItem" onclick="location.href=\'https://wa.me/' . esc_attr(get_option("netgsm_asistan_whatsappnumber")) . '\'">						
                            <h6><i class="fab fa-whatsapp squareIcon whatsappBg"></i> WhatsApp</h6>
                          </div>';
                }
                
            if (sanitize_text_field(get_option('netgsm_asistan_netasistan'))=='1'){
                $customer = WC()->session->get('customer');
                $customerx = new WC_Customer( $customer['id'] );
                $phone =netasistan_phonecontrol($customerx->billing['phone']);


                echo '<div class="btnItem" onclick="netasistanform_open()">						
                        <h6><i class="fas fa-phone-alt squareIcon siziarayalımBg" ></i> Sizi Arayalım</h6>
                    </div>';
            }
        echo '
			</div>
			
			<div class="netasistan_itemContent-input" id="netasistan-form">
			<form method="post">
            <h5 class="baslikinput">Sizi Arayalım</h5>
            <div class="netasistan-input-group">
                <input type="text" class="netasistan-form-element" placeholder="Ad" name="netasistan_name" id="netasistan_name" value="<?php echo esc_attr($customerx->first_name); ?>" maxlength="50" required>
            </div>
            <div class="netasistan-input-group">
                <input type="text" class="netasistan-form-element" placeholder="Soyad" name="netasistan_lastname" id="netasistan_lastname" value="<?php echo esc_attr($customerx->last_name); ?>" maxlength="50" required>
            </div>
            <div class="netasistan-input-group">						  
                <input type="number" class="netasistan-form-element" placeholder="Telefon" name="netasistan_number" id="netasistan_number" value="<?php echo esc_attr($phone); ?>" min="0" max="9"  maxlength="13" required>
            </div>
            <div class="netasistan-input-group">						  
                <input type="email" class="netasistan-form-element" placeholder="E-posta" name="netasistan_email" id="netasistan_email" value="<?php echo esc_attr($customerx->email); ?>" required>
            </div>
            <div class="netasistan-input-group">
                <input type="text" class="netasistan-form-element" placeholder="Konu" name="netasistan_header" id="netasistan_header" maxlength="70" required>
            </div>
            <div class="netasistan-input-group">
                <textarea class="netasistan-form-element" placeholder="Açıklama" name="netasistan_content" id="netasistan_content" rows="4" maxlength="500" required></textarea>
            </div>
            
            <div align="right">
                <button type="button" class="form-btn  btnIptal" id="back_button" name="back_button" onclick="back()">İptal</button>
                <button type="button" class="form-btn btnGonder" id="send_button" name="send_button" onclick="netasistanform_send()">Gönder</button>
            </div>
				
				</form>
				<label name="send_response" id="send_response" style="font-size: x-small; color: #545ea5"></label>
			</div>		
		</div>';
        echo '</div>';

        ; ?>
                    <script type="text/javascript">

                        function netasistanform_open() {
                            var formobj = document.getElementById("netasistan-form");
                            var buttonsobj = document.getElementById("netasistan-buttons");
                            if (formobj.style.display === "blok") {
                                formobj.style.display = "none";
                                buttonsobj.style.display = "block";

                            } else {
                                formobj.style.display = "block";
                                buttonsobj.style.display = "none";
                            }
                        }

                        function back(){
                            var formobj = document.getElementById("netasistan-form");
                            var buttonsobj = document.getElementById("netasistan-buttons");
                            document.getElementById('send_response').innerHTML = formobj.style.display;
                            if (formobj.style.display === "block") {
                                formobj.style.display = "none";
                                buttonsobj.style.display = "block"
                                document.getElementById('send_response').innerHTML = "";

                            } else {
                                formobj.style.display = "block";
                                buttonsobj.style.display = "none";
                            }
                        }

                        document.addEventListener('mouseup', function(e) {
                            var connectionform  = document.getElementById('connection-form');
                            var connectmenu  = document.getElementById('netasistan-altlogo');
                            var connectmenucheck  = document.getElementById('connect-menu');
                            var connectclose  = document.getElementById('connect-close');
                            var formobj = document.getElementById("netasistan-form");
                            var buttonsobj = document.getElementById("netasistan-buttons");


                            if (!connectionform.contains(e.target) && !connectmenu.contains(e.target)) {
                                connectionform.style.display = 'none';
                                connectmenucheck.checked = 'false';
                                formobj.style.display = 'none'
                                document.getElementById('send_response').innerHTML = "";
                                buttonsobj.style.display = 'block'

                            }
                            if(connectmenu.contains(e.target)){
                                if (connectmenucheck.checked == false){
                                    connectionform.style.display = 'none';
                                    formobj.style.display = 'none'
                                    buttonsobj.style.display = 'block'
                                    document.getElementById('send_response').innerHTML = "";
                                }else{
                                    connectionform.style.display = 'block';
                                }
                            }
                            if (connectclose.contains(e.target)){
                                connectionform.style.display = 'none';
                                connectmenucheck.checked = 'false';
                                formobj.style.display = 'none'
                                document.getElementById('send_response').innerHTML = "";
                                buttonsobj.style.display = 'block'
                            }
                        });
                        function netasistanform_send(){
                            var netasistan_name = document.getElementById('netasistan_name').value;
                            var netasistan_lastname = document.getElementById('netasistan_lastname').value;
                            var netasistan_number =document.getElementById('netasistan_number').value;
                            var netasistan_email =document.getElementById('netasistan_email').value;
                            var netasistan_header =document.getElementById('netasistan_header').value;
                            var netasistan_content =document.getElementById('netasistan_content').value;

                            if (netasistan_name=="" || netasistan_lastname=="" || netasistan_number=="" || netasistan_email=="" || netasistan_header=="" || netasistan_content==""){
                                document.getElementById('send_response').innerHTML = "Tüm alanlar dolu olmalıdır!";
                            }else{
                                var data = {
                                    'action': 'netgsm_netasistanticket',
                                    'netasistan_name': netasistan_name,
                                    'netasistan_lastname': netasistan_lastname,
                                    'netasistan_number' : netasistan_number,
                                    'netasistan_email': netasistan_email,
                                    'netasistan_header': netasistan_header,
                                    'netasistan_content' : netasistan_content,
                                };

                                jQuery.post(ajaxurl, data, function(response) {
                                    var obje = JSON.parse(response);
                                    if (obje){
                                        if(obje.code=="1000"){
                                            document.getElementById('netasistan_name').value="";
                                            document.getElementById('netasistan_lastname').value="";
                                            document.getElementById('netasistan_number').value="";
                                            document.getElementById('netasistan_email').value="";
                                            document.getElementById('netasistan_header').value="";
                                            document.getElementById('netasistan_content').value="";
                                            document.getElementById('send_response').innerHTML = "Kaydınız oluşturuldu.";
                                        }else if (obje.code=="1101"){
                                            document.getElementById('send_response').innerHTML = "Telefon numaranızı kontrol ediniz. (5XXXXXXXXX)";
                                        }else if (obje.code=="1105") {
                                            document.getElementById('send_response').innerHTML = "E-mail adresinizi kontrol ediniz. (test@test.com)";
                                        }else if (obje.code=="0") {
                                            document.getElementById('send_response').innerHTML = obje.mesaj;
                                        }else {
                                            document.getElementById('send_response').innerHTML = obje.mesaj;
                                        }
                                    }else{
                                        document.getElementById('send_response').innerHTML = "Kayıt oluşturulamadı!";
                                    }

                                    document.getElementById('send_button').disabled = false;
                                    document.getElementById('send_response').disabled = false;

                                });
                            }
                        }
                    </script>
               <?php

    }
}

function netasistan_phonecontrol($phone){
    if ($phone!=""){
        if(substr($phone , 0 , 1)=="0"){
            $phone_new = substr($phone , 1 , 10);
        }else if(substr($phone , 0 , 2)=="90"){
            $phone_new = substr($phone , 2 , 10);
        }else if(substr($phone , 0 , 3)=="+90"){
            $phone_new = substr($phone , 3 , 10);
        }else{
            $phone_new = $phone;
        }
        return $phone_new;
    }
}
require_once 'wc-netgsm-api.php';
/*
    global $jal_db_version;
    $jal_db_version = '4.1';

    function jal_install() {
        global $wpdb;
        global $jal_db_version;
        $installed_ver = get_option( "jal_db_version" );

        if ( $installed_ver != $jal_db_version ) {

            $table_name = $wpdb->prefix . 'netgsmsmsreports';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            phone tinytext NOT NULL,
            trchar int,
            jobid int,
            errorcode int,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            title tinytext NOT NULL,
            properties tinytext NOT NULL,
            text text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            update_option( "jal_db_version", $jal_db_version );
        }
    }
    register_activation_hook( __FILE__, 'jal_install' );

    function netgsm_setData($phone, $title, $trChar, $text, $properties, $time, $jobid=0, $errorcode=0){
        global $wpdb;
        $table_name = $wpdb->prefix . 'netgsmsmsreports';

        $wpdb->insert(
            $table_name,
            array(
                'phone' => $phone,
                'title' => $title,
                'text' => $text,
                'properties' => $properties,
                'time' => $time,
                'jobid' => $jobid,
                'errorcode' => $errorcode,
                'trchar' => $trChar,
            )
        );
    }




    function netgsm_update_db_check() {
        global $jal_db_version;
        if ( get_site_option( 'jal_db_version' ) != $jal_db_version ) {
            jal_install();
        }
    }
    add_action( 'plugins_loaded', 'netgsm_update_db_check' );
*/