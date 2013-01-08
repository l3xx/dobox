<? 

extract($aData);

$comm = !empty($_GET['comments']);
$userID = $this->security->getUserID();

?>
<script type="text/javascript">
$(function(){
    $(".greyLine").corner("8px");
});
</script>

<div class="greyLine">           
    <? 
    $cats_level = &$cats; $cats_numlevel = 1; $cats_parent = 0;
    foreach($cats_active as $cat_id) { 
        $cat_data = &$cats_level[$cat_id]; 
    ?>
    <div class="selectBlock">
        <span class="left">&nbsp;</span>
        <span class="right appdd-opener"><a href="#" class="appdd-link"><span class="appdd-title"><?= $cat_data['title']; ?></span> <img src="/img/dropdown.png" /></a></span>
        <div class="dropdown appdd-dropbox hidden">
            <ul>
                <? if($cats_numlevel>1) { ?> <li><a href="/search?c=<?= $cats_parent; ?>">Все разделы</a></li>
                <? } foreach($cats_level as $k=>$i) { ?>
                    <li><a href="/search?c=<?= $i['id'] ?>"<? if($i['id'] == $cat_id): ?> class="select" onclick="return false;"<? endif; ?>><?= $i['title'] ?></a></li>
                <? } $cats_level = &$cat_data['sub'];  ?>
            </ul>
        </div>
    </div>
    <? if(!empty($cats_level)): ?><span class="arrow"><img src="/img/arrowRight.png" /></span><? endif;
    
        $cats_numlevel++;
        $cats_parent = $cat_id;
    } 
    if(!empty($cat_type)) {
    ?>
    <span class="arrow"><img src="/img/arrowRight.png" alt=""/></span>
    <span class="left"><?= $cat_type_title; ?></span> <? } ?>
    <? if(!empty($cat_subtype)) { ?>
    <span class="arrow"><img src="/img/arrowRight.png" alt=""/></span>
    <span class="left"><?= $cat_subtype_title; ?></span> <? } ?>
    <span class="right"><? if($from_search): ?><a href="#" onclick="history.back(); return false;">К результатам поиска</a><? endif; ?></span>
    <div class="clear"></div>
    
    <div class="clear"></div>
</div>

<div class="tabs">
    <a href="/item/<?= $id ?>" onclick="$('#itemComments').hide(); $('#itemInfo').show(); $(this).addClass('active').next().removeClass('active'); return false;"<? if(!$comm): ?> class="active"<? endif; ?>><span class="left">&nbsp;</span><span>Объявление</span><span class="right">&nbsp;</span></a>
    <a href="/item/<?= $id ?>?comments=1" onclick="$('#itemInfo').hide(); $('#itemComments').show(); $('#itemComments img.captcha').triggerHandler('click');   $(this).addClass('active').prev().removeClass('active'); return false;"<? if($comm): ?> class="active"<? endif; ?>><span class="left">&nbsp;</span><span>Задать вопрос</span><span class="right">&nbsp;</span></a>
    <div class="clear"></div>
</div>

