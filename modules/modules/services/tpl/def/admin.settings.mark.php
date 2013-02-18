<?php
    extract($aData);
?>

<form action="" id="j-services-form-mark" onsubmit="return false;">
<input type="hidden" name="id" value="<?= $id ?>" />
<table class="admtbl tbledit">
    <tr>
        <td class="row1" width="70"><span class="field-title">Цена</span>:</td>
        <td class="row2">
            <input type="text" name="price" value="<?= $settings['price'] ?>" maxlength="6" style="width: 100px;" /><?= $price_prefix; ?>
        </td>
    </tr>
    <tr>
        <td class="row1"><span class="field-title">Описание</span>:</td>
        <td class="row2">
            <textarea name="description" class="stretch wy" id="svc_wy_mark" style="height: 135px; width: 630px;"><?= $description ?></textarea>    
        </td>
    </tr>      
    <tr>
        <td></td>
        <td class="footer">
            <div class="left"><input type="button" class="button submit" value="Сохранить" onclick="jServices.update('#j-services-form-mark');" /></div>
            <div class="right desc" style="margin-right: 5px;"><? if($modified_uid>0){ ?>последние изменения: <?= tpl::date_format2($modified, true); ?>, <a class="bold desc" href="#" onclick="return bff.userinfo(<?= $modified_uid ?>);"><?= $modified_login ?></a><? } ?></div>
            <div class="clear"></div>
        </td>
    </tr>
</table>
</form>
