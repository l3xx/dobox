<script type="text/javascript">
var bbsPropFilter = '&f={$aData.f|escape:"url"}';
{literal}
function bbsItemAct(id, act, extra)
{
    switch(act)
    {                                                  
        case 'edit':   { bff.redirect( 'index.php?s=bbs&ev=items_edit&rec='+id+bbsPropFilter); } break;
        case 'comm':   { bff.redirect( 'index.php?s=bbs&ev=items_comments&rec='+id+bbsPropFilter); } break;
        case 'claims': { bff.redirect( 'index.php?s=bbs&ev=items_complaints&item='+id); } break;
        case 'press':  { 
            bff.ajax('index.php?s=bbs&ev=items_listing&act=press&rec='+id, {}, function(data){
                $(extra).attr('disabled', 'disabled');
                }, '#progress-bbs-items');
                return false;    
            } break;
        case 'del':  { 
            bff.ajaxDelete('sure', id, 'index.php?s=bbs&ev=items_listing&act=delete', extra, 
                {progress: '#progress-bbs-items'});
                return false;    
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
        <input type="hidden" name="uid" value="{$aData.uid}"/>
        <div class="left">
            <label for="filter_id_title" class="placeholder">ID или Описание</label>
            <input type="text" name="search" style="width:170px;" id="filter_id_title" placeholder="ID или Описание" value="{$aData.search}" />
            <input type="submit" class="button submit" value="найти" />
            <select name="cat_id" onchange="document.forms.filter.submit();" style="margin:0 10px; width:240px;">{$aData.cats}</select>
            <select name="svc" onchange="document.forms.filter.submit();" style="margin-right: 10px;">{$aData.svcs}</select>
            по: <select name="perpage" onchange="document.forms.filter.submit();" style="margin-right:10px;">{$aData.perpage}</select>
            {if $aData.uid>0}<div style="padding: 7px 0;">Объявления пользователя: <a class="ajax userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec={$aData.uid}">{$aData.uinfo.name} ({$aData.uinfo.email})</a>&nbsp;<a href="#" onclick="document.forms.filter.uid.value = 0; document.forms.filter.submit(); return false;" class="del_s"></a></div>{/if}
        </div>
        <div class="right" style="margin:5px 0 0 5px;">
            <a href="index.php?s={$class}&amp;ev=items_add{if $aData.cat_id>0}&amp;cat_id={$aData.cat_id}{/if}" class="hidden">+ добавить объявление</a>
        </div>
        <div class="clear-all"></div>
    </form>
</div>

<table class="admtbl tblhover" id="bbs_items_listing">
<tr class="header nordrag nodrop">
    <th width="60">ID</th>
    <th align="left">Объявление<span id="progress-bbs-items" style="display:none;" class="progress"></span></th>
    <th width="70" align="left">Цена</th>
    {if $aData.press}<th width="65">В прессе</th>{/if}
    <th width="135">Действие</th>
</tr>
{foreach from=$aData.items key=k item=v}
    <tr id="i_list_{$v.id}">
        <td><a href="/item/{$v.id}" target="_blank">{$v.id}</a></td>
        <td align="left" style="padding-right:5px;">{$v.descr|truncate:200}{if $v.claims}<br/><a href="#" class="clr-error" onclick="return bbsItemAct({$v.id}, 'claims');">есть жалобы</a>{/if}{if $v.status == $smarty.const.BBS_STATUS_BLOCKED}<br/><span class="clr-error">из черного списка</span>{/if}</td>
        <td align="left">{$v.price} {$curr_sign}</td>  
        {if $aData.press}<td><input type="checkbox" {if $v.press!=$smarty.const.BBS_PRESS_PUBLICATED}onclick="bbsItemAct({$v.id}, 'press', this);"{else}disabled checked{/if} /></td>{/if}
        <td>  
            <a class="but {if $v.status == $smarty.const.BBS_STATUS_PUBLICATED}un{/if}block itemlink" href="index.php?s=bbs&ev=ajax&act=item-info&rec={$v.id}&iltype={if $aData.mod}mod{elseif $aData.press}press{else}1{/if}" onclick="return false; return bbsItemAct({$v.id}, 'publicate', this);"></a>
            {if $v.user_id>0}<a class="but user userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec={$v.user_id}"></a>{else}<a href="javascript:void(0);" class="but user disabled"></a>{/if}
            <a class="but edit" href="#" onclick="return bbsItemAct({$v.id}, 'edit');"></a>
            <a class="but comments" href="#" onclick="return bbsItemAct({$v.id}, 'comm');"></a>
            <a class="but del" href="#" onclick="return bbsItemAct({$v.id}, 'del', this);"></a>
        </td>
    </tr>
{foreachelse}
    <tr class="norecords">
        <td colspan="4">
            объявлений не найдено
        </td>
    </tr>
{/foreach}
</table>
{$pagenation_template}
