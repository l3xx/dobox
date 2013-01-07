<table width="100%" class="productTable">
<?php 

$nUserID = $this->security->getUserID(); 
$favs = $this->getFavorites();
$addDynprops = !empty($aData['dynprops']);
$dp = &$aData['dynprops'];

$bShowPrices = false;
foreach($aData['items'] as $i) {
    if($i['cat_prices']) {
        $bShowPrices = true; break;
    }
}

if(!empty($aData['items'])) {
    
?>
<tr>
    <td></td>
    <td align="center">Фото</td>
    <?
        if($addDynprops) 
        {
            $sHTML = '';
            foreach($dp as $d) {
                $sHTML .= '<td align="center">'.$d['title'].'</td>';
            }
            echo $sHTML;
        }
    ?>    
    <td align="center">Описание</td>
    <? if($bShowPrices){ ?><td align="center">Цена</td><? } ?>
</tr>
<?
}

foreach($aData['items'] as $i) 
{ 
    $my = ($i['user_id']!=0 && $i['user_id'] == $nUserID);
?>
<tr class="item svc<?= $i['svc'] ?><?= $my ? ' my':'' ?>" rel="<?= $i['id'] ?>">
    <td valign="middle" align="center" class="action"><? if(!$my): ?><a href="/items/makefav?id=<?= $i['id'] ?>" onclick="return app.fav(<?= $i['id'] ?>, $(this));" class="fav<?= (!empty( $favs['id']) && in_array($i['id'], $favs['id'])?' active':''); ?>"></a><? endif; ?></td>
    <td class="pic"><a href="/item/<?= $i['id'] ?>"><img src="<?= tpl::imgurl(array('folder'=>'items', 'file'=>(!empty($i['imgfav']) ? $i['id'].'t'.$i['imgfav'] : ''), 'static'=>1)); ?>" /></a></td>
    <?
        if($addDynprops) 
        {
            $sHTML = '';
            foreach($dp as $d)
            {
                $key = 'f'.$d['data_field'];
                $sHTML .= '<td>';
                if(!empty($i[$key])) 
                {
                    switch($d['type'])
                    {
                        case dbDynprops::typeNumber:
                        case dbDynprops::typeRange:
                        case dbDynprops::typeInputText:
                        case dbDynprops::typeTextarea:
                        {
                            $sHTML .= $i[$key];
                        } break;
                        case dbDynprops::typeRadioYesNo:
                        {
                            $sHTML .= ($i[$key] == 2 ? 'Да' : ($i[$key] == 1 ? 'Нет' : ''));
                        } break;
                        case dbDynprops::typeCheckboxGroup:
                        {
                            $value = ( isset($i[$key]) && $i[$key] ? func::bit2source($i[$key]) : 0 );
                            if($value !== 0) {
                                $cbGroup = array();
                                foreach($d['multi'] as $dm) {
                                   if(in_array($dm['value'], $value))
                                       $cbGroup[] = $dm['name'];
                                }
                                $sHTML .= join(', ', $cbGroup);
                            }
                        } break;
                        case dbDynprops::typeCheckbox: {
                            $sHTML .= ($i[$key] ? 'Да':'Нет');
                        } break;
                        case dbDynprops::typeRadioGroup:
                        case dbDynprops::typeSelect:
                        {
                            $sHTML .= $d['multi'][$i[$key]]['name'];
                        } break;
                    }
                }
                $sHTML .= '</td>';
            }
            echo $sHTML;
        }
    ?>
    <td valign="top">
        <?php if($i['cat_type']): ?><b class="upper grey"><?= $i['cat_type_title']; ?>:</b> <?php endif; ?><a href="/item/<?= $i['id'] ?>" class="desc-link"><?= tpl::truncate($i['descr'], 200, '...', true); ?></a>
        <div class="address"><?= $i['cat1_title']; ?><? if($i['cat2_id']): ?> <img src="/img/arrowRightSmall.png" /> <?= $i['cat2_title']; ?><? endif; ?> <?= ($i['cat_regions'] && !empty($i['descr_regions'])?'/ '.$i['descr_regions']:''); ?></div>
    </td>
    <? if($bShowPrices){ ?><td valign="top" class="price"><b class="orange f18"><?= $i['price']; ?></b> <span class="f11Up orange">руб</span><br/><span class="f11"><?= ($i['price_torg'] ? 'торг' : '').($i['price_bart'] ? ($i['price_torg'] ? ', ': '').'бартер' : '' ); ?></span></td><? } ?>
</tr>
<? 
} 
?></table><?

echo $aData['pagenation'];