<?php
    extract($aData);
    $prices_sett = unserialize( $cat_prices_sett );  
    $userID = $this->security->getUserID();
                             
    $nPhotosLimit = $nPhotosLimitTotal = (isset($config['images_limit'])? $config['images_limit'] : $config['images_limit_reg']);
    $nPhotosUploaded = $imgcnt;
    if($nPhotosUploaded > $nPhotosLimit) {
        $nPhotosLimit = 1;
        $nPhotosUploaded = 1;
    }
?>

<script type="text/javascript">                   
//<![CDATA[    
    var bbsEdit;   
    var bbsEditRegions = <?= func::php2js($regions); ?>;
    $(function(){
       bbsEdit = new bbsEditClass({txtMaxLength: <?= $config['adtxt_limit'] ?>, id: <?= $id ?>,
        ssid:'<?= session_id(); ?>', ssname:'<?= session_name(); ?>', 
        p_uploaded: <?= $nPhotosUploaded; ?>,
        p_lmt: <?= $nPhotosLimit; ?>, p_lmt_total: <?= $nPhotosLimitTotal; ?>,
        prices_sett: {p:<?= $cat_prices; ?>, t:<?= $prices_sett['torg']?1:0; ?>, b:<?= $prices_sett['bart']?1:0; ?>}
       }); 
       bffDynpropsTextify.init({block:'#edit-form', prefix:'#edit_d_', selector:'.adtxt', process: function(){ bbsEdit.txtBuild();}  });
    });

//]]>       
</script>

<div class="greyLine">
    <h1>редактировать детали объявления</h1>
    <? if($userID): ?>
    <? if($items_status['blocked']>0){ ?><span class="right"><a href="/items/my?status=<?= BBS_STATUS_BLOCKED ?>">Отклоненные</a> (<?= $items_status['blocked']; ?>)</span><? } ?>
    <span class="right"><a href="/items/my?status=<?= BBS_STATUS_PUBLICATED_OUT ?>">Неактивные</a> (<?= $items_status['notactive']; ?>)</span>
    <span class="right"><a href="/items/my?status=<?= BBS_STATUS_PUBLICATED ?>">Активные</a> (<?= $items_status['active']; ?>)</span>
    <? endif; ?>
    <div class="clear"></div>
</div><script type="text/javascript">$(function(){ $(".greyLine").corner("8px"); }); </script>

<form action="#" id="edit-form">
<input type="hidden" name="id" value="<?= $id; ?>" />
<? if(!$userID): ?><input type="hidden" name="pass" value="<?= $pass; ?>" /> <? endif; ?>
<input type="hidden" name="imo" value="<?= md5('(*&^%$+_)578'.$img); ?>" />
<input type="hidden" name="redirect" value="<?= $redirect; ?>" />

<div class="padBlock">        
    <div class="error hidden" id="edit-error"></div> 
    <?= $dp; ?>
