<?php 
    $fStatus = $aData['f']['status'];
if($aData['show_no_items_block'] && empty($aData['items'])) { ?>

    <div> нет объявлений </div> 
    
<? }

foreach($aData['items'] as $i) 
{ 
   $id = $i['id'];   
   if ($fStatus==BBS_STATUS_BLOCKED){ 
   ?>
<div class="item blockOb svc<?= $i['svc'] ?>" rel="<?= $id; ?>" catid="<?= $i['cat1_id'] ?>">
    <div>
        <div class="clear"></div>
        <div class="action">
            <input type="checkbox" class="check" name="items[]" value="<?= $id; ?>" />
        </div>
        <div class="pic<?= ($i['imgcnt']<2?'2':''); ?>"><a href="/item/<?= $id; ?>"><img src="<?= tpl::imgurl(array('folder'=>'items', 'file'=>(!empty($i['imgfav']) ? $id.'t'.$i['imgfav'] : ''), 'static'=>1)); ?>" /></a></div>
        <div class="desc">
            <b class="upper">Причина отклонения:</b><br/>
            <div class="address"><?= $i['blocked_reason']; ?></div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="actionsUp">
        <ul>
          <? if($i['moderated'] == 0) { ?>
          <li><b>Ожидает проверки модератора</b></li>
          <? } else { ?>
          <li><a href="/items/edit?id=<?= $id ?>" class="edit">Исправить</a></li>
          <? } ?>
          <li><a href="/items/del?id=<?= $id ?>" onclick="return myList.del(<?= $id ?>);" class="delete">Удалить</a></li>
        </ul>
        <div class="clear"></div>
    </div>
</div>
   <? } else { ?>
<div class="item blockOb svc<?= $i['svc'] ?>" rel="<?= $id; ?>" catid="<?= $i['cat1_id'] ?>">
    <div>
        <div class="clear"></div>
        <div class="action">
            <input type="checkbox" class="check svc<?= $i['svc'] ?>" name="items[]" value="<?= $id; ?>" />
        </div>
        <div class="pic<?= ($i['imgcnt']<2?'2':''); ?>"><a href="/item/<?= $id; ?>"><img src="<?= tpl::imgurl(array('folder'=>'items', 'file'=>(!empty($i['imgfav']) ? $id.'t'.$i['imgfav'] : ''), 'static'=>1)); ?>" /></a></div>
        <div class="desc">
            <?php if($i['cat_type']): ?><b class="upper"><?= $i['cat_type_title']; ?>:</b> <?php endif; ?>
            <?php if($i['cat_subtype']): ?><b class="upper"><?= $i['cat_subtype_title']; ?>:</b> <?php endif; ?>
            <a href="/item/<?= $id; ?>" class="desc-link"><?= tpl::truncate($i['descr'], 330, '...', true); ?></a>
            <div class="address"><?= $i['cat1_title']; ?><? if($i['cat2_id']): ?> <img src="/img/arrowRightSmall.png" /> <?= $i['cat2_title']; ?><? endif; ?> <?= ($i['cat_regions'] && !empty($i['descr_regions'])?'/ '.$i['descr_regions']:''); ?></div>
        </div>
        <?php if($i['cat_prices']): ?><div class="price"><b class="f18 orange"><?= $i['price']; ?></b> <span class="f11Up orange">руб</span><br/><?= ($i['price_torg'] ? 'торг' : '').($i['price_bart'] ? ($i['price_torg'] ? ', ': '').'бартер' : '' ); ?></div><? endif; ?>
        <div class="stat">
            <? if ($fStatus==BBS_STATUS_PUBLICATED): ?><span><?= tpl::date_format3($i['publicated'], 'd.m.Y'); ?></span> по <span><?= tpl::date_format3($i['publicated_to'], 'd.m.Y'); ?></span><? endif; ?>
            Просмотры: <span>всего <b><?= $i['views_total']; ?></b></span> / <span>сегодня <b><?= intval($i['views']); ?></b></span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="actionsUp">
        <ul>
          <? if ($fStatus==BBS_STATUS_PUBLICATED)
          { 
             if(!$i['premium']): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typeUp ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typeUp ?>);" class="putUp">Поднять</a></li><? endif; ?>
          <? if($i['svc']!=Services::typeMark && !$i['premium']): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typeMark ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typeMark ?>);" class="mark">Выделить</a></li><? endif; ?>
          <? if(!$i['premium']): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typePremium ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typePremium ?>);" class="premium">Премиум</a></li><? endif; ?>
          <? if(!$i['press']): ?><li><a href="/items/svc/<?= $id ?>/<?= Services::typePress ?>" onclick="return app.svc(<?= $id ?>, <?= Services::typePress ?>);" class="public">В прессу</a></li><? endif; 
          } ?>
          <? if ($i['status']==BBS_STATUS_PUBLICATED_OUT): ?><li><a href="#" class="extend" onclick="return app.publicate2(<?= $id; ?>);">Продлить</a></li><? endif; ?>
          <li><a href="/items/edit?id=<?= $id ?>" class="edit">Редактировать</a></li>
          <li><a href="/items/del?id=<?= $id ?>" onclick="return myList.del(<?= $id ?>);" class="delete">Удалить</a></li>
        </ul>
        <div class="clear"></div>
    </div>
</div>
<? 
    }
}