<?
    $aStatusKey = array(
        BBS_STATUS_PUBLICATED => 'active',
        BBS_STATUS_PUBLICATED_OUT => 'notactive',
        BBS_STATUS_BLOCKED => 'blocked',
    );

?>
<form action="/items/printlist" method="post" target="_blank">
    <input type="hidden" name="from" value="my" />
    <input type="hidden" name="order" value="<?= $aData['f']['order'] ?>" />
    <input type="hidden" name="status" value="<?= $aData['f']['status'] ?>" />
    <input type="hidden" name="cat" value="<?= $aData['f']['cat'] ?>" />
    
    <div class="greyLine">
        <span class="left grey">Сортировать:</span>
        <span class="left"><a href="/items/my?order=1&status=<?= $aData['f']['status'] ?>" class="orderby <?= ($aData['f']['order'] == 1 ? 'green' : 'greyBord'); ?>" onclick="return myList.orderBy(1);" rel="1">срок публикации</a></span>
        <span class="left"><a href="/items/my?order=2&status=<?= $aData['f']['status'] ?>" class="orderby <?= ($aData['f']['order'] == 2 ? 'green' : 'greyBord'); ?>" onclick="return myList.orderBy(2);" rel="2">дата добавления</a></span>
        <div class="progress" id="itemsListProgress" style="margin: 15px 0 0 5px; display: none;"></div>
        <? if($aData['items_status']['blocked']>0){ ?><span class="right"><a href="/items/my?status=<?= BBS_STATUS_BLOCKED ?>&by=<?= $aData['f']['order'] ?>" class="status <?= ($aData['f']['status'] == BBS_STATUS_BLOCKED ? 'green' : 'greyBord'); ?>" onclick="return myList.onStatus(<?= BBS_STATUS_BLOCKED ?>);" rel="<?= BBS_STATUS_BLOCKED ?>">Отклоненные</a> (<?= (int)$aData['items_status']['blocked'] ?>)</span><? } ?>
        <span class="right"><a href="/items/my?status=<?= BBS_STATUS_PUBLICATED_OUT ?>&by=<?= $aData['f']['order'] ?>" class="status <?= ($aData['f']['status'] == BBS_STATUS_PUBLICATED_OUT ? 'green' : 'greyBord'); ?>" onclick="return myList.onStatus(<?= BBS_STATUS_PUBLICATED_OUT ?>);" rel="<?= BBS_STATUS_PUBLICATED_OUT ?>">Неактивные</a> (<?= (int)$aData['items_status']['notactive'] ?>)</span>
        <span class="right"><a href="/items/my?status=<?= BBS_STATUS_PUBLICATED ?>&by=<?= $aData['f']['order'] ?>" class="status <?= ($aData['f']['status'] == BBS_STATUS_PUBLICATED ? 'green' : 'greyBord'); ?>" onclick="return myList.onStatus(<?= BBS_STATUS_PUBLICATED ?>);" rel="<?= BBS_STATUS_PUBLICATED ?>">Активные</a> (<?= (int)$aData['items_status']['active'] ?>)</span>
        <div class="clear"></div>
    </div><script type="text/javascript">$(function(){ $(".greyLine").corner("8px"); }); </script>
              
    <div class="buttonsOb padT16">
        <div class="left dda dda-checks">
            <a href="javascript:void(0);"><span>&nbsp;</span><input type="checkbox"/></a>
            <div class="dropdown hidden">
              <ul>
                <li class="all"><a href="javascript:void(0);" onclick="return myList.check('all', $(this));">Все</a></li>
                <li class="non-blocked non-notactive"><a href="javascript:void(0);" onclick="return myList.check('svc2', $(this));">Выделенные</a></li>
                <li class="non-blocked non-notactive"><a href="javascript:void(0);" onclick="return myList.check('svc3', $(this));">Премиум</a></li>
                <li class="non-blocked non-notactive"><a href="javascript:void(0);" onclick="return myList.check('svc4', $(this));">В прессе</a></li>
                <li class="all"><a href="javascript:void(0);" onclick="return myList.check('no', $(this));">Ни одного</a></li>
              </ul>
            </div>
        </div>
        <div class="left dda dda-actions">
            <a href="javascript:void(0);"><span>&nbsp;</span><b>Действия</b></a>
            <div class="dropdown hidden" style="width:152px;">
              <ul>
                <li class="all"><a href="javascript:void(0);" onclick="myList.action('print');">Распечатать</a></li>
                <li class="all"><a href="javascript:void(0);" onclick="myList.action('del_bulk');">Удалить</a></li>
                <li class="non-blocked non-notactive"><a href="javascript:void(0);" onclick="myList.action('unpubl');">Не публиковать</a></li>
              </ul>
            </div>
        </div>
        <div class="right dda dda-cat">
            <a href="javascript:void(0);"><span>&nbsp;</span><b>Все категории</b></a>
            <div class="dropdown hidden">
              <ul>
                <?  
                $curStatus = $aData['f']['status'];
                $catsOptions = '<li '.$aStatusKey[BBS_STATUS_PUBLICATED].'="1" '.$aStatusKey[BBS_STATUS_PUBLICATED_OUT].'="1" '.$aStatusKey[BBS_STATUS_BLOCKED].'="1"><a href="javascript:void(0);" onclick="myList.cat(0, $(this));" class="select">Все категории</a></li>';
                foreach($aData['cats'] as $i) { 
                    $catsOptions .= '<li '.$aStatusKey[BBS_STATUS_PUBLICATED].'="'.$i[$aStatusKey[BBS_STATUS_PUBLICATED]].'" 
                                         '.$aStatusKey[BBS_STATUS_PUBLICATED_OUT].'="'.$i[$aStatusKey[BBS_STATUS_PUBLICATED_OUT]].'"
                                         '.$aStatusKey[BBS_STATUS_BLOCKED].'="'.$i[$aStatusKey[BBS_STATUS_BLOCKED]].'"
                                         '.($i[$aStatusKey[$curStatus]]==0?'style="display:none"':'').'><a href="javascript:void(0);" onclick="myList.cat('.$i['id'].', $(this));">'.$i['title'].'</a></li>';
                } echo $catsOptions; ?>
              </ul>
            </div>        
        </div>
        <div class="clear"></div>
    </div>
    
    <div id="itemsList">
     <?        
        $aData['show_no_items_block'] = true;
        echo $this->tplFetchPHP($aData, 'items.list.my.items.php');
     ?>
    </div>
    
