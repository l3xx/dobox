
<div style="display:none;">

    <div id="imNewMessage" class="ipopup">
        <div class="ipopup-wrapper">
            <div class="ipopup-title">Написать сообщение</div>
            <div class="ipopup-content" style="width:475px;">

                <form action="" method="post" name="nmForm" enctype="multipart/form-data">
                    <input type="hidden" name="f" value="{$aData.f}" />
                    <input type="hidden" name="act" value="send" />
                    <input type="hidden" name="recipient" id="nmRecipient" value="0" />
                    <table class="admtbl tbledit">
                        <tr>
                            <td class="valignTop field-title" style="width:32px;">Кому:</td>
                            <td class="valignTop field-title" style="height:30px;">
                                <input type="text" value="" name="recipient_login" id="nmRecipientAutocomplete" class="autocomplete" style="width: 425px;"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="left field-title">Сообщение:</div>
                                <div class="right description"><span id="warn-message" style="display:none;"></span></div>
                                <div class="clear-all"></div>
                                <textarea style="height: 150px;" name="message" onkeyup="checkTextLength(4096, this.value, $('#warn-message').get(0));"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="left"> 
                                    <div class="form-upload">
                                        <div class="upload-file">
                                            <table>
                                            <tbody class="desc">
                                                <tr><td>Можете&nbsp;</td>
                                                    <td>
                                                        <div class="upload-btn">
                                                            <span class="upload-mask">
                                                                <input type="hidden" name="MAX_FILE_SIZE" value="{$config.im_attach_maxsize}" />
                                                                <input type="file" onchange="bff.input.file(this, 'im_attach_cur');" name="attach" id="im_attach" />
                                                            </span>
                                                            <a class="ajax">приложить файл</a>
                                                        </div>
                                                    </td>
                                                    <td>&nbsp;до {$config.im_attach_maxsize|filesize}.</td></tr>
                                            </tbody></table>
                                            <div class="upload-res" id="im_attach_cur"></div>
                                        </div>
                                    </div>
                                </div>                                                      
                                <div class="right">
                                    <input type="button" class="button submit" value="Отправить" onclick="imOnSendMessage();" />
                                    <input type="button" class="button cancel" value="Отмена" onclick="$.fancybox.close();" />
                                </div>
                                <div class="clear-all"></div>
                            </td>
                        </tr>
                    </table> 
                </form>  
            
            </div>
        </div>
    </div>
    
</div>

                                                                
<script type="text/javascript">
var avatar_default = "{imgurl folder='avatars'}"; 
{literal}
//<![CDATA[  

var imSendingProgress = false;
function imOnSendMessage()
{
    if(imSendingProgress) return;
    var form = document.forms.nmForm;
    if(form['recipient'].value==0 || form['recipient'].value==''){ form['recipient_login'].focus(); return; }    
    if(form['message'].value.trim() == ''){ form['message'].focus(); return; }
    imSendingProgress = true;
    form.submit();
}

function removeConv(iid)
{
    bff.ajax('index.php?s=internalmail&ev=ajax&action=delete-conv', {iid: iid}, function(data){
        if(data) {
            $('#i'+iid).remove();    
        }
    }, '#progress-im');    
}

function move2Folder(iid, fid)
{
    bff.ajax('index.php?s=internalmail&ev=ajax&action=move2folder', {iid: iid, fid: fid}, function(data){
        $('#folder'+fid+'u'+iid+', #vfolder'+fid+'u'+iid).
            addClass((data.added?'active':'passive')).
            removeClass((data.added?'passive':'active'));
    }, '#progress-im');        
}

var fPopupVisible = false;
function fPopupHide(num)
{
  if(!fPopupVisible)
  {
     var e = document.getElementById(num);
     e.style.display = 'none';    
  }
}

$(document).ready( function(){

    $('div.fpopup').each(function(i,e){
        var id = $(e).attr('rel');
       $(e).mouseenter(function(){
            fPopupVisible = true;                                    
        }).mouseleave(function(){ 
            fPopupVisible = false;                                
            setTimeout("fPopupHide('fpopup"+id+"')", 500);
        }).parents('td').find('a.v2folder').mouseleave(function(){
            fPopupVisible = false;  
            setTimeout("fPopupHide('fpopup"+id+"')", 500);                                  
        }).click(function(){    
            document.getElementById('ipopup'+id).blur();
            document.getElementById('fpopup'+id).style.display = 'block';           
        });      
    });
    
    var imNewMessageInited = false;
    $('#imNewMessageLink').fancybox({
        onStart: function(){              
            if(imNewMessageInited) return;
            imNewMessageInited = true;
            $('#nmRecipientAutocomplete').autocomplete('index.php?s=internalmail&ev=ajax&action=recipients', 
                        {valueInput: '#nmRecipient', width: 429, placeholder: 'Укажите логин пользователя'});
        }, onComplete:function(){
           $('#nmRecipientAutocomplete').focus();
        }
    });    
                                    
});