<div id="itemInfo"<?if($comm): ?> style="display:none;"<? endif; ?>>
    <div class="leftMenu">
        <div class="whiteBlock">
            <span class="caption">Характеристики:</span>
            <?= $dp; ?>
            <div class="padTop">
                Просмотры:<br/>
                <span>всего:</span> <?= $views_total; ?>&nbsp;&nbsp;<span>сегодня:</span> <?= intval($views_today); ?>
            </div>
            <div class="padTop">
                Публикуется:<br/>
                <span>с:</span> <?= tpl::date_format3($publicated, 'd.m.Y'); ?>&nbsp;&nbsp;<span>по:</span> <?= tpl::date_format3($publicated_to, 'd.m.Y'); ?>
            </div>
        </div>
        <div class="whiteBlock">
            <ul>
                <? if(!$premium): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typeUp ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typeUp ?>);" class="putUp">Поднять в списке</a></li><? endif; ?>
                <? if(!$premium && $svc!=Services::typeMark): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typeMark ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typeMark ?>);" class="mark">Выделить</a></li><? endif; ?>
                <? if(!$premium): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typePremium ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typePremium ?>);" class="premium">Премиум</a></li><? endif; ?>
                <? if(!$press): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typePress ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typePress ?>);" class="public">Опубликовать в прессу</a></li><? endif; ?>
                <? if($my) { ?>
                <? if ($status==BBS_STATUS_PUBLICATED_OUT): ?><li><a href="#" class="extend" onclick="return app.publicate2(<?= $id; ?>, false);">Продлить</a></li><? endif; ?>
                <li><a href="/items/edit?id=<?= $id ?>" class="edit">Редактировать</a></li>
                <li><a href="/items/del?id=<?= $id ?>" class="delete" onclick="if(!confirm('Удалить объявление?')) return false;">Удалить</a></li
                <? } else { 
                    if($user_id == 0 && $this->isEditPassGranted($id)) {
                    ?><li><a href="/items/edit?id=<?= $id ?>" class="edit">Редактировать</a></li> <? } else { ?>
                      <li><a href="#" class="edit" onclick="return bbsItemView.edit(<?= $id.','.($user_id==0?1:($userID==0?2:3)) ?>);">Редактировать</a></li>
                <? }
                } ?>
            </ul>
        </div>
    </div>
    
    <? if($user_id==0) { ?>
        <div class="popupCont" id="ipopup-enter-edit-pass" style="display:none;">
          <div class="popup">
            <div class="top"></div>
            <div class="center">
              <div class="close"><a href="#" title="" rel="close"><img src="/img/close.png" alt=""/></a></div>
              <h1 class="ipopup-title">Редактирование объявления</h1>
              <div class="ipopup-content">
                
                  <form action="/ajax/bbs?act=item-edit-pass">
                    <input type="hidden" name="id" value="<?= $id; ?>" />
                    <div class="error hidden"></div>
                    <div class="padTop">Укажите пароль для редактирования:</div>
                    <div class="padTop"><input type="password" name="pass" class="inputText edit-pass" value="" tabindex="1" /></div>
                    <div class="padTop">
                        <div class="button left">
                            <span class="left">&nbsp;</span>
                            <input type="submit" tabindex="2" value="продолжить"/> 
                        </div><div class="progress" style="margin:7px 0 0 12px; display:none;"></div>
                        <div class="clear"></div>
                    </div>
                  </form> 
              </div>
            </div>
            <div class="bottom"></div>
          </div>
        </div>
    <? } ?>

    <div class="popupCont" id="ipopup-item-claim" style="display:none;">
        <div class="popup">
            <div class="top"></div>
            <div class="center">
              <div class="close"><a href="#" title="" rel="close"><img src="/img/close.png" alt=""/></a></div>
              <h1 class="ipopup-title">Подать жалобу</h1>
              <div class="ipopup-content">
                  <form action="/ajax/bbs?act=item-claim">
                    <input type="hidden" name="id" value="<?= $id; ?>" />    
                    <div class="error hidden"></div>
                    <div class="padTop">Укажите причины, по которым вы считаете объявление некорректным <span class="req">*</span>:</div>
                    <div class="padTop">
                        <? 
                        $claimTypes = $this->getItemClaimReasons(); 
                        foreach($claimTypes as $key=>$claim) {
                            echo '<label><input type="checkbox" name="reasons[]" value="'.$key.'" /> '.$claim.'</label><br/>';
                        } ?>
                    </div>  
                    <div class="other_reason" style="display: none;">
                        <div class="padTop">Оставьте ваш комментарий</div>
                        <div class="padTop">
                            <textarea class="inputText2" name="comment" style="height:100px; width:245px;"></textarea>
                        </div>
                    </div>
                    <? if(!$userID) { ?>
                    <div class="padTop">Введите результат с картинки:</div>
                    <div class="padTop"><input type="text" name="captcha" class="inputText left" style="width:136px;" /> <img src="/captcha2.php?bg=E5E5E3" style="cursor: pointer;" onclick="$(this).attr('src', '/captcha2.php?bg=E5E5E3&r='+Math.random(1));" class="right captcha" /><div class="clear"></div></div>
                    <? } ?>
                    <div class="padTop">
                        <div class="button left">
                            <span class="left">&nbsp;</span>
                            <input type="submit" tabindex="2" value="отправить"/> 
                        </div><div class="progress" style="margin:7px 0 0 12px; display:none;"></div>
                        <div class="clear"></div>
                    </div>
                  </form> 
              </div>
            </div>
            <div class="bottom"></div>
        </div>
    </div>
    
    <div class="centerBlock w440">
        <div class="actionLinks">
            <ul>
                <? if(!$my){ 
                    $favs = $this->getFavorites();
                    $isFav = ($favs['total']>0 && in_array($id, $favs['id']));
                    ?>
                <? if(!$isFav): ?><li><a href="/items/makefav?id=<?= $id ?>" onclick="return app.fav(<?= $id ?>, $(this), 'view');" class="favorites">Добавить в избранное</a></li><? endif; ?>
                <li><a href="mailto:?subject=<?= BFF_EMAIL_FROM_NAME ?>&body=http://<?= SITEHOST; ?>/item/<?= $id ?>" class="send">Отправить другу</a></li>
                <li><a href="/item/<?= $id ?>?print=1" class="print">Распечатать</a></li>
                <li><a href="#" class="report" onclick="return bbsItemView.claim(<?= $id ?>);">Пожаловаться</a></li>
                <?  $emailWrite = (!empty($contacts_email) ? $contacts_email : ( !empty($contacts_email2) ? $contacts_email2 : false));
                    if(!empty($emailWrite)){ ?><li><a href="mailto:<?= tpl::escape($emailWrite); ?>?subject=<?= BFF_EMAIL_FROM_NAME ?>" class="write">Написать автору</a></li><? } 
                } else { ?>                                                   
                <li><a href="mailto:?subject=<?= BFF_EMAIL_FROM_NAME ?>&body=http://<?= SITEHOST; ?>/item/<?= $id ?>" class="send">Отправить другу</a></li>
                <li><a href="/item/<?= $id ?>?print=1" class="print">Распечатать</a></li>      
                <? } ?>
            </ul>
        </div>
        <span class="grey"><?= ($cat_regions && !empty($descr_regions) ? $descr_regions : ''); ?></span>
        <?php if($cat_prices): ?><p class="padTop"><b class="orange f24"><?= $price ?></b>&nbsp;<span class="orange f12Up">руб</span><br/><?= ($price_torg ? 'торг' : '').($price_bart ? ($price_torg ? ', ': '').'возможен бартер' : '' ); ?></p><? endif; ?>
        <p class="padTop">
            <span class="caption">Контакты:</span>
            <? if(!empty($contacts_phone)): ?><span class="grey">Тел:</span> <span class="blue"><?= $contacts_phone; ?></span><br/><? endif; ?>
            <? if(!empty($contacts_email)): ?><span class="grey">E-mail:</span> <?= $contacts_email; ?><br/><? endif; ?>
            <? if(!empty($contacts_skype)): ?><span class="grey">Skype:</span> <?= $contacts_skype; ?><br/><? endif; ?>
            <? if(!empty($contacts_site)):  ?><span class="grey">Сайт:</span> <a href="http://<?= $contacts_site ?>"><?= $contacts_site; ?></a><br/><? endif; ?>
            <span class="f14"><?= $contacts_name; ?></span><br/>
            <? if($user_id>0): ?><a href="/items/user?id=<?= $user_id; ?>" class="grey f11">Все объявления этого пользователя</a><? endif; ?>
        </p>
        <p class="padTop">
            <?php if($cat_type): ?><b class="grey f12Up"><?= $cat_type_title; ?>:</b> <?php endif; ?><?= nl2br($descr); ?><br/>
            <?php if($cat_subtype): ?><b class="grey f12Up"><?= $cat_subtype_title; ?></b> <?php endif; ?><?= nl2br($descr); ?><br/>
        </p> <?
        
        if($imgcnt) 
        {
            $imgURL = bff::buildUrl('items', 'images');
            $img = explode(',', $img);
            if(!empty($img)) {
            $imgfav = (!empty($imgfav) ? $imgfav : current($img)); ?>
            <div class="picCont">
            <span class="left"><img src="<?= $imgURL.($id.$imgfav); ?>" id="itemMainImg" /></span>
                <div class="smallPics"><?
                foreach($img as $i) {
                    echo '<span class="right"><a href="'.$imgURL.($id.$i).'" target="_blank" onclick="$(\'#itemMainImg\').attr(\'src\',$(this).attr(\'href\')); return false;"><img src="'.$imgURL.($id.'s'.$i).'" /></a></span>';
                }
              ?></div> 
                <div class="clear"></div>
            </div><?
            }
        } 
        
        if(!empty($video) && stripos($video, 'watch?v=')!==FALSE) {
            $video = str_replace('watch?v=', 'v/', $video);
            ?><br/>
            <object height="250" width="350">
                <param value="<?= $video; ?>" name="movie" />
                <param value="true" name="allowFullScreen"><param value="always" name="allowscriptaccess" />
                <embed height="250" width="350" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash" src="<?= $video; ?>" />
            </object>
            <?
        }?> 
    </div>
