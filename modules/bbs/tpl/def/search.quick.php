<?php 
    extract($aData);  
?>

<form action="/search" method="get" id="searchQuickForm">
<input type="hidden" name="quick" value="1" />
<input type="hidden" name="c" value="<?= $f['c']; ?>" />
<input type="hidden" name="page" value="<?= $f['page']; ?>" />
<div class="greyBlock">
    <div class="top"><span class="left">&nbsp;</span><div class="clear"></div></div>
    <div class="center">
        <div class="left">поиск среди <b class="f18 orange"><?= $total; ?></b> <?= func::declension($total, array('объявления','объявлений','объявлений'), false); ?></div> <div class="left progress" style="margin: 10px 0 0 10px; display: none;"></div>
        <div class="clear"></div>
        <div class="padTop">
            <span class="left"><input type="text" name="q" value="<?= tpl::escape($f['q']); ?>" class="inputText"/></span>
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

<div class="greyLine marg12">
    <? if($total_items!=0) { ?> 
    <div class="searchLinks">          
        <table>
            <tr>
            <td valign="top">
                <ul>
                <li<? if(!$f['c']): ?> class="active"<? endif; ?>><a href="#" rel="0">Все</a> (<?= $total_items; ?>)</li>
                </ul>
            </td>
            <td>
            <ul>
                <? foreach($cats as $v) { 
                    echo '<li'.($v['id'] == $f['c'] ? ' class="active"':'').'><a href="#" rel="'.$v['id'].'">'.$v['title'].'</a> ('.$v['items'].')</li>';
                } ?>
            </ul>                     
            </td>
            </tr>
        </table>
    </div>      
    <div class="clear"></div>
    <? } 
    else {?>  
    <div class="notify">По вашему запросу ничего не найдено.</div>
    <? } ?>
</div>
</form>

<div class="padT24" id="searchQuickResults">
<? if(!empty($items)) {
    echo $this->tplFetchPHP($aData, 'search.results.list.php');
} ?>
</div> 
    
<script type="text/javascript">
    var bbsSearchQuick = (function(){
    var $form = $('#searchQuickForm'), $list = $('#searchQuickResults'), $progress = $('>div>div>div.progress', $form),
        f = <?= func::php2js($f, true); ?>, process = false;
        
    function setFormParam(name, val) {
        $('input[name="'+name+'"]', $form).val(val);
        return false;
    }
    
    function search()
    {
        bff.ajax('/search', $form.serialize(), function(data) {
            if(data && data.res) {
                $list.html(data.list);
            }
        }, $progress);        
    }
    
    return {
    
    init: function() 
    {
        $(">div.greyLine", $form).corner("8px");
        $(">div.greyLine .searchLinks ul li", $form).corner("4px");
        $(".navigation", $form.parent()).corner("8px");
                
        $form.submit(function(){
            if($.trim(this.q.value).length>0) { 
                return true;
            }
            return false;
        });

        $('>div>div.searchLinks a', $form).click(function(e){
            nothing(e);
            var cat = intval( this.rel );
            if(cat!=f.cat && !process) {                                 
                $('.searchLinks li.active', $form).removeClass('active');
                $(this).parent().addClass('active');
                setFormParam('page', (f.page = 1));
                setFormParam('c', (f.cat = cat));
                search();
            }
        });
        
        $list.delegate( 'div.my', 'mouseenter mouseleave click', function(e){
            switch(e.type) {
                case 'mouseover': {
                    $(this).addClass('advanced');
                    $('>.actionsLink', this).removeClass('hidden');
                } break;
                case 'mouseout': {
                    $(this).removeClass('advanced');
                    $('>.actionsLink', this).addClass('hidden');
                } break;
                case 'click': {
                    if(!(!e.target || $(e.target).is('a') || $(e.target.parentNode).is('a'))) {
                        document.location = '/item/'+$(this).attr('rel');
                    }
                } break;
            }
        });
    }, onPage: function(page){
        setFormParam('page', (f.page = page));
        search();         
        return false;    
    }
}}());

$(function(){
    bbsSearchQuick.init();
});
</script>