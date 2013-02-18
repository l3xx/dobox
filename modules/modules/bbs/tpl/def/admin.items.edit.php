<?php
    extract($aData);
    
    $prices_sett = unserialize( $cat_prices_sett );  
    
    $userID = $this->security->getUserID();
    
    $aConfig = array('adtxt_limit');
    $aConfig[] = 'images_limit'.($this->security->isMember() ? '_reg':'');
    $config = config::get($aConfig, false, $this->module_name.'_');
    
    $blocked = ($status == BBS_STATUS_BLOCKED);
?>

<script type="text/javascript">                   
//<![CDATA[    
    var bbsEdit;   
    var bbsEditRegions = <?= func::php2js($regions); ?>;
    $(function(){
       bbsEdit = new bbsEditClass({txtMaxLength: <?= $config['adtxt_limit'] ?>, id: <?= $id ?>, 
            ssid:'<?= session_id(); ?>', puploaded: <?= $imgcnt ?>, plmt: <?= $this->items_images_limit; ?>,
        prices_sett: {p:<?= $cat_prices; ?>, t:<?= $prices_sett['torg']?1:0; ?>, b:<?= $prices_sett['bart']?1:0; ?>}
       }); 
       bffDynpropsTextify.init({block:'#edit-form', prefix:'#edit_d_', selector:'.adtxt', process: function(){ bbsEdit.txtBuild();}  });
    });
    
    function iChangeBlocked(step, block)
    {
        var form = document.forms.itemEditForm;
        if(step==1){//заблокировать/изменить блокировку
            $('#i_blocked').hide();
            $('#i_blocked_error, #i_blocking, #i_blocking_warn').show(0, function(){
                form['blocked_reason'].focus();    
            }); 
        } else if(step==2) {//отменить
            if(form['blocked'].value == 1){
                $('#i_blocking, #i_blocking_warn').hide();
                $('#i_blocked').show(); 
            } else {
                $('#i_blocked_error, #i_blocking_warn').hide(); 
            }
        } else if(step==3) {//сохранить
            bff.ajax('index.php?s=bbs&ev=ajax&act=item-block', $(form).serializeArray(), function(data){
                if(data)
                {
                    if(!block){                                                  
                        $('#i_blocked_error, #i_blocking_warn').hide();
                    } else {
                        $('#i_blocking_warn').hide();
                        $('#i_blocked_text').html(form['blocked_reason'].value);
                        $('#i_blocked_error').show();
                        iChangeBlocked(2);
                    }
                    history.back();
                }
            }, '#progress-i-block');
        }
        return false;
    }
    function iItemApprove()
    {
        bff.ajax('index.php?s=bbs&ev=ajax&act=item-approve', $(document.forms.itemEditForm).serializeArray(), function(data){
            if(data) { 
               history.back();
            }
        }, '#progress-i-block');
    }
    function iItemUnpublicate()
    {
        if(bff.confirm('sure'))
        bff.ajax('index.php?s=bbs&ev=ajax&act=item-unpublicate', $(document.forms.itemEditForm).serializeArray(), function(data){
            if(data) {                               
                history.back();
            }
        }, '#progress-i-block');
    }
    function iItemPublicate2(step)
    {             
        var $block = $('#i_publicate2');
        if(step==0)
        {
            $block.show();
        }
        else if(step==1) //сохранить
        {
            bff.ajax('index.php?s=bbs&ev=ajax&act=item-publicate2', $(document.forms.itemEditForm).serializeArray(), function(data){
                if(data) { 
                   history.back();
                }
            }, '#progress-i-block');
        }
        else if(step==2) //отмена
        {
            $block.hide();
        }
    }   
//]]>       
</script>

<form method="post" action="" id="edit-form" name="itemEditForm" enctype="multipart/form-data">
<input type="hidden" name="action" value="info" />
<input type="hidden" name="id" value="<?= $id; ?>" />
<input type="hidden"  name="blocked" value="<?= ($blocked?1:0) ?>" />
<table class="admtbl tbledit dp">
<tr>
    <td class="row1" width="90">Категория:</td>
    <td class="row2 bold"><div style="height:20px;"><?= $cats_path.($cat_type>0 ? '&nbsp;&nbsp;<img src="/img/arrowRightSmall.png" />&nbsp;&nbsp;'.$cat_type_title : ''); ?></div><input type="hidden" name="cat_id" value="<?= $cat_id; ?>" /></td>
