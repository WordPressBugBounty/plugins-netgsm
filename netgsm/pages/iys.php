<?php

if (!current_user_can('administrator')) {
    return;  // Admin olmayan kullanıcılar erişemez
}
?>
<div class="tab-pane container-fluid" id="iys"> <!-- İYS ayarları-->
    <div class="vx-page-header">
        <div class="vx-page-header-icon"><i class="ti ti-shield-check"></i></div>
        <div class="vx-page-header-text">
            <h2 class="vx-page-header-title">İYS</h2>
            <p class="vx-page-header-subtitle">İleti Yönetim Sistemi izin ayarları</p>
        </div>
    </div>
    <div class="vx-screen">
        <div class="vx-secbox">
            <div class="vx-sec-header">
                <i class="ti ti-shield-check vx-sec-icon"></i>
                <h3>İYS izin ayarları</h3>
            </div>
            <p class="vx-sec-desc">İleti Yönetim Sistemi kapsamında izin toplama seçeneklerini yönetin.</p>
            <hr class="vx-sec-rule">

            <?php $ngsm_iys15_checked = (esc_attr(get_option('netgsm_brandcode_control')) == 1); ?>
            <div class="vx-wc-item">
                <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                    <div class="vx-toggle-row-left">
                        <i class="ti ti-address-book"></i>
                        <div>
                            <p class="vx-toggle-row-label">Yeni üyeliklerde İYS'ye adres ekleme</p>
                            <p class="vx-toggle-row-desc">Yeni üyelerin numarası izin verilirse İYS'ye yüklenir.</p>
                        </div>
                    </div>
                    <label class="vx-switch">
                        <input name="netgsm_brandcode_control" id="netgsm_switch15" type="checkbox" onchange="netgsm_field_onoff(15)" value="1" <?php checked($ngsm_iys15_checked); ?>>
                        <span class="vx-switch-track"></span>
                    </label>
                </div>
                <div class="vx-wc-reveal" id="netgsm_field15" style="<?php echo $ngsm_iys15_checked ? '' : 'display:none;'; ?>">
                    <div class="vx-grid-2">
                        <div class="vx-field">
                            <label for="netgsm_textarea15">Brandcode</label>
                            <input type="number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0,this.maxLength);" name="netgsm_brandcode_text" maxlength="6" id="netgsm_textarea15" class="vx-input" placeholder="Brandcode" value="<?= esc_attr(get_option("netgsm_brandcode_text")) ?>">
                        </div>
                        <div class="vx-field">
                            <label for="netgsm_recipient_type">Adres türü</label>
                            <select name="netgsm_recipient_type" id="netgsm_recipient_type" class="vx-select">
                                <?php
                                $netgsm_recipienttype = esc_html(get_option("netgsm_recipient_type"));
                                if ($netgsm_recipienttype == "1") { ?>
                                    <option value="0">Adres türü seçiniz</option>
                                    <option value="1" selected>Bireysel</option>
                                    <option value="2">Tacir</option>
                                <?php } else if ($netgsm_recipienttype == "2") { ?>
                                    <option value="0">Adres türü seçiniz</option>
                                    <option value="1">Bireysel</option>
                                    <option value="2" selected>Tacir</option>
                                <?php } else { ?>
                                    <option value="0" selected>Adres türü seçiniz</option>
                                    <option value="1">Bireysel</option>
                                    <option value="2">Tacir</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:500;color:var(--text-primary);display:block;margin-bottom:8px;">İleti Kanalı</label>
                        <div style="display:flex;gap:20px;flex-wrap:wrap;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:14px;color:var(--text-primary);cursor:pointer;">
                                <input type="checkbox" name="netgsm_message" id="netgsm_message" value="1" <?php if (esc_attr(get_option('netgsm_message')) == 1) { ?>checked <?php } ?>> Mesaj
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:14px;color:var(--text-primary);cursor:pointer;">
                                <input type="checkbox" name="netgsm_call" id="netgsm_call" value="1" <?php if (esc_attr(get_option('netgsm_call')) == 1) { ?>checked <?php } ?>> Arama
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:14px;color:var(--text-primary);cursor:pointer;">
                                <input type="checkbox" name="netgsm_email" id="netgsm_email" value="1" <?php if (esc_attr(get_option('netgsm_email')) == 1) { ?>checked <?php } ?>> E-posta
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <?php $ngsm_iys14_checked = (esc_attr(get_option('netgsm_iys_check_control')) == 1); ?>
            <div class="vx-wc-item">
                <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                    <div class="vx-toggle-row-left">
                        <i class="ti ti-speakerphone"></i>
                        <div>
                            <p class="vx-toggle-row-label">Yeni üyeliklerde ticari SMS izin alanı oluştur</p>
                            <p class="vx-toggle-row-desc">Kayıt sayfasına kampanya/tanıtım izin kutusu eklenir.</p>
                        </div>
                    </div>
                    <label class="vx-switch">
                        <input name="netgsm_iys_check_control" id="netgsm_switch14" type="checkbox" onchange="netgsm_field_onoff(14)" value="1" <?php checked($ngsm_iys14_checked); ?>>
                        <span class="vx-switch-track"></span>
                    </label>
                </div>
                <div class="vx-wc-reveal" id="netgsm_field14" style="<?php echo $ngsm_iys14_checked ? '' : 'display:none;'; ?>">
                    <textarea name="netgsm_iys_check_text" id="netgsm_textarea14" rows="2" class="vx-textarea" placeholder="Kampanya, tanıtım, kutlama vb. içerik onay metni"><?= esc_textarea(get_option("netgsm_iys_check_text")) ?></textarea>
                </div>
            </div>

            <?php $ngsm_iys16_checked = (esc_attr(get_option('netgsm_iys_checkout_control')) == 1); ?>
            <div class="vx-wc-item">
                <div class="vx-toggle-row" style="border-bottom:none;padding:0;">
                    <div class="vx-toggle-row-left">
                        <i class="ti ti-shopping-bag"></i>
                        <div>
                            <p class="vx-toggle-row-label">Üyeliksiz satın alımlarda izin alanı oluştur</p>
                            <p class="vx-toggle-row-desc">Üye olmadan alışverişte ticari SMS izni sorulur.</p>
                        </div>
                    </div>
                    <label class="vx-switch">
                        <input name="netgsm_iys_checkout_control" id="netgsm_switch16" type="checkbox" onchange="netgsm_field_onoff(16)" value="1" <?php checked($ngsm_iys16_checked); ?>>
                        <span class="vx-switch-track"></span>
                    </label>
                </div>
                <div class="vx-wc-reveal" id="netgsm_field16" style="<?php echo $ngsm_iys16_checked ? '' : 'display:none;'; ?>">
                    <textarea name="netgsm_iys_checkout_text" id="netgsm_textarea16" rows="2" class="vx-textarea" placeholder="Kampanya, tanıtım, kutlama vb. içerik onay metni"><?= esc_textarea(get_option("netgsm_iys_checkout_text")) ?></textarea>
                </div>
            </div>
        </div>

        <div class="vx-note vx-note-warning" style="margin-top:16px;">
            Kampanya, tanıtım, kutlama vb. içerikli SMS'ler ticari içerik kapsamındadır; bilgilendirme, kargo, şifre vb. içerikler ticari kapsamda değildir. İzin alanına metin veya yönlendirme adresi ekleyebilirsiniz.
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:22px;">
            <button type="submit" class="vx-btn vx-btn-primary" id="login_save7" name="login_save7" onclick="return saveIys();"><i class="ti ti-device-floppy"></i> Değişiklikleri Kaydet</button>
        </div>
    </div>

    <script>
        function saveIys() {
            var isBrandCodeControlChecked = document.getElementById('netgsm_switch15').checked;
            var brandCode = document.getElementById('netgsm_textarea15').value.trim();
            var recipientType = document.getElementById('netgsm_recipient_type').value;

            var isMessageChecked = document.getElementById('netgsm_message').checked;
            var isCallChecked = document.getElementById('netgsm_call').checked;
            var isEmailChecked = document.getElementById('netgsm_email').checked;

            if (isBrandCodeControlChecked) {
                if (brandCode === '' || recipientType === '0') {
                    alert("Lütfen Brandcode ve Adres türü alanlarını doldurunuz.");
                    return false;
                }

                if (!isMessageChecked && !isCallChecked && !isEmailChecked) {
                    alert("Lütfen en az bir İleti Kanalı seçiniz: Mesaj, Çağrı veya E-posta.");
                    return false;
                }
            }

            jQuery('#sayfayi_yenile').val(1);
            return true;
        }
    </script>
</div>
