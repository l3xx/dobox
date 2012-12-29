<?php /* Smarty version 2.6.7, created on 2012-12-26 00:58:05
         compiled from admin.member.form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'admin.member.form.tpl', 18, false),array('modifier', 'long2ip', 'admin.member.form.tpl', 141, false),array('modifier', 'date_format2', 'admin.member.form.tpl', 147, false),array('modifier', 'intval', 'admin.member.form.tpl', 196, false),array('function', 'imgurl', 'admin.member.form.tpl', 86, false),)), $this); ?>


<?php if ($this->_tpl_vars['aData']['edit']): ?>
<form action="" name="userBlockForm" method="post">
    <input type="hidden"  name="rec" value="<?php echo $this->_tpl_vars['aData']['user_id']; ?>
" />
    <input type="hidden"  name="tuid" value="<?php echo $this->_tpl_vars['aData']['tuid']; ?>
" />     
    <input type="hidden"  name="blocked" value="<?php echo $this->_tpl_vars['aData']['blocked']; ?>
" />
    
    <div class="warnblock <?php if (! $this->_tpl_vars['aData']['blocked']): ?>hidden<?php endif; ?>" id="u_blocked_error" style="width:100%;">
        <div class="warnblock-content">                  
            <div><b>заблокирован по причине</b>: <div class="right description" id="u_blocking_warn" style="display:none;"></div></div>
            <div class="clear"></div> 
            <div id="u_blocked">
                <span id="u_blocked_text"><?php echo $this->_tpl_vars['aData']['blocked_reason']; ?>
</span> - <a href="#" onclick="uChangeBlocked(1,0); return false;" class="ajax description">изменить</a>
            </div>
            <div id="u_blocking" style="display: none;">
                <textarea name="blocked_reason" id="u_blocking_text" class="autogrow" style="height:60px; min-height:60px;"
                    onkeyup="checkTextLength(300, this.value, $('#u_blocking_warn').get(0));"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['blocked_reason'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea>
                <a onclick="return uChangeBlocked(3,1);" class="ajax" href="#">заблокировать, изменить причину</a>, &nbsp;
                <a onclick="return uChangeBlocked(2);" class="ajax desc" href="#">отмена</a>
            </div>           
        </div>
    </div>   
</form>
<script type="text/javascript"> 
<?php echo '
//<![CDATA[  
function uChangeBlocked(step, block)
{
    if(step==1){//заблокировать/изменить блокировку
        $(\'#u_blocked\').hide();
        $(\'#u_blocked_error, #u_blocking, #u_blocking_warn\').show(0, function(){
            document.forms.userBlockForm[\'blocked_reason\'].focus();    
        }); 
    } else if(step==2) {//отменить
        if(document.forms.userBlockForm[\'blocked\'].value == 1){
            $(\'#u_blocking, #u_blocking_warn\').hide();
            $(\'#u_blocked\').show(); 
        } else {
            $(\'#u_blocked_error, #u_blocking_warn\').hide(); 
        }
    } else if(step==3) {//сохранить
        document.forms.userBlockForm[\'blocked\'].value = block;
        bff.ajax(\'index.php?s=users&ev=user_ajax&action=user-block\', $(document.forms.userBlockForm).serializeArray(), function(data){
            if(data)
            {
                $(\'#u_unblock_lnk, #u_block_lnk\').hide().filter(\'#u_\'+(block==1?\'un\':\'\')+\'block_lnk\').show();
                if(!block){                                                  
                    $(\'#u_blocked_error, #u_blocking_warn\').hide();
                } else {
                    $(\'#u_blocking_warn\').hide();
                    $(\'#u_blocked_text\').html(document.forms.userBlockForm[\'blocked_reason\'].value);
                    $(\'#u_blocked_error\').show();
                    uChangeBlocked(2);
                }
                delete _userinfo[ document.forms.userBlockForm[\'rec\'].value ];
            }
        }, \'#progress-u-block\'); //, \'#progress-categories\'
    }
    return false;
}                
//]]> 
'; ?>

</script>
<?php endif; ?> 

<form action="" name="modifyMemberForm" method="post" enctype="multipart/form-data">  
<table class="admtbl tbledit">
<tr class="check-email<?php if (! $this->_tpl_vars['aData']['edit']): ?> required<?php endif; ?>">
	<td class="row1 field-title" style="width:175px;">
        <div class="left">E-mail<span class="required-mark">*</span>:</div>
        <?php if ($this->_tpl_vars['aData']['edit'] && $this->_tpl_vars['aData']['social']): ?><div class="right" style="padding:4px 0 0 0;"><a class="social-small social-small-<?php echo $this->_tpl_vars['aData']['social']; ?>
" target="_blank" href="<?php echo $this->_tpl_vars['aData']['social_link']; ?>
">&nbsp;</a></div><?php endif; ?>
    </td>   
	<td class="row2 valignMiddle">
        <input type="text" id="email" name="email" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['email'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
" tabindex="1" class="text-field left" />
        <?php if ($this->_tpl_vars['aData']['edit']): ?>
        <div style="opacity:0.5; margin:3px 0 0 10px;" class="left">
            <span id="progress-u-block" style="margin-right:5px; display:none;" class="progress"></span>
            <a href="#" onclick="if(confirm('Продолжить?')) return uChangeBlocked(3,0); return false;" id="u_unblock_lnk" class="ajax clr-success <?php if (! $this->_tpl_vars['aData']['blocked']): ?>hidden<?php endif; ?>">разблокировать</a>
            <a href="#" onclick="uChangeBlocked(1,0,0); return false;" id="u_block_lnk" class="ajax clr-error <?php if ($this->_tpl_vars['aData']['blocked']): ?>hidden<?php endif; ?>">заблокировать</a>
        </div>
        <?php endif; ?>
    </td>
    <td class="row2 valignMiddle" width="180" align="right" rowspan="13">
        <span class="avatar">            
            <img id="avatar" src="<?php echo smarty_function_imgurl(array('folder' => 'avatars','file' => ((is_array($_tmp=@$this->_tpl_vars['aData']['avatar'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')),'id' => ((is_array($_tmp=@$this->_tpl_vars['aData']['user_id'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))), $this);?>
" />
        </span>
        <br/>
        <span class="desc">баланс</span>:<br/>
        <a href="index.php?s=bills&ev=listing&uid=<?php echo $this->_tpl_vars['aData']['user_id']; ?>
" class="bold orange" style="font-size:16px;" target="_blank"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['balance'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</a>
    </td>
</tr>
<tr>
    <td class="row1 field-title">ФИО:</td>
    <td class="row2">
        <input maxlength="35" type="text" name="name" tabindex="2" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['name'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
"  class="text-field" />
    </td>
</tr>    
<?php if (! $this->_tpl_vars['aData']['edit']): ?> 
<tr class="required check-password">
	<td class="row1 field-title">Пароль<span class="required-mark">*</span>:</td>
	<td class="row2">
        <input type="password" id="password" name="password" tabindex="3" autocomplete="off" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['password'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
" class="text-field" />
    </td>
</tr> 
<tr class="required check-password">
	<td class="row1 field-title">Подтверждение пароля<span class="required-mark">*</span>:</td>
	<td class="row2">
        <input type="password" id="password2" name="password2" tabindex="4" class="check-password2 text-field" autocomplete="off" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['password2'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
" />
    </td>
</tr>
<?php else: ?>  
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
    <td class="row2"><a style="color:#000;" href="index.php?s=ban&ev=users"><?php echo ((is_array($_tmp=$this->_tpl_vars['aData']['ip_reg'])) ? $this->_run_mod_handler('long2ip', true, $_tmp) : long2ip($_tmp)); ?>
</a></td>
</tr> 
<tr>
    <td class="row1 field-title">Время авторизации:</td>
    <td class="row2">
        <div style="height: 34px;">
        <?php echo ((is_array($_tmp=$this->_tpl_vars['aData']['login_ts'])) ? $this->_run_mod_handler('date_format2', true, $_tmp, true) : smarty_modifier_date_format2($_tmp, true)); ?>
<span class="desc"> - последнее, <a class="bold desc" href="index.php?s=ban&ev=users"><?php echo ((is_array($_tmp=$this->_tpl_vars['aData']['ip_login'])) ? $this->_run_mod_handler('long2ip', true, $_tmp) : long2ip($_tmp)); ?>
</a></span><br />
        <?php echo ((is_array($_tmp=$this->_tpl_vars['aData']['login_last_ts'])) ? $this->_run_mod_handler('date_format2', true, $_tmp, true) : smarty_modifier_date_format2($_tmp, true)); ?>
<span class="desc"> - предпоследнее</span>
        </div>
    </td>
</tr>
<?php endif; ?> 

<tr>
    <td class="row1 field-title">E-mail<span class="desc"> (для связи)</span>:</td>
    <td class="row2">
        <input type="text" name="email2" tabindex="5" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['email2'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
" class="text-field"  />
    </td>
</tr>    
<tr>
    <td class="row1 field-title">Телефон:</td>
    <td class="row2">
        <input type="text" name="phone" tabindex="6" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['phone'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
" class="text-field"  />
    </td>
</tr>  

<tr class="footer">
    <td colspan="3" class="row1">
        <input type="submit" class="button submit" tabindex="7" value="<?php if (! $this->_tpl_vars['aData']['edit']): ?>Добавить<?php else: ?>Сохранить<?php endif; ?>" />
        <?php if ($this->_tpl_vars['aData']['edit'] && ((is_array($_tmp=@$this->_tpl_vars['aData']['session_id'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?>
        <input type="button" class="clr-error button" value="Разлогинить" onclick="bff.redirect('index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&ev=user_action&member=1&type=logout&rec=<?php echo $this->_tpl_vars['aData']['user_id']; ?>
&tuid=<?php echo $this->_tpl_vars['aData']['tuid']; ?>
');" />
        <?php endif; ?> 
        <input type="button" class="button cancel" value="Отмена" onclick="history.back();" />
    </td>
</tr>

</table>
</form>

<script type="text/javascript">
var uFormEdit = <?php echo ((is_array($_tmp=$this->_tpl_vars['aData']['edit'])) ? $this->_run_mod_handler('intval', true, $_tmp) : intval($_tmp)); ?>
;
<?php echo '
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
    if(confirm(\'Удалить текущий аватар?\'))
    bff.ajax(url, {rec: id, action: \'avatar-delete\'}, function(data){
        if(data)
        {
            $(\'#avatar\').attr(\'src\', defaultAvatar);
            $(\'#avatar_delete_link\').remove();
        }
    });
},

doChangePassword: function(change)
{
    document.getElementById(\'passwordCurrent\').style.display = (change?\'none\':\'block\');
    document.getElementById(\'passwordChange\').style.display = (change?\'block\':\'none\'); 
    $(\'#changepass\').val( change );

    fchecker.check();
    
    if(change)
        $(\'#password\').focus();
        
    return false;
},
city: 0,
onCity: function(sel, progress)
{
    this.city = sel.value;
    if(this.city == -1){
        //show all cities
        bff.ajax(\'index.php?s=sites&ev=ajax&act=city-list&pos=edit\',{},function(data){
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
'; ?>

</script>