</div>

<div id="itemComments"<?if(!$comm): ?> style="display:none;"<? endif; ?>>
    <div class="padForm" id="reply_0">
        <form action="/item/<?= $id; ?>" id="itemCommentForm" method="post" onsubmit="return bbsItemView.submitComment(this);">
            <input type="hidden" name="id" value="<?= $id; ?>" />
            <input type="hidden" name="reply" value="0" id="itemCommentReply" />
            <div class="error" id="itemCommentError" style="display:none;"></div>
            <div class="left ">
                <div class="padTop">Текст сообщения:</div>
                <div class="padTop"><textarea class="inputText" name="message" style="width:391px; height:76px; resize:none;" tabindex="1"></textarea></div>
                <div class="padTop2">
                    <div class="button left" style="margin-right:5px;">
                        <span class="left">&nbsp;</span>
                        <input type="submit" value="ОСТАВИТЬ СООБЩЕНИЕ" style="width:162px;" tabindex="4" />
                    </div>
                    &nbsp;
                    <div class="button left" id="itemCommentCancelReply" style="display: none;">
                        <span class="left">&nbsp;</span>
                        <input type="button" value="ОТМЕНА" onclick="bbsItemView.cancelReply();" style="width:102px;" tabindex="5" />
                    </div>                    
                    <div class="left progress" id="itemCommentProgress" style="margin: 8px 15px 0; display: none;"></div>
                    <div class="clear"></div>
                </div>
            </div>
            <? if(!$userID){ ?>
            <div class="right" style="width:235px;">
                <div class="padTop">Введите ваше имя:</div>
                <div class="padTop"><input type="text" name="name" maxlength="50" class="inputText" style="width:224px;" tabindex="2" /></div>
                <div class="padTop">Введите результат с картинки:</div>
                <div class="padTop"><input type="text" name="captcha" class="inputText left" style="width:122px;" tabindex="3" /> <img src="/captcha2.php?bg=F6F7F8" style="cursor: pointer;" onclick="$(this).attr('src', '/captcha2.php?bg=F6F7F8&r='+Math.random(1));" class="right captcha"/></div>
            </div><? } ?>
        <div class="clear"></div>
        </form>
    </div>
    <div class="commentsBlock">

            <? 
            $nesting = -1; $j = 1;
            foreach($comments['aComments'] as $v) {
                $cmtlevel = $v['level'];
                if($nesting < $cmtlevel) { }
                else if($nesting > $cmtlevel) {
                    for($k=0;$k<($nesting-$cmtlevel+1);$k++) echo '</div></div>';
                } else if ($j>1) {
                    echo '</div></div>';
                } ?>
                <div class="comment2" id="comment_id_<?= $v['id']; ?>">
                        <a name="comment<?= $v['id']; ?>" ></a>                            
                        <div class="name"><? if($v['user_id']>0){ echo '<a href="/items/user?id='.$v['user_id'].'">'.$v['name'].'</a>'; } else { echo $v['name']; } ?> <span class="date"><?= tpl::date_format3($v['created']) ?></span></div>
                        <div class="comment<?= ( $v['user_id'] == $user_id ? ' whiteBg' : '')  ?>">
                            <div class="top"></div>
                            <div class="msg"><? if($v['deleted']>0) { 
                                switch($v['deleted']) {
                                    case 1: echo '<span class="deleted">Комментарий удален владельцем объявления</span>'; break;
                                    case 2: echo '<span class="deleted">Комментарий удален модератором</span>'; break;
                                    case 3: echo '<span class="deleted">Комментарий удален автором комментария</span>'; break;
                                }
                            } elseif($v['user_blocked']) {
                                echo '<span class="deleted">Комментарий от заблокированного пользователя</span>';
                            } else { echo nl2br($v['comment']); } ?></div>
                        </div>
                        <? if(!$v['deleted']): ?><div class="links"><a href="javascript:void(0);" onclick="bbsItemView.replyComment(<?= $v['id']; ?>);" class="greyGBord">Ответить</a><? if($my || $v['user_id']>0 && $v['user_id'] == $userID): ?> / <a href="javascript:void(0);" onclick="bbsItemView.delComment(<?= $v['id']; ?>, <?= $v['item_id']; ?>, $(this));" class="greyRBord">Удалить</a><? endif; ?></div><? endif; ?>
     
                        <div class="padForm" id="reply_<?= $v['id']; ?>" style="display: none;"></div>    

                        <div class="comment-children" id="comment-children-<?= $v['id']; ?>">  
                        <? $nesting = $cmtlevel;  
                        if(sizeof($comments['aComments']) == $j++) {
                            for($k=0;$k<($nesting+1);$k++) echo '</div></div>';
                        }
            }
            if(empty( $comments['aComments'] )) { ?>
                 <div id="comment-no" style="height:30px; padding-top:15px; text-align:center;">
                    К данному объявлению не оставлено ни одного комментария.
                 </div>            
            <? } ?>

            <span id="comment-children-0"></span> 

    </div>
