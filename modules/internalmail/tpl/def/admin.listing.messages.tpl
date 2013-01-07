<script src="{$site_url}/js/messagebox.js" type="text/javascript" charset="utf-8"></script> 
                 
<div id="mboxNMContent" style="display: none;">
    <form action="" method="post" id="im_form">
    <input type="hidden" name="f" value="{$aData.f}" />
    <input type="hidden" name="act" value="send" />
    <table><tr>
    <td class="valignTop" style="width:42px;">Кому:</td>
    <td class="valignTop" style="height:30px;">
        <select name="recipient" id="im_recipient" style="width:312px;" onchange="onRecipient(this.value);">
            <option value="0">Выбрать</option> 
        </select>
<!--        <div class="input left"><span class="left"></span>
            <input type="text" value="" name="recipient" id="recipient" autocomplete="off" style="width: 300px;"/>
        <span class="right"></right></div>   -->
     </td>
     <td class="valignTop" rowspan="2" style="width:52px;">
        <span class="avatar">
            <img id="im_avatar" src="{imgurl folder='avatars'}" />
        </span>
    </td></tr><tr>
    <td class="valignTop">Тема:</td>
    <td  class="valignTop" style="height:30px;">
        <div class="input left"><span class="left"></span>
            <input type="text" value="" name="theme" id="im_theme" autocomplete="off" maxlength="128" style="width: 300px;"/>
        <span class="right"></span></div>
    </td></tr>
    <tr>
        <td class="valignTop">Текст:</td>
        <td colspan="2"><textarea style="width: 370px; height: 100px; min-height:100px; " class="autogrow" id="im_message" name="message"></textarea></td>
    </tr></table></form>
</div>   
                                                        
<script type="text/javascript">
var avatar_default = "{imgurl folder='cavatars'}"; 
{literal}
//<![CDATA[  

var mboxNM, mboxRM, im_sending = false, imLastReply=0;
$(document).ready(function(){
    //new message (messagebox)
    mboxNM = new MessageBox({title: 'Написать сообщение', width: '435px',
        onShow: function(){ 
            $('textarea.autogrow').autogrow();
            loadRecipients();
            mboxNM.e.rec.focus(); },
        onHide: function(){
            if(imLastReply == 1) {
                imLastReply = 0;
                mboxNM.e.rec.val(0);
                onRecipient(0);
                mboxNM.e.theme.val('');
            }            
        }
        });
    mboxNM.addButton({title: 'Отмена', onClick: function(){mboxNM.hide();} });
    mboxNM.addButton({title: 'Отправить', onClick: function(){
        if(im_sending) return;
        if(mboxNM.e.rec.val()==0){ mboxNM.e.rec.focus(); return; }
        if(mboxNM.e.theme.val()==''){ mboxNM.e.theme.focus(); return; }
        if(mboxNM.e.msg.val()==''){ mboxNM.e.msg.focus(); return; }
        im_sending = true;
        mboxNM.e.frm.submit();
    }});
    mboxNM.content( document.getElementById('mboxNMContent').innerHTML );
    
    mboxNM.e = {
            ava: mboxNM.ge('#im_avatar'),
            rec: mboxNM.ge('#im_recipient'),
            theme: mboxNM.ge('#im_theme'),
            msg: mboxNM.ge('#im_message'),  
            frm: mboxNM.ge('#im_form')
        };
});              

function loadRecipients(callback)
{
    callback = callback || new Function();
    if(mboxNM.recipients == undefined)
    {
        mboxNM.e.ava.attr('src', avatar_default);
        bff.ajax('index.php?s=internalmail&ev=ajax&action=list', {}, 
        function(data){
            if(data) { 
                mboxNM.recipients = data.recipients;
                mboxNM.e.rec[0].innerHTML = data.options;
            }
            callback.call();
        });
    } else {
        callback.call();
    }
}

function onCheck(type)
{
    var cAll = document.getElementById('imCheckAll');
    if(cAll) {
        cAll.checked = false; 
        cAll.value = 0;
    } else { return false; }
    
    switch(type)
    {
        case 'check-all':   {
            cAll.checked = true; cAll.value = 1; 
            $('input.imCheck').attr('checked', 1);
        } break;
        case 'uncheck-all': { 
            $('input.imCheck').removeAttr('checked'); 
        } break; 
        case 'check-read':  { 
            $('input.imCheck').removeAttr('checked'); //uncheck all 
            //$('input.imCheckNew').each(function(i,e){ alert($(e).attr('id')) });
            $('input.imCheckRead').attr('checked', 'checked'); 
        } break; 
        case 'check-new':   { 
            $('input.imCheck').removeAttr('checked'); //uncheck all 
            $('input.imCheckNew').attr('checked', 'checked'); 
        } break; 
    }
    return false;
}

function onRecipient(rid)
{
    var src = avatar_default;
    if(mboxNM.recipients != undefined || rid>0) {
         jQuery.each(mboxNM.recipients, function(i, v){
           if(v.id == rid) {
            src = v.avatar;
            return false;
           }
         });
    }
    mboxNM.e.ava.attr('src', src); 
}

function onReply(theme, iid)
{
    loadRecipients(function(){  
        mboxNM.e.rec.val(iid);
        onRecipient(iid);
        imLastReply = 1;
        mboxNM.e.theme.val('Re: '+(theme || ''));
        mboxNM.show();
        mboxNM.e.msg.focus();
    });
}

