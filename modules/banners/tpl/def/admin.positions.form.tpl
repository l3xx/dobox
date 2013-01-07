<form method="post" action="" name="bannersPositionForm">
<table class="admtbl tbledit">
{if $fordev}
<tr class="required check-digits">
    <td class="row1">Ширина:</td>
    <td class="row2">
        <input type="text" name="width" value="{$aData.width|default:''}" style="width:50px;" /> 
        <span class="desc">&nbsp;&nbsp;px</span>
    </td>
</tr>
<tr class="required check-digits">
    <td class="row1">Высота:</td>
    <td class="row2">
        <input type="text" name="height" value="{$aData.height|default:''}" style="width:50px;" />
        <span class="desc">&nbsp;&nbsp;px</span>
    </td>
</tr>
<tr class="required check-latin">
    <td class="row1">Keyword:</td>
    <td class="row2">
        <input type="text" name="keyword" value="{$aData.keyword|default:''}" style="width:550px;"  maxlength="30"/>
    </td>
</tr>
{/if}
<tr class="required">
    <td class="row1" width="120">Название позиции:</td>
    <td class="row2">
        <input type="text" name="title" value="{$aData.title|default:''}" style="width:550px;" />
    </td>
</tr>
<tr>
    <td class="row1">Разрешить ротацию:</td>
    <td class="row2">
        <input type="checkbox" value="1" name="rotation" {if $aData.rotation ==1}checked='checked'{/if} />
    </td>
</tr>
{if $config.banners_show_to_reguser == 1}
<tr>
    <td class="row1">Отключить для зарегистрированных пользователей:</td>
    <td class="row2">
        <input type="checkbox" value="1" name="show_to_reguser" {if $aData.show_to_reguser ==1}checked='checked'{/if} />
    </td>
</tr>
{/if}
<tr>
    <td class="row1">Включена</td>
    <td class="row2">
        <input type="checkbox" value="1" name="enabled" {if $aData.enabled == 1}checked='checked'{/if} />
    </td>
</tr>
<tr class="footer">
    <td colspan="2" class="row1">
        <input type="submit" class="button submit" value="Сохранить" />
        {if $aData.edit && $fordev} <input type="button" class="button delete" value="Удалить" onclick="if(confirm('Удалить позицию?')) document.location='index.php?s={$class}&amp;ev=position_confirm_delete&amp;key={$aData.keyword}';" /> {/if}
        <input type="button" class="button cancel" value="Назад" onclick="history.back();" />
    </td>
</tr>
</table>
</form>
<script type="text/javascript">
{literal}
//<![CDATA[ 
    var fchecker = new bff.formChecker(document.forms.bannersPositionForm);
//]]> 
{/literal}
</script>
