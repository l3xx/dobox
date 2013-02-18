<table class="admtbl tblhover" id="faq_categories_listing">
<tr class="header">
    {if $fordev}<th width="60">ID</th>{/if}
    <th align="left">Название<span id="progress-categories" style="margin-right:5px; display:none;" class="progress"></span></th>
    <th width="90">Вопросы</th>
    <th width="85">Действие</th>
</tr>
{foreach from=$aData.categories key=k item=v}
<tr class="row{$k%2}" id="dnd-{$v.id}">
    {if $fordev}<td>{$v.id}</td>{/if}
    <td align="left">{$v.title}</td>
    <td><a href="index.php?s={$class}&amp;ev=questions_listing&amp;category={$v.id}">{$v.questions}</a></td>
    <td >
        <a class="but add" title="Добавить вопрос" href="index.php?s={$class}&amp;ev=questions_add&amp;parent={$v.id}"></a>
        <a class="but edit" title="Редактировать" href="index.php?s={$class}&amp;ev=categories_edit&amp;rec={$v.id}"></a>
        <a class="but del" title="Удалить" href="index.php?s={$class}&amp;ev=categories_delete&amp;rec={$v.id}" onclick="if(!confirm('Удалить категорию?')) return false;" ></a>
    </td>
</tr>
{foreachelse}
<tr class="norecords">
    <td colspan="{if $fordev}4{else}3{/if}">нет категорий (<a href="index.php?s={$class}&amp;ev=categories_add">добавить</a>)</td>
</tr>
{/foreach}
</table>
<div>
    <div class="left"></div>
    <div style="width:80px; text-align:right; margin-top:7px;" class="right desc">{if $aData.categories|@count > 0}&nbsp;&nbsp; &darr; &uarr;{/if}</div>
    <div class="clear"></div>
</div> 

<script type="text/javascript">
    var countersCnt = {$aData.categories|@count};
    {literal}  
    $(function(){ 
    if(countersCnt>1)
        bff.initTableDnD('#faq_categories_listing', 'index.php?s=faq&ev=categories_listing&act=rotate', '#progress-categories');
    });
    {/literal}
</script> 