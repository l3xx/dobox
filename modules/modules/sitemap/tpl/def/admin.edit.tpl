<form method="post" action="">
<input type="hidden" name="rec" value="{$rec}" />
<table class="admtbl tbledit">
<tr>
    <td class="row1 field-title" width="120">Принадлежит к:</td>
    <td class="row2">{$aData.pid_options}</td>
</tr>
<tr>
    <td class="row1 field-title">Название пyнкта меню:</td>
    <td class="row2"><input type="text" name="menu_title" value="{$aData.menu_title|default:''}" style="width:300px;" /></td>
</tr>       
<tr>
    <td class="row1 field-title">Keyword:</td>
    <td class="row2"><input type="text" name="keyword" maxlength="15" value="{$aData.keyword|default:''}" style="width:300px;" /></td>
</tr>   
<tr>
    <td class="row1 field-title">Меню URL:</td>
    <td class="row2"><input type="text" name="menu_link" value="{$aData.menu_link|default:''}" style="width:300px;" /></td>
</tr>
<tr>
    <td class="row1 field-title">Открывать:</td>
    <td class="row2"><select name="menu_target" style="width:130px;">{$target_options}</select></td>
</tr>
<tr>
    <td class="row1 field-title">Meta Keywords:<br /><span class="desc">{$aLang.meta_keywords}</span></td>
    <td class="row2"><textarea name="mkeywords" class="autogrow" style="height:85px; min-height:85px;">{$aData.mkeywords|default:''}</textarea></td>
</tr>
<tr>
    <td class="row1 field-title">Meta Description:<br /><span class="desc">{$aLang.meta_description}</span></td>
    <td class="row2"><textarea name="mdescription" class="autogrow" style="height:85px; min-height:85px;">{$aData.mdescription|default:''}</textarea></td>
</tr>
<tr class="footer">
    <td class="row1" colspan="2">
        <input type="submit" class="button submit" value="Сохранить" />
        <input type="button" class="button cancel" value="Отмена" onclick="history.back();" />
    </td>
</tr>
</table>
</form>