var pgn = new bff.pgn('#pagenation', {type:'prev-next'});

//]]>   
{/literal}
</script>


<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Внутренняя почта / Личные сообщения</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">  
        
 
            <div class="tabsBar">    
                {foreach from=$aData.folders key=k item=v}
                    <span class="tab {if $k==$aData.f}tab-active{/if}" onclick="bff.redirect('index.php?s={$class}&ev={$event}&f={$k}');">{$v.name}</span>
                {/foreach}

                <div class="right">
                    <span id="progress-im" style="margin-right:10px; display:none;" class="progress"></span>
                    <a href="#imNewMessage" id="imNewMessageLink" class="ajax">Написать сообщение</a>
                </div>                        
            </div> 
            
            <table class="admtbl tblhover">  
            <tr class="header">     
                <th align="left" >Собеседник</th> 
                <th width="105">Действие</th> 
            </tr>
            {assign var="fFav" value="1"}
            {assign var="fIgnore" value="2"}
            {foreach from=$aData.contacts item=v key=k name=c}
            <tr class="row{$smarty.foreach.c.iteration%2}" id="i{$v.user_id}">
                <td align="left"><img src="{imgurl folder='avatars' file=$v.avatar|default:'' id=$v.user_id|default:''}" class="left" style="padding-right:10px;" />
              
                    {userlink name=$v.name id=$v.user_id login=$v.login admin=$v.admin}<br />
                    {if $v.newmsgs}<a class="newmess" href="index.php?s={$class}&amp;ev=conversation&amp;i={$v.user_id}">{$v.newmsgs|declension:'новое сообщение;новых сообщения;новых сообщений'}</a>{else}<a href="index.php?s={$class}&amp;ev=conversation&amp;i={$v.user_id}">Сообщений</a> <span class="description">({$v.msgs_count})</span>{/if}
                    <br />
                    <span class="small description">
                        {if $v.lastmsg.author == $v.user_id}
                            {if !$v.lastmsg.newmsg}Вы предпочли промолчать{/if}
                        {else}
                            {if $v.lastmsg.newmsg}Ваше сообщение не прочитано
                            {else}Ваше сообщение прочитано {$v.lastmsg.readed|date_format2:true} 
                            {/if}
                        {/if}                    
                    </span>
                </td>
                <td>
                    <div class="vfolders">
                        <div id="vfolder{$fFav}u{$v.user_id}" class="{if $fFav|in_array:$v.folders}active{else}passive{/if}">Избранные</div>
                        <div id="vfolder{$fIgnore}u{$v.user_id}" class="{if $fIgnore|in_array:$v.folders}active{else}passive{/if}">Игнорирую</div>
                    </div>                            
                    <a href="javascript:void(null);" class="v2folder">
                        <img src="{$theme_url}/img/admin/im2folder.gif" id="ipopup{$v.user_id}" />
                    </a>
                    <div class="folders">                                           
                      <div id="fpopup{$v.user_id}" class="fpopup" rel="{$v.user_id}" style="display: none;">
                        <div onclick="move2Folder('{$v.user_id}',1);" {if $fFav|in_array:$v.folders}class="active"{/if} id="folder1u{$v.user_id}">Избранные</div><br />
                        {if !$v.admin}
                            <div onclick="move2Folder('{$v.user_id}',2);" {if $fIgnore|in_array:$v.folders}class="active"{/if} id="folder2u{$v.user_id}">Игнорирую</div><br />
                            <div onclick="if(bff.confirm('sure')) removeConv('{$v.user_id}'); return false;" class="blue">Удалить</div>                
                        {/if}
                      </div>
                    </div>
                </td>
            </tr>
            {foreachelse}
            <tr class="norecords">
                <td colspan="3">ничего не найдено</td>
            </tr>    
            {/foreach}
            </table>   
            
            <form action="index.php" method="get" name="pagenation" id="pagenation">
                <input type="hidden" name="s" value="{$class}" />
                <input type="hidden" name="ev" value="{$event}" />
                <input type="hidden" name="f" value="{$aData.f}" />                                      
                <input type="hidden" name="offset" value="{$aData.offset}" />  
            </form>
            <div class="pagenation">
                {$aData.pgn}
            </div>

        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div> 