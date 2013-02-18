<div class="actionBar">
    <form action="index.php" method="get" name="bannersListingFilterForm">
        <input type="hidden" name="s" value="{$class}" />
        <input type="hidden" name="ev" value="{$event}" />
        <input type="hidden" name="order" value="{$order_by},{$order_dir}" /> 
        <input type="hidden" name="page" value="1" /> 
        <div class="left">       
            <span>Раздел:</span>
            <select name="cat" style="width:180px;">{$aCategories.options}</select>            
            &nbsp;&nbsp;                     
            <span>Позиция:</span>
            <select name="pos" style="width:210px;">
                 <option value="">Все позиции</option>
                {foreach from=$aPositions item=v key=k}
                    <option value="{$v.keyword}" {if $aInfo.cur_pos == $v.keyword}selected="selected"{/if}>{$v.title}&nbsp;({$v.width}x{$v.height})</option>
                {/foreach}
            </select>
            &nbsp;&nbsp;
            <span>Статус: </span>
            <select name="en" style="width:120px;">
                <option value="0" {if $aInfo.cur_stat == ""}selected="selected"{/if}>любой</option>
                <option value="N" {if $aInfo.cur_stat == "N"}selected="selected"{/if}>выключен</option>  
                <option value="Y" {if $aInfo.cur_stat == "Y"}selected="selected"{/if}>включен</option>
            </select>
            &nbsp;<input class="button submit" type="submit" value="найти"/>
        </div>
        <br/>    
        <div class="left" style="padding-top: 5px;">       
            <span>Дата показа:</span>&nbsp;с&nbsp;
            <input type="text" name="show_start" value="{$aInfo.show_start|date_format:"%d-%m-%Y"}" style="width:75px;" />
            &nbsp;по&nbsp;
            <input type="text" name="show_finish" value="{$aInfo.show_finish|date_format:"%d-%m-%Y"}" style="width:75px;" />
            &nbsp;&nbsp;<label >Показанные за последние 24 часа:&nbsp;<input type="checkbox" name="cur_day" value="1" {if $aInfo.cur_day ==1} checked="checked"{/if}/></label>
        </div>
        <div class="clear"></div>
    </form>     
</div> 
<table class="admtbl tblhover">
<tr class="header">
    <th width="65">#</th>
    <th>
        {if $order_by=='title'}
            <a href="javascript:void(0);" onclick="orderList('title,{$order_dir_needed}');">Баннер</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('title,asc');">Баннер</a>
        {/if}
    </th>
    <th width="150">Раздел</th>
    <th width="60">Лимиты</th>
    <th width="65">
        {if $order_by=='show_start'}
            <a href="javascript:void(0);" onclick="orderList('show_start,{$order_dir_needed}');">Начало показа </a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('show_start,desc');">Начало показа</a>
        {/if}
    </th>
    <th width="65">
        {if $order_by=='show_finish'}
            <a href="javascript:void(0);" onclick="orderList('show_finish,{$order_dir_needed}');">Конец показа</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('show_finish,desc');">Конец показа</a>
        {/if}
    </th>
    <th width="65">
        {if $order_by=='numshow'}
            <a href="javascript:void(0);" onclick="orderList('numshow,{$order_dir_needed}');">Показов</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('numshow,desc');">Показов</a>
        {/if}
    </th>
    <th width="65">
        {if $order_by=='numclick'}
            <a href="javascript:void(0);" onclick="orderList('numclick,{$order_dir_needed}');">Кликов</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('numclick,desc');">Кликов</a>
        {/if}
    </th>
    <th width="65">
        {if $order_by=='ctr'}
            <a href="javascript:void(0);" onclick="orderList('ctr,{$order_dir_needed}');">CTR(%)</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('ctr,desc');">CTR(%)</a>
        {/if}
    </th>
    <th width="155">{$aLang.action}</th>
</tr> 
{if $aData|@count>0}                                                             
{foreach from=$aData item=v key=k}
<tr class="row{$k%2}" {if !$v.enabled} style="color:#808080"{/if} align="center">
        <td>{$k+1}</td>
        <td class="alignCenter" width="200">
            {$v.positioninfo.title}&nbsp;<span class="desc small">({$v.positioninfo.width}x{$v.positioninfo.height})</span> <br />
            <a href="http://{$v.clickurl}" class="but linkout" target="_blank"></a><a href="javascript:void(0)" onclick="return showBanner({$v.id});" title="open" class="button" style="width:70px;">просмотреть</a>
        </td>
        <td class="small">{$v.cat}</td>
        <td>{if $v.show_limit=="0"}нет{else}{$v.show_limit}{/if}</td>
        <td>{$v.show_start|date_format:"%d.%m.%Y"|default:"-"}</td>
        <td>{$v.show_finish|date_format:"%d.%m.%Y"|default:"-"}</td>
        <td>{$v.numshow|default:'0'}</td>
        <td>{$v.numclick|default:'0'}</td>
        <td>{$v.ctr}</td>
        <td>
            <a class="but {if $v.enabled=="1"}unblock{else}block{/if}" title="{if $v.enabled=="1"}выключить{else}включить{/if}" onclick="bn_toggle('{$v.id}',this)"></a>
            <a class="but edit" title="{$aLang.edit}" href="index.php?s={$class}&amp;ev=edit&amp;rec={$v.id}" ></a>
            <a class="but sett" title="Посмотреть статистику" href="index.php?s={$class}&amp;ev=statistic&amp;rec={$v.id}" ></a>
            <a class="but del" title="{$aLang.delete}" href="index.php?s={$class}&amp;ev=delete&amp;rec={$v.id}" onclick="{$aLang.delete_confirm}"></a>
        </td>       
</tr>
{/foreach}
{else}
<tr class="norecords">
    <td colspan="10">нет баннеров</td>
</tr>
{/if}
</table>
<script type="text/javascript">
{literal}
//<![CDATA[
    $(function () {
        $('input[name^=show_]').attachDatepicker({yearRange: '-5:+5'});
    });
    function bn_toggle(val,row)
    {
        bff.ajax('index.php?s=banners&ev=ajax&act=banner_toggle', {rec:val}, function(data){
            if(data && data!=0) {
                if(data == 'Y') $(row).attr('class','but unblock'); 
                else            $(row).attr('class','but block');  
            }
        });    
    }
    function orderList(order)
    {
       $('[name=order]').val(order).parent().submit();
        
    }
    function showBanner(id)
    {
        bff.ajax('index.php?s=banners&ev=showBanner&rec='+id, {}, function(data){
            if(data)
            {
                $.fancybox(data).resize();
            }
        });
        return false;
    }
//]]> 
{/literal}
</script>
