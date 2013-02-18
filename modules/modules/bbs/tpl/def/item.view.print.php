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
      <span class="right">
      <? for($i=0, $n = sizeof($cats); $i<$n; $i++) { ?>
         <?= $cats[$i]['title']; ?>&nbsp;<? if($i<$n-1): ?><img src="/img/arrowRight.png" /><? endif; ?>
      <? } if($cat_type){ ?>
        <img src="/img/arrowRight.png" /> <?= $cat_type_title; ?>
      <? } if($cat_subtype){ ?>
        <img src="/img/arrowRight.png" /> <?= $cat_subtype_title; ?>
      <? }?>
      </span>
      <div class="clear"></div>
    </div>
    <div class="content">
      <div class="leftMenu">
        <div class="whiteBlock">
          <span class="caption">Характеристики:</span>
          <?= $dp; ?>
          <p>
            Просмотры:<br/>
            всего: <?= $views_total; ?>&nbsp;&nbsp;сегодня: <?= intval($views_today); ?>
          </p>
          <p>
            Публикуется:<br/>
            <span>с:</span> <?= tpl::date_format3($publicated, 'd.m.Y'); ?>&nbsp;&nbsp;<span>по:</span> <?= tpl::date_format3($publicated_to, 'd.m.Y'); ?>
          </p>
        </div>
      </div>
      <div class="centerBlock">
        <?= ($cat_regions && !empty($descr_regions) ? $descr_regions : ''); ?>
        <?php if($cat_prices): ?><p><b class="orange f24"><?= $price; ?></b><span class="orange f12Up">$</span><br/><?= ($price_torg ? 'торг' : '').($price_bart ? ($price_torg ? ', ': '').'возможен бартер' : '' ); ?></p><? endif; ?>
        <p>
          <span class="caption">Контакты:</span>
          <? if(!empty($contacts_phone)): ?><?= $contacts_phone; ?><br/><? endif; ?>
          <? if(!empty($contacts_email)): ?><?= $contacts_email; ?><br/><? endif; ?>
          <? if(!empty($contacts_skype)): ?>skype: <?= $contacts_skype; ?><br/><? endif; ?>
          <?= $contacts_name; ?>
        </p>
        <p class="margTop"><?= $descr; ?></p>
        <?
        if($imgcnt) 
        {
            $imgURL = bff::buildUrl('items', 'images');
            $img = explode(',', $img);
            if(!empty($img)) {
                foreach($img as $i) {
                    echo '<div class="picCont"><img src="'.$imgURL.($id.$i).'" /></div>';
                }
            }
        } ?>        
          <div class="padTop footer">
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="button" value="РАСПЕЧАТАТЬ" onclick="window.print();" style="width:105px;" />
                </div>
                <span class="left"><a href="javascript:void(0);" onclick="history.back();" class="greyBord">Отменить печать</a></span>
                <div class="clear"></div>
          </div> 
      </div>
      <div class="clear"></div>
    </div>
  </div>
</div>
</body>
</html>