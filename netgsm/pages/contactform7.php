<div class="tab-pane container-fluid" id="cf7sms"> <!-- CONTACT FORM7 SMS ayarları-->
    <hr>
    <div class="form-group">
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="netgsm_cf7_success_customer_control"><i class="fa fa-certificate" style="color: #A62F00;"></i> Başarılı Form göndermelerinde sms gönder:</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_cf7_success_customer_control" id="switch_netgsm_cf7_1" type="checkbox" onchange="netgsm_field_onoff_custom('netgsm_cf7_1')" value="1" <?php if (esc_attr(get_option('netgsm_cf7_success_customer_control')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="col-sm-9" id="field_netgsm_cf7_1" style="<?php if (esc_attr(get_option('netgsm_cf7_success_customer_control')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-comment" style="color: #17A2B8;"></i>
                            </div>
                            <select name="netgsm_cf7_form_list_1" id="netgsm_cf7_form_list_1" style="height: 30px" class="form-control" onchange="cf7_form_change(this.value, 'customer', 'activeStatus_cf7' )">
                                <option value="0">Form Seçiniz</option>
                                <?php foreach ($cf7_list as $item) { ?>
                                    <option value="<?php echo esc_attr($item->ID) ?>"><?php echo esc_html($item->ID) . ' - ' . esc_html($item->post_title) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <span id="activeStatus_cf7_customer" data=""></span>
                <div class="row">
                    <div class="col-sm-12">
                        <?php if (isset($cf7_list)) {
                            foreach ($cf7_list as $item) {
                                $cf7_list_text_success_customer = array($item->ID => 'netgsm_cf7_list_text_success_customer_' . $item->ID);
                        ?>
                                <textarea style="display: none;" name="netgsm_cf7_list_text_success_customer_<?php echo esc_attr($item->ID); ?>" id="netgsm_cf7_list_text_success_customer_<?php echo esc_attr($item->ID); ?>" class="form-control cf7_list_text_success_customer" placeholder="Örnek : Mesajınız iletilmiştir."><?php echo esc_textarea(get_option($cf7_list_text_success_customer[$item->ID])); ?></textarea>
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
                                    array_push($tags,  '<mark onclick="varfill(\'netgsm_cf7_list_text_success_customer_\'+jQuery(\'#activeStatus_cf7_customer\').attr(\'data\'), \'' . esc_js($form_tag->name) . '\')">[' . esc_html($form_tag->name) . ']</mark>');
                                }
                                ?>
                                <p style="display: none; padding-top: 5px" class="cf7_list_text_success_customer" id="netgsm_cf7_list_tags_success_customer_<?php echo esc_attr($item->ID); ?>">Kullanılabilir etiketler : <?php echo esc_html(implode(' ', $tags)); ?></p>
                        <?php }
                        } ?>
                        <p id="netgsm_tags_text5" style="margin-top: 10px"><i class="fa fa-angle-double-right"></i> <i> Kullanabileceğiniz Değişkenler : </i>
                            Hangi form için sms oluşturuyorsanız o formda oluşturduğunuz etiketleri kullanın.
                            

                        </p>
                        <p>
                        <div class="alert alert-warning"><i class="fa fa-warning"></i><strong> ÖNEMLİ : </strong>Smsler <b>[telephone]</b> etiketi var ise burada girilen numaraya gönderilir!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="netgsm_cf7_success_admin_control"><i class="fa fa-certificate" style="color: #A62F00;"></i> Başarılı Form göndermelerinde belirlenen numaralara sms gönder:</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_cf7_success_admin_control" id="switch_netgsm_cf7_2" type="checkbox" onchange="netgsm_field_onoff_custom('netgsm_cf7_2')" value="1" <?php if (esc_attr(get_option('netgsm_cf7_success_admin_control')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <div class="col-sm-9" id="field_netgsm_cf7_2" style="<?php if (esc_attr(get_option('netgsm_cf7_success_admin_control')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-phone" style="color: #17A2B8;"></i>
                            </div>
                            <input name="netgsm_cf7_to_admin_no" id="netgsm_cf7_to_admin_no" type="text" class="form-control" placeholder="Sms gönderilecek numaraları giriniz. Örn: 05xxXXXxxXX,05xxXXXxxXX" value="<?= esc_html(get_option("netgsm_cf7_to_admin_no")) ?>">
                        </div>
                        <p id="vars_neworder_to_admin_no"> </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-comment" style="color: #17A2B8;"></i>
                            </div>
                            <select name="netgsm_cf7_form_list_2" id="netgsm_cf7_form_list_2" style="height: 30px" class="form-control" onchange="cf7_form_change(this.value, 'admin', 'activeStatus_cf7_2' )">
                                <option value="0">Form Seçiniz</option>
                                <?php foreach ($cf7_list as $item) { ?>
                                    <option value="<?php echo esc_attr($item->ID); ?>"><?php echo esc_html($item->ID) . ' - ' . esc_html($item->post_title); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <span id="activeStatus_cf7_admin" data=""></span>

                <div class="row">
                    <div class="col-sm-12">
                        <?php if (isset($cf7_list)) {
                            foreach ($cf7_list as $item) {
                                $cf7_list_text_success_admin = array($item->ID => 'netgsm_cf7_list_text_success_admin_' . $item->ID);
                        ?>
                                <textarea style="display: none;" name="netgsm_cf7_list_text_success_admin_<?php echo esc_attr($item->ID); ?>" id="netgsm_cf7_list_text_success_admin_<?php echo esc_attr($item->ID); ?>" class="form-control cf7_list_text_success_admin" placeholder="Örnek : Mesajınız iletilmiştir."><?php echo esc_textarea(get_option($cf7_list_text_success_admin[$item->ID])); ?></textarea>
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
                                    array_push($tags,  '<mark onclick="varfill(\'netgsm_cf7_list_text_success_admin_\'+jQuery(\'#activeStatus_cf7_admin\').attr(\'data\'), \'' . $form_tag->name . '\')">[' . $form_tag->name . ']</mark>');
                                }
                                ?>
                                <p style="display: none; padding-top: 5px" class="cf7_list_text_success_admin" id="netgsm_cf7_list_tags_success_admin_<?php echo esc_attr($item->ID); ?>">Kullanılabilir etiketler : <?php echo esc_html(implode(' ', $tags)); ?></p>
                        <?php }
                        } ?>
                        <p id="netgsm_tags_text5" style="margin-top: 10px;"><i class="fa fa-angle-double-right"></i> <i> Kullanabileceğiniz Değişkenler : </i>
                            Hangi form için sms oluşturuyorsanız o formda oluşturduğunuz etiketleri kullanın.
                           

                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="netgsm_cf7_contact_control"><i class="fa fa-certificate" style="color: #A62F00;"></i> Başarılı Form göndermelerinde Telefon numarasını Netgsm Rehberine kaydet:</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_cf7_contact_control" id="switch_netgsm_cf7_3" type="checkbox" onchange="netgsm_field_onoff_custom('netgsm_cf7_3')" value="1" <?php if (esc_attr(get_option('netgsm_cf7_contact_control')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="col-sm-9" id="field_netgsm_cf7_3" style="<?php if (esc_attr(get_option('netgsm_cf7_contact_control')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
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
        </div>
    <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-10 text-right">
            <button class="btn btn-primary" id="login_save5" name="login_save5" onclick="login();"><i class="fa fa-folder"></i> Değişiklikleri Kaydet </button>
        </div>
    </div>
    <div class="form-group">
        <p><i class="fa fa-certificate" style="color: #A62F00;"></i> Contact form 7 eklentisi ile beraber çalışır. kullandığınız değişkenler forma ait değişkenler olmalıdır. <br>
            örneğin : [adsoyad] şeklinde mesaj metnine yazmalısınız. Formdan gelen değerlerde böyle bir alan var ise bu değişken o değer ile değişecektir.
            <br>
            formlardaki telefon inputunun etiketi <b>[telephone]</b> olmalıdır. Sms gönderimi ve rehbere kaydetmede bu numara kullanılacaktır.
            <br><br>
            <i class="fa fa-exclamation-triangle" style="color: #D35400;"></i> Mesaj içeriği boş olan formlarda sms gönderilmez. <br>
            <i class="fa fa-exclamation-triangle" style="color: #D35400;"></i> Bu sayfa yüklendiğinde daha önce girdiğiniz metinler görünmez. Formu seçtiğiniz taktirde görünür.
    </div>
</div>
        
