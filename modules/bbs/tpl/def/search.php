<script type="text/javascript">
$(function(){
    $(".greyLine").corner("8px");
    $(".navigation").corner("8px");
    bbsSearch.init({f: <?= func::php2js($aData['f'], true); ?>, pr:<?= func::php2js($aData['cat']['prices_sett']['ranges'], true); ?>});
});
</script>
<?php 
    extract($aData); 
    extract($f);
    $curr = 'руб.';
    
    $total = ($ct? $types[$ct]['items'] : $cat['items']);
    
?>

<form action="/search" method="get"  id="searchForm">

<div class="tabs" id="searchCatSubTypes">
    <a href="#" rel="0" <?= (!$sct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span>Все</span><span class="right">&nbsp;</span></a>
    <? foreach($subtypes as $t) { ?>
    <a href="#" rel="<?= $t['id'] ?>"<?= ($t['id'] == $sct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span><?= $t['title']; ?></span><span class="right">&nbsp;</span></a>
    <? } ?>
    <div class="clear"></div>
</div>

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
    
    if($cat['numlevel'] < 3 && !empty($cats_level)) {
    ?>
    <div class="selectBlock">
        <span class="left">&nbsp;</span>
        <span class="right appdd-opener"><a href="#" class="appdd-link"><span class="appdd-title">Все разделы</span> <img src="/img/dropdown.png" /></a></span>
        <div class="dropdown appdd-dropbox hidden">
            <ul>
                <? foreach($cats_level as $k=>$i) { ?>
                    <li><a href="/search?c=<?= $i['id'] ?>"><?= $i['title'] ?></a></li>
                <? } ?>
            </ul>
        </div>
    </div>
    <? } ?>
    <div class="clear"></div>
</div>


<input type="hidden" name="c" value="<?= $c; ?>" />
<input type="hidden" name="fe" value="<?= $fe; ?>" />
<input type="hidden" name="fh" value="<?= $fh; ?>" />
<input type="hidden" name="pp" value="<?= $pp; ?>" />
<input type="hidden" name="v" value="<?= $v; ?>" />
<input type="hidden" name="ct" value="<?= $ct; ?>" />
<input type="hidden" name="sct" value="<?= $sct; ?>" />
<input type="hidden" name="page" value="1" />

<div class="tabs" id="searchCatTypes">
    <a href="#" rel="0" <?= (!$ct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span>Все</span><span class="right">&nbsp;</span></a>
    <? foreach($types as $t) { ?>
        <a href="#" rel="<?= $t['id'] ?>"<?= ($t['id'] == $ct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span><?= $t['title']; ?></span><span class="right">&nbsp;</span></a>
    <? } ?>
    <div class="clear"></div>
</div>
<div class="padT24">
    <div class="greyBlock">
        <div class="top"><span class="left">&nbsp;</span><div class="clear"></div></div>
        <div class="center">
            поиск среди <b class="f18 orange"><?= $total ?></b> <?= func::declension($total, array('объявления','объявлений','объявлений'), false); ?>
            <div class="padTop">
                    <span class="left"><input type="text" class="searchInput" name="q" value="<?= tpl::escape($q); ?>" /></span>
                    <span class="left"><select class="inputText" name="p" style="width:160px; :margin-top:3px;"><?= $period_select; ?></select></span>
                    <div class="button left">
                        <span class="left">&nbsp;</span>
                        <input type="submit" value="НАЙТИ" />
                    </div>
                    <div class="clear"></div>   
            </div>
        </div>
        <div class="bottom"><span class="left">&nbsp;</span><div class="clear"></div></div>
    </div>
</div>
<div class="filter<?= (!$fh?' open':'') ?>" id="searchFilter">
    <div class="top"><span class="left">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
    <div class="center">
        <span class="left">Фильтр объявлений</span>
        <? if($fe){ ?>
        <span class="on"><a href="#" class="filter-on">включен</a></span>
        <span class="off"><a href="#" class="filter-off">отключить</a></span>
        <? } else {?>
        <span class="off"><a href="#" class="filter-on">включить</a></span>
        <span class="on"><a href="#" class="filter-off">отключен</a></span>
        <? } ?>
        <span class="right"><a href="#" class="greyBord filter-show"><?= (!$fh?'свернуть':'развернуть') ?></a></span>
        <div class="clear"></div>
        <div class="filterContent<?= ($fh?' hidden':'') ?>">
            <? 
            if($cat['prices']) 
            { 
                $priceSel = false;
                foreach($cat['prices_sett']['ranges'] as $k=>$i){ 
                    $cat['prices_sett']['ranges'][$k]['title'] = ($i['from']&&$i['to'] ? $i['from'].'...'.$i['to'] : ($i['from'] ? '> '.$i['from'] : '< '.$i['to'])).' '.$curr;
                    if(isset($pr[$k]) && $priceSel===false) {
                        $priceSel = $cat['prices_sett']['ranges'][$k]['title'];
                    }
                }
                $priceActive = ((($prf||$prt)?1:0) + sizeof($pr));
                $priceSel = ($prf||$prt?($prf&&$prt?$prf.' - '.$prt.' '.$curr:(($prf?'от '.$prf:'до '.$prt).' '.$curr)):($priceSel === false ? 'не важно': $priceSel) ); 
                ?>
            <div class="selectBlock pr<? if($priceActive): ?> active<? endif; ?>">
                <span class="left">&nbsp;</span>
                <? 
                ?>                                  
                <a class="right" href="#"><span class="pad"><b>Цена</b><br/><span class="sel<? if($priceActive>1): ?> plus<? endif; ?>"><?= $priceSel; ?></span></span> <span class="drop">&nbsp;</span></a>
                <div class="clear"></div>
                <div class="dropdown hidden">
                    <div>
                        <label>от <input name="prf" value="<?= $prf>0?$prf:''; ?>" class="from" type="text" /></label>&nbsp;
                        <label>до <input name="prt" value="<?= $prt>0?$prt:''; ?>" class="to" type="text" /></label>
                    </div>
                    <div class="clear"></div>
                    <ul>
                    <? if(!empty($cat['prices_sett']['ranges'])) { 
                                                                                                                                                                                                                                                                                      
                        $sHTML = '<ul><li'.(empty($pr) ? ' class="select"':'').'><input type="checkbox"'.(empty($pr) ? ' checked="checked" disabled="disabled"':'').' class="checkAll" id="pr0" /><label for="pr0"> <span>не важно</span></label></li></ul><div class="clear"></div>';
                        buildCheckboxesBlocks($sHTML, $cat['prices_sett']['ranges'], sizeof($cat['prices_sett']['ranges']), $pr, create_function('$k,$i,$values', '
                            return \'<li><input type="checkbox" name="pr[\'.$k.\']" id="pr\'.$k.\'" 
                                \'.(isset($values[$k])?\' checked\':\'\').\' value="1" /><label for="pr\'.$k.\'"> <span>\'.$i[\'title\'].\'</span></label></li>\';
                        '));
                        echo $sHTML; 
                        
                      /*foreach($cat['prices_sett']['ranges'] as $k=>$i){ ?>
                        <li><input type="checkbox" name="pr[<?= $k; ?>]" id="pr<?= $k; ?>" <?= isset($pr[$k])?'checked':'' ?> value="1" /><label for="pr<?= $k; ?>"> <span><?= $i['title']; ?></span></label></li>
                    <? }*/
                    } ?>
                    <div class="clear"></div>                     
                    <div class="buttonsCont">
                        <div class="button">
                            <span class="left">&nbsp;</span>
                            <input type="button" class="submit" value="отфильтровать" onclick="bbsSearch.filter(11, 0, '.pr', 0, 'руб.', this);" style="width:114px;" />
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>                
            <? }
             
            if($cat['regions']){ 
                $regionSel = array(); 
                $r1_options = ''; $r2_options = ''; $r3_options = '';
                foreach($regions as $i) { 
                    $r1_options .= '<option value="'.$i['id'].'"'.($i['id'] == $r[1]?' selected':'').'>'.$i['title'].'</option>';
                    if($i['id'] == $r[1]) { $regionSel[] = $i['title']; }
                }
                if($r[1]) {
                    foreach($regions[$r[1]]['sub'] as $i) { 
                        $r2_options .= '<option value="'.$i['id'].'"'.($i['id'] == $r[2]?' selected':'').'>'.$i['title'].'</option>';
                        if($i['id'] == $r[2]) { $regionSel[] = $v['title']; }
                    }
                    if($r[2] && !empty($cities)) {
                        foreach($cities as $i) { 
                            $r3_options .= '<option value="'.$i['id'].'"'.($i['id'] == $r[3]?' selected':'').'>'.$i['title'].'</option>';
                            if($i['id'] == $r[3]) { $regionSel[] = $v['title']; }
                        }
                    }
                }
                if(sizeof($regionSel) == 3) array_shift($regionSel);
                unset($regions);
                ?>
            <div class="selectBlock reg<? if(!empty($regionSel)): ?> active<? endif; ?>">
                <span class="left">&nbsp;</span>
                <a class="right" href="#"><span class="pad"><b>Местоположение</b><br/><span class="sel"><?= (!empty($regionSel) ? join(', ', $regionSel) : 'не важно' ) ?></span></span> <span class="drop">&nbsp;</span></a>
                <div class="clear"></div>
                <div class="dropdown hidden">
                    <div>
                        <div class="r1"><select name="r[1]" class="inputText2" onchange="bbsSearch.regionSelect(this, 1);">
                        <option value="0">все страны</option><?= $r1_options ?></select></div>
                        <div class="r2<? if(!$r[1]){ ?> hidden<? } ?>"><select name="r[2]" class="inputText2" onchange="bbsSearch.regionSelect(this, 2);">
                        <option value="0">все регионы</option><?= $r2_options ?></select></div>
                        <div class="r3<? if(!$r[2]){ ?> hidden<? } ?>"><select name="r[3]" class="inputText2">
                        <option value="0">все города / ст. метро</option><?= $r3_options ?></select></div>
                    </div>
                    <div class="clear"></div>                     
                    <div class="buttonsCont">
                        <div class="button">
                            <span class="left">&nbsp;</span>
                            <input type="button" class="submit" value="отфильтровать" onclick="bbsSearch.filter('r', 0, '.reg', 0, '', this);" style="width:114px;" />
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <? }
            
               echo $dp['form']; unset($dp);
               
             ?>
            <div class="photo"><label><input type="checkbox" name="ph" <?= ($ph?' checked="checked"':''); ?> onclick="bbsSearch.enableFilter(); bbsSearch.update();" value="1" /> <span class="left">с фото</span></label></div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="bottom"><span class="left">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
</div>

<script type="text/html" id="child_filter_tmpl">
<div id="<%=pid%>_c<%=vid%>">
    <div class="childseparator"><b><%=vname%></b></div>
    <div><%=form%></div>
    <div class="clear"></div>
</div>
</script>

<div class="kindBlock" id="searchSett">
    <div class="left viewTypes">Вид: <a href="#" class="table<?= ($v==2?' table-active active':'') ?>"><span>таблица</span></a> <a href="#" class="list<?= ($v==1?' list-active active':'') ?>"><span>список</span></a>
        <div class="progress" style="margin-left: 10px; display: none;"></div>
    </div>
    <div class="right viewPP">
        на странице: <a href="#" class="down"><span>по <?= $pp ?></span></a>
        <div class="clear"></div>
        <div class="dropdown" style="display:none;">
            <ul>
                <li><a href="#" rel="10"<?= ($pp==10?' class="select"':'') ?>>по 10</a></li>
                <li><a href="#" rel="20"<?= ($pp==20?' class="select"':'') ?>>по 20</a></li>
                <li><a href="#" rel="30"<?= ($pp==30?' class="select"':'') ?>>по 30</a></li>
            </ul>
        </div>
    </div>
    <div class="clear"></div>
</div>                             
</form>
<div id="searchResults">
<?  if(!empty($items)) {
        echo $this->tplFetchPHP($aData, 'search.results.'.($v==1?'list':'table').'.php');
    } 
 ?>
</div>
