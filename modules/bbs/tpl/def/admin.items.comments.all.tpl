
<script type="text/javascript">
var listingupdating = false;  
var progress = null;
{literal} 
$(document).ready(function(){
    progress = $('#progress-precomments');
});

function doPage(offset)
{
    if(listingupdating)
        return;
        
    document.forms.filters['offset'].value = offset;
    bbsUpdateItemCommentsListing(false);
}

function bbsUpdateItemCommentsListing(resetoffset)
{   
    if(listingupdating)
        return;
    
    progress.show();
        
    if(resetoffset!==false)
        document.forms.filters['offset'].value = 0;
        
    listingupdating = true;
    
    $('#precomments-tbl').animate({'opacity': 0.65}, 300);
    bff.ajax('index.php?s=bbs&ev=items_comments_all', $(document.forms.filters).serializeArray(), function(data){
        listingupdating = false;
        progress.hide(); 
        if(data!=0)
        {
            $('#precomments-tbl').animate({'opacity': 1}, 100);
            $('#precomments-tbl-body').html(data.list);
            $('#pagenation').replaceWith(data.pages); 
        }    
    }, '#progress-precomments');
}

function bbsDeleteItemComment(itemID, commentID)
{
    if(bff.confirm('sure'))
    bff.ajax('index.php?s=bbs&ev=items_comments&act=comment-delete', {rec: itemID, comment_id: commentID}, function(data) { 
            if(data) {   
                $('#comm'+commentID).remove();
            }
        }, '#progress-precomments'); 
      return false;   
}

{/literal}              
</script>

<form action="index.php" method="get" name="filters">
    <input type="hidden" name="s" value="{$class}" />
    <input type="hidden" name="ev" value="{$event}" />
    <input type="hidden" name="order" value="{$order_by},{$order_dir}" /> 
    <input type="hidden" name="offset" value="0" /> 
</form>

<table class="admtbl tblhover" id="precomments-tbl">
    <tr class="header">
        <th style="text-align:left;" width="100">Автор</th>
        <th>
            <div class="left">Комментарий</div>
            <div class="left progress" id="progress-precomments" style="margin:3px 0 0 5px; display:none;"></div>
        </th>
        <th width="72" nowrap="nowrap">
            Создан <div class="order-asc"></div>
        </th>
    </tr>
    {if $aData.comments|@count>0}
    {foreach from=$aData.comments item=v key=k}
    <tr class="row{$k%2}" id="comm{$v.id}">
        <td align="left" style="vertical-align:top;">{if $v.user_id}<a class="userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec={$v.user_id}">{$v.user_name}</a>{else}{$v.name}{/if}</td>
        <td align="left">
            <div style="width:455px; x-overflow:scroll;">{if $v.user_blocked}<span class="desc">Комментарий от заблокированного пользователя</span>{else}{$v.comment}{/if}</div>
            <br /><a href="/item/{$v.item_id}?comments=1#comment{$v.id}" class="but linkout" target="_blank"></a><a href="index.php?s={$class}&amp;ev=items_comments&amp;rec={$v.item_id}#comment{$v.id}"># {$v.item_id}</a>
            <br /><a class="desc" href="index.php?s={$class}&amp;ev=items_listing&amp;cat_id={$v.cat_id}">{$v.cat_title}</a>
        </td>                                                    
        <td class="small" style="vertical-align:top;">
            {$v.created|date_format3}<br /><div style="margin-top:3px;">
            <a class="but del" href="javascript:void(0);" onclick="return bbsDeleteItemComment('{$v.item_id}', '{$v.id}');" ></a> 
        </div></td>
    </tr>
    {/foreach} 
    </table>

    {$pagenation_template}
    
    {else}
    <tr class="norecords">
        <td colspan="3">нет комментариев</td>
    </tr>
    </table>
    {/if} 
  
