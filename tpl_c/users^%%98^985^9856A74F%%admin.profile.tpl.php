<?php /* Smarty version 2.6.7, created on 2012-12-26 00:57:58
         compiled from admin.profile.tpl */ ?>

<script type="text/javascript">
<?php echo '
//<![CDATA[
    function doChangePass( submitForm )
    {    
        var change = $(\'#changepass\').val();
        $(\'#changepass\').val( change==1?0:1 ); 
        $(\'#changepassdiv\').slideToggle(\'fast\', function(){ if(change==0) $(\'#password0\').focus(); if(submitForm) document.forms.adminProfileForm.submit(); } );
    }
    
    function onSubmit()
    {
        if($(\'#changepass\').val()==1) {
            if($(\'#password0\').val() == \'\' || $(\'#password1\').val() == \'\' || $(\'#password2\').val() == \'\') {
                doChangePass(true);
                return false;
            }
        }
        return true;
    }
//]]> 
'; ?>

</script>

<form action="" method="post" name="adminProfileForm">
<input type="hidden" name="changepass" id="changepass" value="0" />
<table class="admtbl tbledit">
<tr>
	<td class="row2"><input type="text" name="login" value="<?php echo $this->_tpl_vars['aData']['login']; ?>
" class="text-field<?php if (! $this->_tpl_vars['aData']['changelogin']): ?> desc<?php endif; ?>" <?php if (! $this->_tpl_vars['aData']['changelogin']): ?>readonly="readonly"<?php endif; ?> /></td>
</tr>
<tr>
    <td class="row2"><input type="text" name="email" value="<?php echo $this->_tpl_vars['aData']['email']; ?>
" class="text-field" /></td>
</tr>
<tr>
	<td class="row1" colspan="2" align="left">
	<div id="changepassdiv" style="display:none; text-align:left; margin:-4px 0 0 -4px;">
		<table style="width:100%;">
        <tr>                               
            <td class="row1 field-title" width="65">Текущий:</td>
            <td class="row2"><input type="password" name="password0" id="password0" class="text-field" /></td>
        </tr>
		<tr>                               
			<td class="row1 field-title">Новый:</td>
			<td class="row2"><input type="password" name="password1" id="password1" class="text-field" /></td>
		</tr>
		<tr>
			<td class="row1 field-title">Еще раз:</td>
			<td class="row2"><input type="password" name="password2" id="password2" class="text-field" /></td>
		</tr>
		</table>
        </div>
	</td>
</tr>
<tr class="footer">
	<td colspan="3">
        <input type="submit" class="button submit" value="Сохранить" onclick="return onSubmit();" />
        <input type="button" class="button submit" value="Изменить пароль" onclick="doChangePass(false);" />
        
        <?php if ($this->_tpl_vars['fordev']): ?>
        <input type="button" class="button submit" value="Редактировать" onclick="bff.redirect('index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=mod_edit&amp;rec=<?php echo $this->_tpl_vars['aData']['user_id']; ?>
&amp;tuid=<?php echo $this->_tpl_vars['aData']['tuid']; ?>
');" />
        <?php endif; ?>
    </td>
</tr>
</table>
</form>
        