</div>


<script type="text/javascript">
var itemComments = null;
var bbsItemView = (function() {
    var $commBlock, $commList, commError = '#itemCommentError', $commProgress, commProcess = false;
    var currentReply = 0,
        iCommentIdLastView = null;  
        
    function showError($err, msg, success)
    {
        if(msg) {
            if($.isArray(msg)) {
                msg = msg.join('<br/>');
            }
            if(success) {
                $err.removeClass('error').addClass('success');
                setTimeout(function(){
                    $err.hide();
                }, 3000)
            }                         
            $err.html(msg).show();
        } else {
            $err.hide().html('').removeClass('success').addClass('error');
        }
    }

    function toggleCommentForm (idComment, show) 
    {
        if (!$('#reply_'+currentReply, $commBlock) || !$('#reply_'+idComment, $commBlock)) {
            return;
        } 
        var divCurrentForm = $('#reply_'+currentReply, $commBlock);
        var divNextForm    = $('#reply_'+idComment, $commBlock);
        
        if (currentReply == idComment) {
            if(show) divCurrentForm.show();
            else divCurrentForm.toggle();
            return;
        }
                   
        divCurrentForm.hide();
        divNextForm.html(divCurrentForm.html());
        divCurrentForm.html('');        
        divNextForm.css('display','block').show();
        
        $('#itemCommentReply', $commBlock).attr('value', idComment);
        currentReply = idComment;
        var $cancel = $('#itemCommentCancelReply', $commBlock);
        if(currentReply == 0) $cancel.hide(); else $cancel.show();
    }
    
    function hideCommentForm(idComment) 
    {
        var comm = $('#reply_'+idComment, $commBlock);
        if(comm.length) comm.hide();
    }
    
    function doCancelReply()
    {
        toggleCommentForm(currentReply, false);
        toggleCommentForm(0); 
    }
    
    var epInited = false;
    function enterEditPassInit(id)
    {
        if(epInited) return;
        epInited = true;
        
        var $block = $('#ipopup-enter-edit-pass'), $form, $error, $progress, process = false;
        $progress = $('div.progress', $block);
        $error = $('div.error', $block);
        ($form = $('form:first', $block)).submit(function(){
            if(process) return false; 
            if(this.pass.value == '') {
                this.pass.focus(); return false;
            }
            process = true;
            bff.ajax( $form.attr('action'), $form.serialize(),
            function(data, errors) {
                if(data && data.result) {
                    document.location = '/items/edit?id='+id;
                } else {
                    showError($error, errors);
                }
                process = false;
            }, $progress );
            return false;
        });
    }
    
    var claimInited = false;
    function claimInit(id)
    {
        if(claimInited) return;
        claimInited = true;
        
        var $block = $('#ipopup-item-claim'), $form, $error, $progress, process = false, isOther = false;
        $progress = $('div.progress', $block);
        $error = $('div.error', $block);
        $('input[value="32"]', $block).click(function(){
            isOther = $(this).is(':checked');
            var txt = $('div.other_reason', $block);
            txt.slideToggle(300, function(){ if(isOther) $('textarea', this).focus(); });
        });
        
        ($form = $('form:first', $block)).submit(function(){
            if(process) return false; 
            if( $('input:checked', this).length==0 ) {
                showError($error, 'Укажите причину'); return false;
            }
            if( isOther && $.trim(this.comment.value) == '' ) {
                this.comment.focus(); return false;
            }
            
            if(!app.m) {
                if($.trim(this.captcha.value) == '') {
                    this.captcha.focus(); return false;
                }
            }
            process = true;
            bff.ajax( $form.attr('action'), $form.serialize(),
            function(data, errors) { var form = $form.get(0);
                if(data && data.result) {
                    showError($error, 'Ваша жалоба принята', true);
                    setTimeout(function() {
                        bff.popup('#ipopup-item-claim', '', false, {busylayer:true, onHide: function(){
                            form.reset();
                            $('div.other_reason', $form).hide();
                            $error.removeClass('success').addClass('error');
                        }});
                    }, 1500);
                } else {
                    if(errors && errors.length) {
                        showError($error, errors, false);
                        if(data.captcha_wrong) {
                            form.captcha.value = '';
                            form.captcha.focus();
                            $(form.captcha).next().triggerHandler('click'); 
                        }
                    }
                }
                process = false;
            }, $progress );
            return false;
        });
    }
    
    return {
        init: function()
        {
            $commBlock = $('#itemComments');
            $commProgress = $('#itemCommentProgress', $commBlock); 
            $commList = $('div.commentsBlock', $commBlock);
        },
        edit: function(id,t)
        {
            switch(t) {
                case 1: { enterEditPassInit(id); 
                    bff.popup('#ipopup-enter-edit-pass', '', true, {busylayer:true, onShow: function(box){ $('.edit-pass', box).focus(); }}); 
                } break;
                case 2: { appUserEnter.show(false); } break; //auth
                case 3: { bff.popupMsg('Вы не являетесь владельцем данного объявления', {title: 'Редактирование объявления'}); } break; // forbidden
            } return false;
        },
        submitComment: function(form)
        {
            if(commProcess) return false;
            var val = $.trim(form.message.value);
            if(val == '') { form.message.focus(); return false; }
            if(!app.m) {
                var val = $.trim(form.name.value);
                if(val == '') { form.name.focus(); return false; }
                var val = $.trim(form.captcha.value);
                if(val == '') { form.captcha.focus(); return false; }
            }
            
            commProcess = true;
            bff.ajax('/items/view?act=comment', $(form).serialize(), function(data, errors){
                if(data && data.res) {   
                    form.reset();
                    if(currentReply == 0) {
                        showError($(commError), 'Сообщение успешно отправлено', true);
                        $commList.append(data.comment);
                        $(form.captcha).next().triggerHandler('click');
                        $('#comment-no', $commList).remove();
                    } else {
                        $('#comment-children-'+currentReply, $commList).append( data.comment );
                        $(form.captcha).next().triggerHandler('click');
                        $('#comment-no', $commList).remove();
                        doCancelReply();
                    }                    
                } else {               
                    if(errors && errors.length) {
                        showError($(commError), errors);
                        if(data.captcha_wrong) {
                            form.captcha.value = '';
                            form.captcha.focus();
                            $(form.captcha).next().triggerHandler('click'); 
                        }
                    }
                }
                commProcess = false;
            });
            return false;
        },
        replyComment: function(commentID)
        {
            toggleCommentForm(commentID);
        },
        cancelReply: function()
        {
            doCancelReply();
        },
        delComment: function(commentID, itemID, $link)
        {
            if(commProcess || !confirm('Удалить комментарий?')) return;
            commProcess = true;
            bff.ajax('/items/view?act=comment_del', {id: itemID, comment_id: commentID}, function(data, errors){
                if(data && data.res) {   
                    $('#comment_id_'+commentID+' .msg:first', $commList).html('<span class="deleted">Комментарий удален '+(data.by == 1?'владельцем объявления':'автором комментария')+'</span>')
                    $link.parent().remove();
                } else {               
                    if(errors && errors.length) {
                        showError($(commError), errors);
                    }
                }
                commProcess = false;
            });
        },
        claim: function(id)
        {
            claimInit(id);
            $('#ipopup-item-claim [name="captcha"]').next().triggerHandler('click');
            bff.popup('#ipopup-item-claim', '', true, {busylayer:true, onShow: function(box){ }});
            return false;
        }
    }
}());
$(function(){ 
    bbsItemView.init(); 
});
</script>