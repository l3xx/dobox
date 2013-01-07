<?php /* Smarty version 2.6.7, created on 2012-12-30 01:07:27
         compiled from admin.member.listing.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'admin.member.listing.tpl', 25, false),array('modifier', 'date_format', 'admin.member.listing.tpl', 79, false),)), $this); ?>

<script type="text/javascript">
<?php echo '
//<![CDATA[ 
function uOnCity(sel, progress)
{
    if(sel.value == -1){
        //show all cities
        bff.ajax(\'index.php?s=sites&ev=ajax&act=city-list&pos=list\',{},function(data){
            $(sel).html(data); 
        }, progress);                           
    }
}
     
//]]> 
'; ?>

</script>

<form action="" method="get" name="filters">
<input type="hidden" name="s" value="users" />
<input type="hidden" name="ev" value="listing" />
<input type="hidden" name="order" value="<?php echo $this->_tpl_vars['order_by']; ?>
,<?php echo $this->_tpl_vars['order_dir']; ?>
" /> 
<input type="hidden" name="page" value="1" />                       
<div class="actionBar">                                                                                          
    статус: <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['aData']['status_options'],'style' => 'width:117px;','name' => 'status','selected' => $this->_tpl_vars['aData']['status']), $this);?>
 
    <input type="submit" class="button submit" value="Найти" />
    <div class="right">
        <span style="margin-right: 5px; display: none;" id="progress-users" class="progress"></span>
    </div>
</div>                                             
</form>

<table class="admtbl tblhover">
<tr class="header">
    <th width="60">
        <?php if ($this->_tpl_vars['order_by'] == 'user_id'): ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=user_id,<?php echo $this->_tpl_vars['order_dir_needed']; ?>
&amp;page=1">ID
            <div class="order-<?php echo $this->_tpl_vars['order_dir']; ?>
"></div></a> 
        <?php else: ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=user_id,desc&amp;page=1">ID</a>
        <?php endif; ?>
    </th>
    <th width="165" align="left">
        <?php if ($this->_tpl_vars['order_by'] == 'login'): ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=login,<?php echo $this->_tpl_vars['order_dir_needed']; ?>
&amp;page=1">Email
            <div class="order-<?php echo $this->_tpl_vars['order_dir']; ?>
"></div></a> 
        <?php else: ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=login,asc&amp;page=1">Email</a>
        <?php endif; ?>
    </th>     
    <th align="left">ФИО</th> 
    <th width="100">
        <?php if ($this->_tpl_vars['order_by'] == 'items'): ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=items,<?php echo $this->_tpl_vars['order_dir_needed']; ?>
&amp;page=1">Объявлений
            <div class="order-<?php echo $this->_tpl_vars['order_dir']; ?>
"></div></a> 
        <?php else: ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=items,desc&amp;page=1">Объявлений</a>
        <?php endif; ?>
    </th>  
    <th width="125">
        <?php if ($this->_tpl_vars['order_by'] == 'login_ts'): ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=login_ts,<?php echo $this->_tpl_vars['order_dir_needed']; ?>
&amp;page=1">Был
            <div class="order-<?php echo $this->_tpl_vars['order_dir']; ?>
"></div></a> 
        <?php else: ?>
            <a href="index.php?s=users&amp;ev=listing&amp;order=login_ts,asc&amp;page=1">Был</a>
        <?php endif; ?>
    </th>   
    <th width="85">Действие</th>
</tr>
<?php if (count($_from = (array)$this->_tpl_vars['aData']['users'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
<tr class="row<?php echo $this->_tpl_vars['k']%2; ?>
">
    <td class="small"><?php echo $this->_tpl_vars['v']['user_id']; ?>
</td>
    <td align="left">
        <a class="social-small social-small-<?php echo $this->_tpl_vars['v']['social']; ?>
" target="_blank" href="<?php echo $this->_tpl_vars['v']['social_link']; ?>
">&nbsp;</a>
        <span<?php if ($this->_tpl_vars['v']['admin']): ?> class="admin"<?php endif; ?>><a href="mailto:<?php echo $this->_tpl_vars['v']['login']; ?>
"><?php echo $this->_tpl_vars['v']['login']; ?>
</a></span>
    </td>
    <td align="left"><?php echo $this->_tpl_vars['v']['name']; ?>
</td>                           
    <td><a href="index.php?s=bbs&ev=items_listing&uid=<?php echo $this->_tpl_vars['v']['user_id']; ?>
"><?php echo $this->_tpl_vars['v']['items']; ?>
</a></td>
    <td><?php echo ((is_array($_tmp=$this->_tpl_vars['v']['created'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d.%m.%Y <span class=\"desc\">%H:%M</span>") : smarty_modifier_date_format($_tmp, "%d.%m.%Y <span class=\"desc\">%H:%M</span>")); ?>
</td>
    <td>
        <a id="u<?php echo $this->_tpl_vars['v']['user_id']; ?>
" class="but <?php if (! $this->_tpl_vars['v']['blocked']): ?>un<?php endif; ?>block userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec=<?php echo $this->_tpl_vars['v']['user_id']; ?>
"></a>
        <a class="but edit" title="Редактировать" href="index.php?s=users&amp;ev=member_edit&amp;rec=<?php echo $this->_tpl_vars['v']['user_id']; ?>
&amp;tuid=<?php echo $this->_tpl_vars['v']['tuid']; ?>
&amp;members=1"></a> 
            </td>
</tr>
<?php endforeach; else: ?>
<tr class="norecords">
    <td colspan="7">нет пользователей</td>
</tr>
<?php endif; unset($_from); ?>
</table>
<?php echo $this->_tpl_vars['pagenation_template']; ?>
