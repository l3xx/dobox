                                                
<script type="text/javascript">
var fldr = {$aData.f|default:0};
{literal}
//<![CDATA[  
    function imAnswer()
    {
        var msg = $('#message');
        if(msg.val() == '') {
            msg.focus();
            return false;
        }
        return true;
    }        
    function imGetHistory(iid,lnk)
    {
        if(iid<=0){ 
            $.assert(this.form, 'imGetHistory: wrong interlocutor_id');
            return; 
        }
                          
        bff.ajax('index.php?s=internalmail&ev=ajax&action=history', {'iid': iid}, function(data){
            if(data) {         
               $(lnk).remove();
               $('#imHistoryContent').html(data.history);
               $('#imHistory').fadeIn();
            }
        }, '#progress-history');
    }
    $(document).ready(function(){
        if(fldr == 0){ $('#message').focus(); }
    }); 
//]]>   
{/literal}
</script>

<div class="blueblock lightblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Просмотр сообщения</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">    
            <form action="" method="post" onsubmit="return imAnswer();">
            <input type="hidden" name="theme" value="{$aData.m.theme|default:''}" />
            <input type="hidden" name="iid" value="{$aData.m.iid}" />
            <table border="0" class="admtbl">
            <tr>
                <td rowspan="4" style="width:62px; vertical-align:top;">
                    <span class="avatar">
                        <img id="im_avatar" src="{imgurl folder='cavatars' file=$aData.m.iavatar|default:'' id=$aData.m.iid}" />
                    </span>
                </td>
                <td class="valignMiddle bold" style="width:70px;">От кого:</td>
                <td class="valignMiddle">{if $aData.f==1}<a href="#" onclick="return userinfo('{$aData.uinfo.id}');">{$aData.uinfo.login}</a>{else}<a href="#" onclick="return userinfo('{$aData.m.iid}');">{$aData.m.ilogin}</a>{/if}</td>
                <td class="valignMiddle description" align="right" style="width:140px;">{$aData.m.created|date_format2:true}</td>
            </tr>
            <tr>
                <td class="valignMiddle bold">Кому:</td>
                <td class="valignMiddle" colspan="2">{if $aData.f==1}<a href="#" onclick="return userinfo('{$aData.m.iid}');">{$aData.m.ilogin}</a>{else}<a href="#" onclick="return userinfo('{$aData.uinfo.id}');">{$aData.uinfo.login}</a>{/if}</td>
            </tr>
            <tr>
                <td class="valignMiddle bold">Тема:</td>
                <td class="valignMiddle" colspan="2">{$aData.m.theme|default:''}</td>
            </tr>
            <tr>
                <td class="bold" style="vertical-align:top;">Сообщение:</td>
                <td colspan="2">{$aData.m.message|default:''|nl2br}</td>
            </tr>
            {if $aData.m.iblocked}
            <tr>
                <td></td>
                <td colspan="3">
                    <div>
                        <div class="warning"></div>
                        <div class="clr-error" style="float: left; margin-top:3px;">Аккаунт собеседника заблокирован.</div>
                    </div>                
                </td>
            </tr>
            {else}
            <tr>
                <td></td>
                <td colspan="3"><textarea style="height: 100px;" id="message" name="message"></textarea></td>
            </tr>        
            {/if}
            <tr>
                <td></td>
                <td colspan="3">
                    <div class="left" style="width:330px;">
                        <input type="submit" class="button submit" value="Ответить" {if $aData.m.iblocked}disabled="disabled"{/if} /> 
                        <input type="button" class="button delete" value="Удалить" onclick="bff.redirect('index.php?s={$class}&ev=delete&f={$aData.f}&mid={$aData.m.id}', 'Удалить сообщение?');"  />
                        <input type="button" class="button cancel" value="Отмена" onclick="bff.redirect('index.php?s={$class}&ev=listing&f={$aData.f}&r='+Math.random());" />
                    </div> 
                    <div class="right" style="padding-top:10px;">
                        <span id="progress-history" style="margin-right:5px; display:none;" class="progress"></span>
                        <a class="ajax" onclick="imGetHistory({$aData.m.iid}, this); return false;" href="#">История сообщений</a> 
                    </div>
                </td>
            </tr>
            </table>
            </form>
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div> 

<div class="blueblock whiteblock" id="imHistory" style="display:none; padding-top:5px;">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">История сообщений</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text" id="imHistoryContent">  
            
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div> 