</tr>
<? if($this->url_keywords){ ?>
<tr>
    <td class="row1"><span class="field-title">Keyword</span>:</td>   
    <td class="row2">                   
        <div class="left relative" style="width:100%">
            <label for="keyword" class="placeholder">keyword для URL</label>  
            <input class="stretch" type="text" maxlength="90" name="keyword" id="keyword" placeholder="keyword для URL" value="<?= $keyword; ?>" />
        </div> 
        <div class="clear"></div>
    </td>
</tr>
<? } if($dp!==0) { ?>
<tr>
    <td class="row1"><span class="field-title">Параметры</span>:</td>
    <td class="row2" style="padding-top: 0;"><?= $dp; ?></td>
</tr>
<? } ?>
<tr><td colspan="2"><hr class="cut"></td></tr> 
<tr>
    <td class="row1"><span class="field-title">Текст вашего объявления</span>: </td>
    <td class="row2"><textarea name="info" style="height: 110px;" id="edit-infotext" class="adtxt stretch"><?= $info; ?></textarea></td>
</tr>
<? if($cat_prices){ ?>
<tr id="edit-prices">
    <td class="row1"><span class="field-title">Цена</span> <span class="desc">(<?= $this->items_currency['short']; ?>)</span>:<span class="req">*</span></td>
    <td class="row2">
        <input type="text" maxlength="9" name="price" id="edit-prices-price" class="adtxt req" value="<?= $price; ?>" style="width:100px;" /> &nbsp;&nbsp;
        <? if($prices_sett['torg']): ?><label id="edit-prices-torg"><input type="checkbox" <?= ($price_torg?'checked':'') ?> name="price_torg" class="adtxt" /> торг</label><? endif; ?>
        <? if($prices_sett['bart']): ?><label id="edit-prices-bart"><input type="checkbox" <?= ($price_bart?'checked':'') ?> name="price_bart" class="adtxt" /> бартер</label><? endif; ?>
    </td>
</tr>            
<? } 
 if($cat_regions){ ?>
<tr> 
    <td class="row1"><span class="field-title">Местоположение</span>:</td>
    <td class="row2">
        <div class="left" id="edit-region-1" style="padding-right: 10px;">
            <select name="reg[1]" class="inputText2" onchange="bbsEdit.regionSelect(this, 1);">
            <option value="0">Выберите...</option>
            <?php foreach($regions as $v) { ?>    
                <option value="<?= $v['id'] ?>"<?= ($v['id'] == $country_id? ' selected ':''); ?>><?= $v['title'] ?></option>
            <?php } ?>
            </select>
        </div>
        <div class="left <? if(!$region_id){ ?> hidden<? } ?>" id="edit-region-2" style="padding-right: 10px;">
            <select name="reg[2]" class="inputText2" onchange="bbsEdit.regionSelect(this, 2);">
                <? if($country_id && $region_id){ 
                   foreach($regions[$country_id]['sub'] as $v) { 
                    ?>
                    <option value="<?= $v['id'] ?>"<?= ($v['id'] == $region_id? ' selected ':''); ?>><?= $v['title'] ?></option>
                <? } } else { ?><option value="0">Выберите...</option><? } ?>
            </select>
        </div>
        <div class="left<? if(!$city_id){ ?> hidden<? } ?>" id="edit-region-3">
           <select name="reg[3]" class="inputText2">
                <? if($country_id && $region_id && $city_id && !empty($cities)){ 
                   foreach($cities as $v) { 
                    ?>
                    <option value="<?= $v['id'] ?>"<?= ($v['id'] == $city_id? ' selected ':''); ?>><?= $v['title'] ?></option>
                <? } } else { ?><option value="0">Выберите...</option><? } ?>            
            </select>
        </div>
        <div class="clear"></div>
    </td>
</tr>
<? } ?>
<tr><td colspan="2"><hr class="cut"></td></tr>    
<tr>
    <td class="row1"><span class="field-title">Контакты</span>: </td>
    <td class="row2">
        <div>Имя:<span class="req">*</span></div>
        <div class="padTop"><input type="text" name="contacts[name]" value="<?= $contacts_name; ?>" class="req" style="width:427px;" /></div>
        <div class="left padRight">
            <div class="padTop">Телефон:<span class="req">*</span></div>
            <div class="padTop"><input type="text" name="contacts[phone]" value="<?= $contacts_phone; ?>" class="req" style="width: 126px;" /></div>
        </div>
        <div class="left padRight">
            <div class="padTop padLeft">E-mail:</div>
            <div class="padTop"><input type="text" name="contacts[email]" value="<?= $contacts_email; ?>" class="adtxt" style="width: 126px;" /></div>
        </div>
        <div class="left">
            <div class="padTop padLeft">Skype:</div>
            <div class="padTop"><input type="text" name="contacts[skype]" value="<?= $contacts_skype; ?>" style="width: 127px;" /></div>
        </div>
        <div class="clear"></div>
        <div class="padTop">Ccылка на сайт:</div>
        <div class="padTop"><input type="text" name="contacts[site]" value="<?= $contacts_site; ?>" style="width:427px;" /></div>
    </td>
