<?php

if (!current_user_can('administrator')) {
    return;  // Admin olmayan kullanıcılar erişemez
}
?>
<div class="tab-pane container-fluid" id="cf7sms"> <!-- CONTACT FORM7 SMS ayarları-->
    <div class="vx-page-header">
        <div class="vx-page-header-icon"><i class="ti ti-forms"></i></div>
        <div class="vx-page-header-text">
            <h2 class="vx-page-header-title">Contact Form7 SMS</h2>
            <p class="vx-page-header-subtitle">Form gönderimlerinde otomatik SMS</p>
        </div>
    </div>
    <div class="vx-screen">
        <div class="vx-note vx-note-neutral" style="margin-bottom:20px;">
            <ul>
                <li>Contact Form 7 eklentisi ile beraber çalışır. Kullandığınız değişkenler forma ait değişkenler olmalıdır. Örneğin: <b>[adsoyad]</b> şeklinde mesaj metnine yazmalısınız. Formdan gelen değerlerde böyle bir alan var ise bu değişken o değer ile değişecektir.</li>
                <li>Formlardaki telefon inputunun etiketi <b>[telephone]</b> olmalıdır. SMS gönderimi ve rehbere kaydetmede bu numara kullanılacaktır.</li>
                <li>Mesaj içeriği boş olan formlarda SMS gönderilmez.</li>
                <li>Bu sayfa yüklendiğinde daha önce girdiğiniz metinler görünmez. Formu seçtiğiniz takdirde görünür.</li>
            </ul>
        </div>

        <?php $ngsm_cf7_1_checked = (esc_attr(get_option('netgsm_cf7_success_customer_control')) == 1); ?>
        <div class="vx-secbox">
            <div class="vx-sec-header vx-sec-header-toggle">
                <div style="display:flex;align-items:center;gap:9px;">
                    <i class="ti ti-user vx-sec-icon"></i>
                    <h3>Müşteriye SMS gönder</h3>
                </div>
                <label class="vx-switch">
                    <input name="netgsm_cf7_success_customer_control" id="switch_netgsm_cf7_1" type="checkbox" onchange="netgsm_field_onoff_custom('netgsm_cf7_1')" value="1" <?php checked($ngsm_cf7_1_checked); ?>>
                    <span class="vx-switch-track"></span>
                </label>
            </div>
            <p class="vx-sec-desc">Başarılı form gönderiminde form içindeki telefon alanına gönderilir.</p>
            <?php if ($ngsm_cf7_1_checked) : ?><hr class="vx-sec-rule"><?php endif; ?>
            <div id="field_netgsm_cf7_1" class="vx-sec-reveal" style="<?php echo $ngsm_cf7_1_checked ? '' : 'display:none;'; ?>">
                <p class="vx-help" style="margin:0 0 10px;">Kullanabileceğiniz değişkenler: Hangi form için SMS oluşturuyorsanız o formda oluşturduğunuz etiketleri kullanın.</p>
                <div class="vx-field">
                    <label for="netgsm_cf7_form_list_1">Form seçiniz</label>
                    <select name="netgsm_cf7_form_list_1" id="netgsm_cf7_form_list_1" class="vx-select" style="max-width:400px;" onchange="cf7_form_change(this.value, 'customer', 'activeStatus_cf7' )">
                        <option value="0">Form Seçiniz</option>
                        <?php foreach ($cf7_list as $item) { ?>
                            <option value="<?php echo esc_attr($item->ID) ?>"><?php echo esc_html($item->ID) . ' - ' . esc_html($item->post_title) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <span id="activeStatus_cf7_customer" data=""></span>
                <?php if (isset($cf7_list)) {
                    foreach ($cf7_list as $item) {
                        $cf7_list_text_success_customer = array($item->ID => 'netgsm_cf7_list_text_success_customer_' . $item->ID);
                ?>
                        <textarea style="display: none;" name="netgsm_cf7_list_text_success_customer_<?php echo esc_attr($item->ID); ?>" id="netgsm_cf7_list_text_success_customer_<?php echo esc_attr($item->ID); ?>" rows="2" class="vx-textarea cf7_list_text_success_customer" placeholder="Örnek : Mesajınız iletilmiştir."><?php echo esc_textarea(get_option($cf7_list_text_success_customer[$item->ID])); ?></textarea>
                        <?php
                        $form_tags = [];
                        if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
                            $ContactForm = WPCF7_ContactForm::get_instance($item->ID);
                            $form_tags = $ContactForm->scan_form_tags();
                        }
                        $tags = [];
                        foreach ($form_tags as $form_tag) {
                            if ($form_tag->name == '') {
                                continue;
                            }
                            $onclick = "varfill('netgsm_cf7_list_text_success_customer_'+jQuery('#activeStatus_cf7_customer').attr('data'), '" . esc_js($form_tag->name) . "')";
                            $tags[] = '<mark onclick="' . esc_attr($onclick) . '">[' . esc_html($form_tag->name) . ']</mark>';
                        }
                        ?>
                        <p style="display: none;" class="vx-chip-list cf7_list_text_success_customer" id="netgsm_cf7_list_tags_success_customer_<?php echo esc_attr($item->ID); ?>">
                            Kullanılabilir etiketler: <?php echo implode(' ', $tags); ?>
                        </p>
                <?php }
                } ?>
                <div class="vx-note vx-note-warning" style="margin-top:14px;">SMS'lerde <b>[telephone]</b> etiketi varsa burada girilen numaraya gönderilir.</div>
            </div>
        </div>

        <?php $ngsm_cf7_2_checked = (esc_attr(get_option('netgsm_cf7_success_admin_control')) == 1); ?>
        <div class="vx-secbox" style="margin-top:16px;">
            <div class="vx-sec-header vx-sec-header-toggle">
                <div style="display:flex;align-items:center;gap:9px;">
                    <i class="ti ti-phone vx-sec-icon"></i>
                    <h3>Belirlenen numaralara SMS gönder</h3>
                </div>
                <label class="vx-switch">
                    <input name="netgsm_cf7_success_admin_control" id="switch_netgsm_cf7_2" type="checkbox" onchange="netgsm_field_onoff_custom('netgsm_cf7_2')" value="1" <?php checked($ngsm_cf7_2_checked); ?>>
                    <span class="vx-switch-track"></span>
                </label>
            </div>
            <p class="vx-sec-desc">Başarılı form gönderiminde sabit numaralara bildirim gönderilir.</p>
            <?php if ($ngsm_cf7_2_checked) : ?><hr class="vx-sec-rule"><?php endif; ?>
            <div id="field_netgsm_cf7_2" class="vx-sec-reveal" style="<?php echo $ngsm_cf7_2_checked ? '' : 'display:none;'; ?>">
                <p class="vx-help" style="margin:0 0 10px;">Kullanabileceğiniz değişkenler: Hangi form için SMS oluşturuyorsanız o formda oluşturduğunuz etiketleri kullanın.</p>
                <div class="vx-grid-2">
                    <div class="vx-field">
                        <label for="netgsm_cf7_to_admin_no">Numaralar</label>
                        <input name="netgsm_cf7_to_admin_no" id="netgsm_cf7_to_admin_no" type="text" class="vx-input" placeholder="Örn: 05XX XXX XX XX, 05XX XXX XX XX" value="<?= esc_attr(get_option("netgsm_cf7_to_admin_no")) ?>">
                    </div>
                    <div class="vx-field">
                        <label for="netgsm_cf7_form_list_2">Form seçiniz</label>
                        <select name="netgsm_cf7_form_list_2" id="netgsm_cf7_form_list_2" class="vx-select" onchange="cf7_form_change(this.value, 'admin', 'activeStatus_cf7_2' )">
                            <option value="0">Form Seçiniz</option>
                            <?php foreach ($cf7_list as $item) { ?>
                                <option value="<?php echo esc_attr($item->ID); ?>"><?php echo esc_html($item->ID) . ' - ' . esc_html($item->post_title); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <span id="activeStatus_cf7_admin" data=""></span>
                <?php if (isset($cf7_list)) {
                    foreach ($cf7_list as $item) {
                        $cf7_list_text_success_admin = array($item->ID => 'netgsm_cf7_list_text_success_admin_' . $item->ID);
                ?>
                        <textarea style="display: none;" name="netgsm_cf7_list_text_success_admin_<?php echo esc_attr($item->ID); ?>" id="netgsm_cf7_list_text_success_admin_<?php echo esc_attr($item->ID); ?>" rows="2" class="vx-textarea cf7_list_text_success_admin" placeholder="Örnek : Mesajınız iletilmiştir."><?php echo esc_textarea(get_option($cf7_list_text_success_admin[$item->ID])); ?></textarea>
                        <?php
                        $form_tags = [];
                        if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
                            $ContactForm = WPCF7_ContactForm::get_instance($item->ID);
                            $form_tags = $ContactForm->scan_form_tags();
                        }
                        $tags = [];
                        foreach ($form_tags as $form_tag) {
                            if ($form_tag->name == '') {
                                continue;
                            }
                            $onclick = "varfill('netgsm_cf7_list_text_success_admin_'+jQuery('#activeStatus_cf7_admin').attr('data'), '" . esc_js($form_tag->name) . "')";
                            $tags[] = '<mark onclick="' . esc_attr($onclick) . '">[' . esc_html($form_tag->name) . ']</mark>';
                        }
                        ?>
                        <p style="display: none;" class="vx-chip-list cf7_list_text_success_admin" id="netgsm_cf7_list_tags_success_admin_<?php echo esc_attr($item->ID); ?>">
                            Kullanılabilir etiketler: <?php echo implode(' ', $tags); ?>
                        </p>
                <?php }
                } ?>
            </div>
        </div>

        <?php
        // --- Rehbere ekleme (netgsm_cf7_contact_control) ---
        // Bu bölümü açacak bir switch/checkbox kod tabanının hiçbir yerinde yok (register_setting
        // dışında hiçbir UI onu 1 yapmıyor); kullanıcı tarafından her zaman erişilemez durumda.
        // Karar (2026-09 redesign): dokunmadan, yeni tasarıma dahil etmeden olduğu gibi bırakıldı.
        ?>
        <div style="display:none;">
            <div class="col-sm-9" id="field_netgsm_cf7_3" style="<?php if (esc_attr(get_option('netgsm_cf7_contact_control')) != 1) { ?>display:none; <?php } ?>">
                <div class="row" style="
    margin-top: 30px;
    margin-left: 30px;
