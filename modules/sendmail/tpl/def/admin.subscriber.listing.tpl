
<table class="admtbl">
<tr align="center">
	<th width="30">#</th>
	<th>
        {if $order_by=='name'}
            <a href="index.php?s={$class}&amp;ev={$event}&amp;order=name,{$order_dir_needed}&amp;page=1">Имя</a>
            <div class="order-{$order_dir}"></div>
        {else}
            <a href="index.php?s={$class}&amp;ev={$event}&amp;order=name,asc&amp;page=1">Имя</a>
        {/if}
    </th>
    <th>
        {if $order_by=='email'}
            <a href="index.php?s={$class}&amp;ev={$event}&amp;order=email,{$order_dir_needed}&amp;page=1">E-mail</a>
            <div class="order-{$order_dir}"></div> 
        {else}
            <a href="index.php?s={$class}&amp;ev={$event}&amp;order=email,asc&amp;page=1">E-mail</a>
        {/if}
    </th>
    <th width="120">
        {if $order_by=='create_datetime'}
            <a href="index.php?s={$class}&amp;ev={$event}&amp;order=create_datetime,{$order_dir_needed}&amp;page=1">Дата подписки</a>
            <div class="order-{$order_dir}"></div> 
        {else}
            <a href="index.php?s={$class}&amp;ev={$event}&amp;order=create_datetime,asc&amp;page=1">Дата подписки</a>
        {/if}
    </th>
	<th width="80">{$aLang.action}</th>
</tr>
{if $aData|@count >0}
{foreach from=$aData item=v key=k}
<tr class="row{$k%2}" align="center">
	<td class="alignCenter">{$k+1}</td>
	<td align="left"><span style="color:gray; font-weight:bold;">{$v.name}</span></td>
    <td align="left">{$v.email}</td>
    <td class="alignCenter">{$v.create_datetime|date_format:"%d.%m.%Y"}</td> 
	<td class="alignCenter">
        <a class="but edit" title="редактировать" href="index.php?s={$class}&amp;ev=subscriber_edit&amp;rec={$v.id}"></a>
        <a class="but del" title="удалить" href="index.php?s={$class}&amp;ev=subscriber_delete&amp;rec={$v.id}" onclick="if(!confirm('{$aLang.delete}?')) return false;"></a>
    </td>
</tr>
{/foreach}
{else}
    <tr class="norecords">
        <td colspan="5">нет записей (<a title="Добавить" href="index.php?s={$class}&amp;ev=subscriber_add">{$aLang.add}</a>)</td>
    </tr>
{/if}
</table>

{if $aData|@count >0}
<br />
{$pagenation_template}
{/if}
<br />
<div id="divlnk"><a class="but add" title="{$aLang.add}" href="index.php?s={$class}&ev=subscriber_add"></a></div>