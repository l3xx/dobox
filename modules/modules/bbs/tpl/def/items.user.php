<?php 
    extract($aData);  
?>

<form action="/items/user" method="get" id="userListForm">
<input type="hidden" name="id" value="<?= $f['id']; ?>" />
<input type="hidden" name="c" value="<?= $f['c']; ?>" />
<input type="hidden" name="page" value="<?= $f['page']; ?>" />

<div class="greyLine marg12">
    <div class="searchLinks">
        <ul>
            <li<? if(!$f['c']): ?> class="active"<? endif; ?>><a href="#" rel="0">Все</a> (<?= $total_items; ?>)</li>
            <? foreach($cats as $v) { 
                echo '<li'.($v['id'] == $f['c'] ? ' class="active"':'').'><a href="#" rel="'.$v['id'].'">'.$v['title'].'</a>('.$v['items'].')</li>';
            } ?>
        </ul>
    </div>
    <div class="clear"></div>
</div>
</form>

<div class="padT24" id="userListItems">
<? if(!empty($items)) {
    echo $this->tplFetchPHP($aData, 'search.results.list.php');
} ?>
</div> 
    
<script type="text/javascript">
var bbsUserList = (function(){
    var $form = $('#userListForm'), $list = $('#userListItems'), $progress = $('>div>div>div.progress', $form),
        f = <?= func::php2js($f, true); ?>, process = false;
        
    function setFormParam(name, val) {
        $('input[name="'+name+'"]', $form).val(val);
        return false;
    }
    
    function update()
    {
        bff.ajax('/items/user', $form.serialize(), function(data) {
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
                update();
            }
        });

    }, onPage: function(page){
        setFormParam('page', (f.page = page));
        update();         
        return false;    
    }
}}());

$(function(){
    bbsUserList.init();
});
</script>