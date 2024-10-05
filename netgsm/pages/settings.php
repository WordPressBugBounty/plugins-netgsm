<div class="tab-pane" id="settings">
    <?php
    if (!in_array($session->roles[0], ['administrator'])) {
    ?>
        <hr>
        <div class="alert alert-danger">
            <h3>Eklenti ayarlarını sadece Yönetici(administrator) yapabilir.</h3>

        </div>
    <?php
    } else {
    ?>
        <div class="row">
            <div class="col-md-12">
                <hr>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-3">
                            <div class="col-md-5">
                                <div class="alert alert-info">Rol Listesi</div>
                            </div>
                            <div class="col-md-2">

                            </div>
                            <div class="col-md-5">
                                <div class="alert alert-success">İzin verilen Roller </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="col-sm-7">
                                <label class="control-label" for="">Tam Yetkili Roller :</label>
                            </div>
                            <div class="col-sm-5">
                                <label class="switch">
                                    <input name="netgsm_auth_roles_control"  id="" type="checkbox" value="1" <?php if (esc_attr(get_option('netgsm_auth_roles_control')) == 1) { ?>checked <?php } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control" id="netgsm_auth_roles_text" name="netgsm_auth_roles" value="<?php echo esc_attr(get_option('netgsm_auth_roles')); ?>">
                            <div class="row">
                                <div class="col-md-5">
                                    <select multiple name="" id="netgsm_auth_roles" class="form-control" style="height: 150px;font-size: 15px;">
                                        <?php
                                        foreach ($role_list as $key => $role) {
                                            if ($key == 'administrator') {
                                                continue;
                                            }
                                            if (in_array($key, $auth_roles)) {
                                                continue;
                                            }
                                        ?>
                                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($role); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2 text-center">
                                    <button type="button" class="btn btn-success btn-sm" id="netgsm_add_role_btn" data-toggle="tooltip" data-placement="top" title="Seçili rolleri ekle"><i class="fa fa-plus-square"></i> İzin Ver</button>
                                    <hr>
                                    <button type="button" class="btn btn-danger btn-sm" id="netgsm_delete_role_btn" data-toggle="tooltip" data-placement="top" title="Seçili rolleri Sil"><i class="fa fa-minus-square"></i> Kaldır</button>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <label for=""><i class="fa fa-check"></i> administrator (Yönetici)</label>
                                    </div>
                                    <select multiple name="" id="netgsm_auth_roles_selected" class="form-control" style="height: 128px;font-size: 15px;">
                                        <?php
                                        foreach ($auth_roles as $key => $role) {
                                            if ($role == 'administrator') {
                                                continue;
                                            }
                                        ?>
                                            <option value="<?php echo esc_attr($role); ?>"><?php echo esc_html($role_list[$role]); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row" style="padding-top: 5px">
                                <div class="col-md-12">
                                    <div class="alert alert-warning">
                                        <p><i class="fa fa-certificate"></i> <b>Administrator(Yönetici)</b> rolü, her durumda bu eklentide <b>tam yetkilidir</b>.</p>
                                        <p><i class="fa fa-certificate"></i> <b>CTRL</b> tuşu ile birden fazla rol için yetki tanımlaması yapabilirsiniz.</p>
                                        <p><i class="fa fa-certificate"></i> Ekleme çıkarma işlemlerinden sonra değişiklikleri kaydetmelisiniz.</p>
                                    </div>
                                    <div class="alert alert-danger">
                                        <p><i class="fa fa-certificate"></i> Özelliğin açık olmaması durumunda yetkili kullanıcılar dikkate alınmayacaktır.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        jQuery('#netgsm_add_role_btn').click(function() {
                            if (jQuery('#netgsm_auth_roles option:selected').val() != null) {
                                var tempSelect = jQuery('#netgsm_auth_roles option:selected').val();
                                jQuery('#netgsm_auth_roles option:selected').remove().appendTo('#netgsm_auth_roles_selected');
                                jQuery("#netgsm_auth_roles").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
                                jQuery("#netgsm_auth_roles_selected").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
                                jQuery("#netgsm_auth_roles_selected").val(tempSelect);

                                var text = '';
                                var variable = [];
                                text = jQuery('#netgsm_auth_roles_text').val();
                                jQuery("#netgsm_auth_roles_selected option").each(function() {
                                    variable.push(jQuery(this).val());
                                });
                                text = variable.join(',');
                                jQuery('#netgsm_auth_roles_text').val(text);
                                tempSelect = '';
                            }
                        });

                        jQuery('#netgsm_delete_role_btn').click(function() {
                            if (jQuery('#netgsm_auth_roles_selected option:selected').val() != null) {
                                var tempSelect = jQuery('#netgsm_auth_roles_selected option:selected').val();
                                jQuery('#netgsm_auth_roles_selected option:selected').remove().appendTo('#netgsm_auth_roles');
                                jQuery("#netgsm_auth_roles_selected").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
                                jQuery("#netgsm_auth_roles").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");

                                jQuery("#netgsm_auth_roles").val(tempSelect);
                                var text = '';
                                var variable = [];
                                text = jQuery('#netgsm_auth_roles_text').val();
                                jQuery("#netgsm_auth_roles_selected option").each(function() {
                                    variable.push(jQuery(this).val());
                                });
                                text = variable.join(',');
                                jQuery('#netgsm_auth_roles_text').val(text);
                                tempSelect = '';
                            }
                        });
                    </script>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <hr>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-3">
                            <div class="col-md-5">
                                <div class="alert alert-info">Kullanıcı Listesi</div>
                            </div>
                            <div class="col-md-2">

                            </div>
                            <div class="col-md-5">
                                <div class="alert alert-success">Yetki Verilen Kullanıcılar</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="col-sm-7">
                                <label class="control-label" for="">Yetkili Kullanıcılar:</label>
                            </div>
                            <div class="col-sm-5">
                                <label class="switch">
                                    <input name="netgsm_auth_users_control" id="netgsm_switch144" type="checkbox" value="1" <?php if (esc_attr(get_option('netgsm_auth_users_control')) == 1) { ?>checked <?php } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control" id="netgsm_auth_users_text" name="netgsm_auth_users" value="<?php echo esc_attr(get_option('netgsm_auth_users')); ?>">
                            <div class="row">
                                <div class="col-md-5">
                                    <select multiple name="" id="netgsm_auth_users" class="form-control" style="height: 150px;font-size: 15px;">
                                        <?php
                                        foreach ($users as $key => $user) {
                                            if (
                                                in_array($user->roles[0], ['administrator'])
                                                || (in_array($user->roles[0], $auth_roles) && $netgsm_auth_roles_control == 1)
                                            ) {
                                                if ($user->user_login == 'admin') {
                                                    continue;
                                                }
                                                if (in_array($user->ID, $auth_users)) {
                                                    continue;
                                                }
                                        ?>
                                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name) . ' (' . esc_html($role_list[$user->roles[0]]) . ')' ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php

                                ?>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success btn-sm" id="netgsm_add_user_btn" data-toggle="tooltip" data-placement="top" title="Seçili rolleri ekle"><i class="fa fa-plus-square"></i> İzin Ver</button>
                                    <hr>
                                    <button type="button" class="btn btn-danger btn-sm" id="netgsm_delete_user_btn" data-toggle="tooltip" data-placement="top" title="Seçili rolleri Sil"><i class="fa fa-minus-square"></i> Kaldır</button>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <label for=""><i class="fa fa-check"></i> admin</label>
                                    </div>
                                    <select multiple name="" id="netgsm_auth_users_selected" class="form-control" style="height: 128px;font-size: 15px;">
                                        <?php
                                        foreach ($auth_users as $key => $userID) {
                                            $user = netgsm_findUser($users, $userID);
                                            if (in_array($user->roles[0], ['administrator']) || (in_array($user->roles[0], $auth_roles) && $netgsm_auth_roles_control == 1)) {
                                                if ($user->user_login == 'admin') {
                                                    continue;
                                                }
                                        ?>
                                                <option value="<?php echo esc_attr($userID); ?>"><?php echo esc_html($user->display_name) . ' (' . esc_html($role_list[$user->roles[0]]) . ')' ?></option>




                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row" style="padding-top: 5px">
                                <div class="col-md-12">
                                    <div class="alert alert-warning">
                                        <p><i class="fa fa-certificate"></i> <b>admin</b> kullanıcısı, administrator olduğu sürece her durumda bu eklentide <b>tam yetkilidir</b>.</p>
                                        <p><i class="fa fa-certificate"></i> Sadece izin verilen rollerin kullanıcıları arasından tam yetki verilen kullanıcılar seçebilirsiniz.</p>
                                        <p><i class="fa fa-certificate"></i> <b>CTRL</b> tuşu ile birden fazla rol için yetki tanımlaması yapabilirsiniz.</p>
                                        <p><i class="fa fa-certificate"></i> Ekleme çıkarma işlemlerinden sonra değişiklikleri kaydetmelisiniz.</p>
                                    </div>
                                    <div class="alert alert-danger">
                                        <p><i class="fa fa-certificate"></i> Özelliğin açık olmaması durumunda yetkili kullanıcılar dikkate alınmayacaktır.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3">
                            <div class="col-sm-7">
                                <label class="control-label" for="">Yeni üyeliklerin telefon alanında 0 zorunluluğu:</label>
                            </div>
                            <div class="col-sm-5">
                                <label class="switch">
                                    <input name="netgsm_phonenumber_zero1" id="netgsm_switch145" type="checkbox" value="1" onclick="netgsm_field_onoff('145')" <?php if ((get_option('netgsm_phonenumber_zero1')) == 1) { ?>checked <?php } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-9" id="netgsm_field145" style="<?php if (esc_attr(get_option('netgsm_phonenumber_zero1')) != 1) { ?>display:none; <?php } ?>">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="alert alert-warning">
                                            <b>
                                                Yeni üyelik özelliklerinde eklenen telefon alanlarında, telefon numarasının başında 0 (sıfır) girilmesi zorunlu olur.
                                            </b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3">
                            <div class="col-sm-7">
                                <label class="control-label" for="">Licance Key bilgisini siparişin meta anahtarına kaydet :</label>
                            </div>
                            <div class="col-sm-5">
                                <label class="switch">
                                    <input name="netgsm_licence_key_to_meta" id="netgsm_switch146" type="checkbox" value="1" onclick="netgsm_field_onoff('146')" <?php if ((get_option('netgsm_licence_key_to_meta')) == 1) { ?>checked <?php } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-9" id="netgsm_field146" style="<?php if (esc_attr(get_option('netgsm_licence_key_to_meta')) != 1) { ?>display:none; <?php } ?>">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="alert alert-warning">
                                            <b>
                                                Licence Maneger eklentisi ile oluşturulan lisans anahtarları, siparişin meta anahtarlarına eklenir.
                                            </b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <script>
                        function cf7_form_change(id, tip, activestatus) {
                            jQuery('.cf7_list_text_success_' + tip).hide('slow');
                            jQuery('#netgsm_cf7_list_text_success_' + tip + '_' + id).show('slow');
                            jQuery('#' + activestatus).attr('data', id);
                        }
                    </script>

                </div>
            </div>
        </div>
        <div class="alert alert-danger">
            <p><i class="fa fa-certificate"></i> Rollerin yetki kapasiteleri Netgsm eklentisi üzerinden ayarlanamamaktadır. Bunun için Wordpress rollerin yetkilerini yönetme konusunu araştırabilirsiniz. </p>
        </div>
        <script>
            jQuery('#netgsm_add_user_btn').click(function() {
                if (jQuery('#netgsm_auth_users option:selected').val() != null) {
                    var tempSelect = jQuery('#netgsm_auth_users option:selected').val();
                    jQuery('#netgsm_auth_users option:selected').remove().appendTo('#netgsm_auth_users_selected');
                    jQuery("#netgsm_auth_users").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
                    jQuery("#netgsm_auth_users_selected").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
                    jQuery("#netgsm_auth_users_selected").val(tempSelect);

                    var text = '';
                    var variable = [];
                    text = jQuery('#netgsm_auth_users_text').val();
                    jQuery("#netgsm_auth_users_selected option").each(function() {
                        variable.push(jQuery(this).val());
                    });
                    text = variable.join(',');
                    jQuery('#netgsm_auth_users_text').val(text);
                    tempSelect = '';
                }
            });

            jQuery('#netgsm_delete_user_btn').click(function() {
                if (jQuery('#netgsm_auth_users_selected option:selected').val() != null) {
                    var tempSelect = jQuery('#netgsm_auth_users_selected option:selected').val();
                    jQuery('#netgsm_auth_users_selected option:selected').remove().appendTo('#netgsm_auth_users');
                    jQuery("#netgsm_auth_users_selected").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
                    jQuery("#netgsm_auth_users").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");

                    jQuery("#netgsm_auth_users").val(tempSelect);
                    var text = '';
                    var variable = [];
                    text = jQuery('#netgsm_auth_users_text').val();
                    jQuery("#netgsm_auth_users_selected option").each(function() {
                        variable.push(jQuery(this).val());
                    });
                    text = variable.join(',');
                    jQuery('#netgsm_auth_users_text').val(text);
                    tempSelect = '';
                }
            });
        </script>
        <div class="form-group">
            <div class="col-sm-2"></div>
            <div class="col-sm-10 text-right">
                <button class="btn btn-primary" id="login_save6" name="login_save6" onclick="login();"><i class="fa fa-folder"></i> Değişiklikleri Kaydet </button>
            </div>
        </div>
    <?php
    }
    ?>

</div>
<?php
function netgsm_findUser($users, $id)
{
    foreach ($users as $user) {
        if ($user->ID == $id) {
            return $user;
        }
    }
    return false;
}
?>