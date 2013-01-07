                
    <table class="admtbl tblhover" border="0">
    
    <tr class="header">
        <th style="width:65px;">Дата</th>
        <th style="width:150px;" align="left">Собеседник</th>
        <th align="left">Сообщение</th>
    </tr>
    {foreach from=$aData.messages item=v key=k}
    <tr class="row{$k%2}">
        <td class="description">{$v.created|date_format:"%d.%m.%Y"}</td>
        <td>{if $v.author == $aData.uid}<a href="#" onclick="return userinfo('{$aData.uid}');">{$aData.ulogin}</a>{else}<a href="#" onclick="return userinfo('{$aData.il.id}');">{$aData.il.login}</a>{/if}:</td>
        <td>
            <div style="overflow: hidden; width: 335px;">
            {$v.message|default:''|nl2br}
            </div>
        </td>
    </tr>
    {foreachelse}
    <tr class="footer">
        <td colspan="3" class="alignCenter">
            <span class="desc">нет сообщений</span>
        </td>
    </tr>    
    {/foreach}
    
    </table> 