</form>
    
    <script type="text/javascript">
var myList = (function(){   
    var $list, $form, $ddCont, ddActive = false, f = {}, $progress, process = false, h; 
    var statusKey = {<?= BBS_STATUS_PUBLICATED ?>: 'active', <?= BBS_STATUS_PUBLICATED_OUT ?>: 'notactive', <?= BBS_STATUS_BLOCKED ?>: 'blocked'};   
    function ddHideOpened() {
        $('div.dda-open', $ddCont).removeClass('dda-open').find('>div.dropdown').hide();
    }
    function ddOpen($link) {
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
    function setFormParam(name, val) {
        $('input[name="'+name+'"]', $form).val(val);
        f[name] = val;
        return false;
    }
    
    function updateList()
    {
        if(process) return false;
        process = true;
        h.pushState(null, document.title, '/items/my?'+$.param(f) );
        bff.ajax('/items/my', $form.serialize(), function(data){
            if(data && data.res) {
                $list.html( data.list );
            }
            process = false;
        }, $progress);
    }
    
    function updateDDA(status)
    {
        var key = statusKey[status];
        $('div.dda-cat ul li').hide().filter('li['+key+'!="0"]').show();
        $('div.dda-checks ul li').hide().filter('li.all,li:not(.non-'+key+')').show();
        $('div.dda-actions ul li').hide().filter('li.all,li:not(.non-'+key+')').show(); 
    }
    
    return {
        init: function(o)
        {
            f = o.f;
            h = window.History;
            if(h.emulated.pushState) {
                var hash = h.getHash();
                if(hash && hash.length>0) {
                    if(/items\/my\?/i.test(hash)) document.location = '/'+hash;
                }
            }
            $list = $('#itemsList');
            $form = $list.parent();
            $progress = $('#itemsListProgress')
            $list.delegate( 'div.item > div:first', 'click', function(e){
                if(!(!e.target || $(e.target).is('a,input') || $(e.target.parentNode).is('a'))) {
                    document.location = '/item/'+$(this).parent().attr('rel');
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

            updateDDA( f.status );
        }, 
        check: function(action, $link)                                                
        {
            var $c = $link.parents('div.dda').find('input:first');
            switch(action) {
                case 'all': $('input.check', $list).attr('checked', 'checked'); $c.attr('checked', 'checked'); break;
                case 'svc2': $('input.check', $list).removeAttr('checked'); $('input.svc2', $list).attr('checked', 'checked'); $c.removeAttr('checked'); break;
                case 'svc3': $('input.check', $list).removeAttr('checked'); $('input.svc3', $list).attr('checked', 'checked'); $c.removeAttr('checked'); break;
                case 'svc4': $('input.check', $list).removeAttr('checked'); $('input.svc4', $list).attr('checked', 'checked'); $c.removeAttr('checked'); break;
                case 'no':  $('input.check:checked', $list).removeAttr('checked'); $c.removeAttr('checked');  break; 
            }
            ddHideOpened();
        },
        del: function(id)
        {
            id = intval(id);
            if(id>0 && confirm('Удалить объявление?'))
            bff.ajax('/items/del?act=del', {id: id}, function(data){
                if(data && data.res && data.deleted == 1) {
                    $('div.item[rel="'+id+'"]', $list).remove();
                }
            }); return false;
        },
        action: function(action, extra)                                                
        {
            var selected = $list.find('input.check:checked');
            ddHideOpened();
            if(!selected.length) return;
            switch(action) {
                case 'print': { 
                    $list.parent().submit();
                } break;
                case 'del_bulk': {
                    if(confirm('Удалить отмеченные объявления?'))
                    bff.ajax('/items/del?act=bulk', $form.serialize(), function(data){
                        if(data && data.res) {
                            selected.parents('div.item').remove();
                        }
                    });
                } break; 
                case 'unpubl': {
                    if(confirm('Снять отмеченные объявления с публикации?'))
                    bff.ajax('/items/my?act=unpubl_bulk', $form.serialize(), function(data){
                        if(data && data.total!=0) {
                            location.reload();
                        }
                    });
                } break;
            }                
        },
        onStatus: function(status)
        {
            if(f.status==status) return false;           
            $('a.status', $form).removeClass('green greyBord').addClass('greyBord').filter('[rel="'+status+'"]').removeClass('greyBord').addClass('green');
            setFormParam('status', status);
            this.cat(0,$('div.dda-cat ul li a:first'), true);
            updateDDA( status );
            updateList();
            return false;
        },
        orderBy: function(by)
        {
            if(f.order==by) return false;
            $('a.orderby', $form).removeClass('green greyBord').addClass('greyBord').filter('[rel="'+by+'"]').removeClass('greyBord').addClass('green');
            setFormParam('order', by);
            updateList();
            return false;
        },
        cat: function(id, $link, onlyset)
        {
            id = intval(id);
            if(f.cat==id) return false;
            setFormParam('cat', id); 
            
            if(!onlyset) {
                updateList(); 
                ddHideOpened();
            }
            
            $link.parent().parent().find('a').removeClass('select');
            $link.addClass('select');
            $link.parents('div.dda').find('a:first > b').html( $link.html() );
        }
    };
}());
$(function(){
    myList.init({f: <?= func::php2js($aData['f'], true); ?>});    
});
</script>