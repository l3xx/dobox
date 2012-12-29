{if $aData|@count>0}                                          
<div class="actionBar">
    <form action="index.php" method="get" name="statListingFilterForm">
        <input type="hidden" name="s" value="{$class}" />
        <input type="hidden" name="rec" value="{$aData.0.id}" />
        <input type="hidden" name="ev" value="{$event}" /> 
        <input type="hidden" name="page" value="{$f.page}" />
        <input type="hidden" name="order" value="{$order_by},{$order_dir}" /> 
        <div class="left">       
            <span>Статистика:</span>&nbsp;с&nbsp;
            <input type="text" name="date_start" value="{$f.date_start|default:''}" style="width:75px;" />
            &nbsp;по&nbsp;
            <input type="text" name="date_finish" value="{$f.date_finish|default:''}" style="width:75px;" />
            &nbsp;&nbsp;<label >За последние 24 часа:&nbsp;<input type="checkbox" name="cur_day" value="1" {if $f.cur_day ==1} checked="checked"{/if}/></label>
            &nbsp;<input type="submit" class="button submit" value="Показать" />
        </div>
        <div class="right">

        </div>
        <div class="clear"></div>
    </form>
</div> 
<div class="left" style="padding:10px;">
    {if $aData.0.banner_type==3}
        {$aData.0.banner}
    {elseif $aData.0.banner_type==2}
        <script type="text/javascript">
        var fG = new Flash ();
        var width = {$aData.0.positioninfo.width}*0.25;
        var height = {$aData.0.positioninfo.height}*0.25;
        fG.setSWF ('{$site_url}/files/bnnrs/{$aData.0.id}_src_{$aData.0.banner}', width, height);
        fG.setParam ('wmode', 'transparent');
        fG.display ();
        </script>
    {else}
        <img src="{$aData.0.img_thumb}" title="" alt="" />
    {/if}
</div>
<div class="bn_statinfo" style="margin-top:10px;">
    <div><b>Лимит показов:</b> &nbsp;{if $aData.0.show_limit =='0'} нет {else}{$aData.0.show_limit}{/if}</div>
    <div><b>Позиция: </b>&nbsp;{$aData.0.positioninfo.title}&nbsp;(ширина :{$aData.0.positioninfo.width}px;высота {$aData.0.positioninfo.height}px;)</div>
    <div><b>Время показа:</b> &nbsp; {$aData.0.show_start|date_format:"%d.%m.%Y"} - {$aData.0.show_finish|date_format:"%d.%m.%Y"}</div>
    <div><b>Статус:</b>&nbsp;{if $aData.0.enabled == 1}включен{else}выключен{/if}</div>
</div>
<table class="admtbl tblhover">
<tr class="header">
    <th width="50">
        {if $order_by=='period'}
            <a href="javascript:void(0);" onclick="orderList('period,{$order_dir_needed}');">Дата</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('period,asc');">Дата</a>
        {/if}
    </th>
    <th>
        {if $order_by=='shows'}
            <a href="javascript:void(0);" onclick="orderList('shows,{$order_dir_needed}');">Показов</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('shows,asc');">Показов</a>
        {/if}    
    </th>
    <th>
        {if $order_by=='clicks'}
            <a href="javascript:void(0);" onclick="orderList('clicks,{$order_dir_needed}');">Переходов</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('clicks,asc');">Переходов</a>
        {/if}        
    </th>
    <th width="65">
        {if $order_by=='ctr'}
            <a href="javascript:void(0);" onclick="orderList('ctr,{$order_dir_needed}');">CTR(%)</a>
            <div class="order-{$order_dir}"></div></a>
        {else}
            <a href="javascript:void(0);" onclick="orderList('ctr,asc');">CTR(%)</a>
        {/if}    
    </th>
</tr>
{if !$bNorecords} 
    {foreach from=$aData item=v key=k}
    <tr class="row{$k%2}" {if !$v.enabled} style="color:#808080"{/if} align="center">
        <td>{$v.period|date_format:"%d.%m.%Y"}</td>
        <td>{$v.shows|default:'0'}</td>
        <td>{$v.clicks|default:'0'}</td>
        <td>{$v.ctr}</td>
    </tr>
    {/foreach}
{else}
    <tr class="norecords">
        <td colspan="4">нет данных (возможно баннер еще не просматривался)</td>
    </tr>
{/if}
</table> 
{else}
<table class="admtbl">
<tr class="norecords">
    <td colspan="4">нет данных (возможно баннер еще не просматривался)</td>
</tr>
</table>
{/if}
{$pagenation_template|default:''}
<script type="text/javascript" charset="utf-8">
{literal}
    $(document).ready(function (){
        $('input[name^=date_]').attachDatepicker({yearRange: '-5:+5'});
    });
    
    function orderList(order)
    {
       $('[name=order]').val(order).parent().submit();
        
    }
{/literal}    
</script>

