<? $v = &$aData; ?>
<div class="comment2" id="comment_id_<?= $v['id']; ?>">
    <a name="comment<?= $v['id']; ?>" ></a>                            
    <div class="name"><? if($v['user_id']>0){ echo '<a href="/items/user?id='.$v['user_id'].'">'.$v['name'].'</a>'; } else { echo $v['name']; } ?> <span class="date"><?= tpl::date_format3($v['created']) ?></span></div>
    <div class="comment<?= ($v['user_id'] == $v['owner_id'] && $v['owner_id']>0 ? ' whiteBg' : '')  ?>">
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
    <? if(!$v['deleted']): ?><div class="links"><a href="javascript:void(0);" onclick="bbsItemView.replyComment(<?= $v['id']; ?>);" class="greyGBord">Ответить</a><? if($v['my'] || $v['user_id']>0 && $v['user_id'] == $v['cur_user_id']): ?> / <a href="javascript:void(0);" onclick="bbsItemView.delComment(<?= $v['id']; ?>, <?= $v['item_id']; ?>, $(this));" class="greyRBord">Удалить</a><? endif; ?></div><? endif; ?>

    <div class="padForm" id="reply_<?= $v['id']; ?>" style="display: none;"></div>    
    <div class="comment-children" id="comment-children-<?= $v['id']; ?>"></div>
</div>