
<div id="popupUserinfo" class="ipopup">
    <div class="ipopup-wrapper">
        <div class="ipopup-title">Информация о пользователе</div>
        <div class="ipopup-content" style="width:643px;">

                    <form action="" name="userBlockForm" method="post">
                        <input type="hidden"  name="rec" value="{$aData.user_id}" />
                        <input type="hidden"  name="tuid" value="{$aData.tuid}" />     
                        <input type="hidden"  name="blocked" value="{$aData.blocked}" />
                        
                        <div class="warnblock {if !$aData.blocked}hidden{/if}" id="u_blocked_error">
                            <div class="warnblock-content">  
                                <div><b>заблокирован по причине</b>: <div class="right description" id="u_blocking_warn" style="display:none;"></div></div>
                                <div class="clear"></div> 
                                <div id="u_blocked">
                                    <span id="u_blocked_text">{$aData.blocked_reason|default:'?'}</span> - <a href="#" onclick="uChangeBlocked(1,0); return false;" class="ajax description">изменить</a>
                                </div>
                                <div id="u_blocking" style="display: none;">
                                    <textarea name="blocked_reason" id="u_blocking_text" class="autogrow" style="height:60px; min-height:60px;"
                                        onkeyup="checkTextLength(300, this.value, $('#u_blocking_warn').get(0));">{$aData.blocked_reason|default:''}</textarea>
                                    <a onclick="return uChangeBlocked(3,1);" class="ajax" href="#">заблокировать, изменить причину</a>, &nbsp;
                                    <a onclick="return uChangeBlocked(2);" class="ajax desc" href="#">отмена</a>
                                </div> 
                            </div>
                        </div>   
                    </form>
                    <script type="text/javascript"> 
                    {literal}
                    //<![CDATA[  
                    function uChangeBlocked(step, block)
                    {
                        if(step==1){//заблокировать/изменить блокировку
                            $('#u_blocked').hide();
                            $('#u_blocked_error, #u_blocking, #u_blocking_warn').show(0, function(){
                                $.fancybox.resize();
                                document.forms.userBlockForm['blocked_reason'].focus();    
                            }); 
                        } else if(step==2) {//отменить
                            if(document.forms.userBlockForm['blocked'].value == 1){
                                $('#u_blocking, #u_blocking_warn').hide();
                                $('#u_blocked').show(); 
                            } else {
                                $('#u_blocked_error, #u_blocking_warn').hide(); 
                            }
                            $.fancybox.resize();
                        } else if(step==3) {//сохранить
                            document.forms.userBlockForm['blocked'].value = block;
                            bff.ajax('index.php?s=users&ev=user_ajax&action=user-block', $(document.forms.userBlockForm).serializeArray(), function(data){
                                if(data)
                                {
                                    var uid = document.forms.userBlockForm['rec'].value;
                                    $('#u_unblock_lnk, #u_block_lnk').hide().filter('#u_'+(block==1?'un':'')+'block_lnk').show();   
                                    if(!block){                                                  
                                        $('#u_blocked_error, #u_blocking_warn').hide();
                                        $('#u'+uid).removeClass('block').addClass('unblock'); 
                                        $.fancybox.resize();
                                    } else {
                                        $('#u'+uid).removeClass('unblock').addClass('block');
                                        $('#u_blocking_warn').hide();
                                        $('#u_blocked_text').html(document.forms.userBlockForm['blocked_reason'].value);
                                        $('#u_blocked_error').show();
                                        uChangeBlocked(2);
                                    }
                                    delete _userinfo[ uid ];
                                }
                            }, '#progress-u-block');
                        }
                        return false;
                    }                
                    //]]> 
                    {/literal}
                    </script>   
                    
                    <table class="admtbl">          
                        <tr class="row1">
                            <td class="field-title" align="right" width="150">Пользователь:</td>
                            <td align="left" style="padding-left:20px;">  
                                {userlink id=$aData.user_id name=$aData.name surname=$aData.surname admin=$aData.admin login=$aData.login blocked=$aData.blocked nopopup=1}
                                <span class="right" style="opacity:0.7;">
                                    <span id="progress-u-block" style="margin-right:5px; display:none;" class="progress"></span>
                                    <a href="#" onclick="if(confirm('Продолжить?')) return uChangeBlocked(3,0); return false;" id="u_unblock_lnk" class="ajax clr-success {if !$aData.blocked}hidden{/if}">разблокировать</a>
                                    <a href="#" onclick="uChangeBlocked(1,0,0); return false;" id="u_block_lnk" class="ajax clr-error {if $aData.blocked}hidden{/if}">заблокировать</a>
                                </span>
                            </td>
                            <td class="row2 alignCenter valignMiddle" width="50" rowspan="3">
                                <span class="avatar">            
                                    <img id="avatar" src="{imgurl folder='avatars' file=$aData.avatar|default:'' id=$aData.user_id}" />
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="field-title" align="right">Email:</td>
                            <td align="left" style="padding-left:20px;"><a href="mailto:{$aData.email}">{$aData.email}</a></td>
                        </tr>
                        <tr>
                            <td align="right" class="field-title">Баланс:</td>
                            <td align="left" style="padding-left:20px;"><a class="bold orange" href="index.php?s=bills&ev=listing&uid={$aData.user_id}">{$aData.balance} р.</a>
                            </td>
                        </tr>    
                        <tr>
                            <td align="right" class="field-title">IP <span class="desc">(при регистрации)</span>:</td>
                            <td align="left" style="padding-left:20px;"><a style="color:#000;" href="index.php?s=ban&ev=users">{$aData.ip_reg|long2ip}</a>
                            </td>
                        </tr> 
                        <tr>
                            <td class="field-title valignTop" align="right">Время авторизации:</td>
                            <td align="left" style="padding-left:20px;">
                                <div style="height: 34px;">
                                {$aData.login_ts|date_format2:true}<span class="desc"> - последнее, <a class="bold desc" href="index.php?s=ban&ev=users">{$aData.ip_login|long2ip}</a></span><br />
                                {$aData.login_last_ts|date_format2:true}<span class="desc"> - предпоследнее</span>
                                </div>
                            </td>
                        </tr>
                        {* 
                         <tr>
                            <td class="field-title" align="right">Дата рождения:</td><td align="left" style="padding-left:20px;">{$aData.birthdate|date_format2}</td>
                        </tr>
                        *}
                         <tr>
                            <td class="field-title" align="right">Email (для связи):</td><td align="left" style="padding-left:20px;">{$aData.email2|default:'-'}</td>
                        </tr> 
                        <tr>
                            <td class="field-title" align="right">Телефон:</td><td align="left" style="padding-left:20px;">{$aData.phone|default:'-'}</td>
                        </tr>
                    </table>

                    {if $aData.sendmsg}
                    <script type="text/javascript"> 
                    {literal}
                    //<![CDATA[
                    var uSendMessageProcess = false;  
                    function uSendMessage(step)
                    {
                        if(step==1){//показать форму
                            $('#u_sendmessage').show(0, function(){
                                $.fancybox.resize();
                                $('#u_sendmessage_text').focus(); 
                            }); 
                        } else if(step==2) {//отменить
                            $('#u_sendmessage').hide(0, function(){ $.fancybox.resize(); });
                        } else if(step==3) {//отправить
                            if(uSendMessageProcess) return false;
                            
                            if($('#u_sendmessage_text').val().trim() == ''){
                                $('#u_sendmessage_text').focus();
                                return false;
                            }
                            
                            uSendMessageProcess = true;
                            bff.ajax('index.php?s=internalmail&ev=ajax&action=send-msg', $(document.forms.userSendMessageForm).serializeArray(), function(data){
                                if(data) {
                                        document.forms.userSendMessageForm.reset();
                                        $('#u_sendmessage').slideUp();
                                        $('#u_sendmessage_success').slideDown();
                                        setTimeout(function(){ 
                                            $('#u_sendmessage_success').slideUp('fast'); 
                                        }, 5000); 
                                    }
                                    uSendMessageProcess = false;
                                }, '#progress-u-sendmessage'); 
                        }    
                        return false;
                    }                
                    //]]> 
                    {/literal}
                    </script>   
                    
                    <div class="warnblock" id="u_sendmessage" style="display:none;">
                        <div class="warnblock-content" style="padding:10px 20px;">      
                            <form action="" method="post" name="userSendMessageForm">
                                <input type="hidden" name="iid" value="{$aData.user_id}" />
                                <div style="width:100%;"><b>Отправить сообщение:</b><div class="right description" id="u_sendmessage_warn" style="display:none;"></div></div>
                                <div class="clear"></div>                                
                                <textarea name="message" id="u_sendmessage_text" class="autogrow" style="height:90px; min-height:90px;"
                                    onkeyup="checkTextLength(4096, this.value, $('#u_sendmessage_warn').get(0));"></textarea>
                                <a onclick="return uSendMessage(3);" class="ajax" href="#">отправить</a><span class="description"> | </span>
                                <a onclick="return uSendMessage(2);" class="ajax description" href="#">отмена</a>
                                <span id="progress-u-sendmessage" style="margin-right:5px; display:none;" class="progress"></span>
                            </form>
                        </div>
                    </div> 
                    <div class="warnblock" id="u_sendmessage_success" style="display:none;">
                        <div class="warnblock-content success">
                            <ul class="warns"><li>Сообщение было успешно отправлено.</li></ul>
                        </div>
                    </div>
                    {/if}

                <div class="ipopup-content-bottom">
                    <ul class="right">
                        {if $aData.sendmsg}<li><a href="#" class="edit_s ajax" onclick="return uSendMessage(1);"> написать сообщение</a></li>{/if}
                        <li><a href="index.php?s=bbs&ev=items_listing&uid={$aData.user_id}"> объявления ({$aData.items})</a></li>
                        <li><span class="post-date" title="дата регистрации">{$aData.created|date_format2:true}</span></li>
                        <li><a href="index.php?s={$class}&ev={if $aData.admin}mod{else}member{/if}_edit&rec={$aData.user_id}&tuid={$aData.tuid}" class="edit_s"> редактировать <span style="display:inline;" class="desc">#{$aData.user_id}</span></a></li>
                    </ul> 
                </div>
        
            </div>
        </div>
    </div>
</div>
