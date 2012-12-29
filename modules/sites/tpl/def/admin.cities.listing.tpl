
<form action="index.php" method="get" name="citiesFiltersForm">
<input type="hidden" name="s" value="{$class}" />
<input type="hidden" name="ev" value="{$event}" /> 
<div class="actionBar">
    <div class="left">
        {if $aData.users}
            <a href="index.php?s={$class}&amp;ev={$event}" class="bold">города с пользователями</a>
        {else}
            <a href="index.php?s={$class}&amp;ev={$event}&amp;users=1">города с пользователями</a>
        {/if}                         
    </div>
    <div class="right">
        <span id="progress-cities" style="margin-right:5px; display:none;" class="progress"></span>
        <label class="bold">Область:
        <select name="region" style="width:200px;" onchange="document.forms.citiesFiltersForm.submit();">
            <option class="bold" value="0">любая</option>
            {$aData.regions_options}
        </select></label>
    </div> 
    <div class="clear-all"></div>
</div>
</form>
            
<table class="admtbl" id="listing">
<tr class="header nodrag nodrop">
    {if $fordev}<th width="65">ID</th>{/if}
    <th align="left" style="padding-left:5px;">Город</th>
    <th align="left" width="140">Область</th>
    <th width="100">Keyword</th>
    <th width="120">Пользователи</th>
    <th width="120">Действие</th>
</tr>
{foreach from=$aData.cities item=v}
<tr class="row1" id="dnd-{$v.id}">              
    {if $fordev}<td class="small alignCenter">{$v.id}</td>{/if}
    <td align="left">{$v.title}</td>
    <td align="left" class="desc">{$aData.regions[$v.region_id].title}</td>
    <td>{$v.keyword}</td>
    <td><a href="index.php?s=users&amp;ev=listing&amp;adv=1&amp;city={$v.id}">{$v.users}</a></td>
    <td>
        <a class="but {if $v.enabled}un{/if}block" style="margin-left:10px;" id="citytoggle_enabled{$v.id}"  title="включить/выключить" onclick="citiesToggleEnabled('{$v.id}'); return false;" href="#"></a>
        <a class="but {if !$v.main}un{/if}fav" id="citytoggle_main{$v.id}"  title="сделать основным" onclick="citiesToggleMain('{$v.id}'); return false;" href="#"></a>
        <a class="but edit" href="index.php?s={$class}&amp;ev=cities_edit&amp;rec={$v.id}" title="редактировать"></a>
        {* <a class="but del" href="index.php?s={$class}&amp;ev=ajax&amp;action=city-delete&amp;rec={$v.id}" onclick="if(!confirm('Удалить город?')) return false;" title="удалить"></a> *}
    </td>
</tr>
{foreachelse}
<tr class="norecords">
    <td colspan="{if $fordev}6{else}5{/if}">нет городов (<a href="index.php?s={$class}&amp;ev=cities_add">Добавить</a>)</td>
</tr>
{/foreach}
</table>   
<div>
    <br />  
    <div class="left">
        {if $aData.main}                                                                                                        
            <a class="but add" href="#popupCityAddMain" id="popupCityAddMainLink"></a>
        {else}
            <a class="but add" title="Добавить город" href="index.php?s={$class}&amp;ev=cities_add&amp;main={$aData.main}" ></a>
        {/if}
    </div>
    {if $aData.rotate}
    <div class="right desc" style="width:80px; text-align:right;">
        <span id="progress-city" style="margin-left:5px; display:none;" class="progress"></span>
        &nbsp;&nbsp; &darr; &uarr;
    </div>
    {/if}
    <br />
</div>   
                                                                                               
<script type="text/javascript">
var citiesRotate = {$aData.rotate};
var fcCityAddMain = null;
{literal}
//<![CDATA[              
    function citiesToggleEnabled(cityID)
    {
        bff.ajaxToggle(cityID, 'index.php?s=sites&ev=cities_listing&act=toggle-enabled&rec='+cityID, 
                       {link: '#citytoggle_enabled', progress: '#progress-cities'});
    }
    function citiesToggleMain(cityID)
    {
        bff.ajaxToggle(cityID, 'index.php?s=sites&ev=cities_listing&act=toggle-main&rec='+cityID, 
                       {link: '#citytoggle_main', progress: '#progress-cities', block: 'fav', unblock: 'unfav'});
    }
    
    $(function()
    {                                          
        if(citiesRotate)
            bff.initTableDnD('#listing', 'index.php?s=sites&ev=cities_listing&act=rotate&f={/literal}{$aData.rotate_field}{literal}', '#progress-city');

        $('#popupCityAddMainLink').fancybox({
            onStart: function(){   
                if(fcCityAddMain) return;
                fcCityAddMain = new bff.formChecker( $('#popupCityAddMain form').get(0), {
                    ajax:function(data){  
                        if(data){ 
                            $.fancybox.close();
                        }}
                } );
                $('#dynTitle').autocomplete('index.php?s=sites&ev=cities_listing&act=notmain-list', 
                            {valueInput: '#camCity', width: 194});             
            }, onComplete: function(){
                $('#dynTitle').focus();
                fcCityAddMain.check();
            }
        });
        return false; 
    });
//]]>
{/literal}
</script> 

<div style="display:none;">

    <div id="popupCityAddMain" class="ipopup">
        <div class="ipopup-wrapper">
            <div class="ipopup-title">Добавить основной город</div>
            <div class="ipopup-content">

                <form action="index.php?s=sites&ev=cities_listing&act=main-add" method="post">
                    <input type="hidden" name="city" id="camCity" value="0" />
                    <table class="admtbl tbledit">
                    <tr class="required">
                        <td class="row1" width="100">
                            <span class="field-title">Укажите город</span>: 
                        </td>
                        <td class="row2">                                                                                                                                                   
                            <input type="text" id="dynTitle" name="ctitle" value="" placeholder="Введите название города" class="autocomplete" style="width:190px;" />
                        </td>
                    </tr>
                    <tr class="footer">
                        <td colspan="2" style="text-align: center;">
                            <input type="submit" class="button submit" value="Добавить" />
                            <input type="button" class="button cancel" value="Отмена" onclick="$.fancybox.close();" />
                        </td>
                    </tr>

                    </table>
                </form>
            
            </div>
        </div>
    </div>   
      
</div>
