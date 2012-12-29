<?
    extract($aData);
    $blocked = ($status == BBS_STATUS_BLOCKED);
?>
<div id="popupIteminfo" class="ipopup">
    <div class="ipopup-wrapper">
        <div class="ipopup-title">Информация об объявлении №<?= $id ?></div>
        <div class="ipopup-content" style="width:643px;">

                    <script type="text/javascript"> 
                    //<![CDATA[  
                    var iListType = '<?= $iltype; ?>';
                    function iGetIListRow() { return $('#i_list_<?= $id; ?>'); }
                    function iChangeBlocked(step, block)
                    {
                        var form = document.forms.itemBlockForm;
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
                                        $('a.itemlink', iGetIListRow()).removeClass('unblock').addClass('block');
                                        iChangeBlocked(2);
                                    }
                                    $.fancybox.close();
                                }
                            }, '#progress-i-block');
                        }
                        return false;
                    }
                    function iItemApprove()
                    {
                        bff.ajax('index.php?s=bbs&ev=ajax&act=item-approve', $(document.forms.itemBlockForm).serializeArray(), function(data){
                            if(data) {                               
                                if(iListType == 'mod') {
                                    var row = iGetIListRow();
                                    row.hide();
                                }
                                $.fancybox.close();
                            }
                        }, '#progress-i-block');
                    }                          
                    function iItemUnpublicate()
                    {
                        if(bff.confirm('sure'))
                        bff.ajax('index.php?s=bbs&ev=ajax&act=item-unpublicate', $(document.forms.itemBlockForm).serializeArray(), function(data){
                            if(data) {                               
                                $('a.itemlink', iGetIListRow()).removeClass('unblock').addClass('block');
                                $.fancybox.close();
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
                            bff.ajax('index.php?s=bbs&ev=ajax&act=item-publicate2', $(document.forms.itemPublicate2Form).serializeArray(), function(data){
                                if(data) { 
                                   $('a.itemlink', iGetIListRow()).removeClass('block').addClass('unblock');
                                   $.fancybox.close();
                                }
                            }, '#progress-i-block');
                        }
                        else if(step==2) //отмена
                        {
                            $block.hide();
                        }
                        return false;
                    }                
                    //]]> 
                    </script>   
                    
                    <table class="admtbl tbledit">          
                        <tr class="row1">
                            <td class="field-title" align="right" width="150">Пользователь:</td>
                            <td align="left">  
                                <?= ($user_id ? $user_name.' ['.$user_email.']' : 'Аноним' ); ?>
                                <span class="right" style="opacity:0.7;">
                                    <span id="progress-i-block" style="margin-right:5px; display:none;" class="progress"></span>
                                    <a href="#" onclick="if(confirm('Продолжить?')) return iChangeBlocked(3,0); return false;" id="i_unblock_lnk" class="ajax clr-success hidden">разблокировать</a>
                                    <a href="#" onclick="iChangeBlocked(1,0,0); return false;" id="i_block_lnk" class="ajax clr-error hidden">заблокировать</a>
                                </span>
                            </td>
                        </tr>
                        <tr class="row1">
                            <td class="field-title" align="right">Категория:</td>
                            <td align="left"><?= $cats_path.($cat_type>0 ? '&nbsp;&nbsp;<img src="/img/arrowRightSmall.png" />&nbsp;&nbsp;'.$cat_type_title : ''); ?></td>
                        </tr>
                        <tr class="row1">
                            <td class="field-title" align="right">Статус:</td>
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
                            <td class="field-title" align="right">Период публикации:</td>
                            <td align="left"><b><?= tpl::date_format3($publicated, 'd.m.Y').' - '.tpl::date_format3($publicated_to, 'd.m.Y'); ?></b></td>
                        </tr>
                        <? } ?>
                        <tr class="row1">
                            <td class="field-title" align="right"><a href="index.php?s=bills&ev=listing&item=<?= $id; ?>">Услуги</a>:</td>
                            <td align="left"><? 
                                switch($svc) {
                                    case Services::typeUp:      { echo '<img src="/img/putUp.png" />&nbsp;&nbsp;поднятое '.tpl::date_format3($publicated_order, 'd.m.Y'); } break;
                                    case Services::typeMark:    { echo '<img src="/img/mark.png" />&nbsp;&nbsp;выделенное до '.tpl::date_format3($marked_to, 'd.m.Y'); } break;
                                    case Services::typePremium: { echo '<img src="/img/premium.png" />&nbsp;&nbsp;премиум до '.tpl::date_format3($premium_to, 'd.m.Y'); } break;
                                    default: { echo 'нет'; }
                                }
                            ?></td>
                        </tr>                        
                        <tr class="row1">
                            <td class="field-title" align="right">Текст объявления:</td>
                            <td align="left"><?= $descr; ?></td>
                        </tr>
                        <tr class="row1">
                            <td></td>
                            <td>
                                <form action="" name="itemBlockForm" method="post">
                                    <input type="hidden"  name="id" value="<?= $id; ?>" />       
                                    <input type="hidden"  name="blocked" value="<?= ($blocked?1:0) ?>" />
                                    
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
                                </form>
                                <div class="warnblock hidden" id="i_publicate2" style=margin-bottom:10px;">
                                    <div class="warnblock-content">
                                        <form action="" name="itemPublicate2Form" method="post">
                                            <input type="hidden"  name="id" value="<?= $id; ?>" />
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
                                                var bbsPublicate2Periods = <?= func::php2js( $periods['data'] ); ?>;
                                                </script>
                                                <select class="inputText2" tabindex="1" name="period" onchange="$('#publicated2-till').html( bbsPublicate2Periods[this.value] );">
                                                    <?= $periods['html'] ?>
                                                </select>
                                                </td>
                                            </tr>       
                                            <tr class="row1"><td>Срок публикации:</td><td><?= ($status == BBS_STATUS_PUBLICATED_OUT ? 'c '.date('d.m.Y').' по ' : ' до ' ) ?><span id="publicated2-till"><?= date('d.m.Y', $from+$week ); ?></span></td></tr>
                                            <tr class="row1"><td style="margin-bottom:10px;" colspan="2">
                                                <a onclick="return iItemPublicate2(1);" class="ajax" href="#">опубликовать</a>&nbsp;&nbsp;<a onclick="return iItemPublicate2(2);" class="ajax desc" href="#">отмена</a>
                                            </td></tr>
                                            </table>
                                        </form>
                                    </div> 
                                </div>                               
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
                                   <input class="cancel button" onclick="$.fancybox.close(); return false;" type="button" value="отмена" />
                            </td>
                        </tr>
                    </table>    
                
                <div class="ipopup-content-bottom">
                    <ul class="right">
                        <? if($blocked_num>1){ ?><li class="clr-error">отклонений: <?= $blocked_num; ?></li><? } ?>
                        <? if($claims){ ?><li><a href="index.php?s=bbs&ev=items_complaints&item=<?= $id ?>" class="clr-error">есть жалобы</a></li><? } ?>
                        <li><span class="post-date" title="дата создания"><?= tpl::date_format2($created); ?></span></li>
                        <li><a href="index.php?s=bbs&ev=items_edit&rec=<?= $id ?>" class="edit_s"> редактировать <span style="display:inline;" class="desc">#<?= $id ?></span></a></li>
                    </ul> 
                </div>
        
            </div>
        </div>
    </div>
</div>
