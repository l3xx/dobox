<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="{$theme_url}/css/admin-sd.css" type="text/css">  
<link rel="stylesheet" href="{$theme_url}/css/admin.css" type="text/css">    
<title>Панель управления | {$config.title}</title> 
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
                                <td valign="top"><input class="img" type="image" alt="Войти" onclick="document.getElementById('progress-login').style.display='inline-block';" tabindex="3" src="{$theme_url}/img/admin/login.png" /></td>
                            </tr>
                        </table>
                    </form>
                    <script type="text/javascript"> 
                        document.getElementById('login').focus();
                        var prgss = new Image(); prgss.src = '{$theme_url}/img/progress-mini.gif'; 
                    </script>
                </div>     
            </div>
        </div>
        {if $aErrors|default:''} 
        <div class="warnblock left"  style="margin-left:15px;">
            <div class="warnblock-content error">
                <ul class="warns">
                {foreach from=$aErrors item=v}
                    <li>{$v.msg}</li>
                {/foreach}
                </ul> 
            </div>
        </div>    
        {/if}                
        <div class="clear"></div>        
        <!--End LOGIN block--> 
        <div id="push"></div>                    
    </div>
    
    <div id="footer">
        <div class="content">
            <div class="left">
                {$config.copyright}
            </div>
            <div class="right">
            </div>
            <div class="clear-all"></div>
        </div>
    </div>  
     
</body>
</html>     