</tr>
<tr><td colspan="2"><hr class="cut"></td></tr>
<tr>
    <td class="row1">
        <span class="field-title">Текст объявления при публикации</span>:<br/>
        <span class="desc" id="edit-ad-text-counter"><?= func::declension($config['adtxt_limit'], array('символ','символа','символов')); ?></span>
    </td>
    <td class="row2"><textarea class="stretch" id="edit-ad-text" readonly="readonly" name="descr" style="height: 110px;"><?= $descr; ?></textarea></td>
</tr> 
<tr>
    <td class="row1"><span class="field-title">Видео</span>: <br/><span class="desc">(You Tube)</span></td>
    <td class="row2">
        <div>
            <span class="left"><input type="text" name="video" id="edit-video-code" value="<?= $video; ?>" style="width:325px; margin-right: 5px;"/></span>
            <input type="button" class="button submit left" value="добавить видео" onclick="bbsEdit.addVideo();" />
            <input type="button" class="button submit left hidden" value="удалить видео" id="edit-video-del" onclick="bbsEdit.delVideo(this);" />
            <div class="clear"></div>
        </div>
        <div class="padTop hidden" id="edit-video-preview">
            <object height="233" width="260">
                <param value="" name="movie" />
                <param value="true" name="allowFullScreen"><param value="always" name="allowscriptaccess" />
                <embed height="233" width="260" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash" src="" id="embVideo" />
            </object>
        </div> 
    </td>
</tr> 
<tr><td colspan="2"><hr class="cut"></td></tr>
<tr>
    <td class="row1"><span class="field-title">Meta Keywords</span>:</td>
    <td class="row2"><textarea name="mkeywords" style="height: 110px;" class="stretch"><?= $mkeywords; ?></textarea></td>
</tr>
<tr>
    <td class="row1"><span class="field-title">Meta Description</span>:</td>
    <td class="row2"><textarea name="mdescription" style="height: 110px;" class="stretch"><?= $mdescription; ?></textarea></td>
</tr>
<tr class="row1">
    <td class="field-title">Статус:</td>
    <td align="left"><b><? 
        switch($status) {
            case BBS_STATUS_NEW: { echo 'Новое'; } break;
            case BBS_STATUS_PUBLICATED: { echo 'Публикуется'; } break;
            case BBS_STATUS_PUBLICATED_OUT: { echo 'Период публикации завершился'; } break;
            case BBS_STATUS_BLOCKED: { echo ($moderated == 0?'Ожидает проверки (из черного списка)':'Заблокировано'); } break;
        }
    ?></b></td>
</tr>
<? if($status != BBS_STATUS_NEW) { ?>
<tr class="row1">
    <td class="field-title">Период публикации:</td>
    <td align="left"><b><?= tpl::date_format3($publicated, 'd.m.Y').' - '.tpl::date_format3($publicated_to, 'd.m.Y'); ?></b></td>
</tr>
<? } ?>
<tr class="row1">
    <td class="field-title">Услуги:</td>
    <td align="left"><? 
        switch($svc) {
            case Services::typeUp:      { echo '<img src="/img/putUp.png" />&nbsp;&nbsp;поднятое '.tpl::date_format3($publicated_order, 'd.m.Y'); } break;
            case Services::typeMark:    { echo '<img src="/img/mark.png" />&nbsp;&nbsp;выделенное до '.tpl::date_format3($marked_to, 'd.m.Y'); } break;
            case Services::typePremium: { echo '<img src="/img/premium.png" />&nbsp;&nbsp;премиум до '.tpl::date_format3($premium_to, 'd.m.Y'); } break;
            default: { echo 'нет'; }
        }
    ?></td>
