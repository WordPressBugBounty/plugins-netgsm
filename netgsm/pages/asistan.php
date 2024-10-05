<div class="tab-pane container-fluid" id="asistan">
    <hr>
    <div class="form-group">

        <div class="row" style="margin-top: 20px;">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="">
                        </i> Netasistan İletişim Butonu </label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <?php
                        // 'netgsm_asistan' seçeneğinin değerini alıyoruz
                        $netgsm_asistan_option = get_option('netgsm_asistan');

                        // Değerin 0 veya 1 olup olmadığını kontrol ediyoruz
                        $netgsm_asistan_option = filter_var($netgsm_asistan_option, FILTER_VALIDATE_INT, [
                            'options' => ['default' => 0, 'min_range' => 0, 'max_range' => 1]
                        ]);
                        ?>

                        <input name="netgsm_asistan"
                            id="netgsm_switch21" type="checkbox"
                            onchange="netgsm_field_onoff(21)" value="1"
                            <?php if ($netgsm_asistan_option === 1) {
                                echo 'checked';
                            } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <div class="col-sm-9" id="">
                <div class="row">
                    <div class="col-sm-1">
                        <div class="input-group">
                            <img style="margin-top: -10px;" src="<?php echo esc_url(plugins_url('/lib/image/netasistan-alt-logo.svg', dirname(__FILE__))); ?>">
                        </div>

                    </div>
                    <div class="col-sm-7">
                        <div class="input-group">
                            Netasistan iletişim butonu özelliği aktif edildiğinde belirtilen icon ile Web sitenizde Netasistan robotu oluşur.<br>
                            Netasistan iletişim butonu aktif edilmemişse diğer iletişim butonlarının hiçbiri oluşmaz
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <br><br><br><br>
        <div class="row">
            <div class="col-sm-8">
                <div class="col-sm-7">
                    <label style="color: #BB77AE; font-size: 15px"> Müşterilerinizin size hangi kanallardan ulaşabileceğini seçin; </label>
                </div>
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="">
                        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Mesaj gönderin</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_asistan_message"
                            id="netgsm_switch16" type="checkbox"
                            onchange="netgsm_field_onoff(16)" value="1"
                            <?php if (esc_attr(get_option('netgsm_asistan_message')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <?php
            // PHP kodu burada işlenir
            $netgsmAsistanMessage = get_option('netgsm_asistan_message');
            $displayStyle = ($netgsmAsistanMessage != 1) ? 'display: none;' : '';
            ?>

        <div class="col-sm-9" id="netgsm_field16" style="<?php echo esc_attr($displayStyle); ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-comment" style="color: #17A2B8;"></i>

                            </div>
                            <textarea name="netgsm_asistan_messagenumber" rows="1"
                                id="netgsm_textarea16" class="form-control"
                                placeholder="Yönlendirilecek telefon numarası (format: +905XXXXXXXXX)"><?= esc_textarea(get_option("netgsm_asistan_messagenumber")) ?></textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <br></br>
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="">
                        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Çağrı merkezimizi arayın</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_asistan_call"
                            id="netgsm_switch17" type="checkbox"
                            onchange="netgsm_field_onoff(17)" value="1"
                            <?php if (esc_attr(get_option('netgsm_asistan_call')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="col-sm-9" id="netgsm_field17"
                style="<?php if (esc_attr(get_option('netgsm_asistan_call')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-phone" style="color: #17A2B8;"></i>

                            </div>
                            <textarea name="netgsm_asistan_callnumber" rows="1"
                                id="netgsm_textarea17" class="form-control"
                                placeholder="Yönlendirilecek telefon numarası (format: +905XXXXXXXXX)"><?= esc_textarea(get_option("netgsm_asistan_callnumber")) ?></textarea>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <br><br>
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="">
                        <i class="fa fa-certificate" style="color: #BB77AE;"></i> E-posta gönderin</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_asistan_email"
                            id="netgsm_switch18" type="checkbox"
                            onchange="netgsm_field_onoff(18)" value="1"
                            <?php if (esc_attr(get_option('netgsm_asistan_email')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="col-sm-9" id="netgsm_field18"
                style="<?php if (esc_attr(get_option('netgsm_asistan_email')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-envelope" style="color: #17A2B8;"></i>

                            </div>
                            <textarea name="netgsm_asistan_emailaddress" rows="1"
                                id="netgsm_textarea18" class="form-control"
                                placeholder="Yönlendirilecek E-mail adresi"><?= esc_textarea(get_option("netgsm_asistan_emailaddress")) ?></textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="">
                        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Whatsapp</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_asistan_whatsapp"
                            id="netgsm_switch19" type="checkbox"
                            onchange="netgsm_field_onoff(19)" value="1"
                            <?php if (esc_textarea(get_option('netgsm_asistan_whatsapp')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="col-sm-9" id="netgsm_field19"
                style="<?php if (esc_textarea(get_option('netgsm_asistan_whatsapp')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-whatsapp" style="color: #17A2B8;"></i>

                            </div>
                            <textarea name="netgsm_asistan_whatsappnumber" rows="1"
                                id="netgsm_textarea19" class="form-control"
                                placeholder="Yönlendirilmek istenen Whatsapp numarası (format: +905XXXXXXXXX)"><?= esc_textarea(get_option("netgsm_asistan_whatsappnumber")) ?></textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="">
                        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Sizi arayalım </label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_asistan_netasistan"
                            id="netgsm_switch20" type="checkbox"
                            onchange="netgsm_field_onoff(20)" value="1"
                            <?php if (esc_attr(get_option('netgsm_asistan_netasistan')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="col-sm-9" id="netgsm_field20"
                style="<?php if ((get_option('netgsm_asistan_netasistan')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-key" style="color: #17A2B8;"></i>

                            </div>
                            <textarea name="netgsm_netasistan_appkey" class="form-control"
                                placeholder="Netasistan appkey giriniz."><?= esc_textarea(get_option("netgsm_netasistan_appkey")) ?></textarea>
                        </div>
                        <br>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-key" style="color: #17A2B8;"></i>

                            </div>
                            <textarea name="netgsm_netasistan_userkey" class="form-control"
                                placeholder="Netasistan userkey giriniz."><?= esc_textarea(get_option("netgsm_netasistan_userkey")) ?></textarea>
                        </div>
                        <br>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-tags" style="color: #17A2B8;"></i>
                            </div>
                            <textarea name="netgsm_netasistan_etiket" rows="1" class="form-control"
                                placeholder="Netasistanda tanımlı etiketlerinizi giriniz. (Örn: etiketadi1,etiketadi2,etiketadi3)"><?= esc_textarea(get_option("netgsm_netasistan_etiket")) ?></textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-10 text-right">
            <button class="btn btn-primary" id="login_save11" name="login_save11" onclick="login();"><i class="fa fa-folder"></i> Değişiklikleri Kaydet </button>
        </div>
    </div>

    <div class="form-group">
        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Netasistan iletişim butonu aktif edildikten sonra oluşan Netasistan robotuna tıklandığında açılacak iletişim kanallarıdır.<br>
        <i class="fa fa-exclamation-triangle" style="color: #D35400;"></i> Etiketler, appkey ve userkey değerleri netasistan arayüzünden alınmalıdır. Netasistan arayüzünden Ayarlar> Hesap Ayarları> API sayfasından appkey ve userkey alınabilir.<br>
        <i class="fa fa-exclamation-triangle" style="color: #D35400;"></i> Netasistan iletişim butonu hakkında detaylı bilgiye <a href="https://bilgibankasi.netgsm.com.tr/bilgi-bankasi/wordpress-eklentisi/">buradan</a> ulaşabilirsiniz.<br>
    </div>

</div>