<?php 

$nUserID = $this->security->getUserID();
$favs = $this->getFavorites();

$bShowPrices = false;
foreach($aData['items'] as $i) {
    if($i['cat_prices']) {
        $bShowPrices = true; break;
    }
}

foreach($aData['items'] as $i) 
{ 
    $my = ($i['user_id']!=0 && $i['user_id'] == $nUserID);
?>
    <div class="item blockOb svc<?= $i['svc'] ?><?= $my ? ' my':'' ?>" rel="<?= $i['id'] ?>">
        <div class="clear"></div>
        <div class="action"><? if(!$my): ?><a href="/items/makefav?id=<?= $i['id'] ?>" onclick="return app.fav(<?= $i['id'] ?>, $(this));" class="fav<?= (!empty( $favs['id']) && in_array($i['id'], $favs['id'])?' active':''); ?>"></a><? endif; ?></div>
        <div class="pic<?= ($i['imgcnt']<2?'2':''); ?>"><a href="/item/<?= $i['id'] ?>"><img src="<?= tpl::imgurl(array('folder'=>'items', 'file'=>(!empty($i['imgfav']) ? $i['id'].'t'.$i['imgfav'] : ''), 'static'=>1)); ?>" /></a></div>
        <div class="desc">
            <?php if($i['cat_type']): ?><b class="upper"><?= $i['cat_type_title']; ?>:</b> <?php endif; ?>
            <?php if($i['cat_subtype']): ?><b class="upper"><?= $i['cat_subtype_title']; ?>:</b> <?php endif; ?>
            <a href="/item/<?= $i['id'] ?>" class="desc-link">
                <?= tpl::truncate($i['title'], 330, '...', true); ?>
                <br />
                <?= tpl::truncate($i['descr'], 330, '...', true); ?>
            </a>
            <div class="address"><?= $i['cat1_title']; ?><? if($i['cat2_id']): ?> <img src="/img/arrowRightSmall.png" /> <?= $i['cat2_title']; ?><? endif; ?> <?= ($i['cat_regions'] && !empty($i['descr_regions'])?'/ '.$i['descr_regions']:''); ?></div>
        </div>
        <?php if($bShowPrices): ?><div class="price"><b class="f18 orange"><?= $i['price']; ?></b> <span class="f11Up orange">$</span><br/><?= ($i['price_torg'] ? 'торг' : '').($i['price_bart'] ? ($i['price_torg'] ? ', ': '').'бартер' : '' ); ?></div><? endif; ?>
        <div class="stat"><?= tpl::date_format3($i['publicated']) ?></div>
        <div class="clear h9"></div> 
        <? if($my){ ?><div class="actionsLink hidden">
            <? if(!$i['premium']): ?><a href="/items/svc/<?= $i['id'] ?>/<?= Services::typeUp ?>" onclick="return app.svc(<?= $i['id'] ?>, <?= Services::typeUp ?>);"><img src="/img/putUp.png" /></a><? endif; ?>
            <? if($i['svc']!=Services::typeMark && !$i['premium']): ?><a href="/items/svc/<?= $i['id'] ?>/<?= Services::typeMark ?>" onclick="return app.svc(<?= $i['id'] ?>, <?= Services::typeMark ?>);"><img src="/img/mark.png" /></a><? endif; ?>
            <? if(!$i['premium']): ?><a href="/items/svc/<?= $i['id'] ?>/<?= Services::typePremium ?>" onclick="return app.svc(<?= $i['id'] ?>, <?= Services::typePremium ?>);"><img src="/img/premium.png" /></a><? endif; ?>
            <? if(!$i['press']): ?><a href="/items/svc/<?= $i['id'] ?>/<?= Services::typePress ?>" onclick="return app.svc(<?= $i['id'] ?>, <?= Services::typePress ?>);" class="last"><img src="/img/public.png" /></a><? endif; ?>
          </div><? } ?>
    </div>
<? 
} 

echo $aData['pagenation'];


