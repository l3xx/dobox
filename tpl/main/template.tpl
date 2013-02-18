<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$config.title}</title>
<meta name="keywords" content="{$config.mkeywords}" />
<meta name="description" content="{$config.mdescription}"  />
<link rel="stylesheet" type="text/css" href="/css/main.css" media="screen" />
{include file="style.tpl"}
{include file="js.template.tpl"}
</head>
<body> 
<div id="wsPay"></div>
<div id="wrap">
    
    <div class="header">
        <div class="container">
          <span class="left"><a href="/" title=""><img src="/img/logo.png" alt=""/></a></span>
          <span class="right">{$aBanners.top}</span>
            <div class="advertise" style="float: right;"><a href="/items/add">
                <span class="left">&nbsp;</span>
                <span class="btCont"><input type="button" value="РАЗМЕСТИТЬ ОБЪЯВЛЕНИЕ" /></span></a>
            </div>
          <div class="clear"></div>
      </div>
    </div>
        <div class="content">
            <div class="container">
            <div class="leftBlock" style="width: 968px;">
                <div class="topLinks {if $userLogined} logined{/if}" id="userMenu">
                    {if $userLogined}
                        {if $config.userMenuCurrent == 1}<span class="cont left activeFav"><span class="left"><b class="favorite">Избранное</b> (<span id="favCounter">{$config.bbs_favs.total}</span>)</span><span class="rightCor">&nbsp;</span></span>
                        {else}<span class="cont left"><span class="left"><a href="/items/fav" title="Избранное" class="favorite">Избранное</a> (<span id="favCounter">{$config.bbs_favs.total}</span>)</span><span class="rightCor">&nbsp;</span></span>{/if}
                        {if $config.userMenuCurrent == 2}<span class="cont left activeAds"><span class="left"><b class="ads">Мои объявления</b></span><span class="rightCor">&nbsp;</span></span>
                        {else}<span class="cont left"><span class="left"><a href="/items/my" title="Мои объявления" class="ads">Мои объявления</a></span><span class="rightCor">&nbsp;</span></span>{/if}
                        {if $config.userMenuCurrent == 3}<span class="cont left activeCount"><span class="left"><b class="ads">Мой счет</b> (<b class="f14">{$config.user_balance}</b> <span class="f10Up">$</span>)</span><span class="rightCor">&nbsp;</span></span>
                        {else}<span class="cont left"><span class="left"><a href="/bill" title="Мой счет" class="count"  style="color:grey;">Мой счет</a> (<b class="f14">{$config.user_balance}</b> <span class="f10Up">$</span>)</span><span class="rightCor">&nbsp;</span></span>{/if}
                        <span class="exit"><a href="/user/logout" title="Выход">Выход</a></span>
                        {if $config.userMenuCurrent == 4}<span class="cont right activeSet"><span class="left"><b class="settings">Настройки</b></span><span class="rightCor">&nbsp;</span></span>
                        {else}<span class="cont right"><span class="left"><a href="/user/profile" title="Настройки" class="settings">Настройки</a></span><span class="rightCor">&nbsp;</span></span>{/if}
                    {else}
                        {if $config.userMenuCurrent == 1}<span class="cont left activeFav"><span class="left"><b class="favorite">Избранное</b> (<span id="favCounter">{$config.bbs_favs.total}</span>)</span><span class="rightCor">&nbsp;</span></span>
                        {else}<span class="cont left"><span class="left"><a href="/items/fav" class="favorite">Избранное</a> (<span id="favCounter">{$config.bbs_favs.total}</span>)</span><span class="rightCor">&nbsp;</span></span>{/if}
                        <span class="cont right"><span class="left"><a href="#" class="enter user-enter">Вход</a> / <a href="#" class="user-enter">Регистрация</a></span><span class="rightCor">&nbsp;</span></span>
                    
                        <div class="popupCont" id="ipoРАЗМЕЩЕНИЕ ОБЪЯВЛЕНИЯpup-user-enter" style="display:none;">
                          <div class="popup">
                            <div class="top"></div>
                            <div class="center">
                              <div class="close"><a href="#" title="" rel="close"><img src="/img/close.png" alt=""/></a></div>
                              <h1 class="ipopup-title"><span class="enter-title">Вход</span> на <span class="blue">w</span><span class="orange">sell.</span><span class="blue">ru</span></h1>
                              <div class="ipopup-content">
                                
                                  <form action="/ajax/users?act=enter">
                                    <div class="error enter-error hidden"></div>
                                    <div class="padTop">Введите ваш e-mail:</div>
                                    <div class="padTop"><input type="text" value="" name="email" class="inputText enter-email" tabindex="1" /></div>
                                    <div class="padTop2"><span class="left">Введите пароль:</span><span class="right"><a href="/user/forgot" target="_blank">Напомнить пароль</a></span><div class="clear"></div></div>
                                    <div class="padTop"><input type="password"  name="pass" class="inputText enter-pass"  tabindex="2" /></div>
                                    <div class="padTop2"><label><input type="checkbox" class="enter-reg" name="reg" /> Я новый пользователь</label></div>
                                    <div class="padTop">
                                        <div class="button left">
                                            <span class="left">&nbsp;</span>
                                            <input type="submit" class="enter-submit" tabindex="3" value="войти"/> 
                                        </div><div class="progress enter-progress" style="margin:7px 0 0 12px; display:none;"></div>
                                        <div class="clear"></div>
                                    </div>
                                  </form> 
                              </div>
                            </div>
                            <div class="bottom"></div>
                          </div>
                        </div>
                    {/if}
                    <div class="clear"></div>

                </div>
            </div>
            <div class="leftBlock" style="width: 220px; margin-right: 52px;">
                {$menu}
            </div>
                <div class="leftBlock" style="">
                {$center_area}
            </div>
            {*<div class="rightBlock">*}
                {*<div class="advertise"><a href="/items/add">*}
                    {*<span class="left">&nbsp;</span>*}
                    {*<span class="btCont"><input type="button" value="РАЗМЕСТИТЬ ОБЪЯВЛЕНИЕ" /></span></a>*}
                {*</div>*}
                {*<div class="clear"></div>*}
                {*<div class="padTop">*}
                {*{if $config.bbs_instruction}*}
                    {*<div class="textDiv" id="add-intructions">*}
                        {*<span class="caption">Инструкция</span>*}
                        {*{if $bbsInstructions.cur<4}*}
                        {*<div class="add-instruction-1">{$bbsInstructions.i.add_instruct1}</div>*}
                        {*<div class="add-instruction-2 hidden">{$bbsInstructions.i.add_instruct2}</div>*}
                        {*<div class="add-instruction-3 hidden">{$bbsInstructions.i.add_instruct3}</div>*}
                        {*{else}*}
                        {*<div>{$bbsInstructions.i}</div>*}
                        {*{/if}*}
                    {*</div>*}
                {*{else}*}
                    {*{if $aBanners.right1}<div class="padTop">{$aBanners.right1}</div>{/if}*}
                    {*{if $aBanners.right2}<div class="padTop">{$aBanners.right2}</div>{/if}*}
                    {*{if $aBanners.right3}<div class="padTop">{$aBanners.right3}</div>{/if}*}
                {*{/if}*}
                {*</div>*}
                {*<div class="padTop useful">*}
                    {*<span class="caption">полезное</span>*}
                    {*<ul>*}
                        {*{foreach from=$menu_useful item=v name=menu_useful}*}
                            {*<li{if $smarty.foreach.menu_useful.last} class="last"{/if}><a href="{$v.menu_link}">{$v.menu_title}</a></li>*}
                        {*{/foreach}*}
                    {*</ul>*}
                {*</div>*}
                {*<div class="popupCont" id="ipopup-common" style="display:none;">*}
                  {*<div class="popup">*}
                    {*<div class="top"></div>*}
                    {*<div class="center">*}
                      {*<div class="close"><a href="#" rel="close"><img src="/img/close.png" alt=""/></a></div>*}
                      {*<h1 class="ipopup-title"></h1>*}
                      {*<div class="ipopup-content">*}
                      {*</div>*}
                    {*</div>*}
                    {*<div class="bottom"></div>*}
                  {*</div>*}
                {*</div>*}
            {*</div>*}
            <div class="clear"></div>
        </div>
    </div>
    <div class="push"></div>
</div>
<div class="footer">
    <div class="container">
        <div class="greyBlock">
            <div class="top"><span class="left">&nbsp;</span><div class="clear"></div></div>
            <div class="center">
                <div class="advertise">
                  <a href="/items/add"><span class="left">&nbsp;</span>
                  <span class="btCont"><input type="button" value="РАЗМЕСТИТЬ ОБЪЯВЛЕНИЕ" /></span></a>
                </div>
                <ul class="footerLinks">
                    {if $footer_faq}
                    {foreach from=$footer_faq item=v}
                        <li><a href="/help/{$v.id}">{$v.question}</a></li>
                    {/foreach}
                    {/if}
                </ul>
                <div class="clear"></div>
            </div>
            <div class="bottom"><span class="left">&nbsp;</span><div class="clear"></div></div>
        </div>
        <div class="footerbottom">
            <span class="left">{$config.copyright}</span>
            <span class="right">
                {foreach from=$counters item=v}
                    {$v.code}    
                {/foreach}
            </span>
            <div class="clear"></div>
        </div>
    </div>
</div>    
</body>
</html>
