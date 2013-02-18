
<script type="text/javascript">  
{literal}
//<![CDATA[  
    var contacts = {
        del: function(id, link)
        {
            bff.ajaxDelete('Удалить?', id, 'index.php?s=contacts&ev=ajax&act=del', link,
                {progress: '#progress-contacts'});
            return false;            
        }
    };
    
    var pgn = new bff.pgn('#pagenation', {type:'prev-next'});     
//]]>   
{/literal}
</script>
<table class="admtbl tblhover" id="contacts-tbl">

{if $aData.type == $smarty.const.CONTACTS_TYPE_CONTACT} 

    <tr class="header">           
        <th width="140" align="left" style="padding-left:10px;">Отправитель</th> 
        <th width="115" align="left">Телефон</th> 
        <th align="left">Сообщение</th>
        <th width="76">Дата <div class="order-desc"></div></th>
    </tr>              
    {foreach from=$aData.contacts item=v key=k}
    <tr class="row{$k%2}" id="c{$v.id}">
            <td style="vertical-align: top;" align="left">
                {if $v.user_id}{userlink name=$v.uname id=$v.user_id login=$v.ulogin admin=$v.uadmin}{else}<a href="mailto:{$v.email}">{$v.email}</a>{/if}
            </td>
            <td align="left">{$v.phone}</td>
            <td align="left">{$v.message}</td>
            <td class="alignCenter small" style="vertical-align:top;">
                <div style="margin-top:3px;">
                    <div class="left{if !$v.viewed} clr-error{/if}">{$v.created|date_format:'%d.%m.%Y <br /><span class="description">%H:%M:%S</span>'}</div>
                    <a class="but del right" style="margin:0px;" title="Удалить" href="#" onclick="return contacts.del('{$v.id}', this);" ></a>
                </div>
            </td>
    </tr>
    {foreachelse}
    <tr class="norecords">
        <td colspan="3">нет сообщений</td>
    </tr>
    {/foreach}
{/if}

</table>

<form action="index.php" method="get" name="pagenation" id="pagenation">
    <input type="hidden" name="s" value="{$class}" />
    <input type="hidden" name="ev" value="{$event}" />
    <input type="hidden" name="type" value="{$aData.type}" />                                      
    <input type="hidden" name="offset" value="{$aData.offset}" />  
</form> 
<div class="pagenation">
    {$aData.pgn}
</div>

