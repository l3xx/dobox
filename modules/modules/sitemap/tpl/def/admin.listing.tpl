<table class="admtbl tblhover">
<tr class="header">                                           
    <th>Пункты меню</th>
    <th width="20">Статус</th>
    <th width="135">Действие</th>
</tr>
{if $aData|@count>0}
{foreach from=$aData item=v key=k} 
<tr class="row{$k%2}" style="height:30px;">                                                             
    <td align="left" {if $v.node}class="clr-success"{/if} style="padding-left:{math equation="x*15-10" x=$v.numlevel}px;">
        <a href="{$v.menu_link|default:''}" {if $v.node}class="clr-success"{/if}>{$v.menu_title}</a> {if $fordev && $v.keyword}(<span style="font-weight:bold;">{$v.keyword}</span>){/if}
    </td>        
    <td class="alignCenter">
        <a class="but {if $v.enabled}un{/if}block" style="margin-left:10px;" title="вкл/выкл" href="index.php?s={$class}&amp;ev=actions&amp;action=enable&amp;rec={$v.id}"></a>
    </td>
    <td align="left" style="padding-left:10px;">                                        
        <a class="but up" href="index.php?s={$class}&amp;ev=actions&amp;action=move&amp;dir=up&amp;rec={$v.id}" title="переместить вверх"></a>
        <a class="but down" href="index.php?s={$class}&amp;ev=actions&amp;action=move&amp;dir=down&amp;rec={$v.id}" title="переместить вниз"></a>
        <a class="but edit" href="index.php?s={$class}&amp;ev=edit&amp;rec={$v.id}" title="редактировать"></a>
        <a class="but del" href="index.php?s={$class}&amp;ev=actions&amp;action=delete&amp;rec={$v.id}" onclick="{$aLang.delete_confirm}" title="удалить"></a>
        {if $v.keyword && ($v.numlevel==1 || $v.numlevel==2)}
        <a class="but add" href="index.php?s={$class}&amp;ev=add&amp;pid={$v.id}" title="добавить"></a>    
        {/if}
    </td>
</tr>  
{/foreach}
{else}
<tr class="norecords">
    <td colspan="3">нет пунктов меню</td>
</tr>
{/if}
</table>
<br />

<a class="but add" href="index.php?s={$class}&amp;ev=add" title="Добавить пункт меню/новое меню"></a>
<br />
