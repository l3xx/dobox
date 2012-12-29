<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>{$config.title} - панель управления</title>                          
<link rel="stylesheet" href="{$theme_url}/css/admin.css" type="text/css">
{foreach from=$tplCSSIncludes item=v}
    <link rel="stylesheet" href="{$v}" type="text/css">
{/foreach}
           
{include file="js.template.tpl"}
<script type="text/javascript">   
{literal}       
$(document).ready(function(){  
    var colapse_process = false;
    $('#adminmenu a.main:not(a.logout)').click(function() {
        if($(this).next().is('.empty')) 
            return true;           
            
        if(colapse_process) return false; colapse_process = true;  

        if(! $(this).next().is(':visible') )
            $('#adminmenu ul.sub li:visible').parent().slideFadeToggle();
        
        $(this).next().slideFadeToggle(function(){ colapse_process = false; });
        return false;
    }).next().hide();
    $('#adminmenu a.active.main').next().show();
});
{/literal}
</script>                                                              
</head>
<body> 

    <div class="warnblock warnblock-fixed" id="warning" style="{if !$aErrors}display:none;{/if}">
        <div class="warnblock-content {if !$isSuccessfull && $errno|default:''!=1}error{else}success{/if}"> 
            <a class="right desc ajax close" href="#" onclick="$('#warning').fadeOut(); return false;">закрыть</a>             
            <ul class="warns"> 
                {foreach from=$aErrors item=v key=k}         
                <li>{$v.msg} {if $v.errno|default:'' == 403}(<a href="#" onclick="history.back();">назад</a>){/if}</li>
                {/foreach}   
            </ul>
        </div>
    </div>  
    {if $aErrors && $errAutohide}
    <script type="text/javascript">
    {literal}
        $(document).ready(function(){
            var errClicked = false;
            $('#warning').click(function(){ 
                if(!errClicked){ errClicked = true; $(this).unbind(); }
                //else $(this).slideUp('fast').unbind(); 
            }); 
            setTimeout(function(){ 
                if(!errClicked) $('#warning').fadeOut(); 
            }, 5000); 
        });
    {/literal}
    </script>    
    {/if} 

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
                              {foreach from=$menuTabs item=v key=k}
                                    <a {if $v.active}class="main active" href="#" {else} class="main" href="{$v.url}"{/if}>{$v.title}</a>
                                    <ul class="sub {if !$v.subtabs}empty{/if}" style="{if !$v.active}display: none;{/if}">
                                       {foreach from=$v.subtabs item=v2 key=kk name=submenu}
                                        <li>
                                            {if $v2.active}
                                                <a href="#" class="active {if $smarty.foreach.submenu.last}last{/if}">{$v2.title}</a>
                                            {else}
                                                {if !$v2.separator}
                                                    <a href="{$v2.url}" {if $smarty.foreach.submenu.last}class="last"{/if}>{$v2.title}</a>
                                                {else}
                                                    <hr size="1" />
                                                {/if}
                                            {/if}
                                            {if $v2.rlink!==false}<a href="{$v2.rlink.url}" class="rlink">{$v2.rlink.title}</a>{/if}
                                            </li>
                                        {/foreach}
                                    </ul>
                              {/foreach}
                              <a class="logout main" href="index.php?s=users&ev=logout">Выход &rarr; </a> <ul></ul>
                              {if $fordev|default:''}<a class="logout main" href="index.php?s=users&amp;ev=profile&amp;fordev=off">Выход из fordev &rarr; </a><ul></ul>{/if}
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
                {if $config.tpl_custom_center_area|default:''}
                    {$center_area}
                {else}  
                <div class="blueblock {if $config.tpl_is_listing || $event|strpos:'listing'!==FALSE }whiteblock{else}lightblock{/if}">    
                    <div class="title">
                        <span class="leftC"></span>
                        <span class="left">{$config.tpl_page_title}</span>
                        <span class="rightC"></span>                        
                    </div>
                    <div class="content clear">
                        <div class="text">    
                            {$center_area}          
                        </div>
                        <div class="bottom">
                            <span class="left"></span>
                            <span class="right"></span>
                        </div>
                    </div>                                    
                </div>
                {/if} 
            </div>
            <div class="clear-all"></div>
        </div>
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
