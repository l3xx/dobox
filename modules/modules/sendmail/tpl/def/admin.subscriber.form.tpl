<form method="post" action="">
<input type="hidden" name="rec" value="{$rec}" />
<table class="admtbl">
<tr>
    <td class="row1" width="160">Имя:</td>
    <td class="row2"><input type="text" size="40" name="name" value="{$aData.name|default:''}" /></td>
</tr>
<tr>
    <td class="row1">E-mail:</td>
    <td class="row2"><input type="text" size="40" name="email" value="{$aData.email|default:''}" /></td>
</tr>                                           
<tr>
    <td colspan="2" class="row1" align="center">
        <input type="submit" class="admButton" value="{if $event=='subscriber_add'}{$aLang.create}{else}{$aLang.save}{/if}" />&nbsp;
        {if $event=='subscriber_edit'}
            <input type="button" class="admButton" value="Удалить" onclick="bff.redirect('index.php?s={$class}&amp;ev=subscriber_delete&amp;rec={$rec}', 'Удалить?');" />&nbsp;
        {/if}
        <input type="button" class="admButton" value="Отмена" onclick="bff.redirect('index.php?s={$class}&amp;ev=subscriber_listing');" />        
    </td>
</tr>
</table>
</form>