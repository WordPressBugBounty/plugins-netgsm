<?php

if (!current_user_can('administrator')) {
    return;  // Admin olmayan kullanıcılar erişemez
}
?>
<div class="tab-pane" id="voip">
    <div class="row" >
        <div class="col-md-12">
            <div class="pull-right" id="santralInfo"></div>
            <hr>
            <div id="santralTable"></div>
        </div>
    </div>
</div>