<?php /* Smarty version 2.6.7, created on 2012-12-26 00:57:32
         compiled from admin.listing.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'userlink', 'admin.listing.tpl', 31, false),array('modifier', 'date_format', 'admin.listing.tpl', 37, false),)), $this); ?>

<script type="text/javascript">  
<?php echo '
//<![CDATA[  
    var contacts = {
        del: function(id, link)
        {
            bff.ajaxDelete(\'Удалить?\', id, \'index.php?s=contacts&ev=ajax&act=del\', link,
                {progress: \'#progress-contacts\'});
            return false;            
        }
    };
    
    var pgn = new bff.pgn(\'#pagenation\', {type:\'prev-next\'});     
//]]>   
'; ?>

</script>
<table class="admtbl tblhover" id="contacts-tbl">

<?php if ($this->_tpl_vars['aData']['type'] == @CONTACTS_TYPE_CONTACT): ?> 

    <tr class="header">           
        <th width="140" align="left" style="padding-left:10px;">Отправитель</th> 
        <th width="115" align="left">Телефон</th> 
        <th align="left">Сообщение</th>
        <th width="76">Дата <div class="order-desc"></div></th>
    </tr>              
    <?php if (count($_from = (array)$this->_tpl_vars['aData']['contacts'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
    <tr class="row<?php echo $this->_tpl_vars['k']%2; ?>
" id="c<?php echo $this->_tpl_vars['v']['id']; ?>
">
            <td style="vertical-align: top;" align="left">
                <?php if ($this->_tpl_vars['v']['user_id']):  echo smarty_function_userlink(array('name' => $this->_tpl_vars['v']['uname'],'id' => $this->_tpl_vars['v']['user_id'],'login' => $this->_tpl_vars['v']['ulogin'],'admin' => $this->_tpl_vars['v']['uadmin']), $this); else: ?><a href="mailto:<?php echo $this->_tpl_vars['v']['email']; ?>
"><?php echo $this->_tpl_vars['v']['email']; ?>
</a><?php endif; ?>
            </td>
            <td align="left"><?php echo $this->_tpl_vars['v']['phone']; ?>
</td>
            <td align="left"><?php echo $this->_tpl_vars['v']['message']; ?>
</td>
            <td class="alignCenter small" style="vertical-align:top;">
                <div style="margin-top:3px;">
                    <div class="left<?php if (! $this->_tpl_vars['v']['viewed']): ?> clr-error<?php endif; ?>"><?php echo ((is_array($_tmp=$this->_tpl_vars['v']['created'])) ? $this->_run_mod_handler('date_format', true, $_tmp, '%d.%m.%Y <br /><span class="description">%H:%M:%S</span>') : smarty_modifier_date_format($_tmp, '%d.%m.%Y <br /><span class="description">%H:%M:%S</span>')); ?>
</div>
                    <a class="but del right" style="margin:0px;" title="Удалить" href="#" onclick="return contacts.del('<?php echo $this->_tpl_vars['v']['id']; ?>
', this);" ></a>
                </div>
            </td>
    </tr>
    <?php endforeach; else: ?>
    <tr class="norecords">
        <td colspan="3">нет сообщений</td>
    </tr>
    <?php endif; unset($_from); ?>
<?php endif; ?>

</table>

<form action="index.php" method="get" name="pagenation" id="pagenation">
    <input type="hidden" name="s" value="<?php echo $this->_tpl_vars['class']; ?>
" />
    <input type="hidden" name="ev" value="<?php echo $this->_tpl_vars['event']; ?>
" />
    <input type="hidden" name="type" value="<?php echo $this->_tpl_vars['aData']['type']; ?>
" />                                      
    <input type="hidden" name="offset" value="<?php echo $this->_tpl_vars['aData']['offset']; ?>
" />  
</form> 
<div class="pagenation">
    <?php echo $this->_tpl_vars['aData']['pgn']; ?>

</div>
