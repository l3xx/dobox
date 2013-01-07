<?php /* Smarty version 2.6.7, created on 2012-12-30 01:07:27
         compiled from pagenation.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'pagenation.tpl', 6, false),array('modifier', 'count', 'pagenation.tpl', 9, false),)), $this); ?>

<div style="margin-top:7px;">
    <table>
        <tr>
            <td align="left">
            <?php if (((is_array($_tmp=@$this->_tpl_vars['pgPrev'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?><a href="<?php echo $this->_tpl_vars['pgPrev']; ?>
" id="previous_page">Назад</a><?php else: ?>Назад<?php endif; ?>&nbsp;&nbsp;[<span class="black bold"><?php echo $this->_tpl_vars['pgFromTo']; ?>
 из <?php echo $this->_tpl_vars['pgTotalCount']; ?>
</span>]&nbsp;&nbsp;<?php if (((is_array($_tmp=@$this->_tpl_vars['pgNext'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?><a href="<?php echo $this->_tpl_vars['pgNext']; ?>
" id="next_page">Вперёд</a><?php else: ?>Вперёд<?php endif; ?>
            </td>
            <td>
                <?php if (count($this->_tpl_vars['pagenation']) > 1): ?>
                <div style="margin-left:20px;">
                    <?php if (((is_array($_tmp=@$this->_tpl_vars['pgsPrev'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?><a href="<?php echo $this->_tpl_vars['pgsPrev']; ?>
">&lt;&lt;</a><?php endif; ?>
                    <?php $this->_foreach['pagenation'] = array('total' => count($_from = (array)$this->_tpl_vars['pagenation']), 'iteration' => 0);
if ($this->_foreach['pagenation']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
        $this->_foreach['pagenation']['iteration']++;
?>
                        <?php if ($this->_tpl_vars['v']['active']): ?><b><?php echo $this->_tpl_vars['v']['page']; ?>
&nbsp;</b>
                        <?php else: ?><a href="<?php echo $this->_tpl_vars['v']['link']; ?>
"><?php echo $this->_tpl_vars['v']['page']; ?>
</a>&nbsp;<?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
                    <?php if (((is_array($_tmp=@$this->_tpl_vars['pgsNext'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?><a href="<?php echo $this->_tpl_vars['pgsNext']; ?>
">&gt;&gt;</a><?php endif; ?>  
                </div>
                <?php endif; ?>
            </td>
        </tr>                                                 
    </table>
</div>