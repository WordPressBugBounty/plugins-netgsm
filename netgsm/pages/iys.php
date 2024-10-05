<div class="tab-pane container-fluid" id="iys"> <!-- İYS ayarları-->
    <hr>
    <div class="form-group">
        <!--  Brancode özelliği -->
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for=""><i class="fa fa-certificate" style="color: #E74C3C;"></i> Yeni üyeliklerde İYS'ye adres ekleme</label>
                </div>
                <div class="col-sm-5">

                    <label class="switch">
                        <input name="netgsm_brandcode_control" id="netgsm_switch15" type="checkbox" onchange="netgsm_field_onoff(15)" value="1" <?php if (esc_attr(get_option('netgsm_brandcode_control')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <div class="col-sm-9" id="netgsm_field15" style="<?php if (esc_attr(get_option('netgsm_brandcode_control')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-building" style="color: #17A2B8;"></i>
                            </div>
                            <input type="number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0,this.maxLength);" name="netgsm_brandcode_text" maxlength="6" id="netgsm_textarea15" class="form-control" placeholder="Brandcode" value="<?= esc_attr(get_option("netgsm_brandcode_text")) ?>" style="border-color: #ccc" />
                        </div>
                        <br>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-toggle-off" style="color: #17A2B8;"></i>
                            </div>
                            <select name="netgsm_recipient_type" id="netgsm_recipient_type" class="form-control" style="height: 35px; font-size: 12px; border-color: #ccc">
                                <?php
                                $netgsm_recipienttype = esc_html(get_option("netgsm_recipient_type"));
                                if ($netgsm_recipienttype == "1") { ?>
                                    <option value="0"> Adres türü seçiniz</option>
                                    <option value="1" selected> Bireysel Adres</option>
                                    <option value="2">Tacir Adres </option>
                                <?php } else if ($netgsm_recipienttype == "2") { ?>
                                    <option value="0"> Adres türü seçiniz</option>
                                    <option value="1"> Bireysel Adres</option>
                                    <option value="2" selected>Tacir Adres </option>
                                <?php } else {
                                ?>
                                    <option value="0" selected> Adres türü seçiniz</option>
                                    <option value="1"> Bireysel Adres</option>
                                    <option value="2">Tacir Adres </option>
                                <?php
                                } ?>
                            </select>
                        </div>
                        <br>
                        <label>İleti Kanalı</label>
                        <div>
                            <label class="netiys_type_container">Mesaj
                                <input type="checkbox" name="netgsm_message" id="netgsm_message" value="1" style="height: 20px; width: 20px; border-color: #ccc" ; <?php if (esc_attr(get_option('netgsm_message')) == 1) { ?>checked <?php } ?> />
                                <span class="checkmark"></span>
                            </label>

                            <label class="netiys_type_container">Arama
                                <input type="checkbox" name="netgsm_call" id="netgsm_call" value="1" style="height: 20px; width: 20px; border-color: #ccc" <?php if (esc_attr(get_option('netgsm_call')) == 1) { ?>checked <?php } ?> />
                                <span class="checkmark"></span>
                            </label>

                            <label class="netiys_type_container">E-posta
                                <input type="checkbox" name="netgsm_email" id="netgsm_email" value="1" style="height: 20px; width: 20px; border-color: #ccc" <?php if (esc_attr(get_option('netgsm_email')) == 1) { ?>checked <?php } ?> />
                                <span class="checkmark"></span>
                            </label>

                        </div>
                    </div>

                </div>
            </div>
        </div>
        <br>
        <!--  Yeni üye olurken kampanya SMSleri için izin al özelliği -->
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-7">
                    <label class="control-label" for="">
                        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Yeni üyeliklerde kampanya, tanıtım, kutlama vb. içerikli SMS gönderimi için izin alanı oluştur</label>
                </div>
                <div class="col-sm-5">
                    <label class="switch">
                        <input name="netgsm_iys_check_control" id="netgsm_switch13" type="checkbox" onchange="netgsm_field_onoff(13)" value="1" <?php if (esc_attr(get_option('netgsm_iys_check_control')) == 1) { ?>checked <?php } ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="col-sm-9" id="netgsm_field13" style="<?php if (esc_attr(get_option('netgsm_iys_check_control')) != 1) { ?>display:none; <?php } ?>">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-check-square" style="color: #17A2B8;"></i>

                            </div>
                            <textarea name="netgsm_iys_check_text" id="netgsm_textarea13" class="form-control" placeholder="Kampanya, tanıtım, kutlama vb. içerik onay metni"><?= esc_textarea(get_option("netgsm_iys_check_text")) ?></textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-10 text-right">
            <button class="btn btn-primary" id="login_save7" name="login_save7" onclick="login();"><i class="fa fa-folder"></i> Değişiklikleri Kaydet </button>
        </div>
    </div>
    <div class="form-group">
        <i class="fa fa-certificate" style="color: #E74C3C;"></i> Aktif olduğunda, Brandcode doğru kaydedilmişse ve adres türü seçilmişse yeni üyeliklerde kampanya, tanıtım, kutlama vb.
        içerikli SMS gönderimlerine izin verilmesi halinde telefon numarası İYS'ye yüklenir.
        <br>
        <i class="fa fa-certificate" style="color: #BB77AE;"></i> Aktif olduğunda, hesap
        oluşturma sayfasına Ad,Soyad, Telefon numarası ve kampanya, tanıtım, kutlama vb. içerikli SMS gönderim izni alanları ekler.
        <br>
        <hr>
        <p>
            <i class="fa fa-exclamation-triangle" style="color: #D35400;"></i> "Yeni üyeliklerde kampanya, tanıtım, kutlama vb.içerikli
            SMS gönderimi için izin alanı oluştur" kısmında text oluşturabilir veya a etiketi ile yönlendirme adresi verebilirsiniz.<br>
            <i class="fa fa-exclamation-triangle" style="color: #D35400;"></i> Kampanya, tanıtım, kutlama vb. içerikli SMS'ler ticari içerik kapsamındadır
            bilgilendirme, kargo, şifre vb. içerikler ise ticari içerik kapsamında değildir.<br>
    </div>
</div>