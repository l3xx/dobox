
<div class="greyBlock">
    <div class="top"><span class="left">&nbsp;</span><div class="clear"></div></div>
    <div class="center">
        <? $total = bff::i()->Bbs_getTotalItemsCount(); ?>
        поиск среди <b class="f18 orange"><?= $total ?></b> <?= tpl::declension($total, array('объявления','объявлений','объявлений'), false); ?>
        <div class="padTop">
            <form action="/search" method="get">
                <input type="hidden" name="quick" value="1" />
                <span class="left"><input type="text" class="searchInput" name="q" /></span>
                <span class="left"><select class="inputText" name="p" style="width:160px; :margin-top:3px;"><?= bff::i()->Bbs_periodSelect(); ?></select></span>
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="button" value="НАЙТИ" onclick="if($.trim(this.form.q.value)=='') this.form.q.focus(); else this.form.submit();" />
                </div>
                <div class="clear"></div>
            </form>
        </div>
    </div>
    <div class="bottom"><span class="left">&nbsp;</span><div class="clear"></div></div>
</div>
<div class="categories" id="indexCategoriesList">
    <?php
    $aCats = bff::i()->Bbs_getIndexCategories();
    $k = 0;
    foreach($aCats as $v) { $k++; ?>
        <div class="blockCat" rel="<?= $v['id'] ?>">
            <div class="top"></div>
            <div class="center">
                <div onclick="blockClick(<?= $v['id'] ?>)" class="blockClick" >
                    <span class="caption"><?= $v['title']; ?></span>
                </div>
                <div id="block<?= $v['id'] ?>" style="padding: 0 0 10px 0;" class="block_front" >
                    <!--ul-->
                    <?php  $i=1;
                    foreach($v['sub'] as $v2) { ?>
                        <!--li--><a href="/search?c=<?= $v2['id'] ?>"><?= $v2['title']; ?></a> (<?= $v2['items']; ?>)&nbsp;&nbsp;<!--/li-->
                        <?php $i++; //if($i>5) break;
                    }

                    //if(sizeof($v['sub'])>5) {
                    ?>
                    <!--li><a href="javascript:void(0);" onclick="indexCatExpand(<?= $v['id'] ?>, true);" class="greyBord">другие рубрики</a></li-->
                    <? //} ?>
                    <!--/ul-->
                    <br />
                </div>
            </div>
            <div class="bottom"></div>
        </div>
        <?php if($k==3){ echo '<div class="clear"></div>'; $k=0; }  } ?>
    <div class="clear"></div>
</div>

<script type="text/javascript">
    var indexCatCache = {}, $indexCatList, indexCatExpandLastID = 0;

    function blockClick(id){
        $('.block_front').css({"display":"none"});

        if ($("#block"+id).is(":hidden")) {
            $("#block"+id).slideDown("slow");
        } else {
            $("#block"+id).hide();
        }

    }



    $(function(){
        $indexCatList = $('#indexCategoriesList');
        $('body').click(function(e) {
            if( ! $(e.target).is('a') && ! $(e.target).parents('div.openBlock').length ) {
                $('div.openBlock', $indexCatList).remove();
            }
        });

        /*$(".block_front").click(function(){
            $('.block_front').css({"display":"none;"});
        });*/

    });

    function indexCatExpand(id, expand)
    {
        if(expand)
        {
            var $block = $('div.blockCat[rel="'+id+'"]', $indexCatList);
            var pos = $block.position();
            if(indexCatCache[id]) {
                if(indexCatExpandLastID>0) {
                    $('div.expand-'+indexCatExpandLastID, $indexCatList).remove();
                }
                $block.after( indexCatCache[id] );
                $('div.expand-'+id, $indexCatList).css({top: pos.top, left: pos.left}).show();
            } else {
                bff.ajax('/items/getIndexCategories?act=expand', {id:id}, function(data){
                    if(data && data.res) {
                        if(indexCatExpandLastID>0) {
                            $('div.expand-'+indexCatExpandLastID, $indexCatList).remove();
                        }
                        $block.after( ( indexCatCache[id] = data.block ) );
                        $('div.expand-'+id, $indexCatList).css({top: pos.top, left: pos.left}).show();
                    }
                });
            }
            indexCatExpandLastID = id;
        } else {
            $('div.expand-'+id, $indexCatList).hide().remove();
            indexCatExpandLastID = 0;
        }
    }
</script>
