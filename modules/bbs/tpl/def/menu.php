<div class="categories" id="indexCategoriesList">
    <?php
    $aCats = bff::i()->Bbs_getIndexCategories();
    $k = 0;
    ?>
    <div class="blockCat" rel="<?= $v['id'] ?>">
        <div class="center">
            <a href="/search?c=1"><span class="caption">Все объявления</span></a>
        </div>
    </div>
    <?
    foreach($aCats as $v) { $k++; ?>
        <div class="blockCat" rel="<?= $v['id'] ?>">
            <!--            <div class="top"></div>-->
            <div class="center">
                <!--                <div onclick="blockClick(--><?//= $v['id'] ?><!--)" class="blockClick" >-->
                <div class="blockClick" >
                    <span class="caption"><?= $v['title']; ?></span>
                </div>
                <div id="block<?= $v['id'] ?>" style="padding: 0 0 10px 0;" class="block_front" >
                    <ul>
                        <?php  $i=1;
                        $num = 5;
                        foreach($v['sub'] as $v2) {
                            ?>
                            <li <? if ($i>$num) print 'class="cats-another" style="display: none;"' ?>><a href="/search?c=<?= $v2["id"] ?>"><?= $v2["title"]; ?></a> (<?= $v2["items"]; ?>)&nbsp;&nbsp;</li>
                            <?php
                            $i++;
                        }
                        //                        if($i>5) break;
                        //                        if($i>$num) {
                        //                            ?>
                        <!--                            <li "><a href="/search?c=--><?//= $v2['id'] ?><!--">--><?//= $v2['title']; ?><!--</a> (--><?//= $v2['items']; ?><!--)&nbsp;&nbsp;</li>-->
                        <!--                            --><?//
                        //                        }
                        //                    }

                        if(sizeof($v['sub'])>5) {
                            ?>
                            <!--                    <li><a href="javascript:void(0);" onclick="indexCatExpand(--><?//= $v['id'] ?><!--, true);" class="greyBord">другие рубрики</a></li-->
                        <li><a href="javascript:void(0);" onclick="indexCatExpand2(<?= $v['id'] ?>, true);" class="greyBord">другие рубрики</a></li
                        <? } ?>
                    </ul>
                    <!--                    <br />-->
                </div>
            </div>
            <!--            <div class="bottom"></div>-->
        </div>
        <?php if($k==3){ echo '<div class="clear"></div>'; $k=0; }  } ?>
    <div class="clear"></div>
</div>

<script type="text/javascript">
    function indexCatExpand2(id, expand) {
        var indexCatCache = {}, $indexCatList, indexCatExpandLastID = 0;
    var $block = $('div.blockCat[rel="'+id+'"]', $indexCatList);
    $block.find('li.cats-another').toggle();
    //        if(expand)
    //        {
    //            $block.find('li.cats-another').show();
    //        }
    //        else {
    //            $block.find('li.cats-another').hide();
    //        }
    }
</script>