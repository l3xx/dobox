<script type="text/javascript">
//<![CDATA[ 
var helper = null;                        
{literal}    
function bbsSettingsHelper()
{   
    this.currentTab = 'general';
    this.changeTab = function( tab )
    {   
        if(this.currentTab == tab)
            return;
                            
        $('div.conftab[id!="'+tab+'"]').hide();
        $('#'+tab).show();
        
        $('#tabs span.tab').removeClass('tab-active');
        $('#tabs span[rel="'+tab+'"]').addClass('tab-active');
        
        this.currentTab = document.getElementById('tab').value = tab;
    } 
    
    this.editPS = function(ps)
    {
        $('div[id^="ps_"][id!="ps_'+ps+'"]').slideUp('fast');
        $('#ps_'+ps).slideToggle('fast', function(){ 
            //document.getElementById('psupdate').value = ($(this).is(":visible")?ps:'');  
        });
        return false;
    } 
}

$(function(){
    helper = new bbsSettingsHelper();
    helper.changeTab("{/literal}{$tab|default:'general'}{literal}", 0); 
});
//]]> 
{/literal}
</script>

<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span>Объявления / Настройки</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">  

<form method="post" action="">                        
<input type="hidden" name="save" value="1" />                 
<input type="hidden" name="tab" id="tab" value="" />


<div class="tabsBar" id="tabs">    
    {foreach from=$aData.tabs key=k item=v}
        <span class="tab {if $v.a}tab-active{/if}" onclick="helper.changeTab('{$k}');" rel="{$k}">{$v.t}</span>
    {/foreach}                     
</div>   

<!-- general -->
<div id="general" class="conftab" style="display:;">
    <table class="admtbl tbledit" style="margin:10px 0 10px 10px; border-collapse:separate;"> 
    <tr style="display:none;">
	    <td class="row1 field-title">Количество объявлений на странице:</td>
	    <td class="row2">
             <input style="width:95px;" type="text" name="config[items_perpage]" value="{$aData.items_perpage|default:10}" />
        </td>
    </tr>
    <tr>
        <td class="row1 field-title" width="350">Доступное кол-во бесплатных публикаций в одном разделе:<br/>для зарегистрированных</td>
        <td class="row2">
             <input style="width:95px;" type="text" name="config[items_freepubl_category_limit_reg]" value="{$aData.items_freepubl_category_limit_reg|default:10}" />
        </td>
    </tr>
    <tr>
        <td class="row1 field-title">Доступное кол-во бесплатных публикаций в одном разделе:<br/>для <b>не</b>зарегистрированных</td>
        <td class="row2">
             <input style="width:95px;" type="text" name="config[items_freepubl_category_limit]" value="{$aData.items_freepubl_category_limit|default:10}" />
        </td>
    </tr>   
    <tr>
        <td class="row1 field-title" style="width:170px;">Максимальный объем текста сообщения (символов):<br/></td>
        <td class="row2">
             <input style="width:95px;" type="text" name="config[adtxt_limit]" value="{$aData.adtxt_limit}" />
        </td>
    </tr>    
    </table>
</div>             

<!-- svc -->  
<div id="svc" class="conftab" style="display: none;">
   
   <table class="admtbl tbledit" style="margin:10px 0 10px 10px; border-collapse:separate;"> 
        <tr>
            <td style=" vertical-align:top;">Платное размещение:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_publicate_price]" value="{$aData.svc_publicate_price}" /> <span class="desc">&nbsp;$.</span> </label>
                <textarea name="config[svc_publicate_desc]" id="svc_publicate_desc" class="svc_desc" style="height: 135px; width: 560px;">{$aData.svc_publicate_desc|default:''}</textarea>
            </td>
        </tr>   
        <tr>
            <td style="width:130px; vertical-align:top;">Поднять объявление:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_up_price]" value="{$aData.svc_up_price}" /> <span class="desc">&nbsp;$.</span> </label>
                <textarea name="config[svc_up_desc]" id="svc_up_desc" class="svc_desc" style="height: 135px; width: 560px;">{$aData.svc_up_desc|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td style=" vertical-align:top;">Выделить объявления:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_mark_price]" value="{$aData.svc_mark_price}" /> <span class="desc">&nbsp;$.</span> </label>
                <textarea name="config[svc_mark_desc]" id="svc_mark_desc" class="svc_desc" style=" height: 135px; width: 560px;">{$aData.svc_mark_desc|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td style=" vertical-align:top;">Премиум:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_premium_price]" value="{$aData.svc_premium_price}" /> <span class="desc">&nbsp;$.</span> </label>
                <textarea name="config[svc_premium_desc]" id="svc_premium_desc" class="svc_desc" style="height: 135px; width: 560px;">{$aData.svc_premium_desc|default:''}</textarea>
            </td>
        </tr>
        <tr>
            <td style=" vertical-align:top;">Публикация в прессе:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_press_price]" value="{$aData.svc_press_price}" /> <span class="desc">&nbsp;$.</span> </label>
                <textarea name="config[svc_press_desc]" id="svc_press_desc" class="svc_desc" style="height: 135px; width: 560px;">{$aData.svc_press_desc|default:''}</textarea>
            </td>
        </tr>
    </table> 
    
</div>

<!-- files -->  
<div id="files" class="conftab" style="display: none;">
   
   <table class="admtbl tbledit" style="margin:10px 0 10px 10px; border-collapse:separate;"> 
        <tr>
            <td style="width:150px;">Кол-во изображений<br />для зарегистрированных:</td>
            <td>
                {html_options name='config[images_limit_reg]' options=$aData.options.limit10 style='width:50px;' selected=$aData.images_limit_reg}
            </td>
        </tr>
        <tr>
            <td>Кол-во изображений<br />для <b>не</b>зарегистрированных:</td>
            <td>
                {html_options name='config[images_limit]' options=$aData.options.limit10 style='width:50px;' selected=$aData.images_limit}
            </td>
        </tr>
    </table> 
    
</div>

<!-- add_instruction -->
<div id="add_instruction" class="conftab" style="display: none;">
    <table class="admtbl tbledit"> 
    <tr>
        <td class="row1" style="width:45px;"><span class="field-title">ШАГ 1:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct1]" id="add_instruct1" class="add_instruct" style="height: 135px; width: 680px;">{$aData.add_instruct1|default:''}</textarea></td>
    </tr>
    <tr>
        <td class="row1"><span class="field-title">ШАГ 2:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct2]" id="add_instruct2" class="add_instruct" style="height: 135px; width: 680px;">{$aData.add_instruct2|default:''}</textarea></td>
    </tr>
    <tr>
        <td class="row1"><span class="field-title">ШАГ 3:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct3]" id="add_instruct3" class="add_instruct" style="height: 135px; width: 680px;">{$aData.add_instruct3|default:''}</textarea></td>
    </tr>
    <tr>
        <td class="row1"><span class="field-title">ШАГ 4:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct4]" id="add_instruct4" class="add_instruct" style="height: 135px; width: 680px;">{$aData.add_instruct4|default:''}</textarea></td>
    </tr>
    </table>

<script type="text/javascript">
//<![CDATA[                               
{literal}    
$(function(){
    $('textarea.add_instruct, textarea.svc_desc').bffWysiwyg({autogrow: false});
});
//]]> 
{/literal}
</script>
    
</div>

<table class="admtbl">
<tr class="footer">  
    <td>
        <input type="submit" class="button submit" value="Сохранить" />
    </td>
</tr>
</table>
</form>



        </div>
    </div>                                    
    <div class="bottom"></div>   
    <div class="clear-all"></div>
</div>    
