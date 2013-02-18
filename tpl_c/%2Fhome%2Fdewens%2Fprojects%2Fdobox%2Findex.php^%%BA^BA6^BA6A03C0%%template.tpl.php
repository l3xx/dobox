<?php /* Smarty version 2.6.7, created on 2013-02-07 23:10:05
         compiled from template.tpl */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_tpl_vars['config']['title']; ?>
</title>
<meta name="keywords" content="<?php echo $this->_tpl_vars['config']['mkeywords']; ?>
" />
<meta name="description" content="<?php echo $this->_tpl_vars['config']['mdescription']; ?>
"  />
<link rel="stylesheet" type="text/css" href="/css/main.css" media="screen" />
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "style.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "js.template.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</head>
<body> 
<div id="wsPay"></div>
<div id="wrap">
    
    <div class="header">
        <div class="container">
          <span class="left"><a href="/" title=""><img src="/img/logo.png" alt=""/></a></span>
          <span class="right"><?php echo $this->_tpl_vars['aBanners']['top']; ?>
</span>
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
                <div class="topLinks <?php if ($this->_tpl_vars['userLogined']): ?> logined<?php endif; ?>" id="userMenu">
                    <?php if ($this->_tpl_vars['userLogined']): ?>
                        <?php if ($this->_tpl_vars['config']['userMenuCurrent'] == 1): ?><span class="cont left activeFav"><span class="left"><b class="favorite">Избранное</b> (<span id="favCounter"><?php echo $this->_tpl_vars['config']['bbs_favs']['total']; ?>
</span>)</span><span class="rightCor">&nbsp;</span></span>
                        <?php else: ?><span class="cont left"><span class="left"><a href="/items/fav" title="Избранное" class="favorite">Избранное</a> (<span id="favCounter"><?php echo $this->_tpl_vars['config']['bbs_favs']['total']; ?>
</span>)</span><span class="rightCor">&nbsp;</span></span><?php endif; ?>
                        <?php if ($this->_tpl_vars['config']['userMenuCurrent'] == 2): ?><span class="cont left activeAds"><span class="left"><b class="ads">Мои объявления</b></span><span class="rightCor">&nbsp;</span></span>
                        <?php else: ?><span class="cont left"><span class="left"><a href="/items/my" title="Мои объявления" class="ads">Мои объявления</a></span><span class="rightCor">&nbsp;</span></span><?php endif; ?>
                        <?php if ($this->_tpl_vars['config']['userMenuCurrent'] == 3): ?><span class="cont left activeCount"><span class="left"><b class="ads">Мой счет</b> (<b class="f14"><?php echo $this->_tpl_vars['config']['user_balance']; ?>
</b> <span class="f10Up">$</span>)</span><span class="rightCor">&nbsp;</span></span>
                        <?php else: ?><span class="cont left"><span class="left"><a href="/bill" title="Мой счет" class="count"  style="color:grey;">Мой счет</a> (<b class="f14"><?php echo $this->_tpl_vars['config']['user_balance']; ?>
</b> <span class="f10Up">$</span>)</span><span class="rightCor">&nbsp;</span></span><?php endif; ?>
                        <span class="exit"><a href="/user/logout" title="Выход">Выход</a></span>
                        <?php if ($this->_tpl_vars['config']['userMenuCurrent'] == 4): ?><span class="cont right activeSet"><span class="left"><b class="settings">Настройки</b></span><span class="rightCor">&nbsp;</span></span>
                        <?php else: ?><span class="cont right"><span class="left"><a href="/user/profile" title="Настройки" class="settings">Настройки</a></span><span class="rightCor">&nbsp;</span></span><?php endif; ?>
                    <?php else: ?>
                        <?php if ($this->_tpl_vars['config']['userMenuCurrent'] == 1): ?><span class="cont left activeFav"><span class="left"><b class="favorite">Избранное</b> (<span id="favCounter"><?php echo $this->_tpl_vars['config']['bbs_favs']['total']; ?>
</span>)</span><span class="rightCor">&nbsp;</span></span>
                        <?php else: ?><span class="cont left"><span class="left"><a href="/items/fav" class="favorite">Избранное</a> (<span id="favCounter"><?php echo $this->_tpl_vars['config']['bbs_favs']['total']; ?>
</span>)</span><span class="rightCor">&nbsp;</span></span><?php endif; ?>
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
                    <?php endif; ?>
                    <div class="clear"></div>

                </div>
            </div>
            <div class="leftBlock" style="width: 220px; margin-right: 52px;">
                <?php echo $this->_tpl_vars['menu']; ?>

            </div>
                <div class="leftBlock" style="">
                <?php echo $this->_tpl_vars['center_area']; ?>

            </div>
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
                    <?php if ($this->_tpl_vars['footer_faq']): ?>
                    <?php if (count($_from = (array)$this->_tpl_vars['footer_faq'])):
    foreach ($_from as $this->_tpl_vars['v']):
?>
                        <li><a href="/help/<?php echo $this->_tpl_vars['v']['id']; ?>
"><?php echo $this->_tpl_vars['v']['question']; ?>
</a></li>
                    <?php endforeach; endif; unset($_from); ?>
                    <?php endif; ?>
                </ul>
                <div class="clear"></div>
            </div>
            <div class="bottom"><span class="left">&nbsp;</span><div class="clear"></div></div>
        </div>
        <div class="footerbottom">
            <span class="left"><?php echo $this->_tpl_vars['config']['copyright']; ?>
</span>
            <span class="right">
                <?php if (count($_from = (array)$this->_tpl_vars['counters'])):
    foreach ($_from as $this->_tpl_vars['v']):
?>
                    <?php echo $this->_tpl_vars['v']['code']; ?>
    
                <?php endforeach; endif; unset($_from); ?>
            </span>
            <div class="clear"></div>
        </div>
    </div>
</div>    
</body>
</html>