
<table class="admtbl tblhover">
<tr class="header">
	{if $fordev}<th width="50">ID</th>{/if}
    <th align="left">Название группы<div class="order-asc"></div></th>
    {if $fordev}<th width="110">Keyword</th>{/if}
	<th>Доступ</th>
    <th width="80">Действие</th>
</tr>
{foreach from=$aData item=v key=k}
<tr class="row{$k%2}">
	{if $fordev}<td class="small">{$v.group_id}</td>{/if}
	<td align="left"><span style="color:{$v.color|default:'gray'}; font-weight:bold;">{$v.title}</span></td>
    {if $fordev}<td class="small">{$v.keyword}</td>{/if}
	<td>
        {if !$v.adminpanel}<span class="desc">нет</span>
        {else}<a href="index.php?s={$class}&amp;ev=group_permission_listing&amp;rec={$v.group_id}">доступ</a>{/if}
    </td>                   
    <td>
    {if ((!$v.issystem && $bManageNonsystemGroups) || $fordev) }
        <a class="but edit" title="редактировать" href="index.php?s={$class}&amp;ev=group_edit&amp;rec={$v.group_id}"></a>
        <a class="but del" title="удалить" href="index.php?s={$class}&amp;ev=group_delete&amp;rec={$v.group_id}" onclick="if(!confirm('Удалить группу \'{$v.title}\'?')) return false;" ></a>
    {else}
        <span class="desc">нет</span>
    {/if}
    </td> 
</tr>
{/foreach}
</table>
{if $bManageNonsystemGroups || $fordev}
    <br />
    <a class="but add" title="добавить" href="index.php?s={$class}&ev=group_add"></a>
    <br />
{/if}