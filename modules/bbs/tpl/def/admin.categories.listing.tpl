<script type="text/javascript">
{literal}
var bbsCatState = [];
var bbsCatStateCookie = app.cookiePrefix+'bbs_cats_state';
var bbsCatList = '';
function bbsCatCollapse(id, tr)
{  
    var sub = tr.nextUntil('tr[pid="'+tr.attr('pid')+'"]');
    if( sub.length ) {   
        var pids = [];
        sub.each(function(i,e){
            var p = intval($(e).attr('pid'));
            if($.inArray(p,pids)==-1)
               pids.push(p); 
        });
        
        var vis = sub.is(':visible');
        if(!vis) { if($.inArray(id,bbsCatState)==-1) bbsCatState.push(id); sub.show(); } else {
            for(var i=0; i<pids.length; i++) {
                var j = $.inArray(pids[i],bbsCatState);
                if(j!==-1) {
                    bbsCatState.splice(j, 1);
                }
            }
            sub.hide(); 
        }
    } else {
        bff.ajax('index.php?s=bbs&ev=categories_listing&act=subs-list',{category: id},function(data){
            if(data) {
                $('#dnd-'+id, bbsCatList).after(data.cats);
                //$('#icitems'+id).html(data.itemstotal);
                
                bbsCatState.push(id);      
                $('tr[pid="'+id+'"]', bbsCatList).show();
                bbsCatList.tableDnDUpdate();
            }
        }, '#progress-bbs-categories');
    }
    
    bff.cookie(bbsCatStateCookie, (bbsCatState.length ? bbsCatState.join(',') : ''), {expires:45});

    return false;
}

$(function ()
{
   bbsCatList  = $('#bbs_cats_listing');
   bbsCatState = bff.cookie( bbsCatStateCookie ); 
   bbsCatState = ( !bbsCatState ? [] : bbsCatState.split(',') );   
   for(var i=0; i<bbsCatState.length; i++) {
       bbsCatState[i] = intval(bbsCatState[i]);
   }
   
   bff.initTableDnD(bbsCatList, 'index.php?s=bbs&ev=categories_listing&act=rotate', '#progress-bbs-categories');
   
   catPrices.init();
});

function bbsCatAct(id, act, extra)
{
    switch(act)
    {
        case 'c':        { bbsCatCollapse(id, extra); } break;
        case 'dyn':      { bff.redirect( 'index.php?s=bbs&ev=dynprops_listing&owner='+id); } break;     
        case 'type':     { bff.redirect( 'index.php?s=bbs&ev=types&cat_id='+id); } break;
        case 'edit':     { bff.redirect( 'index.php?s=bbs&ev=categories_edit&rec='+id); } break;
        case 'del':      { 
            bff.ajaxDelete('sure', id, 'index.php?s=bbs&ev=categories_listing&act=delete', extra, 
                {progress: '#progress-bbs-categories'});
                return false;    
            } break;
        case 'toggle':   { 
            bff.ajaxToggle(id, 'index.php?s=bbs&ev=categories_listing&act=toggle&rec='+id, 
                   {link: extra, progress: '#progress-bbs-categories'});
                   return false;
            } break;           
        case 'regions':  { 
            bff.ajaxToggle(id, 'index.php?s=bbs&ev=categories_listing&act=regions&rec='+id, 
                   {link: extra, progress: '#progress-bbs-categories', block: '', unblock: 'disabled',});
                   return false;            
        } break;
        case 'prices':  { 
            catPrices.change(id, extra);
        } break;
    }
    
    return false;
}

var catPrices = (function(){
    var $progress, formTpl;
    function init()
    {
        $progress = $('#progress-bbs-categories');
        formTpl = bff.tmpl('cat_prices_form_tmpl');
        
        $('#catPricesForm').live('submit', function(){
            var $form = $(this);
            bff.ajax($form.attr('action'), $(this).serialize() ,function(data){
                if(data) {    
                    var $link = $('#prc'+data.cat_id);
                    if(intval(data.prices)) $link.removeClass('disabled'); else $link.addClass('disabled');
                    $.fancybox.close();
                }                  
            }, $progress);
            return false;
        })
    }
    
    function change(id, $link)
    {                          
        bff.ajax('index.php?s=bbs&ev=categories_listing&act=prices-form&rec='+id,{},function(data){
            if(data) {                
                $.fancybox( formTpl(data) );
            }                  
        }, $progress);
        return false;
    }
    
    return {init: init, change: change};
}());

{/literal}
</script>

<script type="text/html" id="cat_prices_form_tmpl">
<div class="ipopup" style="width:380px;">
<div class="ipopup-wrapper">
<div class="ipopup-title">Цена</div>
<div class="ipopup-content">   
    <form action="index.php?s=bbs&ev=categories_listing&act=prices-form&rec=<%=cat_id%>&save=1" method="post" id="catPricesForm">
        <table class="admtbl tbledit">
        <%=form%>
        <tr class="footer">
            <td colspan="2" style="text-align: center; padding-top:15px;">
                <input type="submit" class="button submit" value="Сохранить" />
                <input type="button" class="button cancel" value="Отмена" onclick="$.fancybox.close();" />
            </td>
        </tr>
        </table>
    </form>
</div> 
</div>
</div>
</script>

<table class="admtbl tblhover" id="bbs_cats_listing">
<tr class="header nordrag nodrop">
    <th align="left">Название<span id="progress-bbs-categories" style="display:none;" class="progress"></span></th>
    <th width="75">Объявления</th> 
    <th width="70">Регион</th>
    <th width="45">Цена</th>
    <th width="155">Действие</th>
</tr>
{if $aData.cats}
    {$aData.cats_html}
{else}
    <tr class="norecords">
        <td colspan="5">
            нет категорий (<a href="index.php?s={$class}&ev=categories_add">добавить</a>)
        </td>
    </tr>
{/if}        
</table>
<div>
    <br/>
    <div class="left"></div>
    <div style="width:80px; text-align:right;" class="right desc">&nbsp;&nbsp; &darr; &uarr;</div>
    <br/>
</div>