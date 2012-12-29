<script type="text/javascript">
{literal}
//<![CDATA[
function ShowPermissons(id) {
    $(id).show(); 
}
function HidePermissons(id) {
    $(id).hide(); 
}
//]]>  
{/literal}
</script>
			
<form method="post" action="">
<table class="admtbl tblhover">
<tr class="row1">
    <td colspan="3">Доступ группы <b>{$aData.title|default:''}</b></td>
</tr>
{if $aData.keyword|default:'' == $smarty.const.USERS_GROUPS_SUPERADMIN}
    <tr class="row1">
        <td>У данной группы полный доступ</td>
    </tr>
{elseif $aData.adminpanel == 0}
    <tr class="row1">
        <td colspan="3"><span class="clr-error">У данной группы нет доступа в админ. панель. Нет смысла настраивать права :)</span></td>
    </tr>
{else}
    <tr class="header">
	    <th width="380" align="left">Модуль</th>
	    <th colspan="2" align="left">Доступ</th>
    </tr>
    {foreach from=$aData.permissions item=v key=k}
    <tr class="row0">
	    <td align="left">{$v.title}</td>
	    <td align="left" style="width:140px;" {if $v.permissed}class="clr-success"{/if}><label><input type="radio" name="permission[{$v.id}]" value="{$v.id}" {if $v.permissed}checked{/if} {if $v.subitems}onclick="HidePermissons('#subitem_{$k}')"{/if} /> Да (полный доступ)</label></td>
	    <td style="width:70px;"><label><input type="radio" name="permission[{$v.id}]" value="0" {if !$v.permissed}checked{/if} {if $v.subitems} onclick="ShowPermissons('#subitem_{$k}');"{/if} /> Нет</label></td>
    </tr>
    {if $v.subitems} 
		<tbody id="subitem_{$k}" style="{if $v.permissed}display:none;{/if}">
			{foreach from=$v.subitems item=val key=key}
			<tr class="row1">
				<td align="left" style="padding-left:20px;">{$val.title}</td>
				<td align="left" {if $val.permissed}class="clr-success"{/if}><label><input type="radio" name="permission[{$val.id}]" value="{$val.id}" {if $val.permissed}checked{/if}  /> Да</label></td>
				<td><label><input type="radio" name="permission[{$val.id}]" value="0" {if !$val.permissed}checked{/if} /> Нет</label></td>
			</tr>
			{/foreach}	
        </tbody>
    {/if}
    {/foreach}
{/if} 
<tr class="footer">
	<td colspan="3">
        {if $aData.keyword|default:'' != $smarty.const.USERS_GROUPS_SUPERADMIN && $aData.adminpanel == 1}
        <input type="submit" class="button submit" value="Сохранить" />
        {/if}
        <input type="button" class="button cancel" value="Отмена" onclick="history.back();" />
	</td>
</tr>
</table>
</form>
