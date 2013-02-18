<?php
    extract( $aData );
?>

<div class="popupCont" id="ipopup-item-svc" style="display:none;"><div class="popup"><div class="top"></div><div class="center">
<div class="close"><a href="#" title="" rel="close"><img src="/img/close.png" alt=""/></a></div>
<h1 class="ipopup-title">Сделать объявление более эффективным <div class="progress" style="margin:7px 0 0 7px; display:none;"></div></h1>
<div class="ipopup-content">
  <form action="/ajax/services?act=activate">
    <input type="hidden" name="item" value="0" />
    <input type="hidden" name="svc" value="0" />
    <div class="svc-error error hidden"></div>
      <? /* Поднятие в списке */ ?>
      <div class="stepBlock addSuccessBlock svc-<?= Services::typeUp ?>">
        <span class="caption putUp"><a href="#" rel="<?= Services::typeUp ?>" class="togglr">Поднять объявление</a></span>
        <span class="arrow"><a href="#" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" /></a></span>
        <div class="clear"></div>
        <div class="textDiv hidden">
          <?= $up['desc']; ?>
            <div class="cost">
                <div>Стоимость услуги  <b class="f18 orange"><?= $up['price']; ?></b> <span class="f11Up orange">$</span></div>
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="ОПЛАТИТЬ" />
                </div>
                <div class="clear"></div>
            </div>            
        </div>
      </div>
      <? /* Выделение */ ?>
      <div class="stepBlock addSuccessBlock svc-<?= Services::typeMark ?>">
        <span class="caption mark"><a href="#" rel="<?= Services::typeMark ?>" class="togglr">выделить объявления</a></span>
        <span class="arrow"><a href="#" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" /></a></span>
        <div class="clear"></div>
        <div class="textDiv hidden">
            <?= $mark['desc']; ?>
            <div class="cost">
                <div>Стоимость услуги  <b class="f18 orange"><?= $mark['price']; ?></b> <span class="f11Up orange">$</span></div>
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="ОПЛАТИТЬ" />
                </div>
                <div class="clear"></div>
            </div>          
        </div>
      </div>
      <? /* Премиум */ ?>
      <div class="stepBlock addSuccessBlock svc-<?= Services::typePremium ?>">
        <span class="caption premium"><a href="#" rel="<?= Services::typePremium ?>" class="togglr">премиум</a></span>
        <span class="arrow"><a href="#" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" /></a></span>
        <div class="clear"></div>
        <div class="textDiv hidden">
          <?= $premium['desc']; ?>
            <div class="cost">
                <div>Стоимость услуги  <b class="f18 orange"><?= $premium['price']; ?></b> <span class="f11Up orange">$</span></div>
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="ОПЛАТИТЬ" />
                </div>
                <div class="clear"></div>
            </div>
        </div>
      </div>
      <? /* В прессу */ ?>
      <div class="stepBlock addSuccessBlock svc-<?= Services::typePress ?>">
        <span class="caption public"><a href="#" rel="<?= Services::typePress ?>" class="togglr">публикация в прессе</a></span>
        <span class="arrow"><a href="#" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" /></a></span>
        <div class="clear"></div>
        <div class="textDiv hidden">
          <?= $press['desc']; ?>
            <div class="cost">
                <div>Стоимость услуги  <b class="f18 orange"><?= $press['price']; ?></b> <span class="f11Up orange">$</span></div>
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="ОПЛАТИТЬ" />
                </div>
                <div class="clear"></div>
            </div>          
        </div>
      </div>    
  </form> 
</div>
</div><div class="bottom"></div></div></div>
