
<table class="admtbl tblhover">
<tr class="header">
	<th>Шаблоны писем</th>
	<th width="100">Действие</th>
</tr>
{foreach from=$aData name=templates item=v key=k}
<tr class="row{$smarty.foreach.templates.iteration%2}">
	<td align="left">  
        <span class="bold {if !$v.impl}desc{/if}">{$v.title}</span><br /><span class="desc">{$v.description|default:''}</span>
    </td>
    <td>                                 
        <a class="but edit tblhoverlink" style="margin-top:5px; padding:0 0 0 20px; width:95px;" href="index.php?s={$class}&amp;ev=template_edit&amp;tpl={$k}">редактировать</a>
    </td>
</tr>
{foreachelse}
    <tr class="norecords">
        <td colspan="2">нет шаблонов</td>
    </tr>
{/foreach}
</table>