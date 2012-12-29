
<table class="admtbl tblhover" id="bbs-regions-listing">
<tr class="header nodrag nodrop">
    {if $fordev}<th width="65">ID</th>{/if}
    <th align="left">Название</th>
    <th width="80">Объявления</th>
    <th width="80">Действие</th>
</tr>
{foreach from=$aData item=v key=k}
<tr class="row{$k%2}" id="dnd-{$v.id}">              
    {if $fordev}<td class="small">{$v.id}</td>{/if}
    <td align="left" rel="title">{$v.title}</td>
    <td>{if $v.items}<a href="index.php?s=bbs&amp;ev=items_listing&amp;advanced=1&amp;country_id={$v.id}">{$v.items}{else}0{/if}</td>
    <td>
        <a class="but {if $v.enabled}un{/if}block" onclick="return bbsreg.toggle({$v.id}, this);" href="#"></a>
        <a class="but edit" href="#" onclick="return bbsreg.edit({$v.id});"></a>
    </td>
</tr>
{foreachelse}
<tr class="norecords">
    <td colspan="{if $fordev}4{else}3{/if}">
        нет стран (<a href="index.php?s=bbs&amp;ev=cities_add">добавить</a>)
    </td>
</tr>
{/foreach}
</table>  

{include file='admin.regions.common.tpl'}
