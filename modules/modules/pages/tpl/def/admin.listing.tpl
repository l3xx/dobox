<table class="admtbl tblhover">
<tr class="header">
    {if $fordev}<th width="60">ID</th>{/if}
    <th align="left">Заголовок</th>
    <th width="250">Имя файла</th>
    <th width="85">Действие</th>
</tr>
{foreach from=$aData item=v key=k}
<tr class="row{$k%2}">
    {if $fordev}<td class="small">{$v.id}</td>{/if}
    <td align="left">{$v.title}</td>
    <td><a href="{$site_url}/{$v.filename}{$smarty.const.PAGES_EXTENSION}" class="pageview" target="_blank">{$v.filename}{$smarty.const.PAGES_EXTENSION}</a></td>
    <td>
        <a class="but edit" title="редактировать" href="index.php?s={$class}&amp;ev=edit&amp;rec={$v.id}"></a>
        {if !$v.issystem}<a class="but del" title="удалить" href="index.php?s={$class}&amp;ev=action&amp;type=delete&amp;rec={$v.id}" onclick="if(!confirm('Удалить страницу?')) return false;"></a>{/if}
    </td>
</tr>
{foreachelse}
<tr class="norecords">
    <td colspan="{if $fordev}4{else}3{/if}">нет страниц</td>
</tr>
{/foreach}
</table>
<br /> 
<div><a class="but add" title="добавить страницу" href="index.php?s={$class}&amp;ev=add"></a></div>