">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-comment" style="color: #17A2B8;"></i>
                            </div>
                            <select name="netgsm_cf7_form_list_3" id="netgsm_cf7_form_list_3" style="height: 30px" class="form-control" onchange="cf7_form_change2(this.value, 'contact', 'activeStatus_cf7_3' )">
                                <option value="0">Form Seçiniz</option>
                                <?php foreach ($cf7_list as $item) { ?>
                                    <option value="<?php echo esc_attr($item->ID); ?>"><?php echo esc_html($item->ID . ' - ' . $item->post_title); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <span id="activeStatus_cf7_3" data=""></span>


                <div class="row" id="netgsm_cf7_list_contact">
                    <div class="col-sm-12">
                        <?php if (isset($cf7_list)) {
                            foreach ($cf7_list as $item) {
                                $cf7_list_contact = array($item->ID => 'netgsm_cf7_list_contact_' . $item->ID);
                                $cf7_list_contact_firstname = array($item->ID => 'netgsm_cf7_list_contact_firstname_' . $item->ID);
                                $cf7_list_contact_lastname = array($item->ID => 'netgsm_cf7_list_contact_lastname_' . $item->ID);
                                $cf7_list_contact_other = array($item->ID => 'netgsm_cf7_list_contact_other_' . $item->ID);
                        ?>

                                <div class="cf7_list_contact" id="netgsm_cf7_list_contact_<?php echo esc_attr($item->ID); ?>" style="display: none; padding-top: 5px">
                                    <div class="col-sm-4 ">
                                        <div class="input-group" style="display: ;">
                                            <div class="input-group-addon">
                                                <i class="fa fa-user-plus" style="color: #17A2B8;"></i>
                                            </div>
                                            <input name="netgsm_cf7_list_contact_<?php echo esc_attr($item->ID); ?>" class="form-control" placeholder="Grup adı. Örnek : Basvuru" value="<?= esc_attr(esc_textarea(get_option($cf7_list_contact[$item->ID]))); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="input-group" style="display: ;">
                                            <div class="input-group-addon">
                                                <i class="fa fa-tag" style="color: #17A2B8;"></i>
                                            </div>
                                            <input name="netgsm_cf7_list_contact_firstname_<?php echo esc_attr($item->ID); ?>" class="form-control" placeholder="Ad anahtarı. Örnek : ad" value="<?php echo esc_attr(esc_textarea(get_option($cf7_list_contact_firstname[$item->ID]))); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="input-group" style="display: ;">
                                            <div class="input-group-addon">
                                                <i class="fa fa-tag" style="color: #17A2B8;"></i>
                                            </div>
                                            <input name="netgsm_cf7_list_contact_lastname_<?php echo esc_attr($item->ID); ?>" class="form-control" placeholder="Soyad anahtarı. Örnek : soyad" value="<?php echo esc_attr(esc_textarea(get_option($cf7_list_contact_lastname[$item->ID]))); ?>">
                                        </div>
                                    </div>
                                    <span id="activeStatus_cf7_other_contact" data=""></span>
                                    <div class="col-sm-12" style="padding-top: 5px">
                                        <div class="input-group" style="display: ;">
                                            <div class="input-group-addon">
                                                <i class="fa fa-bars" style="color: #17A2B8;"></i>
                                            </div>
                                            <textarea style="" name="netgsm_cf7_list_contact_other_<?php echo esc_attr($item->ID); ?>" id="netgsm_cf7_list_contact_other_<?php echo esc_attr($item->ID); ?>" class="form-control netgsm_cf7_list_contact_other" placeholder="Diğer anahtarlar. format: [rehber_alani]:[form_etiketi];[aciklama]:[detay];... şeklinde eşleştirerek girebilirsiniz."><?php echo esc_textarea(esc_attr(get_option($cf7_list_contact_other[$item->ID]))); ?></textarea>
                                            <?php
                                            $form_tags = [];
                                            if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
                                                $ContactForm = WPCF7_ContactForm::get_instance($item->ID);
                                                $form_tags = $ContactForm->scan_form_tags();
                                            }

                                            $tags = [];
                                            foreach ($form_tags as $form_tag) {
                                                if ($form_tag->name == '') {
                                                    continue;
                                                }
                                                array_push($tags,  '<mark onclick="varfill(\'netgsm_cf7_list_contact_other_\'+jQuery(\'#activeStatus_cf7_other_contact\').attr(\'data\'), \'' . esc_js($form_tag->name) . '\')">[' . esc_html($form_tag->name) . ']</mark>');
                                            }
                                            ?>

                                        </div>
                                        <p id="netgsm_tags_text13" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i>
                                            Kullanılabilir rehber anahtarları :
                                            <?php
                                            $contact_vars = [
                                                'aciklama',
                                                'hitap',
                                                'tckimlik',
                                                'dtarih',
                                                'etarih',
                                                'unvan',
                                                'email',
                                                'sabittel',
                                                'faxtel',
                                                'cinsiyet',
                                                'kangrubu',
                                                'ulke',
                                                'sehir',
                                                'sokak',
                                                'ekbilgi1',
                                                'ekbilgi2',
                                                'ekbilgi3',
                                                'semt',
                                            ];
                                            foreach ($contact_vars as $contact_var) {
                                            ?>
                                                <mark onclick="varfill('netgsm_cf7_list_contact_other_<?php echo esc_attr($item->ID); ?>','<?php echo esc_attr($contact_var); ?>');">[<?php echo esc_html($contact_var); ?>]</mark>
                                            <?php
                                            }
                                            ?>
                                        </p>
                                        <p style="padding-top: 5px" id="netgsm_cf7_list_contact_other_<?php echo esc_attr($item->ID); ?>"><i class="fa fa-angle-double-right"></i> Kullanılabilir form etiketleri : <?php echo esc_html(implode(' ', $tags)); ?></p>
                                    </div>
                                </div>

                        <?php }
                        } ?>
                    </div>


                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:22px;">
            <button type="submit" class="vx-btn vx-btn-primary" id="login_save5" name="login_save5" onclick="login();"><i class="ti ti-device-floppy"></i> Değişiklikleri Kaydet</button>
        </div>
    </div>
</div>
