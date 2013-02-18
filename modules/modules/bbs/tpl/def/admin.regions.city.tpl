
<form action="index.php" method="get" name="bbsRegionsFilter">
<input type="hidden" name="s" value="{$class}" />
<input type="hidden" name="ev" value="{$event}" /> 
<input type="hidden" name="cid" value="{$cid}" />
<input type="hidden" name="main" value="{$aData.main}" />
<div class="actionBar">
    <div class="left">    
        Страна: <select name="cid" onchange="bff.redirect('index.php?s=bbs&ev=regions_region&pid='+(this.value));" />{$aData.country_options}</select>
        Регион: <select name="pid" style="width:200px;" onchange="document.forms.bbsRegionsFilter.submit();">{$aData.region_options}</select>
        <span id="bbs-regions-progress" style="margin-right:5px; display:none;" class="progress"></span>
    </div>
    <div class="right" style="padding-top:5px;">
        {if $aData.main}<a href="#" onclick="document.forms.bbsRegionsFilter.elements['main'].value = 0; document.forms.bbsRegionsFilter.submit(); return false;">все города/станции</a>{else}<a href="#" onclick="document.forms.bbsRegionsFilter.elements['main'].value = 1; document.forms.bbsRegionsFilter.submit(); return false;">показать только основные</a>{/if}
    </div>
    <div class="clear"></div>
</div>
</form>
            
<table class="admtbl tblhover" id="bbs-regions-listing">
<tr class="header nodrag nodrop">
    {if $fordev}<th width="65">ID</th>{/if}
    <th align="left" style="padding-left:5px;">Город</th>
    <th align="center" width="80">Объявления</th>
    <th width="120">Действие</th>
</tr>
{foreach from=$aData.cities item=v key=k}
<tr class="row{$k%2}" id="dnd-{$v.id}">              
    {if $fordev}<td class="small">{$v.id}</td>{/if}
    <td align="left" rel="title">{$v.title}</td>
    <td>{if $v.items}<a href="index.php?s=bbs&amp;ev=items_listing&amp;advanced=1&amp;country_id={$cid}&amp;&amp;region_id={$pid}&amp;city_id={$v.id}">{$v.items}{else}0{/if}</td>
    <td>
        <a class="but {if $v.enabled}un{/if}block" onclick="return bbsreg.toggle({$v.id}, this, 0);" href="#"></a>
        <a class="but {if !$v.main}un{/if}fav" title="основной" onclick="return bbsreg.toggle({$v.id}, this, 1);" href="#"></a>
        <a class="but edit" href="#" onclick="return bbsreg.edit({$v.id});"></a>
        {if !$v.items}<a class="but del" href="#" onclick="return bbsreg.del({$v.id}, this);"></a>{/if}
    </td>
</tr>
{foreachelse}
<tr class="norecords">
    <td colspan="{if $fordev}4{else}3{/if}">нет городов/станций</td>
</tr>
{/foreach}
</table>   

{include file='admin.regions.common.tpl'}  