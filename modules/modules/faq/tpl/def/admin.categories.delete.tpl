
<script type="text/javascript">
var cnt_items = {$aData.cnt_items|default:'0'};     
{literal}
function submitDelete()
{
    if(!cnt_items)
        return true;
        
    if(document.getElementById('category').value <= 0)
    {
        var txt = '';
        if(cnt_items>0)
             txt += ', все вопросы ('+cnt_items+')';             
                 
        if( confirm('Не указав категорию'+txt+'\nбудут удалены. Продолжить?') )
        {
            return true;
        }
        return false; 
    }
    return true;
}
{/literal}
</script>

<form action="" method="post" onsubmit="return submitDelete();" >
<input type="hidden" name="id" value="{$aData.id|default:''}" />   
<table class="admtbl tbledit">
<tr>
    <td class="row1">
        {if $aData.cnt_items|default:'0' > 0}
            Прежде чем удалить категорию '<b>{$aData.title|default:''}</b>', укажите категорию к которой будут <br /> 
            относиться {if $aData.cnt_items>0} все вопросы (<b>{$aData.cnt_items}</b>){/if} относившиеся к удаляемой категории:
            <select name="next" id="category" style="margin-top:10px; width:150px;">
                <option selected="selected" value="0">Выбрать</option>
                {$aData.categories}
            </select>
        {else}
            Вы уверены, что хотите удалить категорию '<b>{$aData.title|default:''}</b>'?            
        {/if}
    </td>
</tr>
<tr class="footer">
    <td class="row1">
        <input type="submit" class="button delete" value="Удалить" />
        <input type="button" class="button cancel" value="Отмена" onclick="return history.back();" />
    </td>
</tr>
</table>
</form>