</div>
<div class="padBlock">
    <div>Текст вашего объявления:</div>
    <div class="padTop"><textarea style="width:652px; height:80px;" id="edit-infotext" class="inputText2 adtxt" name="info"><?= $info; ?></textarea></div>            
    
    <? if($cat_prices) { ?>
    <div id="edit-prices">
        <div class="padTop">Цена($):<span class="req">*</span></div>
        <div class="padTop">
            <input type="text" class="inputText2 adtxt req" name="price" value="<?= $price; ?>" maxlength="25" id="edit-prices-price" />
            <? if($prices_sett['torg']): ?><label id="edit-prices-torg"><input type="checkbox" <?= ($price_torg?'checked':'') ?> name="price_torg" class="adtxt" /> возможен торг</label><? endif; ?>
            <? if($prices_sett['bart']): ?><label id="edit-prices-bart"><input type="checkbox" <?= ($price_bart?'checked':'') ?> name="price_bart" class="adtxt" /> возможен бартер</label><? endif; ?>
        </div>
    </div>
    <? } ?>
    
    <? if($cat_regions){ ?>
    <div id="edit-regions">
        <div class="left padRight" id="edit-region-1">
            <div class="padTop">Местоположение:</div>
            <div class="padTop"><select name="reg[1]" class="inputText2" onchange="bbsEdit.regionSelect(this, 1);">
            <option value="0">Выберите...</option>
            <?php foreach($regions as $v) { ?>    
                <option value="<?= $v['id'] ?>"<?= ($v['id'] == $country_id? ' selected ':''); ?>><?= $v['title'] ?></option>
            <?php } ?>
            </select></div>
        </div>
        <div class="left padRight<? if(!$region_id){ ?> hidden<? } ?>" id="edit-region-2">
            <div class="padTop">&nbsp;</div>
            <div class="padTop"><select name="reg[2]" class="inputText2" onchange="bbsEdit.regionSelect(this, 2);">
                <? if($country_id && $region_id){ 
                   foreach($regions[$country_id]['sub'] as $v) { 
                    ?>
                    <option value="<?= $v['id'] ?>"<?= ($v['id'] == $region_id? ' selected ':''); ?>><?= $v['title'] ?></option>
                <? } } else { ?><option value="0">Выберите...</option><? } ?>
            </select></div>
        </div>
        <div class="left<? if(!$city_id){ ?> hidden<? } ?>" id="edit-region-3">
            <div class="padTop">&nbsp;</div>
            <div class="padTop"><select name="reg[3]" class="inputText2">
                <? if($country_id && $region_id && $city_id && !empty($cities)){ 
                   foreach($cities as $v) { 
                    ?>
                    <option value="<?= $v['id'] ?>"<?= ($v['id'] == $city_id? ' selected ':''); ?>><?= $v['title'] ?></option>
                <? } } else { ?><option value="0">Выберите...</option><? } ?>            
            </select></div>
        </div>
        <div class="clear"></div>        
    </div>
    <? } ?>
    
</div>
<div class="padBlock"> 
    <div class="caption">контактная информация<span class="req">*</span></div>
    <div class="padTop">Имя:<span class="req">*</span></div>
    <div class="padTop"><input type="text" name="contacts[name]" value="<?= $contacts_name; ?>" class="inputText2 req" style="width:427px;" /></div>
    <div class="left padRight">
        <div class="padTop">Телефон:<span class="req">*</span></div>
        <div class="padTop"><input type="text" name="contacts[phone]" value="<?= $contacts_phone; ?>" class="inputText2 req" /></div>
    </div>
    <div class="left padRight">
        <div class="padTop padLeft">E-mail:</div>
        <div class="padTop"><input type="text" name="contacts[email]" value="<?= $contacts_email; ?>" class="inputText2 adtxt" /></div>
    </div>
    <div class="left">
        <div class="padTop padLeft">Skype:</div>
        <div class="padTop"><input type="text" name="contacts[skype]" value="<?= $contacts_skype; ?>" class="inputText2" /></div>
    </div>
    <div class="clear"></div>
    <div class="padTop">Ccылка на сайт:</div>
    <div class="padTop"><input type="text" class="inputText2" name="contacts[site]" value="http://<?= $contacts_site; ?>" style="width:427px;" /></div>
</div>
<div class="padBlock">
    <div class="caption">Текст вашего объявления при публикации</div>
    <div class="textDiv"><textarea class="adText" id="edit-ad-text" name="descr" readonly="readonly"><?= $descr; ?></textarea></div>
    <div class="simbol">Осталось: <span class="orange" id="edit-ad-text-counter"><?= func::declension($add_config['adtxt_limit'], array('символ','символа','символов')); ?></span></div>
