<?php 
    extract($aData);
?>
<h1>размещение объявления</h1>
<form action="#" id="add-form">
<input type="hidden" name="id" value="0" />
<input type="hidden" name="pass" value="" />
<input type="hidden" name="uid" value="" /> 
<div class="addStep active" id="add-step-1">
    <div class="step">шаг 1</div>
    <span class="caption">Раздел и тип:</span>

    <div class="selects-edit">    
        <select name="cat[1]" class="cat" onchange="bbsAdd.categorySelect(this, 1);">
            <option value="0">выбрать</option>
            <?php foreach($cats as $v) { ?>    
                <option value="<?= $v['id'] ?>"><?= $v['title'] ?></option>
            <?php } ?>
        </select>  
    </div>
    <div class="selects-view"></div>
    
    <span class="right add-step-edit hidden"><a class="greyBord" href="#" onclick="return bbsAdd.stepEdit(1);">изменить</a></span>
    
</div>
<div class="addStep" id="add-step-2">
    <div class="step">шаг 2</div>
    <span class="caption">Детали объявления</span>
    <span class="right add-step-edit hidden"><a class="greyBord" href="#" onclick="return bbsAdd.stepEdit(2);">изменить</a></span>
    <div class="clear"></div>
    <div class="add-step-content hidden">
        <div class="padBlock">        
            <div class="error hidden" id="add-error"></div> 
                
            <div id="add-step-dynprops">

            </div>
        </div>
        <div class="padBlock">
            <div>Текст вашего объявления:</div>
            <div class="padTop"><textarea style="width:652px; height:80px;" id="add-step-infotext" class="inputText2 adtxt" name="info"></textarea></div>            
            
            <div id="add-step-prices" style="display: none;">
                <div class="padTop">Цена(руб):<span class="req">*</span></div>
                <div class="padTop">
                    <input type="text" class="inputText2 adtxt req" name="price" maxlength="25" id="add-step-prices-price" />
                    <label style="display: none;" id="add-step-prices-torg"><input type="checkbox" name="price_torg" class="adtxt" /> возможен торг</label>
                    <label style="display: none;" id="add-step-prices-bart"><input type="checkbox" name="price_bart" class="adtxt" /> возможен бартер</label>
                </div>
            </div>            
            
            <div id="add-step-regions" class="hidden">
                <div class="left padRight" id="add-step-region-1">
                    <div class="padTop">Местоположение:</div>
                    <div class="padTop"><select name="reg[1]" class="inputText2" onchange="bbsAdd.regionSelect(this, 1);">
                    <option value="0">Выберите...</option>
                    <?php foreach($regions as $v) { ?>    
                        <option value="<?= $v['id'] ?>"><?= $v['title'] ?></option>
                    <?php } ?>
                    </select></div>
                </div>
                <div class="left padRight hidden" id="add-step-region-2">
                    <div class="padTop">&nbsp;</div>
                    <div class="padTop"><select name="reg[2]" class="inputText2" onchange="bbsAdd.regionSelect(this, 2);"><option value="0">Выберите...</option></select></div>
                </div>
                <div class="left hidden" id="add-step-region-3">
                    <div class="padTop">&nbsp;</div>
                    <div class="padTop"><select name="reg[3]" class="inputText2"><option value="0">Выберите...</option></select></div>
                </div>
                <div class="clear"></div>        
            </div>
        </div>
        <div class="padBlock"> 
                <div class="clear"></div>
                <div class="caption padBig">контактная информация<span class="req">*</span></div>
                <div class="padTop">Имя:<span class="req">*</span></div>
                <div class="padTop"><input type="text" name="contacts[name]" value="<?= $contacts['name']; ?>" class="inputText2 req" style="width:427px;" /></div>
                <div class="left padRight">
                    <div class="padTop">Телефон:<span class="req">*</span></div>
                    <div class="padTop"><input type="text" name="contacts[phone]" value="<?= $contacts['phone']; ?>" class="inputText2 req" /></div>
                </div>
                <div class="left padRight">
                    <div class="padTop padLeft">E-mail:</div>
                    <div class="padTop"><input type="text" name="contacts[email]" value="<?= $contacts['email2']; ?>" class="inputText2 adtxt" /></div>
                </div>
                <div class="left">
                    <div class="padTop padLeft">Skype:</div>
                    <div class="padTop"><input type="text" name="contacts[skype]" value="<?= $contacts['skype']; ?>" class="inputText2" /></div>
                </div>
                <div class="clear"></div>
                <div class="padTop">Ccылка на сайт:</div>
                <div class="padTop"><input type="text" class="inputText2" name="contacts[site]" value="http://" style="width:427px;" /></div>

        </div>
        <div class="padBlock">
            <div class="caption">Текст вашего объявления при публикации</div>
            <div class="textDiv"><textarea class="adText" id="add-ad-text" name="descr" readonly="readonly"></textarea></div>
            <div class="simbol">Осталось: <span class="orange" id="add-ad-text-counter"><?= func::declension($config['adtxt_limit'], array('символ','символа','символов')); ?></span></div>
        </div>
        <div class="padBlock">
            <div class="caption left">Фотографии</div>
            <div class="left" style="margin:-3px 0 0 10px;"><span id="add-images-button"></span></div>
            <div class="left progress hidden" style="margin-top: 4px;" id="add-images-progress"></div>
            <div class="button photoBt hidden">
                <span class="left">&nbsp;</span>
                <input type="button" value="загрузить фото" />
            </div>
            <div class="clear"></div>
            <div class="padTop">
                <input type="hidden" name="imgfav" id="add-images-fav" value="" />
                <div id="add-images">
                </div>
                <div class="clear"></div>
            </div>
            <div class="hint" id="add-images-tip" style="display:none;">Нажмите на звездочку под фото, для того чтобы отметить основное</div>
        </div>
        <div class="padBlock">
            <div class="caption">видео <span class="lower">(You Tube)</span></div>
            <div class="padTop">
                <span class="left"><input type="text" class="inputText2" name="video" id="add-video-code" style="width:325px;"/></span>
                <div class="button addBt">
                    <span class="left">&nbsp;</span>
                    <input type="button" value="добавить видео" onclick="bbsAdd.addVideo();" />
                </div>
                <div class="button addBt hidden" id="add-video-del" style="padding-left: 0px;">
                    <span class="left">&nbsp;</span>
                    <input type="button" value="удалить видео" onclick="bbsAdd.delVideo(this);" />
                </div>                        
                <!--<span class="left" style="padding-top:2px;"><a href="#" class="greyBord">инструкция по размещению</a></span>-->
                <div class="clear"></div>
            </div>
            <div class="padTop hidden" id="add-video-preview">
                <object height="133" width="160">
                    <param value="" name="movie" />
                    <param value="true" name="allowFullScreen"><param value="always" name="allowscriptaccess" />
                    <embed height="133" width="160" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash" src="" id="embVideo" />
                </object>
            </div>                
            <div class="padTop2">
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="сохранить и перейти к публикации" style="width:247px;" />
                </div>
                <div class="left progress hidden" style="margin: 8px 15px 0;" id="add-form-progress"></div>
                <div class="clear"></div>
            </div> 
        </div>      
    </div>
