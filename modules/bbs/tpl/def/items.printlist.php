<? 
 extract($aData);
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= config::get('title'); ?></title>
<meta name="keywords" content="<?= config::get('mkeywords'); ?>" />
<meta name="description" content="<?= config::get('mdescription'); ?>"  />
<link rel="stylesheet" type="text/css" href="/css/print.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/css/print.media.css" media="print" />
</head>
<body>
<div id="wrap">
  <div class="container">
    <div class="header">
      <span class="left"><a href="/"><img src="/img/logo2.png" /></a></span>
      <span class="right about"><?= ( $from == 'fav' ? 'Избранное':'' ); ?></span>
      <div class="clear"></div>
    </div>
    <div class="content">
        <? foreach($items as $i) { ?>    
        <div class="item blockOb">
            <div class="clear"></div>
            <div class="pic"><a href="/item/<?= $i['id']; ?>"><img src="<?= tpl::imgurl(array('folder'=>'items', 'file'=>(!empty($i['imgfav']) ? $i['id'].'t'.$i['imgfav'] : ''), 'static'=>1)); ?>" /></a></div>
            <div class="desc">
                <?php if($i['cat_type']): ?><b class="upper"><?= $i['cat_type_title']; ?>:</b> <?php endif; ?>
                <?php if($i['cat_subtype']): ?><b class="upper"><?= $i['cat_subtype_title']; ?></b> <?php endif; ?>
                <?= tpl::truncate($i['descr'], 330, '...', true); ?>
                <div class="address"><?= $i['cat1_title']; ?><? if($i['cat2_id']): ?> <img src="/img/arrowRightSmall.png" /> <?= $i['cat2_title']; ?><? endif; ?> <?= ($i['cat_regions'] && !empty($i['descr_regions'])?'/ '.$i['descr_regions']:''); ?></div>
                <div class="contact"><?= (!empty($i['contacts_phone']) ? 'Тел: '.$i['contacts_phone'].'&nbsp;&nbsp;':'') ?><?= (!empty($i['contacts_email']) ? 'E-mail: '.$i['contacts_email'].'&nbsp;&nbsp;':'') ?><?= (!empty($i['contacts_skype']) ? 'Skype: '.$i['contacts_skype'].'&nbsp;&nbsp;':'') ?><?= $i['contacts_name'] ?></div>
            </div>
            <div class="price"><span class="date"><?= tpl::date_format3($i['publicated'], 'd.m.Y') ?></span><?php if($i['cat_prices']): ?><b class="f18 orange"><?= $i['price']; ?></b> <span class="f11Up orange">руб</span><br/><?= ($i['price_torg'] ? 'торг' : '').($i['price_bart'] ? ($i['price_torg'] ? ', ': '').'бартер' : '' ); ?><? endif; ?></div>
            <div class="clear"></div>
        </div>
        <? } ?>
        <div class="padTop footer">
            <div class="button left">
                <span class="left">&nbsp;</span>
                <input type="button" value="РАСПЕЧАТАТЬ" onclick="window.print();" style="width:105px;" />
            </div>
            <span class="left"><a href="javascript:void(0);" onclick="window.close();" class="greyBord">Отменить печать</a></span>
            <div class="clear"></div>
        </div>        
    </div>
  </div>
</div>
</body>
</html>