</div>
<div class="padBlock">
    <div class="caption left">Фотографии</div>
    <div class="left" style="margin:-3px 0 0 10px;"><span id="edit-images-button"></span></div>
    <div class="left progress hidden" style="margin-top: 4px;" id="edit-images-progress"></div>
    <div class="button photoBt hidden">
        <span class="left">&nbsp;</span>
        <input type="button" value="загрузить фото" />
    </div>
    <div class="clear"></div>
    <div class="padTop">
        <input type="hidden" name="imgfav" id="edit-images-fav" value="<?= $imgfav; ?>" />
        <div id="edit-images">
            <? 
            if($imgcnt>0) {
            foreach(explode(',',$img) as $i) { ?>
                <div class="picCont">                                
                    <input type="hidden" name="img[]" value="<?= $i ?>" />
                    <div class="pic"><img rel="<?= $i ?>" src="/files/images/items/<?= $id ?>s<?= $i ?>" /></div>
                    <div class="padTop"><a class="star<?= ($i==$imgfav?' select':''); ?>" onclick="return bbsEdit.photos.fav(this, '<?= $i ?>');" href="#"></a>
                        <a class="del" href="#" rel="<?= $i ?>"></a>
                    </div>
                </div>
            <? } } ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="hint" id="edit-images-tip" style="<? if($imgcnt<2): ?>display:none;<? endif; ?>">Нажмите на звездочку под фото, для того чтобы отметить основное</div>
</div>
<div class="padBlock">
    <div class="caption">видео <span class="lower">(You Tube)</span></div>
    <div class="padTop">
        <span class="left"><input type="text" class="inputText2" name="video" id="edit-video-code" value="<?= $video; ?>" style="width:325px;"/></span>
        <div class="button addBt">
            <span class="left">&nbsp;</span>
            <input type="button" value="добавить видео" onclick="bbsEdit.addVideo();" />
        </div>                  
        <div class="button addBt hidden" id="edit-video-del" style="padding-left: 0px;">
            <span class="left">&nbsp;</span>
            <input type="button" value="удалить видео" onclick="bbsEdit.delVideo(this);" />
        </div>        
        <!--<span class="left" style="padding-top:2px;"><a href="#" class="greyBord">инструкция по размещению</a></span>-->
        <div class="clear"></div>
    </div>
    <div class="padTop hidden" id="edit-video-preview">
        <object height="133" width="160">
            <param value="" name="movie" />
            <param value="true" name="allowFullScreen"><param value="always" name="allowscriptaccess" />
            <embed height="133" width="160" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash" src="" id="embVideo" />
        </object>
    </div> 
</div>        
      
<div class="padBlock">
    <? /* ?>
    <div class="left padRight2">
        Публиковать:
        <div class="padTop">
            <script type="text/javascript"> 
            <?  $periods = array();
                $week = (60*60 * 24 * 7);
                $now = time();
                $periods[1] = date('d.m.Y', $now+$week);     //1 неделя
                $periods[2] = date('d.m.Y', $now+($week*2)); //2 неделя
                $periods[3] = date('d.m.Y', $now+($week*3)); //3 неделя
                $periods[4] = date('d.m.Y', mktime(0,0,0,date('m')+1)); //1 месяц  ?>                        
            var bbsPublicatePeriods = <?= func::php2js( $periods );  ?>;
            </script>
            <select class="inputText2" name="period" onchange="$('#edit-publicated-till').html( bbsPublicatePeriods[this.value] );">
                <option value="1" selected="selected">на 1 неделю</option>
                <option value="2">на 2 недели</option>
                <option value="3">на 3 недели</option>
                <option value="4">на 1 месяц</option>
            </select></div>
    </div>
    <div class="left">
        Срок публикации:
        <div class="term">с <?= date('d.m.Y'); ?> по <span id="edit-publicated-till"><?= date('d.m.Y', time()+( 60*60 * 24 * 7) ); ?></span></div>
    </div>
    <? */ ?>
    <div class="clear"></div>
    <div class="padTop2">                        
        <div class="button left">
            <span class="left">&nbsp;</span>
            <input type="submit" <?= ($status == BBS_STATUS_BLOCKED ? 'value="ИСПРАВИТЬ И ОТПРАВИТЬ МОДЕРАТОРУ" style="width:224px;"' : 'value="СОХРАНИТЬ"  style="width:104px;"') ?> />
        </div>
        <div class="button left" style="margin-left: 10px;">
            <span class="left">&nbsp;</span>
            <input type="button" value="ОТМЕНА" style="width:104px;" onclick="history.back();" />
        </div>        
        <div class="left progress hidden" style="margin: 8px 15px 0;" id="edit-form-progress"></div>
        <div class="clear"></div>
    </div>
</div>

</form> 
