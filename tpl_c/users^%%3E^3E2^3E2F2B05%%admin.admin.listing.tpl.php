<?php /* Smarty version 2.6.7, created on 2012-12-26 00:57:57
         compiled from admin.admin.listing.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'admin.admin.listing.tpl', 54, false),array('modifier', 'count', 'admin.admin.listing.tpl', 55, false),)), $this); ?>
<script type="text/javascript">
<?php echo '
//<![CDATA[  
    var pgn = new bff.pgn(\'#filters\', {type:\'prev-next\'});
//]]>   
'; ?>

</script>
<form action="index.php" method="get" name="filters" id="filters">
    <input type="hidden" name="s" value="users" />
    <input type="hidden" name="ev" value="<?php echo $this->_tpl_vars['event']; ?>
" />
    <input type="hidden" name="order" value="<?php echo $this->_tpl_vars['order_by']; ?>
,<?php echo $this->_tpl_vars['order_dir']; ?>
" />                    
    <input type="hidden" name="offset" value="<?php echo $this->_tpl_vars['aData']['offset']; ?>
" />  
</form> 

<table class="admtbl tblhover">
<tr class="header">
    <th width="50">
        <?php if ($this->_tpl_vars['order_by'] == 'user_id'): ?>
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=user_id,<?php echo $this->_tpl_vars['order_dir_needed']; ?>
&amp;page=1">ID
            <div class="order-<?php echo $this->_tpl_vars['order_dir']; ?>
"></div></a> 
        <?php else: ?>
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=user_id,asc&amp;page=1">ID</a>
        <?php endif; ?>
    </th>
    <th>
        <?php if ($this->_tpl_vars['order_by'] == 'login'): ?>
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=login,<?php echo $this->_tpl_vars['order_dir_needed']; ?>
&amp;page=1">Логин
            <div class="order-<?php echo $this->_tpl_vars['order_dir']; ?>
"></div></a> 
        <?php else: ?>
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=login,asc&amp;page=1">Логин</a>
        <?php endif; ?>
    </th> 
    <th>
        <?php if ($this->_tpl_vars['order_by'] == 'email'): ?>
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=email,<?php echo $this->_tpl_vars['order_dir_needed']; ?>
&amp;page=1">E-mail
            <div class="order-<?php echo $this->_tpl_vars['order_dir']; ?>
"></div></a> 
        <?php else: ?>
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=email,asc&amp;page=1">E-mail</a>
        <?php endif; ?>
    </th>        
    <th>Принадлежность к группе</th>
    <th width="85">Действие</th>
</tr>
<?php if (count($_from = (array)$this->_tpl_vars['aData']['users'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
<tr style="<?php if ($this->_tpl_vars['v']['deleted'] == 1): ?>color:#999;<?php endif; ?>" class="row<?php echo $this->_tpl_vars['k']%2; ?>
">
    <td class="small"><?php echo $this->_tpl_vars['v']['user_id']; ?>
</td>
    <td>
        <?php if ($this->_tpl_vars['v']['deleted']): ?><img src="<?php echo $this->_tpl_vars['theme_url']; ?>
/img/admin/icons/icon_del.gif" title="аккаунт удален" /><?php endif; ?> 
        <a title="<?php if ($this->_tpl_vars['fordev']): ?>пароль: '<?php echo $this->_tpl_vars['v']['password']; ?>
'<?php else:  echo $this->_tpl_vars['v']['login'];  endif; ?>" class="admin" style="text-decoration:none; cursor:default;" href="#"><?php echo $this->_tpl_vars['v']['login']; ?>
</a>
    </td>
    <td><a href="mailto:<?php echo $this->_tpl_vars['v']['email']; ?>
"><?php echo $this->_tpl_vars['v']['email']; ?>
</a></td>
    <td>
        <?php if (count($_from = (array)$this->_tpl_vars['v']['groups'])):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['g']):
?>
            <a title="доступ группы '<?php echo $this->_tpl_vars['g']['title']; ?>
'" href="index.php?s=users&amp;ev=group_permission_listing&amp;rec=<?php echo $this->_tpl_vars['g']['group_id']; ?>
" style="color:<?php echo ((is_array($_tmp=@$this->_tpl_vars['g']['color'])) ? $this->_run_mod_handler('default', true, $_tmp, '#000') : smarty_modifier_default($_tmp, '#000')); ?>
; font-weight:bold; text-decoration:none;">
                <?php echo $this->_tpl_vars['g']['title']; ?>
</a></span><?php if ($this->_tpl_vars['key']+1 != count($this->_tpl_vars['v']['groups'])): ?>,<?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
    </td>
    <td>
        <a class="but <?php if (! $this->_tpl_vars['v']['blocked']): ?>un<?php endif; ?>block userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec=<?php echo $this->_tpl_vars['v']['user_id']; ?>
" id="u<?php echo $this->_tpl_vars['v']['user_id']; ?>
"></a>
        <a class="but edit" title="Редактировать" href="index.php?s=users&amp;ev=mod_edit&amp;rec=<?php echo $this->_tpl_vars['v']['user_id']; ?>
&amp;tuid=<?php echo $this->_tpl_vars['v']['tuid']; ?>
"></a>
        <a class="but del" title="Удалить" href="#" onclick="return bff.redirect('index.php?s=users&amp;ev=user_action&amp;type=delete&amp;rec=<?php echo $this->_tpl_vars['v']['user_id']; ?>
&amp;tuid=<?php echo $this->_tpl_vars['v']['tuid']; ?>
', 'Удалить пользователя?');" ></a>
    </td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>

<div class="pagenation">
    <?php echo $this->_tpl_vars['aData']['pgn']; ?>

</div>
