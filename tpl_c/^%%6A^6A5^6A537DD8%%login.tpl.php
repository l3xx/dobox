<?php /* Smarty version 2.6.7, created on 2012-12-26 00:37:58
         compiled from login.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'login.tpl', 41, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['theme_url']; ?>
/css/admin-sd.css" type="text/css">  
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['theme_url']; ?>
/css/admin.css" type="text/css">    
<title>Панель управления | <?php echo $this->_tpl_vars['config']['title']; ?>
</title> 
</head>
<body style="width: 908px;">
    <div id="wrapper">
        <div id="header">
            <div class="logo">
                <a href="/"><img src="/img/logo.png" alt="" /></a>
            </div>
        </div>
         <!--Start LOGIN block-->
        <div class="login-block left">
            <div class="title">
                <span class="left">Авторизация</span><span id="progress-login" style="display:none; margin-top:9px;" class="progress right"></span>                    
            </div>    
            <div class="content clear">
                <div class="text">
                    <form action="" method="post">
                        <input type="hidden" name="s" value="users" />
                        <input type="hidden" name="ev" value="login" /> 
                        <table>
                            <tr>
                                <td><input type="text" value="" name="login" id="login" tabindex="1" style="width:90px;" /></td>
                                <td><input type="password" value="" name="password" tabindex="2" style="width:90px;" /></td>
                                <td valign="top"><input class="img" type="image" alt="Войти" onclick="document.getElementById('progress-login').style.display='inline-block';" tabindex="3" src="<?php echo $this->_tpl_vars['theme_url']; ?>
/img/admin/login.png" /></td>
                            </tr>
                        </table>
                    </form>
                    <script type="text/javascript"> 
                        document.getElementById('login').focus();
                        var prgss = new Image(); prgss.src = '<?php echo $this->_tpl_vars['theme_url']; ?>
/img/progress-mini.gif'; 
                    </script>
                </div>     
            </div>
        </div>
        <?php if (((is_array($_tmp=@$this->_tpl_vars['aErrors'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, ''))): ?> 
        <div class="warnblock left"  style="margin-left:15px;">
            <div class="warnblock-content error">
                <ul class="warns">
                <?php if (count($_from = (array)$this->_tpl_vars['aErrors'])):
    foreach ($_from as $this->_tpl_vars['v']):
?>
                    <li><?php echo $this->_tpl_vars['v']['msg']; ?>
</li>
                <?php endforeach; endif; unset($_from); ?>
                </ul> 
            </div>
        </div>    
        <?php endif; ?>                
        <div class="clear"></div>        
        <!--End LOGIN block--> 
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