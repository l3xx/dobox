
<div style="margin-top:7px;">
    <table>
        <tr>
            <td align="left">
            {if $pgPrev|default:''}<a href="{$pgPrev}" id="previous_page">Назад</a>{else}Назад{/if}&nbsp;&nbsp;[<span class="black bold">{$pgFromTo} из {$pgTotalCount}</span>]&nbsp;&nbsp;{if $pgNext|default:''}<a href="{$pgNext}" id="next_page">Вперёд</a>{else}Вперёд{/if}
            </td>
            <td>
                {if $pagenation|@count>1}
                <div style="margin-left:20px;">
                    {if $pgsPrev|default:''}<a href="{$pgsPrev}">&lt;&lt;</a>{/if}
                    {foreach from=$pagenation name=pagenation item=v key=k}
                        {if $v.active}<b>{$v.page}&nbsp;</b>
                        {else}<a href="{$v.link}">{$v.page}</a>&nbsp;{/if}
                    {/foreach}
                    {if $pgsNext|default:''}<a href="{$pgsNext}">&gt;&gt;</a>{/if}  
                </div>
                {/if}
            </td>
        </tr>                                                 
    </table>
</div>