<? if(empty($aData['items'])) { ?>

    <div class="greyLine"> <div class="notify"> У вас нет избранных объявлений.</div> </div>
    
    <script type="text/javascript">
    $(function(){
    $("div.greyLine").corner("8px");
    });
    </script>

<? 
} else { 
    $nUserID = $this->security->getUserID();
    ?>
    
    <form action="/items/printlist" method="post" target="_blank">
    <input type="hidden" name="from" value="fav" />
    <div class="buttonsOb padT16" id="favCheckDD">
        <div class="left dda">
            <a href="javascript:void(0);"><span>&nbsp;</span><input type="checkbox"/></a>
            <div class="dropdown hidden">
              <ul>
                <li><a href="javascript:void(0);" onclick="return favList.check('all', $(this));">Все</a></li>
                <li><a href="javascript:void(0);" onclick="return favList.check('no', $(this));">Ни одного</a></li>
              </ul>
            </div>
        </div>
        <div class="left dda">
            <a href="javascript:void(0);"><span>&nbsp;</span><b>Действия</b></a>
            <div class="dropdown hidden" style="width:152px;">
              <ul>
                <li><a href="javascript:void(0);" onclick="favList.action('print');">Распечатать</a></li>
                <? if($nUserID>0): ?><li><a href="javascript:void(0);" onclick="favList.action('email');">Отправить себе на e-mail</a></li><? endif; ?>
                <li><a href="javascript:void(0);" onclick="favList.action('unfav');">Удалить из избранного</a></li>
              </ul>
            </div>
        </div>
        <div class="right dda">
            <a href="javascript:void(0);"><span>&nbsp;</span><b>Все категории</b></a>
            <div class="dropdown hidden">
              <ul>
                <?  
                $catsOptions = '<li><a href="javascript:void(0);" onclick="favList.cat(0, $(this));" class="select">Все категории</a></li>';
                foreach($aData['cats'] as $i) { 
                    $catsOptions .= '<li><a href="javascript:void(0);" onclick="favList.cat('.$i['id'].', $(this));">'.$i['title'].'</a></li>';
                } echo $catsOptions; ?>
              </ul>
            </div>        
        </div>
        <div class="clear"></div>
    </div>
    
    <div id="favoritesList">
        <? foreach($aData['items'] as $i) { ?>    
        <div class="item blockOb svc<?= $i['svc'] ?>" rel="<?= $i['id']; ?>" catid="<?= $i['cat1_id'] ?>">
            <div class="clear"></div>
            <div class="action"<? if($i['user_blocked']){ ?> style="padding-top: 7px;"<? } ?>>
                <a href="/items/makefav?id=<?= $i['id'] ?>" onclick="return app.fav(<?= $i['id'] ?>, $(this));" class="fav active"></a>
                <input type="checkbox" class="check" name="items[]" value="<?= $i['id']; ?>" />
            </div>
            <? if($i['user_blocked']){ ?>
                <div style="margin-top: 10px;">Объявление от заблокированного пользователя</div>
            <? } else { ?>
            <div class="pic<?= ($i['imgcnt']<2?'2':''); ?>"><a href="/item/<?= $i['id']; ?>"><img src="<?= tpl::imgurl(array('folder'=>'items', 'file'=>(!empty($i['imgfav']) ? $i['id'].'t'.$i['imgfav'] : ''), 'static'=>1)); ?>" /></a></div>
            <div class="desc">
                <?php if($i['cat_type']): ?><b class="upper"><?= $i['cat_type_title']; ?>:</b> <?php endif; ?>
                <?php if($i['cat_subtype']): ?><b class="upper"><?= $i['cat_subtype_title']; ?>:</b> <?php endif; ?>
                <a href="/item/<?= $i['id']; ?>" class="desc-link"><?= tpl::truncate($i['descr'], 330, '...', true); ?></a>
                <div class="address"><?= $i['cat1_title']; ?><? if($i['cat2_id']): ?> <img src="/img/arrowRightSmall.png" /> <?= $i['cat2_title']; ?><? endif; ?> <?= ($i['cat_regions'] && !empty($i['descr_regions'])?'/ '.$i['descr_regions']:''); ?></div>
            </div>
            <?php if($i['cat_prices']): ?><div class="price"><b class="f18 orange"><?= $i['price']; ?></b> <span class="f11Up orange">руб</span><br/><?= ($i['price_torg'] ? 'торг' : '').($i['price_bart'] ? ($i['price_torg'] ? ', ': '').'бартер' : '' ); ?></div><? endif; ?>
            <div class="stat">
                <? if ($aData['f']['active']!=2): ?><span>13.02.2011</span> по <span>12.03.2011</span><? endif; ?>
                Просмотры: <span>всего <b><?= $i['views_total']; ?></b></span> / <span>сегодня <b><?= intval($i['views']); ?></b></span>
            </div>
            <? } ?>
            <div class="clear h9"></div>
        </div>
        <? } ?>
    </div>
    </form>
    
    <script type="text/javascript">
    var favList = (function(){   
    var $list, 
        $ddCont, ddActive = false;
    function init()
    {
         
        $list = $('#favoritesList');
        $list.delegate( '.item', 'click', function(e){
            if(!(!e.target || $(e.target).is('a,input') || $(e.target.parentNode).is('a'))) {
                document.location = '/item/'+$(this).attr('rel');
            }
        });        
        //init dd
        $ddCont = $('div.buttonsOb');
        $('div.dda > a', $ddCont).live('click', function () {  
            return ddOpen( $(this) );
        });
        $('body').click(function() { if(!ddActive) { ddHideOpened(); } });
        $('div.dda > a, div.dda > div.dropdown', $ddCont)
            .live('mouseleave', function () { ddActive = false; })
            .live('mouseenter', function () { ddActive = true;  });        
    }
    
    function ddHideOpened() {
        $('div.dda-open', $ddCont).removeClass('dda-open').find('>div.dropdown').hide();
    }
    
    function ddOpen($link)
    {
        var $block = $link.parent();
        if($block.hasClass('dda-open')) {
            ddHideOpened();
            return false;
        }
        ddHideOpened();
        $block.addClass('dda-open');
        $('>div.dropdown', $block).fadeIn(0);
        return false;
    }
    
    return {init: init, 
            check: function(action, $link)                                                
            {
                var $c = $link.parents('div.dda').find('input:first');
                switch(action) {
                    case 'all': $('input.check', $list).attr('checked', 'checked'); $c.attr('checked', 'checked'); break;
                    case 'no':  $('input.check:checked', $list).removeAttr('checked'); $c.removeAttr('checked');  break; 
                }
                ddHideOpened();
            },
            action: function(action)                                                
            {
                var selected = $list.find('input.check:checked');
                ddHideOpened();
                if(!selected.length) return;
                switch(action) {
                    case 'print': { 
                        $list.parent().submit();
                    } break;
                    case 'email': {
                        bff.ajax('/items/fav?act=email_bulk', $list.parent().serialize(), function(data){
                            if(data && data.res) {
                                alert('Письмо успешно отправлено');
                                $('input.check:checked', $list).removeAttr('checked');
                                $('div#favCheckDD').find('input:first').removeAttr('checked');
                            }
                        });
                    } break; 
                    case 'unfav': {
                        if(confirm('Удалить из избранного отмеченные объявления?'))
                        bff.ajax('/items/fav?act=unfav_bulk', $list.parent().serialize(), function(data){
                            if(data && data.res) {
                                selected.parents('div.item').remove();
                                app.favInit(true, data.total);
                            }
                        });
                    } break; 
                }                
            },
            cat: function(id, $link)
            {
                id = intval(id);
                if(id == 0) {
                    $('div.item:hidden', $list).show();
                } else {
                    $('div.item', $list).hide().filter('[catid="'+id+'"]').show();
                }
                ddHideOpened();
                $link.parent().parent().find('a').removeClass('select');
                $link.addClass('select');
                $link.parents('div.dda').find('a:first > b').html( $link.html() );
            }
    };
}());
$(function(){
    favList.init();    
});
</script>

<? } ?>
