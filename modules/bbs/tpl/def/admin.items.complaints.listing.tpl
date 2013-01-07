<script type="text/javascript">
var bbsPropFilter = '&f={$aData.f|escape:"url"}';
{literal}
function bbsItemClaimAct(id, act, extra)
{
    var $counter = $('#bbs-items-claims-counter > span');
    var count = intval($counter.html());
    switch(act)
    {                                                                                                   
        case 'del': { 
            if(bff.confirm('sure')) {
            bff.ajax('index.php?s=bbs&ev=items_complaints&act=delete', {id: id}, function(data){
                if(data) {
                    $(extra).parent().parent().remove();
                    if(data.counter_update) {
                        $counter.html( (count>0 ? count-1 : '') );
                    }
                }
            }, '#progress-bbs-items-complaints');
            }
        } break;
        case 'view':  { 
            bff.ajax('index.php?s=bbs&ev=items_complaints&act=view', {id: id}, function(data){
                if(data) {
                    $(extra).attr('disabled','disabled');
                    $counter.html( (count>0 ? count-1 : '') );
                }
            }, '#progress-bbs-items-complaints');
        } break;
    }
    
    return false;
}
{/literal}
</script>

<div class="actionBar">
    <form action="" name="filter">
        <input type="hidden" name="s" value="{$class}"/>
        <input type="hidden" name="ev" value="{$event}"/>
        <div class="left">
            <label for="filter_id_title" class="placeholder">ID объявления</label>
            <input type="text" name="item" style="width:170px;" id="filter_id_title" placeholder="ID объявления" value="{if $aData.item>0}{$aData.item}{/if}" />
            <input type="submit" class="button submit" value="найти" />                                                                   
            по: <select name="perpage" onchange="document.forms.filter.submit();" style="margin-right:10px;">{$aData.perpage}</select>
        </div>
        <div class="right" style="margin:5px 0 0 5px;">
        </div>
        <div class="clear-all"></div>
    </form>
</div>

<table class="admtbl tblhover" id="bbs_items_complaints_listing">
<tr class="header nordrag nodrop">
    <th width="105">Объявление</th>
    <th align="left">Жалоба<span id="progress-bbs-items-complaints" style="display:none;" class="progress"></span></th>
    <th width="42">Просм.</th>
    <th width="110">Дата<div class="order-desc"></div></th>
    <th width="135">Действие</th>
</tr>
{foreach from=$aData.complaints key=k item=v}
    <tr>
        <td><a target="_blank" class="but linkout" href="/item/{$v.item_id}"></a><a class="itemlink" href="index.php?s=bbs&ev=ajax&act=item-info&rec={$v.item_id}"># {$v.item_id}</a>&nbsp;<a href="index.php?s=bbs&ev=items_edit&rec={$v.item_id}" class="edit_s disabled"></a></td>
        <td align="left" style="padding-right:5px;">{$v.reasons_text}{if $v.other}{if $v.reasons!=32}<br/><span class="desc">Другое: </span>{/if}{$v.comment|nl2br|truncate:200}{/if}</td>
        <td><input type="checkbox" {if !$v.viewed}onclick="bbsItemClaimAct({$v.id}, 'view', this);"{else}disabled checked{/if} /></td>
        <td>{$v.created|date_format2:true}</td>
        <td>  
            {if $v.user_id>0}<a class="but user userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec={$v.user_id}"></a>{else}<a href="javascript:void(0);" class="but user disabled" title="{$v.ip}"></a>{/if}
            <a class="but del" href="#" onclick="return bbsItemClaimAct({$v.id}, 'del', this);"></a>
        </td>
    </tr>
{foreachelse}
    <tr class="norecords"><td colspan="5">жалоб не найдено</td></tr>
{/foreach}
</table>

{$pagenation_template}
