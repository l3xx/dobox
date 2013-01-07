<div class="actionBar">
    <a href="index.php?s={$class}&amp;ev=mm_add" class="but add but-text">Добавить модуль/метод</a>
    <div class="progress" style="display:none;" id="progress-mm"></div>
</div>
<table class="admtbl tblhover" id="mm-listing">
<tr class="header nodrag nodrop">
	<th align="left" width="150">ММ</th>
	<th align="left">Название</th>
	<th width="90">Действия</th>
</tr>
{foreach from=$aData item=v key=k}
<tr class="row0" id="dnd-{$v.id}" pid="0" numlevel="1">
	<td align="left" class="bold">{$v.module}</td>
	<td align="left">{$v.title}</td>
	<td>
        <a class="but del" title="удалить" href="#" onclick="mmDelete({$v.id}); return false;"></a>
    </td>
</tr>
    {foreach from=$v.subitems item=v2 key=key}
        <tr class="row1" id="dnd-{$v2.id}" pid="{$v.id}" numlevel="2">
	        <td align="left">{$v2.method}</td>
	        <td align="left">{$v2.title}</td>
	        <td>
                <a class="but del" title="удалить" href="#" onclick="mmDelete({$v2.id}); return false;"></a>
            </td>
        </tr>
    {/foreach}
{/foreach}
</table>

<script type="text/javascript">
{literal}
//<![CDATA[ 
$(function(){ 
    bff.initTableDnD('#mm-listing', 'index.php?s=dev&ev=mm_listing&act=rotate', '#progress-mm');
}); 

function mmDelete(id)
{
    bff.ajaxDelete('Удалить?', id, 'index.php?s=dev&ev=mm_listing&act=delete', false, {progress: '#progress-mm', onComplete: function(data){
        if(data){
            location.reload();
        }
    }});
}

//]]>  
{/literal}
</script>