<form action="index.php" method="get" name="filters">
<input type="hidden" name="s" value="{$class}" />
<input type="hidden" name="ev" value="{$event}" />
<input type="hidden" name="order" value="{$order_by},{$order_dir}" /> 
<input type="hidden" name="page" value="1" />
<div class="actionBar" style="height:20px;">
    <div class="left">
        <label class="bold">Категория:
        <select name="category" style="width:218px;" onchange="if(this.value==0) this.disabled=true; document.forms.filters.submit();">
            <option value="0" class="bold">все категории</option>
            {$aData.categories_options}
        </select></label>
        <span id="progress-faq-questions" style="margin-left:15px; display:none;" class="progress"></span>
    </div>    
    <div class="right">
        <a href="index.php?s=faq&amp;ev=questions_add{if $aData.cat > 0}&amp;parent={$aData.cat}{/if}" style="margin-top: 3px; display:block;">+ добавить вопрос</a>
    </div>
</div>
</form>

<table class="admtbl tblhover" id="faq_questions_listing">
<tr class="header nordrag nodrop">
    {if $fordev}
    <th width="70">
        {if $order_by=='id'}<a href="javascript: doOrder('{$order_by},{$order_dir_needed}');">ID<div class="order-{$order_dir}"></div></a> 
        {else}<a href="javascript: doOrder('id,asc');">ID</a>{/if}
    </th>
    {/if}
    <th style="text-align:left;">
        {if $order_by=='question'}<a href="javascript: doOrder('{$order_by},{$order_dir_needed}');">Вопрос<div class="order-{$order_dir}"></div></a> 
        {else}<a href="javascript: doOrder('question,asc');">Вопрос</a>{/if}
    </th>
    <th width="85">
        {if $order_by=='created'}<a href="javascript: doOrder('{$order_by},{$order_dir_needed}');">Создан<div class="order-{$order_dir}"></div></a> 
        {else}<a href="javascript: doOrder('created,asc');">Создан</a>{/if}
    </th>
    <th width="60">Действие</th>
</tr>
{foreach from=$aData.questions item=v key=k}
<tr class="row{$k%2}" id="dnd-{$v.id}">
    {if $fordev}<td class="small">{$v.id}</td>{/if}
    <td align="left">
        <a href="/help/{$v.id}" class="but linkout" target="_blank"></a><a href="index.php?s={$class}&amp;ev=questions_edit&amp;rec={$v.id}">{$v.question}</a><br />
        <a href="#" onclick="faqOnCategory('{$v.category_id}');" class="desc">{$v.ctitle}</span>
    </td>                                                    
    <td class="small">{$v.created|date_format2}<br /><span class="desc">{$v.creator}</span></td>
    <td>
        <a class="but edit" title="Редактировать" href="index.php?s={$class}&amp;ev=questions_edit&amp;rec={$v.id}"></a>
        <a class="but del" title="Удалить" href="javascript:void(0);" onclick="bff.ajaxDelete('Удалить вопрос?', {$v.id}, 'index.php?s={$class}&ev=questions_ajax&act=delete', this, {ldelim}progress: '#progress-faq-questions'{rdelim}); return false;"></a>
    </td>
</tr>  
{foreachelse}
<tr class="norecords">
    <td colspan="4">нет вопросов в выбранной категории</td>
</tr>
{/foreach}
</table>
<div>
    <div class="left">{$pagenation_template|default:''}</div>
    <div style="width:80px; text-align:right; margin-top:7px;" class="right desc">{if $aData.cat > 0}&nbsp;&nbsp; &darr; &uarr;{/if}</div>
    <div class="clear"></div>
</div>

<script type="text/javascript">
var hasRot = {if $aData.cat > 0}1{else}0{/if}
//<![CDATA[
{literal}
function doOrder(by)
{
   document.forms.filters['order'].value = by;
   document.forms.filters['page'].value = 1;
   document.forms.filters.submit(); 
}

function faqOnCategory(id)
{
   document.forms.filters['category'].value = id; 
   document.forms.filters.submit(); 
}

if(hasRot) {
    $(function(){       
        bff.initTableDnD($('#faq_questions_listing'), 'index.php?s=faq&ev=questions_ajax&act=rotate', '#progress-faq-questions');
    });
}

{/literal}
//]]> 
</script>