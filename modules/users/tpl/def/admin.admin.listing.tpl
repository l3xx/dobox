<script type="text/javascript">
{literal}
//<![CDATA[  
    var pgn = new bff.pgn('#filters', {type:'prev-next'});
//]]>   
{/literal}
</script>
<form action="index.php" method="get" name="filters" id="filters">
    <input type="hidden" name="s" value="users" />
    <input type="hidden" name="ev" value="{$event}" />
    <input type="hidden" name="order" value="{$order_by},{$order_dir}" />                    
    <input type="hidden" name="offset" value="{$aData.offset}" />  
</form> 

<table class="admtbl tblhover">
<tr class="header">
    <th width="50">
        {if $order_by=='user_id'}
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=user_id,{$order_dir_needed}&amp;page=1">ID
            <div class="order-{$order_dir}"></div></a> 
        {else}
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=user_id,asc&amp;page=1">ID</a>
        {/if}
    </th>
    <th>
        {if $order_by=='login'}
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=login,{$order_dir_needed}&amp;page=1">Логин
            <div class="order-{$order_dir}"></div></a> 
        {else}
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=login,asc&amp;page=1">Логин</a>
        {/if}
    </th> 
    <th>
        {if $order_by=='email'}
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=email,{$order_dir_needed}&amp;page=1">E-mail
            <div class="order-{$order_dir}"></div></a> 
        {else}
            <a href="index.php?s=users&amp;ev=admin_listing&amp;order=email,asc&amp;page=1">E-mail</a>
        {/if}
    </th>        
    <th>Принадлежность к группе</th>
    <th width="85">Действие</th>
</tr>
{foreach from=$aData.users item=v key=k}
<tr style="{if $v.deleted==1}color:#999;{/if}" class="row{$k%2}">
    <td class="small">{$v.user_id}</td>
    <td>
        {if $v.deleted}<img src="{$theme_url}/img/admin/icons/icon_del.gif" title="аккаунт удален" />{/if} 
        <a title="{if $fordev}пароль: '{$v.password}'{else}{$v.login}{/if}" class="admin" style="text-decoration:none; cursor:default;" href="#">{$v.login}</a>
    </td>
    <td><a href="mailto:{$v.email}">{$v.email}</a></td>
    <td>
        {foreach from=$v.groups item=g key=key}
            <a title="доступ группы '{$g.title}'" href="index.php?s=users&amp;ev=group_permission_listing&amp;rec={$g.group_id}" style="color:{$g.color|default:'#000'}; font-weight:bold; text-decoration:none;">
                {$g.title}</a></span>{if $key+1!=$v.groups|@count},{/if}
        {/foreach}
    </td>
    <td>
        <a class="but {if !$v.blocked}un{/if}block userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec={$v.user_id}" id="u{$v.user_id}"></a>
        <a class="but edit" title="Редактировать" href="index.php?s=users&amp;ev=mod_edit&amp;rec={$v.user_id}&amp;tuid={$v.tuid}"></a>
        <a class="but del" title="Удалить" href="#" onclick="return bff.redirect('index.php?s=users&amp;ev=user_action&amp;type=delete&amp;rec={$v.user_id}&amp;tuid={$v.tuid}', 'Удалить пользователя?');" ></a>
    </td>
</tr>
{/foreach}
</table>

<div class="pagenation">
    {$aData.pgn}
</div>

