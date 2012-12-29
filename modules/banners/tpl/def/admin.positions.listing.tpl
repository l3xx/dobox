<table class="admtbl tblhover">
<tr class="header">
   {if $fordev} <th align="left">Keyword</th>{/if}
           <th align="left">Название</th>
           <th>Размер</th>
           <th>Ротация</th>
           <th>Баннеры</th>
    {if $config.banners_show_to_reguser == 1} 
            <th>Отобр.для ЗП</th>
    {/if}
            <th width="100">Действие</th>
</tr> 
{if $aData|@count>0}   
{assign var="it" value="1"}
{foreach from=$aData item=v key=k name=dt}
<tr class="row{$smarty.foreach.dt.iteration%2}">
        {if $fordev}<td align="left">{$v.keyword}</td>{/if}
        <td align="left">{$v.title}</td>
        <td >{$v.width}x{$v.height}</td>
        <td>{if $v.rotation==1} Да{else}Нет{/if}</td>
        <td>{if $v.banner_cnt}<a href="{$site_url}/admin/index.php?s=banners&ev=listing&amp;pos={$v.keyword}" >{$v.banner_cnt}</a>{else}Нет{/if}</td>
{if $config.banners_show_to_reguser == 1}
        <td>{if $v.show_to_reguser==1}Нет {else}Да{/if}</td>
{/if}
        <td>
            <a class="but {if $v.enabled=="1"}unblock{else}block{/if}" title="{if $v.enabled=="1"}выключить{else}включить{/if}" onclick="pos_toggle('{$v.keyword}',this)"></a>
            <a class="but edit" title="{$aLang.edit}" href="index.php?s={$class}&amp;ev=position_edit&amp;key={$v.keyword}" ></a>
            {if $fordev}<a class="but del" title="{$aLang.delete}" href="index.php?s={$class}&amp;ev=position_confirm_delete&amp;key={$v.keyword}" onclick="{$aLang.delete_confirm}"></a>{/if}
        </td>       
</tr>
{/foreach}
{else}
<tr class="norecords">
    <td colspan="7">нет позиций</td>
</tr>
{/if}
</table>
<script type="text/javascript">
{literal}
//<![CDATA[
    function pos_toggle(key, row)
    {
        bff.ajax('index.php?s=banners&ev=ajax&act=position_toggle', {keyword:key}, function(data){
            if(data && data!=0)
            {
                if(data == 'Y')
                {
                   $(row).attr('class','but unblock'); 
                }
                else{
                   $(row).attr('class','but block');  
                }
            }
        });    
    }
//]]> 
{/literal}
</script>
 