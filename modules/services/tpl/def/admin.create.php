<?php
    extract($aData, EXTR_REFS);
?>

<form method="post" action="" name="jServiceForm" id="j-service-form">
<table class="admtbl tbledit">
<tr class="required">                          
    <td style="white-space:nowrap; width:70px;" class="row1"><span class="field-title">Название:</span></td>
    <td class="row2">
         <input type="text" name="title" id="j-service-title" class="stretch" value="<?= $title; ?>" />
    </td>
</tr>
<tr class="required check-select">                          
    <td class="row1"><span class="field-title">Тип услуги:</span></td>
    <td class="row2">
         <select name="type" id="j-service-type">
            <option value="0">не указан</option>
            <option value="2"<? if($type==2){ ?> selected="selected"<? } ?>>Объявления</option>
            <option value="1"<? if($type==1){ ?> selected="selected"<? } ?>>Другое</option>
         </select>
    </td>
</tr>
<tr class="required">                          
    <td class="row1"><span class="field-title">Keyword:</span></td>
    <td class="row2">
         <input type="text" name="keyword" id="j-service-keyword" class="stretch" value="<?= $keyword; ?>" />
    </td>
</tr>
<tr class="footer">
    <td colspan="2" class="row1">
        <input type="submit" class="button submit" value="Сохранить" />
        <input type="button" class="button cancel" value="Отмена" onclick="history.back();" />
    </td>
</tr>
</table>
</form>   


<script type="text/javascript">
$(function(){
    new bff.formChecker( document.forms.jServiceForm );
});   
</script>
