<?php
extract($aData);
extract($f);
$curr = '$.';

$total = ($ct? $types[$ct]['items'] : $cat['items']);

?>

<form action="/search" method="get"  id="searchForm">



<!--<div class="padT24">-->

<div class="tabs" id="searchCatSubTypes">
    <a href="#" rel="0" <?= (!$sct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span>Все</span><span class="right">&nbsp;</span></a>
    <? foreach($subtypes as $t) { ?>
    <a href="#" rel="<?= $t['id'] ?>"<?= ($t['id'] == $sct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span><?= $t['title']; ?></span><span class="right">&nbsp;</span></a>
    <? } ?>
    <div class="clear"></div>
</div>

<div class="">
    <div class="greyBlock greyLine" style="background: #ECEEEE; border: none;">

        <!--        <div class="top"><span class="left">&nbsp;</span><div class="clear"></div></div>-->
        <div class="center" style="border: none; padding-top: 8px;">
            <?php if ($categ != 1): ?>поиск среди <b class="f18 orange"><?= $total ?></b> <?= func::declension($total, array('объявления','объявлений','объявлений'), false); ?> <?php endif; ?>
            <div class="padTop">
                <span class="left"><input type="text" class="searchInput" name="q" value="<?= tpl::escape($q); ?>" /></span>

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
                                <? //if($cats_numlevel>1) { ?> <li><a href="/search?c=<?= $cats_parent; ?>">Все объявления</a></li>
                                <? //}
                                foreach($cats_level as $k=>$i) { ?>
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
                        <span class="right appdd-opener"><a href="#" class="appdd-link"><span class="appdd-title">Все объявления</span> <img src="/img/dropdown.png" /></a></span>
                        <div class="dropdown appdd-dropbox hidden">
                            <ul>
                                <? foreach($cats_level as $k=>$i) { ?>
                                <li><a href="/search?c=<?= $i['id'] ?>"><?= $i['title'] ?></a></li>
                                <? } ?>
                            </ul>
                        </div>
                    </div>
                    <? } ?>


<!--                <span class="left"><select class="inputText" name="p" style="width:160px; :margin-top:3px;">--><?//= $period_select; ?><!--</select></span>-->
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="НАЙТИ" />
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <!--        <div class="bottom"><span class="left">&nbsp;</span><div class="clear"></div></div>-->
    </div>
</div>


<div class="greyLine" style="">

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

<script type="text/html" id="child_filter_tmpl">
    <div id="<%=pid%>_c<%=vid%>">
        <div class="childseparator"><b><%=vname%></b></div>
        <div><%=form%></div>
        <div class="clear"></div>
    </div>
</script>

<div style="border-bottom: 2px solid #225b9b;">
    <div class="kindBlock" id="searchSett">
        <div class="left viewTypes" style="margin-left: 8px;">Вид: <a href="#" class="table<?= ($v==2?' table-active active':'') ?>"><span>таблица</span></a> <a href="#" class="list<?= ($v==1?' list-active active':'') ?>"><span>список</span></a>
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
    <div class="tabs" id="searchCatTypes" style="border-bottom: none;">
        <a href="#" rel="0" <?= (!$ct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span>Все</span><span class="right">&nbsp;</span></a>
        <? foreach($types as $t) { ?>
        <a href="#" rel="<?= $t['id'] ?>"<?= ($t['id'] == $ct ? ' class="active"':'') ?>><span class="left">&nbsp;</span><span><?= $t['title']; ?></span><span class="right">&nbsp;</span></a>
        <? } ?>
    </div>
    <div class="clear"></div>
</div>

</form>