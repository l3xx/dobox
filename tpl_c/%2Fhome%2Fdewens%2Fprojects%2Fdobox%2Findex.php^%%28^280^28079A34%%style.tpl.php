<?php /* Smarty version 2.6.7, created on 2012-12-29 12:25:28
         compiled from style.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'lower', 'style.tpl', 2, false),array('modifier', 'default', 'style.tpl', 3, false),array('modifier', 'stristr', 'style.tpl', 4, false),)), $this); ?>

<?php $this->assign('user_agent', ((is_array($_tmp=$_SERVER['HTTP_USER_AGENT'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp))); ?>
<?php if (((is_array($_tmp=@$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?>
    <?php if (((is_array($_tmp=$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('stristr', true, $_tmp, 'msie') : stristr($_tmp, 'msie'))): ?>
        <?php if (((is_array($_tmp=$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('stristr', true, $_tmp, 'msie 6') : stristr($_tmp, 'msie 6'))): ?>
        <link rel="stylesheet" type="text/css" href="/css/ie6.css"  media="screen" />
        <?php else: ?>
        <link rel="stylesheet" type="text/css" href="/css/ie.css" media="screen" />
        <?php endif; ?>
    <?php elseif (((is_array($_tmp=$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('stristr', true, $_tmp, 'opera') : stristr($_tmp, 'opera'))): ?>
    <link rel="stylesheet" type="text/css" href="/css/opera.css" media="screen" /> 
    <?php elseif (((is_array($_tmp=$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('stristr', true, $_tmp, 'safari') : stristr($_tmp, 'safari'))): ?>
    <link rel="stylesheet" type="text/css" href="/css/safari.css" media="screen" /> 
        <?php if (((is_array($_tmp=$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('stristr', true, $_tmp, 'chrome') : stristr($_tmp, 'chrome'))): ?>
        <link rel="stylesheet" type="text/css" href="/css/chrome.css" media="screen" />
        <?php endif; ?> 
    <?php elseif (((is_array($_tmp=$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('stristr', true, $_tmp, 'ns') : stristr($_tmp, 'ns'))): ?>
    <link rel="stylesheet" type="text/css" href="/css/netscape.css" media="screen" /> 
    <?php elseif (((is_array($_tmp=$this->_tpl_vars['user_agent'])) ? $this->_run_mod_handler('stristr', true, $_tmp, 'moz') : stristr($_tmp, 'moz'))): ?>
    <link rel="stylesheet" type="text/css" href="/css/moz.css" media="screen" />
    <?php endif; ?>
<?php endif; ?> 
                        
<?php if (count($_from = (array)$this->_tpl_vars['tplCSSIncludes'])):
    foreach ($_from as $this->_tpl_vars['v']):
?>
    <link rel="stylesheet" href="<?php echo $this->_tpl_vars['v']; ?>
" type="text/css" />
<?php endforeach; endif; unset($_from); ?> 