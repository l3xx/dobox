<?php /* Smarty version 2.6.7, created on 2012-12-26 00:56:31
         compiled from admin.categories.listing.ajax.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'admin.categories.listing.ajax.tpl', 3, false),)), $this); ?>
<?php if (count($_from = (array)$this->_tpl_vars['aData']['cats'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
<tr id="dnd-<?php echo $this->_tpl_vars['v']['id']; ?>
" numlevel="<?php echo $this->_tpl_vars['v']['numlevel']; ?>
" pid="<?php echo $this->_tpl_vars['v']['pid']; ?>
">
    <td style="padding-left:<?php echo smarty_function_math(array('equation' => "x*15-10",'x' => $this->_tpl_vars['v']['numlevel']), $this);?>
px;" align="left">
        <a onclick="return bbsCatAct(<?php echo $this->_tpl_vars['v']['id']; ?>
,'c',$(this).parent().parent());" class="but folder<?php if (! $this->_tpl_vars['v']['node']): ?>_ua<?php endif; ?> but-text"><?php echo $this->_tpl_vars['v']['title']; ?>
</a>
    </td>
    <td><a href="index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=items_listing&amp;cat_id=<?php echo $this->_tpl_vars['v']['id']; ?>
"><?php echo $this->_tpl_vars['v']['items']; ?>
</a></td>
    <td><a class="but status<?php if (! $this->_tpl_vars['v']['regions']): ?> disabled<?php endif; ?>" onclick="return bbsCatAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'regions', this);" href="#"></a></td>
    <td><a class="but price<?php if (! $this->_tpl_vars['v']['prices']): ?> disabled<?php endif; ?>" id="prc<?php echo $this->_tpl_vars['v']['id']; ?>
" onclick="return bbsCatAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'prices', this);" href="#"></a></td>
    <td>
        <a class="but <?php if ($this->_tpl_vars['v']['enabled']): ?>un<?php endif; ?>block" href="#" onclick="return bbsCatAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'toggle', this);"></a>
        <a class="but sett" onclick="return bbsCatAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'dyn');" href="#"></a>
        <a class="but sett disabled" onclick="return bbsCatAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'type');" href="#"></a>
        <a class="but edit" href="index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=categories_edit&amp;rec=<?php echo $this->_tpl_vars['v']['id']; ?>
" title="<?php echo $this->_tpl_vars['aLang']['edit']; ?>
"></a>
        <?php if ($this->_tpl_vars['v']['numlevel'] >= $this->_tpl_vars['bbs']->category_deep): ?><a href="javascript:void(0);" class="but"></a><?php elseif (! $this->_tpl_vars['v']['node'] && ! $this->_tpl_vars['bbs']->category_mixed && $this->_tpl_vars['v']['items']): ?><a href="javascript:void(0);" class="but add disabled"></a><?php else: ?>
            <a class="but add" href="index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=categories_add&amp;pid=<?php echo $this->_tpl_vars['v']['id']; ?>
" title="<?php echo $this->_tpl_vars['aLang']['add']; ?>
"></a>
        <?php endif; ?>
        <?php if ($this->_tpl_vars['v']['node']): ?><a class="but" href="#"></a><?php else: ?>
            <a class="but del" href="#" onclick="return bbsCatAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'del', this);" title="<?php echo $this->_tpl_vars['aLang']['delete']; ?>
"></a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; endif; unset($_from); ?>