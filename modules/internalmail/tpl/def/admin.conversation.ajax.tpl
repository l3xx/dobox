                                                
    {foreach from=$aData.mess item=m}
    <tr>
        <td width="20" align="center" style="vertical-align:top;"><img width="4" height="11" border="0" alt="" src="{$theme_url}/img/admin/triangle_{if $m.my}gray{else}red{/if}.gif"></td>
        <td style="padding-right: 20px; vertical-align:top;" {if !$m.my}class="from"{/if}>
            <strong>{if $m.my}{$aData.uname}{else}{$aData.i.name}{/if} </strong>{$m.created|date_format2:true}:<br />
            {$m.message}
            {attach file=$m.attach url=$smarty.const.INTERNALMAIL_ATTACH_URL scv=true}
        </td> 
    </tr>
    {/foreach} 
