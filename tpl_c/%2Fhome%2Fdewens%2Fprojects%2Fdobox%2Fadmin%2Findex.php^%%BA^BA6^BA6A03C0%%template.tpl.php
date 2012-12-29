<?php /* Smarty version 2.6.7, created on 2012-12-26 00:53:49
         compiled from template.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'template.tpl', 36, false),array('modifier', 'strpos', 'template.tpl', 127, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $this->_tpl_vars['config']['title']; ?>
 - панель управления</title>                          
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['theme_url']; ?>
/css/admin.css" type="text/css">
<?php if (count($_from = (array)$this->_tpl_vars['tplCSSIncludes'])):
    foreach ($_from as $this->_tpl_vars['v']):
?>
    <link rel="stylesheet" href="<?php echo $this->_tpl_vars['v']; ?>
" type="text/css">
<?php endforeach; endif; unset($_from); ?>
           
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "js.template.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<script type="text/javascript">   
<?php echo '       
$(document).ready(function(){  
    var colapse_process = false;
    $(\'#adminmenu a.main:not(a.logout)\').click(function() {
        if($(this).next().is(\'.empty\')) 
            return true;           
            
        if(colapse_process) return false; colapse_process = true;  

        if(! $(this).next().is(\':visible\') )
            $(\'#adminmenu ul.sub li:visible\').parent().slideFadeToggle();
        
        $(this).next().slideFadeToggle(function(){ colapse_process = false; });
        return false;
    }).next().hide();
    $(\'#adminmenu a.active.main\').next().show();
});
'; ?>

</script>                                                              
</head>
<body> 

    <div class="warnblock warnblock-fixed" id="warning" style="<?php if (! $this->_tpl_vars['aErrors']): ?>display:none;<?php endif; ?>">
        <div class="warnblock-content <?php if (! $this->_tpl_vars['isSuccessfull'] && ((is_array($_tmp=@$this->_tpl_vars['errno'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')) != 1): ?>error<?php else: ?>success<?php endif; ?>"> 
            <a class="right desc ajax close" href="#" onclick="$('#warning').fadeOut(); return false;">закрыть</a>             
            <ul class="warns"> 
                <?php if (count($_from = (array)$this->_tpl_vars['aErrors'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>         
                <li><?php echo $this->_tpl_vars['v']['msg']; ?>
 <?php if (((is_array($_tmp=@$this->_tpl_vars['v']['errno'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')) == 403): ?>(<a href="#" onclick="history.back();">назад</a>)<?php endif; ?></li>
                <?php endforeach; endif; unset($_from); ?>   
            </ul>
        </div>
    </div>  
    <?php if ($this->_tpl_vars['aErrors'] && $this->_tpl_vars['errAutohide']): ?>
    <script type="text/javascript">
    <?php echo '
        $(document).ready(function(){
            var errClicked = false;
            $(\'#warning\').click(function(){ 
                if(!errClicked){ errClicked = true; $(this).unbind(); }
                //else $(this).slideUp(\'fast\').unbind(); 
            }); 
            setTimeout(function(){ 
                if(!errClicked) $(\'#warning\').fadeOut(); 
            }, 5000); 
        });
    '; ?>

    </script>    
    <?php endif; ?> 

    <div id="popupMsg" class="ipopup" style="display:none;">
        <div class="ipopup-wrapper">
            <div class="ipopup-title"></div>
            <div class="ipopup-content"></div>
            <div class="ipopup-footer-wrapper">
                <a href="javascript:void(null);" rel="close" class="ajax right">Закрыть</a>
                <div class="ipopup-footer"></div>
            </div>
        </div>
    </div>
    
    <div id="wrapper">
        <div id="header">
            <div class="logo left">
                <a href="/"><img src="/img/logo.png" alt="" /></a> 
                <br />
                <br />
            </div>
        </div>
        <div id="main-side">
            <div id="left-side">
                <div class="blueblock lightblock">    
                    <div class="title">
                        <span class="leftC"></span>
                        <span class="left">Панель управления</span>
                        <span class="rightC"></span>                        
                    </div>
                    <div class="content clear">
                        <div class="text" style="padding:0px;">
                            <div class="adminmenu" id="adminmenu">
                              <?php if (count($_from = (array)$this->_tpl_vars['menuTabs'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
                                    <a <?php if ($this->_tpl_vars['v']['active']): ?>class="main active" href="#" <?php else: ?> class="main" href="<?php echo $this->_tpl_vars['v']['url']; ?>
"<?php endif; ?>><?php echo $this->_tpl_vars['v']['title']; ?>
</a>
                                    <ul class="sub <?php if (! $this->_tpl_vars['v']['subtabs']): ?>empty<?php endif; ?>" style="<?php if (! $this->_tpl_vars['v']['active']): ?>display: none;<?php endif; ?>">
                                       <?php $this->_foreach['submenu'] = array('total' => count($_from = (array)$this->_tpl_vars['v']['subtabs']), 'iteration' => 0);
if ($this->_foreach['submenu']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['kk'] => $this->_tpl_vars['v2']):
        $this->_foreach['submenu']['iteration']++;
?>
                                        <li>
                                            <?php if ($this->_tpl_vars['v2']['active']): ?>
                                                <a href="#" class="active <?php if (($this->_foreach['submenu']['iteration'] == $this->_foreach['submenu']['total'])): ?>last<?php endif; ?>"><?php echo $this->_tpl_vars['v2']['title']; ?>
</a>
                                            <?php else: ?>
                                                <?php if (! $this->_tpl_vars['v2']['separator']): ?>
                                                    <a href="<?php echo $this->_tpl_vars['v2']['url']; ?>
" <?php if (($this->_foreach['submenu']['iteration'] == $this->_foreach['submenu']['total'])): ?>class="last"<?php endif; ?>><?php echo $this->_tpl_vars['v2']['title']; ?>
</a>
                                                <?php else: ?>
                                                    <hr size="1" />
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ($this->_tpl_vars['v2']['rlink'] !== false): ?><a href="<?php echo $this->_tpl_vars['v2']['rlink']['url']; ?>
" class="rlink"><?php echo $this->_tpl_vars['v2']['rlink']['title']; ?>
</a><?php endif; ?>
                                            </li>
                                        <?php endforeach; endif; unset($_from); ?>
                                    </ul>
                              <?php endforeach; endif; unset($_from); ?>
                              <a class="logout main" href="index.php?s=users&ev=logout">Выход &rarr; </a> <ul></ul>
                              <?php if (((is_array($_tmp=@$this->_tpl_vars['fordev'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?><a class="logout main" href="index.php?s=users&amp;ev=profile&amp;fordev=off">Выход из fordev &rarr; </a><ul></ul><?php endif; ?>
                            </div>  
                        </div>
                        <div class="bottom">
                            <span class="left"></span>
                            <span class="right"></span>
                        </div>
                    </div>                                    
                </div>                     
                <div class="clear-all"></div>
            </div>                
            <div id="content-side">
                <?php if (((is_array($_tmp=@$this->_tpl_vars['config']['tpl_custom_center_area'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?>
                    <?php echo $this->_tpl_vars['center_area']; ?>

                <?php else: ?>  
                <div class="blueblock <?php if ($this->_tpl_vars['config']['tpl_is_listing'] || ((is_array($_tmp=$this->_tpl_vars['event'])) ? $this->_run_mod_handler('strpos', true, $_tmp, 'listing') : strpos($_tmp, 'listing')) !== FALSE): ?>whiteblock<?php else: ?>lightblock<?php endif; ?>">    
                    <div class="title">
                        <span class="leftC"></span>
                        <span class="left"><?php echo $this->_tpl_vars['config']['tpl_page_title']; ?>
</span>
                        <span class="rightC"></span>                        
                    </div>
                    <div class="content clear">
                        <div class="text">    
                            <?php echo $this->_tpl_vars['center_area']; ?>
          
                        </div>
                        <div class="bottom">
                            <span class="left"></span>
                            <span class="right"></span>
                        </div>
                    </div>                                    
                </div>
                <?php endif; ?> 
            </div>
            <div class="clear-all"></div>
        </div>
        <div id="push"></div>
    </div>
    
    <div id="footer">
        <div class="content">
            <div class="left">
                <?php echo $this->_tpl_vars['config']['copyright']; ?>

            </div>
            <div class="right">
            </div>
            <div class="clear-all"></div>
        </div>
    </div>                                                                 
    
</body>
</html>