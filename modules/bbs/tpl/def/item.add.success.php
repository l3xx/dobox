<?php 
    extract($aData);  
?>

<div class="padBlock noBorder">
  <div class="allRight">Ваше объявление успешно опубликовано</div>
  <div class="textPad">
    <div class="advBlock">
      <div class="pic"><img src="<?= tpl::imgurl(array('folder'=>'items', 'file'=>(!empty($imgfav) ? $id.'t'.$imgfav : ''), 'static'=>1)); ?>" /></div>
      <div class="desc">
        <?php if($cat_type>0): ?><b class="upper"><?= $cat_type_title; ?>:</b> <?php endif; ?><?= nl2br($descr); ?>
        <?php if($cat_subtype>0): ?><b class="upper"><?= $cat_subtype_title; ?>:</b> <?php endif; ?><?= nl2br($descr); ?>
        <div class="address"><?= $descr_regions; ?></div>
      </div>
      <?php if($cat_prices>0): ?><div class="price"><b class="f18"><?= $price; ?></b> <span class="f11Up">руб</span><br/><?= ($price_torg ? 'торг' : '').($price_bart ? ($price_torg ? ', ': '').'бартер' : '' ); ?></div><?php endif; ?>
      <div class="clear"></div>
    </div>
  </div>
  <? if(!$member): ?><div class="textPad">Для редактирования и других действий с этим объявлением используйте его номер <b class="f14 orange"><?= $id; ?></b> и пароль <span class="blue"><?= $pass; ?></span></div><? endif; ?>
</div>

<script type="text/javascript">                   
//<![CDATA[    
    $(function(){
        
        var $block = $('#svcPublicateBlock');
        var $progressSvc = $('div.progress', $block);
        var $error = $('div.svc-error', $block);
        var $form = $('form:first', $block);
        var process = false;
        $form.submit(function(){
            if(process) return false; process = true;
            bff.ajax(this.action, $form.serialize(), function(data, errors){
                if(data.res) {
                    if(data.pay) {
                        app.pay( data.form );
                        return;
                    } else {
                        app.showError($error, 'Услуга успешно активирована', true);
                        setTimeout(function(){ $error.slideUp(); }, 1500);
                    }
                } else {
                    app.showError($error, errors, false);
                }
                process = false;
            }, $progressSvc)
            return false;
        });        
        
        $('a.togglr', $block).click(function(e){
            nothing(e);
            $('input[name="svc"]', $form).val( this.rel );
            var $block = $(this).parent().parent();
            $('a.togglr-arrow>img', $block).attr('src', '/img/'+($block.hasClass('active')?'arrowBottom.png':'arrowTop.png'));
            $block.toggleClass('active');
            $('.textDiv', $block).slideFadeToggle(150, function(){
                $block.siblings('.active').removeClass('active').find('.textDiv').slideFadeToggle(140).end().
                       find('a.togglr-arrow>img').attr('src', '/img/arrowBottom.png');
            });
        }); 
    });

//]]>       
</script>

<div id="svcPublicateBlock">
  <h1 class="left">сделать объявление более эффективным</h1><div class="progress left" style="margin:13px 0 0 15px; display:none;"></div>
  <div class="clear"></div>
  <form action="/ajax/services?act=activate">
    <input type="hidden" name="item" value="<?= $id; ?>" />
    <input type="hidden" name="svc" value="0" />
    <div class="svc-error error hidden"></div>
  <div class="stepBlock addSuccessBlock">
    <span class="caption putUp"><a href="#" rel="<?= Services::typeUp ?>" class="togglr">Поднять объявление</a></span>
    <span class="arrow"><a href="#" rel="<?= Services::typeUp ?>" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" alt=""/></a></span>
    <div class="clear"></div>
    <div class="textDiv hidden">
      <?= $svc['up']['desc']; ?>
        <div class="cost">
            <div>Стоимость услуги  <b class="f18 orange"><?= $svc['up']['price']; ?></b> <span class="f11Up orange">руб</span></div>
            <div class="button left">
                <span class="left">&nbsp;</span>
                <input type="submit" value="ОПЛАТИТЬ" />
            </div>
            <div class="clear"></div>
        </div>      
    </div>
  </div>
  <div class="stepBlock addSuccessBlock">
    <span class="caption mark"><a href="#" rel="<?= Services::typeMark ?>" class="togglr">Выделить объявления</a></span>
    <span class="arrow"><a href="#" rel="<?= Services::typeMark ?>" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" alt=""/></a></span>
    <div class="clear"></div>
    <div class="textDiv hidden">
      <?= $svc['mark']['desc']; ?>
        <div class="cost">
            <div>Стоимость услуги  <b class="f18 orange"><?= $svc['mark']['price']; ?></b> <span class="f11Up orange">руб</span></div>
            <div class="button left">
                <span class="left">&nbsp;</span>
                <input type="submit" value="ОПЛАТИТЬ" />
            </div>
            <div class="clear"></div>
        </div>       
    </div>
  </div>
  <div class="stepBlock addSuccessBlock">
    <span class="caption premium"><a href="#" rel="<?= Services::typePremium ?>" class="togglr">Премиум</a></span>
    <span class="arrow"><a href="#" rel="<?= Services::typePremium ?>" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" alt=""/></a></span>
    <div class="clear"></div>
    <div class="textDiv hidden">
      <?= $svc['premium']['desc']; ?>
        <div class="cost">
            <div>Стоимость услуги  <b class="f18 orange"><?= $svc['premium']['price']; ?></b> <span class="f11Up orange">руб</span></div>
            <div class="button left">
                <span class="left">&nbsp;</span>
                <input type="submit" value="ОПЛАТИТЬ" />
            </div>
            <div class="clear"></div>
        </div> 
    </div>
  </div>
  <div class="stepBlock addSuccessBlock">
    <span class="caption public"><a href="#" rel="<?= Services::typePress ?>" class="togglr">Публикация в прессе</a></span>
    <span class="arrow"><a href="#" rel="<?= Services::typePress ?>" class="togglr togglr-arrow"><img src="/img/arrowBottom.png" alt=""/></a></span>
    <div class="clear"></div>
    <div class="textDiv hidden">
      <?= $svc['press']['desc']; ?>
        <div class="cost">
            <div>Стоимость услуги  <b class="f18 orange"><?= $svc['press']['price']; ?></b> <span class="f11Up orange">руб</span></div>
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