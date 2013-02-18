

{if $aData.edit}
<form action="" name="userBlockForm" method="post">
    <input type="hidden"  name="rec" value="{$aData.user_id}" />
    <input type="hidden"  name="tuid" value="{$aData.tuid}" />     
    <input type="hidden"  name="blocked" value="{$aData.blocked}" />
    
    <div class="warnblock {if !$aData.blocked}hidden{/if}" id="u_blocked_error" style="width:100%;">
        <div class="warnblock-content">                  
            <div><b>заблокирован по причине</b>: <div class="right description" id="u_blocking_warn" style="display:none;"></div></div>
            <div class="clear"></div> 
            <div id="u_blocked">
                <span id="u_blocked_text">{$aData.blocked_reason}</span> - <a href="#" onclick="uChangeBlocked(1,0); return false;" class="ajax description">изменить</a>
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
            document.forms.userBlockForm['blocked_reason'].focus();    
        }); 
    } else if(step==2) {//отменить
        if(document.forms.userBlockForm['blocked'].value == 1){
            $('#u_blocking, #u_blocking_warn').hide();
            $('#u_blocked').show(); 
        } else {
            $('#u_blocked_error, #u_blocking_warn').hide(); 
        }
    } else if(step==3) {//сохранить
        document.forms.userBlockForm['blocked'].value = block;
        bff.ajax('index.php?s=users&ev=user_ajax&action=user-block', $(document.forms.userBlockForm).serializeArray(), function(data){
            if(data)
            {
                $('#u_unblock_lnk, #u_block_lnk').hide().filter('#u_'+(block==1?'un':'')+'block_lnk').show();
                if(!block){                                                  
                    $('#u_blocked_error, #u_blocking_warn').hide();
                } else {
                    $('#u_blocking_warn').hide();
                    $('#u_blocked_text').html(document.forms.userBlockForm['blocked_reason'].value);
                    $('#u_blocked_error').show();
                    uChangeBlocked(2);
                }
                delete _userinfo[ document.forms.userBlockForm['rec'].value ];
            }
        }, '#progress-u-block'); //, '#progress-categories'
    }
    return false;
}                
//]]> 
{/literal}
</script>
{/if} 

<form action="" name="modifyMemberForm" method="post" enctype="multipart/form-data">  
<table class="admtbl tbledit">
<tr class="check-email{if !$aData.edit} required{/if}">
	<td class="row1 field-title" style="width:175px;">
        <div class="left">E-mail<span class="required-mark">*</span>:</div>
        {if $aData.edit && $aData.social}<div class="right" style="padding:4px 0 0 0;"><a class="social-small social-small-{$aData.social}" target="_blank" href="{$aData.social_link}">&nbsp;</a></div>{/if}
    </td>   
	<td class="row2 valignMiddle">
        <input type="text" id="email" name="email" value="{$aData.email|default:''}" tabindex="1" class="text-field left" />
        {if $aData.edit}
        <div style="opacity:0.5; margin:3px 0 0 10px;" class="left">
            <span id="progress-u-block" style="margin-right:5px; display:none;" class="progress"></span>
            <a href="#" onclick="if(confirm('Продолжить?')) return uChangeBlocked(3,0); return false;" id="u_unblock_lnk" class="ajax clr-success {if !$aData.blocked}hidden{/if}">разблокировать</a>
            <a href="#" onclick="uChangeBlocked(1,0,0); return false;" id="u_block_lnk" class="ajax clr-error {if $aData.blocked}hidden{/if}">заблокировать</a>
        </div>
        {/if}
    </td>
    <td class="row2 valignMiddle" width="180" align="right" rowspan="13">
        <span class="avatar">            
            <img id="avatar" src="{imgurl folder='avatars' file=$aData.avatar|default:'' id=$aData.user_id|default:''}" />
        </span>
        <br/>
        <span class="desc">баланс</span>:<br/>
        <a href="index.php?s=bills&ev=listing&uid={$aData.user_id}" class="bold orange" style="font-size:16px;" target="_blank">{$aData.balance|default:''}</a>
    </td>
</tr>
<tr>
    <td class="row1 field-title">ФИО:</td>
    <td class="row2">
        <input maxlength="35" type="text" name="name" tabindex="2" value="{$aData.name|default:''}"  class="text-field" />
    </td>
</tr>    
{if !$aData.edit} 
<tr class="required check-password">
	<td class="row1 field-title">Пароль<span class="required-mark">*</span>:</td>
	<td class="row2">
        <input type="password" id="password" name="password" tabindex="3" autocomplete="off" value="{$aData.password|default:''}" class="text-field" />
    </td>
</tr> 
<tr class="required check-password">
	<td class="row1 field-title">Подтверждение пароля<span class="required-mark">*</span>:</td>
	<td class="row2">
        <input type="password" id="password2" name="password2" tabindex="4" class="check-password2 text-field" autocomplete="off" value="{$aData.password2|default:''}" />
    </td>
