
<form method="post" action="" name="modifyGroupForm">
<table class="admtbl tbledit">
<tr class="required">
    <td class="row1 field-title" width="160">Название группы:</td>
    <td class="row2">
        <input type="text" name="title" value="{$aData.title|default:''}" class="text-field" />
    </td>
</tr>
<tr class="required">
    <td class="row1 field-title">Идентификатор группы (GID):</td>
    <td class="row2">
        <input type="text" size="40" name="keyword" value="{$aData.keyword|default:''}" class="text-field" />
    </td>
</tr>                                           
<tr>
    <td class="row1 field-title">Доступ к админ. панели:</td>
    <td class="row2"><input name="adminpanel" type="checkbox" {if $aData.adminpanel|default:''}checked{/if} /></td>
</tr>
{if $fordev}
<tr>
    <td class="row1 field-title">Системная группа:</td>
    <td class="row2"><input name="issystem" type="checkbox" {if $aData.issystem|default:''}checked{/if} /></td>
</tr>
{/if}
<tr>
    <td class="row1 field-title">Цвет:</td>
    <td class="row2">
        <input type="text" name="color" value="{$aData.color|default:''}" style="margin-right:10px;" class="text-field" />
        {if $event=='group_edit' || $aData.color|default:''}<span style="background-color:{$aData.color|default:''}; width:20px; height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>{/if}
        <span style="color:#666;">примеры: red, #f00, #ff0000</span>
    </td>
</tr> 
<tr class="footer">
    <td class="row1" colspan="2">
        <input type="submit" class="button submit" value="{if $event=='group_add'}Создать{else}Сохранить{/if}" {if $aData.issystem|default:''}onclick="if(!confirm('Вы уверены?')) return false;"{/if} />

        {if $fordev && $aData.deletable && $event=='group_edit'}
            <input type="button" class="button delete" value="Удалить" onclick="bff.redirect('index.php?s={$class}&amp;ev=group_delete&amp;rec={$aData.group_id}', 'удалить группу \'{$aData.title}\'?');" />
        {/if}
        <input type="button" class="button cancel" value="Отмена" onclick="javascript: history.back();" />
        
    </td>
</tr>
</table>
</form>

<script type="text/javascript">
{literal}
//<![CDATA[ 
var fchecker = new bff.formChecker( document.forms.modifyGroupForm, {}, 
                function(){ document.forms.modifyGroupForm['title'].focus();  } );     
//]]> 
{/literal}
</script>