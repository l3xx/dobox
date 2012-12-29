<form method="post" action="">      
<table class="admtbl tbledit">
<tr>
    <td class="row1">
    Прежде чем удалить позицию '<b>{$aCurInfo.pos_title}</b>', укажите позицию к которой <br> 
    будут относиться  все баннеры (<b><a href="index.php?s=banners&ev=listing&amp;pos={$aCurInfo.pos_keyword}" target="_blank">{$aCurInfo.banners_count}</a></b>) относившиеся к удаляемой позиции:
    <select style="margin-top: 10px; width: 150px;" name="position">
        <option value="0" selected="selected">Выбрать</option>
        {foreach from=$aData item=v key=k}
            <option value="{$k}">{$v.title}</option>                                                                    
        {/foreach}
    </select>
    </td>
</tr>
<tr class="footer">
    <td>
        <input type="submit" class="button delete" value="Удалить с заменой" />
        {if $fordev}<input type="button" class="button delete" value="Удалить позицию и баннеры" onclick="document.location='index.php?s={$class}&amp;ev=position_del_all&amp;key={$aCurInfo.pos_keyword}';" />{/if}
        <input type="button" class="button cancel" onclick="return history.back();" value="Отмена" />
    </td>
</tr>
</table>
</form>