</tr>
{*
<tr class="required check-select">
    <td class="row1"><span class="field-title">Город</span>:</td>
    <td class="row2">
        <select name="city_id" id="citySel" style="width:198px;" onchange="fchecker.onCity(this, '#progress-city');">
             {$aData.city_options|default:''}
        </select>
        <span id="progress-city" style="margin-left:5px; display:none;" class="progress"></span>
    </td>
</tr> 
*}
{else}  
<tr class="required check-password">
    <td class="row1">
        <span class="field-title">Пароль<span class="required-mark">*</span></span>:
        <input type="hidden" name="changepass" id="changepass" value="0" />
    </td>
    <td class="row2">
        <div id="passwordCurrent" style="height:17px; padding-top:5px;">
            <a href="#" class="ajax" onclick="fchecker.doChangePassword(1); return false;">изменить пароль</a>
        </div>
        <div id="passwordChange" style="display:none; height:22px;">
            <input type="text" id="password" name="password" tabindex="3" class="text-field" value="" />
            &nbsp;&nbsp;<a href="#" class="ajax desc" onclick="fchecker.doChangePassword(0); return false;">отмена</a>
        </div>
    </td>
</tr>
<tr>
    <td class="row1 field-title">IP <span class="desc">(при регистрации)</span>:</td>
    <td class="row2"><a style="color:#000;" href="index.php?s=ban&ev=users">{$aData.ip_reg|long2ip}</a></td>
</tr> 
<tr>
    <td class="row1 field-title">Время авторизации:</td>
    <td class="row2">
        <div style="height: 34px;">
        {$aData.login_ts|date_format2:true}<span class="desc"> - последнее, <a class="bold desc" href="index.php?s=ban&ev=users">{$aData.ip_login|long2ip}</a></span><br />
        {$aData.login_last_ts|date_format2:true}<span class="desc"> - предпоследнее</span>
        </div>
    </td>
</tr>
{*
<tr class="required check-select">
    <td class="row1"><span class="field-title">Город</span>:</td>
    <td class="row2" style="height:27px;">
        <span id="cityCur" onmouseover="$('#cityLnkChange').show();" onmouseout="$('#cityLnkChange').hide();">
            {if !$aData.city_id}не указан{else}<a href="index.php?s=users&amp;ev=listing&amp;city={$aData.city_id}">{$aData.city}</a>, <a href="#{$aData.region_id}">{$aData.region}</a>{/if}
            <a href="#" id="cityLnkChange" class="ajax" onclick="$('#cityCur').hide(); $('#citySel').show(); return false;" style="display:none;">- изменить</a>
        </span>
        <select name="city_id" id="citySel" style="width:198px; display:none;" onchange="fchecker.onCity(this, '#progress-city');">
             {$aData.city_options|default:''}
        </select>
        <span id="progress-city" style="margin-left:5px; display:none;" class="progress"></span>
    </td>
</tr> 
*}
{/if} 

<tr>
    <td class="row1 field-title">E-mail<span class="desc"> (для связи)</span>:</td>
    <td class="row2">
        <input type="text" name="email2" tabindex="5" value="{$aData.email2|default:''}" class="text-field"  />
    </td>
</tr>    
<tr>
    <td class="row1 field-title">Телефон:</td>
    <td class="row2">
        <input type="text" name="phone" tabindex="6" value="{$aData.phone|default:''}" class="text-field"  />
    </td>
</tr>  

<tr class="footer">
    <td colspan="3" class="row1">
        <input type="submit" class="button submit" tabindex="7" value="{if !$aData.edit}Добавить{else}Сохранить{/if}" />
        {if $aData.edit && $aData.session_id|default:''}
        <input type="button" class="clr-error button" value="Разлогинить" onclick="bff.redirect('index.php?s={$class}&ev=user_action&member=1&type=logout&rec={$aData.user_id}&tuid={$aData.tuid}');" />
        {/if} 
        <input type="button" class="button cancel" value="Отмена" onclick="history.back();" />
    </td>
</tr>

</table>
</form>

<script type="text/javascript">
var uFormEdit = {$aData.edit|intval};
{literal}
//<![CDATA[ 
var fchecker = null;
bff.extend(bff.formChecker, {

additionalCheck: function()
{
    return true;
},

afterCheck: function()
{

},

deleteAvatar: function(id, url, defaultAvatar)
{
    if(confirm('Удалить текущий аватар?'))
    bff.ajax(url, {rec: id, action: 'avatar-delete'}, function(data){
        if(data)
        {
            $('#avatar').attr('src', defaultAvatar);
            $('#avatar_delete_link').remove();
        }
    });
},

doChangePassword: function(change)
{
    document.getElementById('passwordCurrent').style.display = (change?'none':'block');
    document.getElementById('passwordChange').style.display = (change?'block':'none'); 
    $('#changepass').val( change );

    fchecker.check();
    
    if(change)
        $('#password').focus();
        
    return false;
},
city: 0,
onCity: function(sel, progress)
{
    this.city = sel.value;
    if(this.city == -1){
        //show all cities
        bff.ajax('index.php?s=sites&ev=ajax&act=city-list&pos=edit',{},function(data){
            $(sel).html(data);
            fchecker.check();
        }, progress);                           
    }
}   

});

$(document).ready(function(){
    fchecker = new bff.formChecker( document.forms.modifyMemberForm );    
});

//]]> 
{/literal}
</script>