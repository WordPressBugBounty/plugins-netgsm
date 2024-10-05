<?php
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

$users = get_users();

//yetkilendirme ile ilgili geliştirmeler 16.07.2021
$cntrl = true;
$cntrl2 = true;

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
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> Netgsm Eklenti Ayarları
                    <?php if ($cevap->btnkontrol == "enabled") { ?>
                        <!-- <form action="options.php" method="get" id="formLogout" name="formLogout">
                                <?php /*settings_fields( ' netgsmoptionslogout' ); */ ?>
                                <?php /*do_settings_sections( 'netgsmoptionslogout' ); */ ?>
                                <button class="btn btn-danger btn-sm" id="logout" name="logout" type="submit" style="float: right; margin-top: -20px">Çıkış <i class="fa fa-sign-out"></i></button>
                            </form>-->
                        <button class="btn btn-danger btn-sm" type="text" onclick="logout()" style="float: right; margin-top: -5px">Çıkış <i class="fa fa-sign-out"></i></button>
                    <?php } ?>
                </h3>
            </div>

            <div class="panel-body">
                <div class="col-md-6 text-left">
                    <a href="https://www.netgsm.com.tr/" alt="Yeni nesil telekom operatörü" target="_blank">
                        <img src="<?= esc_url(plugins_url('lib/image/logo.png', dirname(__FILE__))) ?>" width="130" height="40">
                    </a>

                </div>
                <div class="col-md-6 text-right">
                    <div <?php if ($cevap->href != "") { ?>onclick="window.open('<?php echo esc_url($cevap->href); ?>','_blank');" <?php } ?> class="alert alert-<?php echo esc_attr($cevap->durum); ?>" id="bakiye" style="display:inline-block;">
                        <i class='fa <?php echo esc_attr($cevap->icon); ?>'></i><?php echo esc_html($cevap->mesaj); ?>
                    </div>
                </div>


                <form action="options.php" method="post" id="form-module" class="form-horizontal" name="form-module">
                    <?php settings_fields('netgsmoptions'); ?>
                    <?php do_settings_sections('netgsmoptions'); ?>
                    <input type="hidden" name="sayfayi_yenile" id="sayfayi_yenile" value="0">
                    <div class="tab-pane">
                        <ul class="nav nav-tabs" id="language">
                            <li><a href="#login" data-toggle="tab"><i class="fa fa-sign-in"></i> Giriş</a></li>
                            <li><a href="#sms" data-toggle="tab"><i class="fa fa-envelope-o"></i> WooCommerce
                                    SMS</a></li>
                            <li><a href="#tf2sms" data-toggle="tab"><i class="fa fa-key"></i> Üyelik Doğrulama</a>
                            </li>
                            <li><a href="#bulksms" data-toggle="tab"><i class="fa fa-comments-o"></i> Toplu SMS </a></li>
                            <li><a href="#privatesms" data-toggle="tab"><i class="fa fa-commenting"></i> Özel
                                    sms</a></li>
                            <li><a href="#cf7sms" data-toggle="tab"><i class="fa fa-envelope-o"></i> Contact Form7
                                    SMS</a></li>
                            <li><a href="#iys" data-toggle="tab"><i class="fa fa-toggle-on"></i> İYS
                                </a></li>
                            <li><a href="#inbox" data-toggle="tab"><i class="fa fa-inbox"></i> Gelen sms</a></li>
                            <li><a href="#voip" data-toggle="tab"><i class="fa fa-phone"></i> Gelen Çağrılar</a>
                            </li>
                            <li><a href="#asistan" data-toggle="tab"><i class="fa fa-external-link"></i> Netasistan</a>
                            </li>
                            <li><a href="#settings" data-toggle="tab"><i class="fa fa-gear"></i> Ayarlar</a></li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane" id="login">
                                <hr>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="netgsm_user">Kullanıcı Adı : </label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-user" style="color: #17A2B8;"></i>
                                            </div>
                                            <input type="text" name="netgsm_user" id="netgsm_user" placeholder="Kullanıcı Adı" value="<?php echo esc_attr(get_option("netgsm_user")); ?>" class="form-control" onkeypress="return RestrictSpace()" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="netgsm_pass">Şifre : </label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-lock" style="color: #17A2B8;"></i>
                                            </div>
                                            <input type="password" name="netgsm_pass" placeholder="Şifre" id="netgsm_pass" value="<?php echo esc_attr(get_option("netgsm_pass")); ?>" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-8">
                                        <button class="btn btn-success" id="login_save" name="login_save" onclick="login();"> Hesabımı Doğrula
                                        </button>
                                    </div>
                                </div>
                                <?php $netgsm_all_smstitle = $netgsm->getSmsBaslik();
                                $netgsm_input_smstitle = esc_html(get_option("netgsm_input_smstitle")); ?>
                                <div class="form-group">
                                    <label for="input-baslik" class="col-sm-2 control-label" style="color: <?php if (isset($netgsm_input_smstitle) && !empty($netgsm_input_smstitle) && $netgsm_input_smstitle || $netgsm_input_smstitle != 0) { ?>#2ECC71 <?php } else { ?>#E74C3C <?php } ?>;">
                                        SMS Başlığı :
                                    </label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-header" style="color: #17A2B8;"></i>
                                            </div>
                                            <select name="netgsm_input_smstitle" id="netgsm_input_smstitle" class="form-control" style="height: 35px; font-size: 12px;">
                                                <option value="0">Sms Başlığı Seçiniz</option>
                                                <?php
                                                if (isset($netgsm_input_smstitle) && $netgsm_input_smstitle != "" && is_array($netgsm_all_smstitle)) {
                                                    foreach ($netgsm_all_smstitle as $title) {
                                                        if ($title != '') {
                                                            if ($title == $netgsm_input_smstitle) {
                                                ?>
                                                                <option value="<?php echo esc_attr($title); ?>" selected><?= esc_html($title); ?></option><?php
                                                                                                                                                        } else {
                                                                                                                                                            ?>
                                                                <option value="<?php echo esc_attr($title); ?>"><?= esc_html($title); ?></option> <?php
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                                                }
                                                                                                                                            } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php $netgsm_trChar = esc_html(get_option("netgsm_trChar")); ?>
                                    <?php if ($cevap->btnkontrol != "enabled") {
                                        $netgsm_trChar = 0;
                                    } ?>
                                    <label data-toggle="tooltip" data-placement="right" title="SMS lerde yer alan türkçe karakterlerin gidip gitmeyeceğini ayarlayın. Kapalı ise TR karakterler normal karaktere çevrilir. Bir mesajda Türkçe dil seçeneği seçilerek işlem yapılıyorsa 1 boy SMS 155 karakter üzerinden değil 150 karakter üzerinden hesaplanır. Burada düşen 5 karakter mesajın Türkçe Karakterleride desteklemesi için harcanıyor, yani 150 tane Türkçe karakter kullanabilirim demek değildir. 150 tane karakter hakkım var, içinde Türkçe karakterlerde kullanabilirim anlamına gelir.
Sistemdeki Türkçe karakterler >> 'ç , ğ , ı , ş , Ğ , İ , Ş 'dir. " class="col-sm-2 control-label" for="input-status" style="color: <?php if (isset($netgsm_trChar) && !empty($netgsm_trChar) && $netgsm_trChar) { ?>#2ECC71<?php } else { ?>#E74C3C<?php } ?>;">SMS
                                        Türkçe Karakter: </label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-language" style="color: <?php if (isset($netgsm_trChar) && !empty($netgsm_trChar) && $netgsm_trChar) { ?>#2ECC71<?php } else { ?>#E74C3C<?php } ?>;"></i>
                                            </div>
                                            <select name="netgsm_trChar" id="input-trChar" class="form-control" style="height: 35px;">
                                                <?php if ($netgsm_trChar) { ?>
                                                    <option value="1" selected>Açık, Türkçe karakterler
                                                        gönderilsin.
                                                    </option>
                                                    <option value="0">Kapalı, Türkçe karakterler gönderilmesin.
                                                    </option>
                                                <?php } else { ?>
                                                    <option value="1">Açık, Türkçe karakterler gönderilsin.</option>
                                                    <option value="0" selected>Kapalı, Türkçe karakterler
                                                        gönderilmesin.
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <hr>

                                <div class="form-group">
                                    <?php $netgsm_status = esc_html(get_option("netgsm_status")); ?>
                                    <?php if ($cevap->btnkontrol != "enabled") {
                                        $netgsm_status = 0;
                                    } ?>
                                    <label class="col-sm-2 control-label" for="input-status" style="color: <?php if (isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status) { ?>#2ECC71<?php } else { ?>#E74C3C<?php } ?>;">Eklenti
                                        Durumu : </label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-power-off" style="color: <?php if (isset($netgsm_status) && !empty($netgsm_status) && $netgsm_status) { ?>#2ECC71<?php } else { ?>#E74C3C<?php } ?>;"></i>
                                            </div>
                                            <select name="netgsm_status" id="input-status" class="form-control" style="height: 35px;">
                                                <?php if ($netgsm_status) { ?>
                                                    <option value="1" selected>Açık</option>
                                                    <option value="0">Kapalı</option>
                                                <?php } else { ?>
                                                    <option value="1">Açık</option>
                                                    <option value="0" selected>Kapalı</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php if ($netgsm_status) {
                                        } else { ?>
                                            <small>*<i> Modül kapalıyken programlanan sms gönderimleri iptal
                                                    olur.</i></small><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-10 text-right">
                                        <button class="btn btn-primary" id="login_save2" name="login_save2" onclick="login();"><i class="fa fa-folder"></i> Değişiklikleri
                                            Kaydet
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane container-fluid" id="sms">
                                <hr>
                                <div class="form-group">
                                    <!--  Yeni üye olunca, belirlenen numaralara sms göndermek -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_newuser_to_admin_no"><i class="fa fa-certificate" style="color: #E74C3C;"></i>
                                                    <i class="fa fa-certificate" style="color: #BB77AE;"></i> Yeni
                                                    üye olunca, belirlenen numaralara sms gönderilsin:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_newuser_to_admin_control" id="netgsm_switch1" type="checkbox" onchange="netgsm_field_onoff(1)" value="1" <?php if ((get_option('netgsm_newuser_to_admin_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field1" style="<?php if ((get_option('netgsm_newuser_to_admin_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-phone" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <input name="netgsm_newuser_to_admin_no" id="netgsm_newuser_to_admin_no" type="text" class="form-control" placeholder="Sms gönderilecek numaraları giriniz. Örn: 05xxXXXxxXX,05xxXXXxxXX" value="<?= esc_html(get_option("netgsm_newuser_to_admin_no")) ?>">
                                                    </div>
                                                    <p id="vars_newuser_to_admin_no"></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf1')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf1_color" style="color: <?php if (esc_textarea(get_option("netgsm_newuser_to_admin_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_newuser_to_admin_text" id="netgsm_textarea1" class="form-control" placeholder="Örnek : Sayın yetkili, [uye_adi] [uye_soyadi] kullanıcı sisteme kaydoldu. Bilgileri : tel : [uye_telefonu] eposta: [uye_epostasi]"><?= esc_textarea(get_option("netgsm_newuser_to_admin_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_newuser_to_admin_json" name="netgsm_newuser_to_admin_json" class="form-control" value="<?= esc_html(get_option("netgsm_newuser_to_admin_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text1" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group"> <!--  Yeni üye olunca,yeni üyeye sms  -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_newuser_to_customer_no"><i class="fa fa-certificate" style="color: #E74C3C;"></i>
                                                    <i class="fa fa-certificate" style="color: #BB77AE;"></i> Yeni
                                                    üye olunca, müşteriye sms gönderilsin:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_newuser_to_customer_control" id="netgsm_switch2" type="checkbox" onchange="netgsm_field_onoff(2)" value="1" <?php if ((get_option('netgsm_newuser_to_customer_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field2" style="<?php if ((get_option('netgsm_newuser_to_customer_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf2')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf2_color" style="color: <?php if (esc_textarea(get_option("netgsm_newuser_to_customer_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_newuser_to_customer_text" id="netgsm_textarea2" class="form-control" placeholder="Örnek :Sayın [uye_adi] [uye_soyadi], sitemize hoşgeldiniz! [uye_telefonu] telefon numarası ve [uye_epostasi] ile kayıt oldunuz. Keyifli Alışverişler !"><?= esc_textarea(get_option("netgsm_newuser_to_customer_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_newuser_to_customer_json" name="netgsm_newuser_to_customer_json" class="form-control" value="<?= esc_html(get_option("netgsm_newuser_to_customer_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text2" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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


                                <div class="form-group"> <!--  yeni sipariş geldiğinde belirlenen numaralara sms -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_neworder_to_admin_no"><i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    Yeni sipariş geldiğinde, belirlenen numaralara sms gönderilsin:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_neworder_to_admin_control" id="netgsm_switch3" type="checkbox" onchange="netgsm_field_onoff(3)" value="1" <?php if ((get_option('netgsm_neworder_to_admin_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field3" style="<?php if ((get_option('netgsm_neworder_to_admin_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-phone" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <input name="netgsm_neworder_to_admin_no" id="netgsm_neworder_to_admin_no" type="text" class="form-control" placeholder="Sms gönderilecek numaraları giriniz. Örn: 05xxXXXxxXX,05xxXXXxxXX" value="<?= esc_html(get_option("netgsm_neworder_to_admin_no")) ?>">
                                                    </div>
                                                    <p id="vars_neworder_to_admin_no"></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf3')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf3_color" style="color: <?php if (esc_textarea(get_option("netgsm_neworder_to_admin_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_neworder_to_admin_text" id="netgsm_textarea3" class="form-control" placeholder="Örnek : Sayın Yönetici, [siparis_no] no'lu bir sipariş aldınız. Ürün bilgileri : [urun_adlari]-[urun_kodlari]-[urun_adetleri]"><?= esc_textarea(get_option("netgsm_neworder_to_admin_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_neworder_to_admin_json" name="netgsm_neworder_to_admin_json" class="form-control" value="<?= esc_html(get_option("netgsm_neworder_to_admin_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text3" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group"> <!--  Yeni sipariş olunca müşteriye sms -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_neworder_to_customer_no"><i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    Yeni sipariş geldiğinde, Müşteriye bilgilendirme sms
                                                    gönderilsin:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_neworder_to_customer_control" id="netgsm_switch4" type="checkbox" onchange="netgsm_field_onoff(4)" value="1" <?php if ((get_option('netgsm_neworder_to_customer_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field4" style="<?php if ((get_option('netgsm_neworder_to_customer_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf4')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf4_color" style="color: <?php if (esc_textarea(get_option("netgsm_neworder_to_customer_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_neworder_to_customer_text" id="netgsm_textarea4" class="form-control" placeholder="Örnek : [siparis_no]' nolu siparişiniz başarıyla oluşturulmuştur."><?= esc_textarea(get_option("netgsm_neworder_to_customer_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_neworder_to_customer_json" name="netgsm_neworder_to_customer_json" class="form-control" value="<?= esc_html(get_option("netgsm_neworder_to_customer_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text4" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>



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

                                <div class="form-group">
                                    <!--  Sİpariş durumları değiştiğinde müşteriye sms gönderilsin. -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_neworder_to_admin_no"><i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    <i class="fa fa-certificate" style="color: #34495E;"></i> Ürünün
                                                    sipariş durumu değiştiğinde müşteriye sms gönderilsin:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_orderstatus_change_customer_control" id="netgsm_switch5" type="checkbox" onchange="netgsm_field_onoff(5)" value="1" <?php if ((get_option('netgsm_orderstatus_change_customer_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field5" style="<?php if ((get_option('netgsm_orderstatus_change_customer_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">

                                                        <?php if (isset($actives[0])) {
                                                        ?>
                                                            <div class="input-group-addon" data-toggle="tooltip" data-placement="right" data-html="true" title="<b style='color: #2ECC71'>Aktif durumlar : </b><hr> <?= esc_attr(implode('<br>', $actives)) ?>">
                                                                <i class="fa fa-check-square" style="color: #2ECC71;"></i>
                                                            </div>

                                                        <?php
                                                        } else {
                                                        ?>
                                                            <div class="input-group-addon" data-toggle="tooltip" data-placement="right" data-html="true" title="Hiçbir durum aktifleştirilmemiş.">
                                                                <i class="fa fa-times-circle" style="color: #E74C3C;"></i>
                                                            </div>
                                                        <?php
                                                        } ?>


                                                        <div class="input-group-addon">
                                                            <i class="fa fa-truck" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" id="settings-btn-changed" onclick="">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="setting-btn_color" style="color:#2B2B2B;"></i>
                                                            </a>
                                                        </div>

                                                        <select id="order_status" onchange="order_status_change(this.value)" class="form-control" style="height: 35px">
                                                            <option value="" selected>Sipariş Durumu Seçiniz
                                                            </option>
                                                            <?php if (function_exists('wc_get_order_statuses')) {
                                                                $order_statuses = wc_get_order_statuses();
                                                                $arraykeys = array_keys($order_statuses);
                                                                foreach ($arraykeys as $item) { ?>
                                                                    <option value="<?= esc_attr($item) ?>"><?= esc_html($order_statuses[$item]) ?></option>

                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <span id="activeStatus" data=""></span>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <?php if (isset($arraykeys)) {
                                                        foreach ($arraykeys as $item) {
                                                            $order_status_text = array($item => 'netgsm_order_status_text_' . $item);
                                                    ?>
                                                            <textarea style="display: none;" name="netgsm_order_status_text_<?= esc_attr($item) ?>" id="netgsm_order_status_text_<?= esc_attr($item) ?>" class="form-control order_status_text" placeholder="Örnek : Sayın [uye_adi] [uye_soyadi], [siparis_no] numaralı siparişinizin kargo durumu ... olarak değiştirilmiştir. "><?= esc_textarea(get_option($order_status_text[$item])); ?></textarea>
                                                            <input type="hidden" id="netgsm_order_status_text_<?= esc_attr($item) ?>_json" name="netgsm_order_status_text_<?= esc_attr($item) ?>_json" class="form-control" value="<?= esc_attr(get_option("netgsm_order_status_text_" . $item . "_json")) ?>">

                                                    <?php }
                                                    } ?>
                                                    <p id="netgsm_tags_text5" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i>
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'siparis_no')">
                                                            [siparis_no]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_adi')">
                                                            [uye_adi]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_soyadi')">
                                                            [uye_soyadi]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_telefonu')">
                                                            [uye_telefonu]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'uye_epostasi')">
                                                            [uye_epostasi]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'kullanici_adi')">
                                                            [kullanici_adi]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'tarih')">
                                                            [tarih]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'saat')">
                                                            [saat]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'kargo_firmasi')">
                                                            [kargo_firmasi]
                                                        </mark>&nbsp;
                                                        <mark onclick="varfill('netgsm_order_status_text_'+jQuery('#activeStatus').attr('data'), 'takip_kodu')">
                                                            [takip_kodu]
                                                        </mark>&nbsp;
                                                        <i class="fa fa-certificate" style="color: #681947;"></i>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group"> <!--  Yeni özel not eklenince müşteriye sms -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for=""><i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    Siparişe yeni <span style="color: #2ECC71">Özel not</span> eklendiğinde müşteriye sms gönderilsin:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_newnote1_to_customer_control" id="netgsm_switch11" type="checkbox" onchange="netgsm_field_onoff(11)" value="1" <?php if ((get_option('netgsm_newnote1_to_customer_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field11" style="<?php if ((get_option('netgsm_newnote1_to_customer_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf11')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf11_color" style="color: <?php if (esc_textarea(get_option("netgsm_newnote1_to_customer_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_newnote1_to_customer_text" id="netgsm_textarea11" class="form-control" placeholder="Örnek : [siparis_no]' nolu siparişinize yeni not eklendi : [not]"><?= esc_textarea(get_option("netgsm_newnote1_to_customer_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_newnote1_to_customer_json" name="netgsm_newnote1_to_customer_json" class="form-control" value="<?= esc_html(get_option("netgsm_newnote1_to_customer_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text11" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group"> <!--  Yeni özel not eklenince müşteriye sms -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for=""><i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    Siparişe yeni <span style="color: #2ECC71">Müşteriye not</span> eklendiğinde müşteriye sms gönderilsin:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_newnote2_to_customer_control" id="netgsm_switch12" type="checkbox" onchange="netgsm_field_onoff(12)" value="1" <?php if ((get_option('netgsm_newnote2_to_customer_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field12" style="<?php if ((get_option('netgsm_newnote2_to_customer_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf12')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf12_color" style="color: <?php if (esc_textarea(get_option("netgsm_newnote2_to_customer_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_newnote2_to_customer_text" id="netgsm_textarea12" class="form-control" placeholder="Örnek : [siparis_no]' nolu siparişinize yeni not eklendi : [not]"><?= esc_textarea(get_option("netgsm_newnote2_to_customer_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_newnote2_to_customer_json" name="netgsm_newnote2_to_customer_json" class="form-control" value="<?= esc_html(get_option("netgsm_newnote2_to_customer_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text12" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <!--  Sipariş iptal edildiğinde belirlediğim numaralı sms ile bilgilendir -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_neworder_to_admin_no"><i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    Sipariş iptal edildiğinde belirlediğim numaralı sms ile
                                                    bilgilendir:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_order_refund_to_admin_control" id="netgsm_switch6" type="checkbox" onchange="netgsm_field_onoff(6)" value="1" <?php if ((get_option('netgsm_order_refund_to_admin_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field6" style="<?php if ((get_option('netgsm_order_refund_to_admin_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-phone" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <input name="netgsm_order_refund_to_admin_no" id="netgsm_order_refund_to_admin_no" type="text" class="form-control" placeholder="Sms gönderilecek numaraları giriniz. Örn: 05xxXXXxxXX,05xxXXXxxXX" value="<?= esc_html(get_option("netgsm_order_refund_to_admin_no")) ?>">
                                                    </div>
                                                    <p id="netgsm_order_refund_to_admin_no"></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf5')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf5_color" style="color: <?php if (esc_textarea(get_option("netgsm_order_refund_to_admin_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_order_refund_to_admin_text" id="netgsm_textarea6" class="form-control" placeholder="Sayın yönetici, [uye_adi][uye_soyadi] kullanıcısı, [urun] ürününü '[iade_nedeni]' nedeninden dolayı iptal etmiştir."><?= esc_html(get_option("netgsm_order_refund_to_admin_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_order_refund_to_admin_json" name="netgsm_order_refund_to_admin_json" class="form-control" value="<?= esc_html(get_option("netgsm_order_refund_to_admin_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text6" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <!--  ürün stoğa girdiğinde bekleme listesindekilere sms gönder-->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_neworder_to_admin_no">
                                                    <i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    <i class="fa fa-certificate" style="color: #F79500;"></i>
                                                    Ürün stoğa girdiğinde bekleme listesindekilere sms gönder(Wc Waitlist): </label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_product_waitlist1_control" id="netgsm_switch8" type="checkbox" onchange="netgsm_field_onoff(8)" value="1" <?php if ((get_option('netgsm_product_waitlist1_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field8" style="<?php if ((get_option('netgsm_product_waitlist1_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <div class="input-group-addon hoverlay" onclick="settingOpen('conf6')">
                                                            <a href="javascript:void(0)">
                                                                <i class="fa fa-cogs" id="conf6_color" style="color: <?php if (esc_textarea(get_option("netgsm_product_waitlist1_json")) != '') {
                                                                                                                            echo '#17A2B8';
                                                                                                                        } else {
                                                                                                                            echo '#2B2B2B';
                                                                                                                        } ?>;"></i>
                                                            </a>
                                                        </div>
                                                        <textarea name="netgsm_product_waitlist1_text" id="netgsm_textarea8" class="form-control" placeholder="Sayın [uye_adi][uye_soyadi], [urun_adi] ürünü tekrar stoğa girmiştir. Bilginize."><?= esc_html(get_option("netgsm_product_waitlist1_text")) ?></textarea>
                                                        <input type="hidden" id="netgsm_product_waitlist1_json" name="netgsm_product_waitlist1_json" class="form-control" value="<?= esc_html(get_option("netgsm_product_waitlist1_json")) ?>">
                                                    </div>
                                                    <p id="netgsm_tags_text8" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group"> <!--  netgsm rehberine kayıt -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_rehber_add"><i class="fa fa-certificate" style="color: #E74C3C;"></i>
                                                    <i class="fa fa-certificate" style="color: #BB77AE;"></i> Yeni
                                                    üye olunca, numarasını netgsm rehbere ekle:</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_rehber_control" id="netgsm_switch7" type="checkbox" onchange="netgsm_field_onoff(7)" value="1" <?php if ((get_option('netgsm_rehber_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                            <p id="netgsm_tags_text7" style="display: none;">
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field7" style="<?php if ((get_option('netgsm_rehber_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-user-plus" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <input name="netgsm_rehber_groupname" class="form-control" placeholder="Eklemek istediğiniz grup ismini giriniz." value="<?= esc_html(get_option("netgsm_rehber_groupname")) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-10 text-right">
                                        <button class="btn btn-primary" id="login_save3" name="login_save3" onclick="login();"><i class="fa fa-folder"></i> Değişiklikleri
                                            Kaydet
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <p>

                                        <i class="fa fa-bolt" style="color: #00B49C;"></i> Yeni Özellik : SMSlere otomatik geçmiş,güncel veya gelecek tarih ve saat ekleyebilirsiniz. :
                                        Güncel tarih <b>[tarih]</b> formatındadır. <b>[tarih+5]</b> 5 gün sonranın tarihini verir .<b>[tarih-5]</b> 5 gün öncenin tarihini verir. Gün cinsinden ekleme/çıkarma yapılmalıdır. Format g.a.Y şeklindedir.<br>
                                        Güncel saat <b>[saat]</b> formatındadır. <b>[saat+60]</b> 1 saat sonrasının saatini verir. <b>[saat-60]</b> 1 saat öncesinin saatini verir. Dakika cinsinden ekleme/çıkarma yapılmalıdır. Format : S:d:sn şeklindedir.

                                        <br>
                                        <i class="fa fa-bolt" style="color: #00B49C;"></i> Yeni Özellik : Sipariş
                                        SMSlerinde, sipariş meta anahtarlarını kullanabilirsiniz. Kullanım şekli :
                                        <b>[meta:metakeyiniz]</b> formatındadır. metakeyiniz yazan yere sipariş meta
                                        anahtarını girebilirsiniz.
                                        <br>
                                        <i class="fa fa-bolt" style="color: #00B49C;"></i> Yeni Özellik : <i class="fa fa-cogs"></i> simgesi bulunan ayarlarda sunulan ek
                                        konfigürasyonları yapabilirsiniz.<br>

                                        <hr>

                                        <i class="fa fa-certificate" style="color: #E74C3C;"></i> Aktif olduğunda, hesap
                                        oluşturma sayfasına Ad,Soyad ve Telefon numarası kısmını ekler. Kayıt için
                                        zorunlu olur.
                                        <br>
                                        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Bu özellikler
                                        woocommerce e-ticaret eklentisi yüklü ve etkin olduğunda çalışır.
                                        <br>
                                        <i class="fa fa-certificate" style="color: #34495E;"></i> Beklemede özelliğinde
                                        yeni sipariş verildiğinde de sms gönderilir.
                                        <br>
                                        <i class="fa fa-certificate" style="color: #F79500;"></i> <a href="https://docs.woocommerce.com/document/woocommerce-waitlist/" target="_blank">WooCommerce Bekleme Listesi (WooCommerce Waitlist)</a> eklentisi yüklü olduğunda ve ürün düzenleme sayfasında <b>"send instock email"</b> özelliği çalıştırıldığında, bekleme listesindeki kullanıcıların numaralarına gönderilir.
                                        <br>

                                        <i class="fa fa-certificate" style="color: #681947;"></i> Kargo firması ve takip
                                        kodu sadece Kargo Takip(<a href="https://wordpress.org/plugins/kargo-takip/" target="_blank">https://wordpress.org/plugins/kargo-takip/</a>)
                                        eklentisinden aldığı değerler ile çalışır. NOT: eklentiyi kullanırken, kargo
                                        bilgisini kaydettikten sonra sipariş durumunu değiştirin. (1.2 versiyonu ile
                                        test edildi.)
                                        <br>
                                        <i class="fa fa-lightbulb-o" style="color: #D35400;"></i> Satır atlamak için
                                        <strong>\n</strong> kullanabilirsiniz.<br>

                                    </p>

                                </div>
                            </div>

                            <div class="tab-pane container-fluid" id="tf2sms">
                                <hr>
                                <div class="form-group">
                                    <!--  Sipariş iptal edildiğinde belirlediğim numaralı sms ile bilgilendir -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_neworder_to_admin_no">
                                                    <i class="fa fa-certificate" style="color: #3498DB;"></i>
                                                    <i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    <i class="fa fa-certificate" style="color: #E74C3C;"></i> Yeni
                                                    üye olurken <b style="color: #E74C3C;" data-toggle="tooltip" data-placement="top" title="OTP SMS paketinden ücretlendirilir. OTP SMS paketizin olduğuna emin olun.">OTP
                                                        SMS</b> ile doğrulama yap :</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_tf2_auth_register_control" id="netgsm_switch9" type="checkbox" onchange="netgsm_field_onoff(9)" value="1" <?php if ((get_option('netgsm_tf2_auth_register_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field9" style="<?php if ((get_option('netgsm_tf2_auth_register_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <textarea name="netgsm_tf2_auth_register_text" id="netgsm_textarea9" class="form-control" placeholder="Tek seferlik doğrulama kodunuz : [kod]
*OTP SMS tek boy gönderilebilir.
*Metin taslağı 140 karakter ile sınırlandırılmıştır." maxlength="140"><?= esc_html(get_option("netgsm_tf2_auth_register_text")) ?></textarea>
                                                    </div>
                                                    <p id="netgsm_tags_text9" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-2">
                                                    <p style="margin-top: 5px;">Kod geçerlilik süresi(dk):</p>
                                                </div>
                                                <div class="col-sm-10">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-clock-o" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <input name="netgsm_tf2_auth_register_diff" class="form-control" placeholder="Kod geçerlilik süresi (dk.) örn: 120" value="<?= esc_html(get_option("netgsm_tf2_auth_register_diff")) ?>">
                                                    </div>
                                                    <p>Not : Bu süre boyunca aynı numaraya tekrar kod göndermek
                                                        istense bile gönderilmeyecektir. Süreyi dakika olarak
                                                        yazınız. (varsayılan olarak 180dk. )</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <!--  Sipariş iptal edildiğinde belirlediğim numaralı sms ile bilgilendir -->
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="">
                                                    <i class="fa fa-certificate" style="color: #3498DB;"></i>
                                                    <i class="fa fa-certificate" style="color: #BB77AE;"></i>
                                                    <i class="fa fa-certificate" style="color: #2ECC71;"></i>
                                                    Kayıtlı telefon numarası ile yeni üyeliği engelle :</label>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="switch">
                                                    <input name="netgsm_tf2_auth_register_phone_control" id="netgsm_switch10" type="checkbox" onchange="netgsm_field_onoff(10)" value="1" <?php if ((get_option('netgsm_tf2_auth_register_phone_control')) == 1) { ?>checked <?php } ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9" id="netgsm_field10" style="<?php if ((get_option('netgsm_tf2_auth_register_phone_control')) != 1) { ?>display:none; <?php } ?>">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-exclamation-circle" style="color: #17A2B8;"></i>
                                                        </div>
                                                        <textarea name="netgsm_tf2_auth_register_phone_warning_text" id="netgsm_textarea10" class="form-control" placeholder="[telefon_no] numarası ile zaten üyeliğiniz mevcut."><?= esc_html(get_option("netgsm_tf2_auth_register_phone_warning_text")) ?></textarea>
                                                    </div>
                                                    <p id="netgsm_tags_text10" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                                        Kullanabileceğiniz Değişkenler : </i></p> <span style="opacity: 0.7;">(Uyarı metnidir. Bu seçenekte SMS gönderilmez.) </span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-10 text-right">
                                        <button class="btn btn-primary" id="login_save4" name="login_save4" onclick="login();"><i class="fa fa-folder"></i> Değişiklikleri
                                            Kaydet
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <p>
                                    <h4><i class="fa fa-certificate" style="color: #3498DB;"></i> BU ÖZELLİĞİ TEST
                                        ETTİKTEN SONRA KULLANMAYA BAŞLAYIN.</h4>
                                    <br>
                                    <h4><i class="fa fa-certificate" style="color: #E74C3C;"></i> Bu özelliği
                                        kullanabilmeniz için OTP SMS paketinizin olması gereklidir.
                                        https://www.netgsm.com.tr/webportal/ adresinden paket satın alabilirsiniz.
                                    </h4>
                                    <br>
                                    <h4><i class="fa fa-certificate" style="color: #BB77AE;"></i> Bu özellikler
                                        woocommerce e-ticaret eklentisi yüklü ve etkin olduğunda çalışır.
                                        Woocommerce kayıt olma sayfasında geçerlidir.</h4>
                                    <br>
                                    <h4><i class="fa fa-certificate" style="color: #2ECC71;"></i> Bu seçenekteki
                                        metin uyarı olarak gösterilecektir. SMS gönderimi için değildir.</h4>
                                    <br>

                                    <h5>
                                        OTP SMS kuralları için : <a href="https://www.netgsm.com.tr/dokuman/#otp-sms" target="_blank">https://www.netgsm.com.tr/dokuman/#otp-sms</a>
                                        adresini ziyaret edebilirsiniz.
                                    </h5>
                                    <br>
                                    Satır atlamak için <strong>\n</strong> kullanabilirsiniz.

                                </div>
                            </div>
                            <?php include 'contactform7.php' ?>
                            <?php include 'iys.php' ?>
                            <div class="tab-pane container-fluid" id="privatesms">
                                <hr>
                                <div class="row">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="netgsm_user">Telefon no
                                            : </label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-phone" style="color: #17A2B8;"></i>
                                                </div>
                                                <input type="text" name="private_phone" id="private_phone" placeholder="Birden fazla numaraya sms göndermek isterseniz aralarına virgül (,) koyarak numaraları çoğaltabilirsiniz." class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="private_text">Mesaj: </label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-commenting" style="color: #17A2B8;"></i>
                                                </div>
                                                <textarea type="text" name="private_text" id="private_text" cols="5" placeholder="Göndermek istediğiniz mesaj içeriğini girin." class="form-control" /></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="private_text">Mesaj içerik Türü: </label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-envelope" style="color: #17A2B8;"></i>
                                                </div>
                                                <select name="netgsm_content_type" id="netgsm_content_type" style="height: 35px; max-width: 100% ; width: 100% ;  font-size: 12px; border-color: #ccc">
                                                    <option value="0"> Mesaj içerik türü seçiniz</option>
                                                    <option value="1"> Kampanya, tanıtım, kutlama vb. (İYS'ye bireysel kayıtlı alıcılarınıza gönderilir.) </option>
                                                    <option value="1"> Kampanya, tanıtım, kutlama vb. (İYS'ye tacir kayıtlı alıcılarınıza gönderilir.) </option>
                                                    <option value="2"> Bilgilendirme, kargo, şifre vb. (İYS'den sorgulanmaz.)</option>
                                                </select>
                                            </div>
                                            <small>*<i> Hesabınıza tanımlı marka kodunuz bulunmuyorsa Bilgilendirme, kargo, şifre vb. (İYS'den sorgulanmaz.) seçilmelidir.</i></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <button type="button" class="btn btn-success" onclick="privatesmsSend()" name="sendSMS" id="sendSMS"><i class="fa fa-paper-plane"></i> SMS
                                            Gönder
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane container-fluid" id="bulksms">

                                <hr>



                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="col-sm-7">
                                                <label class="control-label" for="netgsm_rehber_add">Telefon Meta
                                                    anahtarı</label>
                                            </div>
                                            <div class="col-sm-5">
                                            </div>
                                        </div>
                                        <div class="col-sm-9">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <?php
                                                            $netgsm_contact_meta_key = 'billing_phone';

                                                            if (esc_html(get_option("netgsm_contact_meta_key")) != '') {
                                                                $netgsm_contact_meta_key = esc_html(get_option("netgsm_contact_meta_key"));
                                                            }
                                                            ?>
                                                            <input type="text" value="<?= esc_attr($netgsm_contact_meta_key) ?>" class="form-control" name="netgsm_contact_meta_key" id="netgsm_contact_meta_key" placeholder="Telefon meta anahtarı">
                                                        </div>

                                                        <div class="col-md-2 text-left">
                                                            <button class="btn btn-primary" id="login_save10" name="login_save10" onclick="login();"><i class="fa fa-folder"></i> Kaydet
                                                            </button>
                                                        </div>
                                                        <div class="col-md-5 text-left">
                                                            <div class="alert alert-warning">
                                                                <span class="close" style="cursor: context-menu;" title="Bilgi"><i class="fa fa-info"></i></span>
                                                                Aşağıdaki tabloda, telefon
                                                                numaraları için burada kaydedilmiş olan meta
                                                                anahtarı değerine bakılacaktır. Varsayılan olarak
                                                                woocommerce telefon anahtarı <b>billing_phone </b>
                                                                kullanılmaktadır.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">


                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <span class="close" style="cursor: context-menu;" title="Bilgi"><i class="fa fa-info"></i></span>
                                            Maksimum 1000 kullanıcı listelenir.
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12 text-right">

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table data-pagination="true" id="table" name="table" class="table table-bordered table-striped dataTable no-footer" data-search="true" data-search-align="left" data-pagination-v-align="bottom" data-click-to-select="true" data-toggle="table" data-page-list="[10, 25, 50, 100, 150, 200]">
                                            <a href="javascript:void(0)" class="btn btn-success" style="float: right;" onclick="netgsm_sendSMS_bulkTab('')"><i class="fa fa-paper-plane"></i> SMS Gönder</a>
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true"></th>
                                                    <th data-visible="false">userid</th>
                                                    <th>Kullanıcı adı</th>
                                                    <th>İsim</th>
                                                    <th><span>E-posta</span></th>
                                                    <th scope="col" id="phone" class="manage-column column-phone">
                                                        Telefon
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="the-list" data-wp-lists="list:user">
                                                <?php
                                                $key2 = 0;
                                                foreach ($users as $key => $user) {
                                                    $billing_phone = get_user_meta($user->ID, $netgsm_contact_meta_key, true);
                                                    if (isset($billing_phone) && !empty($billing_phone)) {
                                                        if ($key2 > 1000) {
                                                            break;
                                                        }
                                                        $key2++;
                                                ?>
                                                        <tr id="user-<?= esc_attr($user->ID) ?>">
                                                            <td></td>
                                                            <td><?= esc_html($user->ID) ?></td>
                                                            <td class="username column-username has-row-actions column-primary" data-colname="Kullanıcı adı">
                                                                <img alt="" src="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=32&amp;d=mm&amp;r=g" srcset="https://1.gravatar.com/avatar/af6a28e91103e7157c9451d7b754efd2?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32">
                                                                <strong><a href="user-edit.php?user_id=<?= esc_attr($user->ID) ?>" target="_blank"><?= esc_html($user->display_name) ?></a></strong><br>
                                                            </td>
                                                            <td class="name column-name" data-colname="İsim"><?php if (!empty($user->first_name)) {
                                                                                                                    echo esc_html($user->first_name . " " . $user->last_name);
                                                                                                                } else {
                                                                                                                    echo '—';
                                                                                                                } ?></td>
                                                            <td class="email column-email" data-colname="E-posta"><a href="mailto:<?= esc_attr($user->user_email) ?>"><?= esc_html($user->user_email) ?></a>
                                                            </td>
                                                            <td class="role column-phone" data-colname="Phone"><?= esc_html(get_user_meta($user->ID, $netgsm_contact_meta_key, true)) ?>
                                                                <div class="row-actions">
                                                                    <span class="view">
                                                                        <a href="javascript:void(0);" onclick="netgsm_sendSMS_bulkTab(<?= esc_attr($user->ID) ?>)" id="bulkSMSbtn">Sms Gönder</a>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        </tr>

                                                <?php }
                                                } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
                                                    foreach ($inboxData as $data) {
                                                        if (isset($data['phone']) && !empty($data['phone'])) {
                                                            $userinfo = $netgsm->phoneToUser($data['phone'], $users); ?>

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
                            <?php include 'voip.php' ?>
                            <?php include 'asistan.php' ?>
                            <?php include 'settings.php' ?>

                        </div>
                    </div>
                </form>
                <div class="col-md-2 text-center">
                    <hr>
                    <p style="opacity: 0.7">version: <a href="https://wordpress.org/plugins/netgsm/" target="_blank"><?= esc_html(plugin_name_get_version()) ?></a> | <a href="https://www.netgsm.com.tr" target="_blank">Netgsm</a></p>
                </div>
            </div>
        </div>
    </div>
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
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
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
        var field8 = ['uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'urun_kodu', 'urun_adi', 'stok_miktari', 'tarih', 'saat'];
        var field9 = ['kod', 'telefon_no', 'ad', 'soyad', 'mail', 'referans_no', 'tarih', 'saat'];
        var field10 = ['telefon_no', 'ad', 'soyad', 'mail', 'tarih', 'saat'];
        var field11 = ['siparis_no', 'not', 'uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'siparis_toplamtutar', 'tarih', 'saat'];
        var field12 = ['siparis_no', 'not', 'uye_adi', 'uye_soyadi', 'uye_telefonu', 'uye_epostasi', 'kullanici_adi', 'siparis_toplamtutar', 'tarih', 'saat'];
        var field13 = [''];
        var field15 = [''];
        var field16 = [''];
        var field17 = [''];
        var field18 = [''];
        var field19 = [''];
        var field20 = [''];
        var field21 = [''];
        var field22 = [''];
        var field23 = [''];
        for (var x = 1; x <= 14; x++) {
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