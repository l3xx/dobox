<table class="admtbl tblhover">
    <tr class="header">
        <th width="300">Путь</th>
        <th>Доступ</th>
    </tr>
    {foreach from=$aData.dirs name=dirs key=k item=v}
        <tr class="row{$smarty.foreach.dirs.iteration%2}">
            <td>{$v.path}</td>
            <td>
                {if $v.access == 0}<span class="clr-success">OK</span>
                {elseif $v.access == 1}<span class="clr-error">Не существует</span>
                {elseif $v.access == 2}<span class="clr-error">Нет прав на запись</span>
                {/if}
            </td>
        </tr>
    {/foreach} 
</table>  