function onSendMore(iid)
{
    loadRecipients(function(){  
        mboxNM.e.rec.val(iid);
        onRecipient(iid);
        imLastReply = 1;
        mboxNM.show();
        mboxNM.e.theme.focus();
    });
}

function imDeleteMessage(mid, recover)
{
    if(recover){                    
        bff.ajax('index.php?s=internalmail&ev=ajax&action=recover-msg', {rec: mid}, function(data){ 
                $('#msg'+mid+'-recover').remove();
                $('#msg'+mid).show();
            }, '#progress-im');
    
    } else {
        bff.ajaxDelete('Удалить сообщение?', mid, 'index.php?s=internalmail&ev=ajax&action=delete-msg', false,
            {progress: '#progress-im', remove:false, onComplete: 
                function(data, options){
                    var cont = $('#msg'+mid);
                    cont.after(
                    '<tr class="'+cont.attr('class')+' rotate" id="msg'+mid+'-recover"><td colspan="6" class="alignCenter" style="height:57px;">\
                        <a href="#" onclick="return imDeleteMessage(\''+mid+'\',1);" class="description">восстановить</a>\
                    </td></tr>'
                    ).hide();    
                }
            }); 
    }
    return false;
}

//]]>   
{/literal}
</script>

    <form action="" name="imActionFrom" method="post" id="imActionFrom">
    <input type="hidden" name="act" value="massmsg" />
    <input type="hidden" name="f" value="{$aData.f}" />
    <div>
        
        <div class="actionBar">
            <div class="left">    
                {if $aData.f==0}<span style="font-size:18px;" class="description">Полученные</span>{else}<a href="index.php?s={$class}&ev={$event}&f=0" style="font-size:18px;">Полученные</a>{/if}
                <span>&nbsp;</span>
                {if $aData.f==1}<span style="font-size:18px;" class="description">Отправленные</span>{else}<a href="index.php?s={$class}&ev={$event}&f=1" style="font-size:18px;">Отправленные</a>{/if}
            </div>
            <div class="right">
                <span id="progress-im" style="margin-right:10px; display:none;" class="progress"></span>
                <a href="#" onclick="mboxNM.show(); return false;" class="ajax">Написать сообщение</a>
            </div>
            <div class="clear-all"></div>
            <b>Выделить:</b>&nbsp;&nbsp;<a onclick="return onCheck('check-all');" href="#">все</a>,
            <a onclick="return onCheck('check-read');" href="#">прочитанные</a>,
            <a onclick="return onCheck('check-new');" href="#">новые</a>,
            <a onclick="return onCheck('uncheck-all');" href="#">ни одного</a>
            <select name="type" onchange="$('#imActionFrom').submit();" style="margin-left:3px; width:160px;">
                <option value="" selected="selected"> -- </option>
                {if $aData.f==0}
                    <option value="read">Отметить как прочитанные</option>
                    <option value="new">Отметить как новые</option>
                {/if}
                <option value="del">Удалить отмеченные</option>
            </select>
            <input type="hidden" name="" />
        </div>
    </div>
    <table class="admtbl">  
    {if $aData.messages} 
    <tr class="header">
        {if $fordev}<th width="60">ID</th>{/if}
        <th class="small" width="40"><input type="checkbox" id="imCheckAll" value="0" onclick="{literal} if(this.value == '1') {onCheck('uncheck-all'); this.value = '0';} else {onCheck('check-all'); this.value = '1';} {/literal}" /></th>
        <th class="small" width="60"></th> 
        <th class="small" width="135" align="left">{if $aData.f==0}Отправитель{else}Получатель{/if}</th>         
        <th class="small" align="left">Тема</th>
        <th class="small" width="65">Действие</th> 
    </tr>
    {foreach from=$aData.messages item=v key=k}
    <tr class="row{$k%2}" id="msg{$v.id}">
        {if $fordev}<td class="alignCenter small">{$v.id}</td>{/if}
        <td class="alignCenter"><input type="checkbox" class="imCheck {if $aData.f==0 && $v.new}imCheckNew{else}imCheckRead{/if}" value="{$v.id}" name="msg[]" id="msg{$k+1}" /></td>
        <td><a href="#" onclick="return userinfo('{$v.iid}');"><img src="{imgurl folder='avatars' file=$v.iavatar|default:'' id=$v.iid|default:''}" width="46" height="46" /></a></td>
        <td><a href="#" onclick="return userinfo('{$v.iid}');">{$v.ilogin}</a><br /><span class="small description">{$v.created|date_format2:true}</span></td>
        <td><a {if $v.new && $aData.f==0}style="color:red;"{/if} href="index.php?s={$class}&ev=read&f={$aData.f}&mid={$v.id}">{$v.theme}</a><br /></td>
        <td class="alignCenter msg-actions">
            {if $aData.f==0}
            <a class="but edit" title="Ответить" href="#" onclick="onReply('{$v.theme}', {$v.iid}); return false;"></a>
            {else}
            <a class="but edit" title="Написать" href="#" onclick="onSendMore({$v.iid}); return false;"></a>
            {/if}
            <a class="but del" title="Удалить" href="javascript:void(0);" onclick="return imDeleteMessage('{$v.id}', 0);" ></a>
        </td>
    </tr>
    {/foreach}
    </table>
    {$pagenation_template}
    {else}
    <tr class="norecords">
        <td colspan="{if $fordev}6{else}5{/if}">нет сообщений</td>
    </tr>
    </table>
    {/if}
    </form>  