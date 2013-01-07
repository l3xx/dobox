                                                
<script type="text/javascript">
var fldr = {$aData.f|default:0};
{literal}
//<![CDATA[  
    function imAnswer()
    {
        var msg = $('#message');
        if(msg.val().trim() == '') {
            msg.focus();
            return false;
        }
        return true;
    }    

    function removeConv(iid)
    {
        bff.ajax('index.php?s=internalmail&ev=ajax&action=delete-conv', {iid: iid}, function(data){
            if(data) {
                bff.redirect('index.php?s=internalmail&ev=listing');    
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

                                                
    });
   
    var listingupdating = false;

    function doPage(page)
    {   
        if(listingupdating)
            return;
            
        document.forms.filters['page'].value = page; 
            
        listingupdating = true;
        
        $('table.messages').animate({'opacity': 0.65}, 300);
        bff.ajax('index.php?s=internalmail&ev=conversation', $(document.forms.filters).serializeArray(), function(data){
            listingupdating = false;
            if(data)
            {
                $('table.messages').animate({'opacity': 1}, 100).html(data.list);
                $('#pagenation').replaceWith(data.pages); 
            }    
        }, '#progress-conversation');
    }

//]]>   
{/literal}
</script>

<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Внутренняя почта / Переписка</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">    
            <table class="admtbl tbledit">
            <tr>
                <td style="width:62px;">
                    <span class="avatar">
                        <a href="#" onclick="return userinfo('{$aData.iid}');">
                            <img id="im_avatar" src="{imgurl folder='avatars' file=$aData.i.avatar|default:'' id=$aData.iid}" />
                        </a>
                    </span>
                </td>
                <td>
                    {userlink name=$aData.i.name id=$aData.iid login=$aData.i.login admin=$aData.i.admin}
                    <span id="progress-conversation" class="progress" style="margin-left:5px; display:none;"></span>
                    <br />
                    <span class="desc">Всего сообщений: {$aData.total}</span>
                </td>
                <td width="105">
                    {assign var="fFav" value="1"}
                    {assign var="fIgnore" value="2"}
                    <div class="vfolders">
                        <div id="vfolder1u{$aData.iid}" class="{if $fFav|in_array:$aData.i.folders}active{else}passive{/if}">Избранные</div>
                        <div id="vfolder2u{$aData.iid}" class="{if $fIgnore|in_array:$aData.i.folders}active{else}passive{/if}">Игнорирую</div>
                    </div>  
                    <a href="javascript:void(null);" class="v2folder">
                        <img src="{$theme_url}/img/admin/im2folder.gif" id="ipopup{$aData.iid}" />
                    </a>
                    <div class="folders">                                           
                      <div id="fpopup{$aData.iid}" class="fpopup" rel="{$aData.iid}" style="display: none;">
                        <div onclick="move2Folder('{$aData.iid}',1);" {if $fFav|in_array:$aData.i.folders}class="active"{/if} id="folder1u{$aData.iid}">Избранные</div><br />
                        {if !$aData.i.admin}
                            <div onclick="move2Folder('{$aData.iid}',2);" {if $fIgnore|in_array:$aData.i.folders}class="active"{/if} id="folder2u{$aData.iid}">Игнорирую</div><br />
                            <div onclick="if(bff.confirm('sure')) removeConv('{$aData.iid}'); return false;" class="blue">Удалить</div>                
                        {/if}
                      </div>
                    </div>  
                </td>                                                                          
            </tr>
            </table>
            
            <hr class="cut" /><br /> 
            
            <form action="" method="post" onsubmit="return imAnswer();" enctype="multipart/form-data">            
                <input type="hidden" name="i" value="{$aData.i.user_id}" />
                {if (!$aData.uadmin && ($aData.i.im_noreply || $aData.ignored)) || $aData.i.blocked}
                 <div align="center">
                    <div class="clr-error" style="margin-top:3px;">
                        {if $aData.i.im_noreply || $aData.ignored}
                            Пользователь запретил отправлять ему сообщения.
                        {else}
                            Аккаунт пользователя заблокирован.
                        {/if}
                    </div>
                </div>
                {else}
                <table class="admtbl">
                <tr>
                    <td colspan="3">
                        <div class="left field-title">Новое сообщение:</div>
                        <div class="right description"><span id="warn-message" style="display:none;"></span></div>
                        <div class="clear-all"></div>
                        <textarea style="height: 150px;" id="message" name="message"
                            onkeyup="checkTextLength(4096, this.value, $('#warn-message').get(0));"></textarea>
                    </td>
                </tr> 
                <tr>
                    <td>                     
                        <div class="left">
                        
                                <div class="form-upload">
                                    <div class="upload-file">
                                        <table>
                                        <tbody class="desc">
                                            <tr><td>Вы можете&nbsp;</td>
                                                <td>
                                                    <div class="upload-btn">
                                                        <span class="upload-mask">
                                                            <input type="hidden" name="MAX_FILE_SIZE" value="{$config.im_attach_maxsize}" />
                                                            <input type="file" onchange="bff.input.file(this, 'im_attach_cur');" name="attach" id="im_attach" />
                                                        </span>
                                                        <a class="ajax">приложить файл</a>
                                                    </div>
                                                </td>
                                                <td>&nbsp;объемом не более {$config.im_attach_maxsize|filesize}.</td></tr>
                                        </tbody></table>
                                        <div class="upload-res" id="im_attach_cur"></div>
                                    </div>
                                </div>                        

                        </div> 
                        <div class="right">
                            <input type="submit" class="button submit" value="Отправить" /> 
                        </div>
                        <div class="clear-all"></div>
                    </td>
                </tr>
                </table>
                {/if}
            </form>
            
            <br /><hr class="cut" /><br />

            <table class="admtbl tbledit messages">
            {foreach from=$aData.mess item=m}
            <tr>
                <td width="20" align="center" style="vertical-align:top;"><div class="im-triangle-{if !$m.my}from{else}to{/if}"></div></td>
                <td style="padding-right: 20px; vertical-align:top;" {if !$m.my}class="from"{/if}>
                    <strong>{if $m.my}{$aData.uname}{else}{$aData.i.name}{/if} </strong>{$m.created|date_format2:true}:<br />
                    {$m.message}<br />
                    {attach file=$m.attach url=$smarty.const.INTERNALMAIL_ATTACH_URL scv=true}
                </td> 
            </tr>
            {foreachelse}
            <tr>
                <td class="desc" align="center">не найдено ни одного сообщения</td> 
            </tr>
            {/foreach}
            </table>

            <form action="index.php" method="get" name="filters">
                <input type="hidden" name="s" value="{$class}" />
                <input type="hidden" name="ev" value="{$event}" />
                <input type="hidden" name="i" value="{$aData.iid}" />                      
                <input type="hidden" name="page" value="1" />
            </form>
            {$pagenation_template}
            
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div> 
