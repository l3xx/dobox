
    <script type="text/javascript">  
    {literal}
    //<![CDATA[  
        function delSubscribe(id, link)
        {
            bff.ajaxDelete('Удалить подписку?', id, 'index.php?s=sites&ev=ajax&act=unsubscribe', 
                link, {progress: '#progress-subscribes'});
            return false;
        }
        
    //]]>   
    {/literal}
    </script>

    <table class="admtbl tblhover">  
        <tr class="header">
            {if $fordev}<th width="60">ID</th>{/if}
            <th align="left">
                {if $order_by=='email'}
                    <a href="index.php?s={$class}&amp;ev={$event}&amp;order=email,{$order_dir_needed}&amp;page=1">E-mail<div class="order-{$order_dir}"></div></a>
                     
                {else}
                    <a href="index.php?s={$class}&amp;ev={$event}&amp;order=email,asc&amp;page=1">E-mail</a>
                {/if}
            </th> 
            <th width="130">
                {if $order_by=='created'}
                    <a href="index.php?s={$class}&amp;ev={$event}&amp;order=created,{$order_dir_needed}&amp;page=1">Дата подписки<div class="order-{$order_dir}"></div></a> 
                {else}
                    <a href="index.php?s={$class}&amp;ev={$event}&amp;order=created,desc&amp;page=1">Дата подписки</a>
                {/if}
            </th> 
            <th width="35"><span id="progress-subscribes" style="display:none;" class="progress"></span></th>
        </tr>    
        {foreach from=$aData item=v key=k}
        <tr class="row{$k%2}">
            {if $fordev}<td width="55">{$v.id}</td>{/if}
            <td align="left">{$v.email}</td>
            <td>{$v.created|date_format:'%d.%m.%Y <span class="desc">%H:%M:%S</span>'}</td>
            <td>
                <a class="but del" title="Удалить" href="#" onclick="return delSubscribe({$v.id}, this);"></a>
            </td>
        </tr>
        {foreachelse}
        <tr class="norecords">
            <td colspan="{if $fordev}4{else}3{/if}">нет подписок</td>
        </tr>
        {/foreach}
    </table> 
    
    {$pagenation_template}