</tr>     
<tr>
    <td class="row1"></td>
    <td class="row2">
        <div class="warnblock<?= (!$blocked ? ' hidden':'') ?>" id="i_blocked_error" style="margin-bottom:10px;">
            <div class="warnblock-content">  
                <div><b>причина отклонения</b>: <div class="right description" id="i_blocking_warn" style="display:none;"></div></div>
                <div class="clear"></div> 
                <div id="i_blocked">
                    <span id="i_blocked_text"><?= (!empty($blocked_reason) ? $blocked_reason :'?') ?></span> - <a href="#" onclick="iChangeBlocked(1,0); return false;" class="ajax description">изменить</a>
                </div>
                <div id="i_blocking" style="display: none;">
                    <textarea name="blocked_reason" id="i_blocking_text" class="autogrow" style="height:60px; min-height:60px;"
                        onkeyup="checkTextLength(300, this.value, $('#i_blocking_warn').get(0));"><?= $blocked_reason; ?></textarea>
                    <a onclick="return iChangeBlocked(3,1);" class="ajax" href="#"><?= (!$blocked ? 'продолжить':'изменить причину') ?></a>&nbsp;&nbsp;<a onclick="return iChangeBlocked(2);" class="ajax desc" href="#">отмена</a>
                </div> 
            </div>
        </div> 
        
        <div class="warnblock hidden" id="i_publicate2" style=margin-bottom:10px;">
            <div class="warnblock-content">                                 
                <table class="admtbl tbledit">
                <tr class="row1">
                    <td width="115">Период публикации:</td>
                    <td>
                    <script type="text/javascript"> 
                    <?  
                        $week = (60*60 * 24 * 7);
                        $from = time();
                        $periods = bff::getPublicatePeriods( $from );
                         ?>                        
                    var bbsPublicate2Periods = <?= func::php2js( $periods['data'] );  ?>;
                    </script>
                    <select class="inputText2" tabindex="1" name="period" onchange="$('#publicated2-till').html( bbsPublicate2Periods[this.value] );">
                        <?= $periods['html'] ?> 
                    </select>
                    </td>
                </tr>       
                <tr class="row1"><td>Срок публикации:</td><td><?= ($status == BBS_STATUS_PUBLICATED_OUT ? 'с '.date('d.m.Y').' по ' : ' до ' ) ?><span id="publicated2-till"><?= date('d.m.Y', $from+$week ); ?></span></td></tr>
                <tr class="row1"><td style="margin-bottom:10px;" colspan="2">
                    <a onclick="return iItemPublicate2(1);" class="ajax" href="#">опубликовать</a>&nbsp;&nbsp;<a onclick="return iItemPublicate2(2);" class="ajax desc" href="#">отмена</a>
                </td></tr>
                </table>   
            </div> 
        </div>           
    </td>
</tr>
<tr>
    <td class="row1" colspan="2">
        <input type="submit" class="button submit" value="сохранить" />
       <?
       if($moderated == 0) { ?>
            <input class="success button" type="button" onclick="iItemApprove();" value="<?= ($status == BBS_STATUS_BLOCKED ? 'проверено, все впорядке' : 'проверено') ?>" />
       <? } else {
           if($status == BBS_STATUS_PUBLICATED_OUT) {
                ?><input class="submit button" type="button" onclick="iItemPublicate2(0);" value="опубликовать" /><?
           } else if($status == BBS_STATUS_PUBLICATED) {
                ?><input class="submit button" type="button" onclick="iItemUnpublicate();" value="снять с публикации" /><?
           }
       }
       if($status!=BBS_STATUS_BLOCKED) { ?> 
           <input class="delete button" onclick="iChangeBlocked(1,0,0); return false;" id="i_block_lnk" type="button" value="отклонить" />
       <? } ?>
        <input type="button" class="button cancel" value="отмена" onclick="history.back();" />
        <div class="progress" style="margin: 8px 8px 0; display: none;" id="edit-form-progress"></div>
    </td>
</tr>
</table>
<?
if($this->items_images) { ?>
<br/><hr class="cut"/><br/> 

<table class="admtbl tbledit">
<tr class="row1">
    <th colspan="2" align="center">изображения</th> 
</tr>
<tr class="row1">
    <td colspan="2">

        <div style="margin-top: 5px;" id="edit-form-images">
            <? 
                if($imgcnt>0) {
                    $img_url = '/files/images/items/';
                    foreach($img as $i) 
                    { if($i) { ?>
                    <div class="left" style="margin-right: 5px;">
                        <a href="javascript:$.fancybox('<?= $img_url.$id.$i; ?>', {type:'image'});"><img src="<?= $img_url.$id.'t'.$i ?>" /></a><br />
                        <a href="#" class="but del but-text" rel="<?= $i ?>">Удалить</a>
                    </div>
                <?  } }
                }
            ?>
        </div>
        <div class="clear"></div>
        <br/>  
        <div><span id="edit-form-images-button"></span></div>
        <div class="left progress" style="margin-top: 4px; display:none;" id="edit-form-images-progress"></div>
        <div class="clear"></div>

    </td>
</tr>
</table>
<? } ?>
</form>