</div>
</form> 
<form action="#" id="publicate-form">
<div class="addStep" id="add-step-3">
    <div class="step">шаг 3</div>
    <span class="caption">публикация на <span class="blue">w</span><span class="orange">sell.</span><span class="blue">ru</span></span>
    <div class="clear"></div>
    <div class="add-step-content hidden">
        <div class="padBlock">
            <div class="error hidden" id="publicate-error" style="margin-bottom: 15px;"></div>
            <div class="left padRight2">
                Публиковать:
                <div class="padTop">
                    <script type="text/javascript"> 
                    <?  
                        $week = (60*60 * 24 * 7);
                        $now = time();
                        $periods = bff::getPublicatePeriods( $now );
                        ?>
                    var bbsPublicatePeriods = <?= func::php2js( $periods['data'] ); ?>;
                    </script>
                    <select class="inputText2" name="period" onchange="$('#add-publicated-till').html( bbsPublicatePeriods[this.value] );">
                        <?= $periods['html'] ?> 
                    </select>
                </div>
            </div>
            <div class="left">
                Срок публикации:
                <div class="term">с <?= date('d.m.Y'); ?> по <span id="add-publicated-till"><?= date('d.m.Y', $now + $week ); ?></span></div>
            </div>
            <div class="clear"></div>
            <div class="padTop2">
                <div class="advBlock">
                    <div class="pic" id="add-view-image"><img src="/img/noImage.gif" /></div>
                    <div class="desc">
                        <b class="upper" id="add-view-type"></b> <span id="add-view-text"></span>
                        <div class="address" id="add-view-region"></div>
                    </div>
                    <div class="price"><b class="f18" id="add-view-price">6000</b> <span class="f11Up">руб</span><br/><span id="add-view-price-torg" style="display: none;">торг</span><span id="add-view-price-bart" style="display: none;"> бартер</span></div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="padTop2">
                <? if($this->security->isLogined()): ?>
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="РАЗМЕСТИТЬ ОБЪЯВЛЕНИЕ" style="width:185px;" />
                </div>
                <? else: ?>
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="РАЗМЕСТИТЬ ОБЪЯВЛЕНИЕ БЕЗ РЕГИСТРАЦИИ" style="width:280px;"/>
                </div>
                <span class="login"><a href="#" class="enter user-enter">Вход</a> / <a href="#" class="user-enter">Регистрация</a></span>
                <? endif; ?>
                <div class="left progress hidden" style="margin: 8px 15px 0;" id="publicate-form-progress"></div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>
</form>

<script type="text/javascript">
//<![CDATA[    
    var bbsAdd;   
    var bbsAddRegions = <?= func::php2js($regions); ?>;
    $(function(){
    
       bbsAdd = new bbsAddClass({txtMaxLength: <?= $config['adtxt_limit'] ?>, 
        ssid:'<?= session_id(); ?>', plmt: <?= (isset($config['images_limit']) ? $config['images_limit'] : $config['images_limit_reg']) ?>
       }); 

       bffDynpropsTextify.init({block:'#add-form', prefix:'#add_d_', selector:'.adtxt', process: function(){ bbsAdd.txtBuild();}  });

    });

//]]>       
</script>