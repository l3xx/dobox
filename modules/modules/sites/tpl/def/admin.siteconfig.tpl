<script type="text/javascript">
//<![CDATA[ 
var helper = null;                        
{literal}    
function siteConfigHelper()
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

$(document).ready(function(){
    helper = new siteConfigHelper();
    helper.changeTab("{/literal}{$tab|default:'general'}{literal}", 0); 
});
//]]> 
{/literal}
</script>

<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span>Настройки сайта / Общие настройки</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">  

<form method="post" action="">                        
<input type="hidden" name="saveconfig" value="1" />
<input type="hidden" name="psupdate" id="psupdate" value="" />
<input type="hidden" name="tab" id="tab" value="" />


<div class="tabsBar" id="tabs">    
    {foreach from=$aData.tabs key=k item=v}
        <span class="tab {if $v.a}tab-active{/if}" onclick="helper.changeTab('{$k}');" rel="{$k}">{$v.t}</span>
    {/foreach}                     
</div>   

<!-- general -->
<div id="general" class="conftab" style="display:;">
    <table class="admtbl tbledit"> 
    <tr>
	    <td class="row1 field-title" style="width:150px;">Название сайта:</td>
	    <td class="row2">
             <input style="width:545px;" type="text" name="config[title]" value="{$aData.title|default:''}" />
        </td>
    </tr>
    <tr>
	    <td class="row1"><span class="field-title">Ключевые слова (Keywords) для сайта:</span><br />
            <span class="desc">Краткое описание, не более 200 символов</span>
        </td>                            
	    <td class="row2"><textarea name="config[mkeywords]" style="height:65px; width:550px;">{$aData.mkeywords|default:''}</textarea></td>
    </tr>
    <tr>
	    <td class="row1"><span class="field-title">Описание (Description) сайта:</span><br />
            <span class="desc">Введите через запятую основные ключевые слова для вашего сайта</span>
        </td>
	    <td class="row2"><textarea name="config[mdescription]" style="height:65px; width:550px;">{$aData.mdescription|default:''}</textarea></td>
    </tr>
    <tr>
        <td class="row1 field-title">Offline режим:</td>
        <td class="row2">       
            <select name="config[enabled]" style="width:60px;">
                <option value="1" {if $aData.enabled|default:'0' == 1}selected="selected"{/if}>Нет</option>
                <option value="0" {if $aData.enabled|default:'0' == 0}selected="selected"{/if}>Да</option>                                                                    
            </select>&nbsp;
            <span class="desc">Перевести сайт в состояние offline, для проведения технических работ</span>
        </td>
    </tr>
    <tr>
        <td class="row1 field-title">Причина отключения сайта:<br />
            <span class="desc">Сообщение для отображения в режиме отключенного сайта</span>
        </td>
        <td class="row2">{$aData.offline_reason|default:''|wysiwyg:'config[offline_reason]':550:185}</td>
    </tr>
    {*
    <tr>
        <td class="row1">Контактная информация</td>
        <td class="row2">{$aData.contactaddress|default:''|wysiwyg:'config[contactaddress]':550:185}</td>
    </tr>
    *}
    <tr>     
        <td class="row1 field-title">Copyright:</td>
        <td class="row2">{$aData.copyright|default:''|wysiwyg:'config[copyright]':550:185}</td>
    </tr>
    </table>
</div>
  
<!-- mail -->
<div id="mail" class="conftab"  style="display: none;">

    <table class="admtbl tbledit"> 
        <tr>
            <td style="width:255px;" class="valignMiddle">E-Mail адрес администратора:<br />
            <span class="desc">E-Mail адрес администратора сайта</span></td>
            <td>
                 <input class="text-field" type="text" name="config[mail_admin]" value="{$aData.mail_admin}" />
            </td>
        </tr>
         <tr>
            <td style="width:255px;" class="valignMiddle">E-Mail адрес уведомлений:<br />
            <span class="desc">E-Mail адрес с которого будут отправлятся уведомления не требующие ответа</span></td>
            <td> <input class="text-field" type="text" name="config[mail_noreply]" value="{$aData.mail_noreply}" /> </td>
        </tr>
        <tr>
            <td class="valignMiddle">Способ отправки почты:<br />
            <span class="desc">Выберите SMTP если хотите или должны отправлять email-сообщения через сервер вместо локальной функции mail.</span></td>
            <td>
                 <select name="config[mail_type]" style="width:198px;">
                    <option value="mail" {if $aData.mail_type=='mail'}selected{/if}>PHP Mail()</option>
                    <option value="sendmail" {if $aData.mail_type=='sendmail'}selected{/if}>Sendmail</option>  
                    <option value="smtp" {if $aData.mail_type=='smtp'}selected{/if}>SMTP</option>  
                 </select>
            </td>            
        </tr>
        <tr><td colspan="2"><hr size="1" style="color:#1892C0;" /><br /><label><input type="checkbox" name="config[mail_smtp1_on]" {if $aData.mail_smtp1_on}checked="checked"{/if} />&nbsp;&nbsp;<b>SMTP сервер #1:</b></label></td></tr>
        <tr>
            <td>Адрес сервера:</td>
            <td> <input class="text-field" type="text" name="config[mail_smtp1_host]" value="{$aData.mail_smtp1_host}" /> </td>
        </tr>
        <tr>
            <td>Порт сервера:</td>
            <td> <input class="text-field" type="text" name="config[mail_smtp1_port]" value="{$aData.mail_smtp1_port}" /> </td>
        </tr>
        <tr>
            <td>Имя пользователя:<br /><span class="desc">Введите имя только в случае, если сервер SMTP требует этого. Не требуется в большинстве случаев, когда используется 'localhost'</span></td>
            <td> <input class="text-field" type="text" name="config[mail_smtp1_user]" value="{$aData.mail_smtp1_user}" /> </td>
        </tr>
        <tr>
            <td>Пароль:<br /><span class="desc">Введите пароль, если SMTP требует этого. Не требуется в большинстве случаев, когда используется 'localhost'</span></td>
            <td> <input class="text-field" type="text" name="config[mail_smtp1_pass]" value="{$aData.mail_smtp1_pass}" /> </td>
        </tr>
        <tr><td colspan="2"><hr size="1" style="color:#1892C0;"/><br /><label><input type="checkbox" name="config[mail_smtp2_on]" {if $aData.mail_smtp2_on}checked="checked"{/if} />&nbsp;&nbsp;<b>SMTP сервер #2:</b></label></td></tr>
        <tr>
            <td>Адрес сервера:</td>
            <td> <input class="text-field" type="text" name="config[mail_smtp2_host]" value="{$aData.mail_smtp2_host}" /> </td>
        </tr>
        <tr>
            <td>Порт сервера:</td>
            <td> <input class="text-field" type="text" name="config[mail_smtp2_port]" value="{$aData.mail_smtp2_port}" /> </td>
        </tr>
        <tr>
            <td>Имя пользователя:<br /><span class="desc">Введите имя только в случае, если сервер SMTP требует этого. Не требуется в большинстве случаев, когда используется 'localhost'</span></td>
            <td> <input class="text-field" type="text" name="config[mail_smtp2_user]" value="{$aData.mail_smtp2_user}" /> </td>
        </tr>
        <tr>
            <td>Пароль:<br /><span class="desc">Введите пароль, если SMTP требует этого. Не требуется в большинстве случаев, когда используется 'localhost'</span></td>
            <td>  <input class="text-field" type="text" name="config[mail_smtp2_pass]" value="{$aData.mail_smtp2_pass}" /> </td>
        </tr>
    </table>
    
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