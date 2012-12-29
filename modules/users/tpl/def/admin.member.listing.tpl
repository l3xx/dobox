
<script type="text/javascript">
{literal}
//<![CDATA[ 
function uOnCity(sel, progress)
{
    if(sel.value == -1){
        //show all cities
        bff.ajax('index.php?s=sites&ev=ajax&act=city-list&pos=list',{},function(data){
            $(sel).html(data); 
        }, progress);                           
    }
}
     
//]]> 
{/literal}
</script>

<form action="" method="get" name="filters">
<input type="hidden" name="s" value="users" />
<input type="hidden" name="ev" value="listing" />
<input type="hidden" name="order" value="{$order_by},{$order_dir}" /> 
<input type="hidden" name="page" value="1" />                       
<div class="actionBar">                                                                                          
    статус: {html_options options=$aData.status_options style='width:117px;' name='status' selected=$aData.status} 
    <input type="submit" class="button submit" value="Найти" />
    <div class="right">
        <span style="margin-right: 5px; display: none;" id="progress-users" class="progress"></span>
    </div>
</div>                                             
</form>

<table class="admtbl tblhover">
<tr class="header">
    <th width="60">
        {if $order_by=='user_id'}
            <a href="index.php?s=users&amp;ev=listing&amp;order=user_id,{$order_dir_needed}&amp;page=1">ID
            <div class="order-{$order_dir}"></div></a> 
        {else}
            <a href="index.php?s=users&amp;ev=listing&amp;order=user_id,desc&amp;page=1">ID</a>
        {/if}
    </th>
    <th width="165" align="left">
        {if $order_by=='login'}
            <a href="index.php?s=users&amp;ev=listing&amp;order=login,{$order_dir_needed}&amp;page=1">Email
            <div class="order-{$order_dir}"></div></a> 
        {else}
            <a href="index.php?s=users&amp;ev=listing&amp;order=login,asc&amp;page=1">Email</a>
        {/if}
    </th>     
    <th align="left">ФИО</th> 
    <th width="100">
        {if $order_by=='items'}
            <a href="index.php?s=users&amp;ev=listing&amp;order=items,{$order_dir_needed}&amp;page=1">Объявлений
            <div class="order-{$order_dir}"></div></a> 
        {else}
            <a href="index.php?s=users&amp;ev=listing&amp;order=items,desc&amp;page=1">Объявлений</a>
        {/if}
    </th>  
    <th width="125">
        {if $order_by=='login_ts'}
            <a href="index.php?s=users&amp;ev=listing&amp;order=login_ts,{$order_dir_needed}&amp;page=1">Был
            <div class="order-{$order_dir}"></div></a> 
        {else}
            <a href="index.php?s=users&amp;ev=listing&amp;order=login_ts,asc&amp;page=1">Был</a>
        {/if}
    </th>   
    <th width="85">Действие</th>
</tr>
{foreach from=$aData.users item=v key=k}
<tr class="row{$k%2}">
    <td class="small">{$v.user_id}</td>
    <td align="left">
        <a class="social-small social-small-{$v.social}" target="_blank" href="{$v.social_link}">&nbsp;</a>
        <span{if $v.admin} class="admin"{/if}><a href="mailto:{$v.login}">{$v.login}</a></span>
    </td>
    <td align="left">{$v.name}</td>                           
    <td><a href="index.php?s=bbs&ev=items_listing&uid={$v.user_id}">{$v.items}</a></td>
    <td>{$v.created|date_format:"%d.%m.%Y <span class=\"desc\">%H:%M</span>"}</td>
    <td>
        <a id="u{$v.user_id}" class="but {if !$v.blocked}un{/if}block userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec={$v.user_id}"></a>
        <a class="but edit" title="Редактировать" href="index.php?s=users&amp;ev=member_edit&amp;rec={$v.user_id}&amp;tuid={$v.tuid}&amp;members=1"></a> 
        {* <a class="but del" title="Удалить" href="#" onclick="return bff.redirect('index.php?s=users&amp;ev=user_action&amp;type=delete&amp;rec={$v.user_id}&amp;tuid={$v.tuid}&amp;member=1', 'Удалить пользователя?');" ></a> *}
    </td>
</tr>
{foreachelse}
<tr class="norecords">
    <td colspan="7">нет пользователей</td>
</tr>
{/foreach}
</table>
{$pagenation_template}
