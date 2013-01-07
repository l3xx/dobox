
<div style="margin-top:7px;" id="pagenation">
    <table>
        <tr>
            <td align="left">
            {if $pgPrev|default:''}<a href="#" onclick="{$pgPrev}; return false;" id="previous_page">Назад</a>{else}Назад{/if}&nbsp;&nbsp;[<span class="black bold">{$pgFromTo} из {$pgTotalCount}</span>]&nbsp;&nbsp;{if $pgNext|default:''}<a href="#" onclick="{$pgNext}; return false;" id="next_page">Вперёд</a>{else}Вперёд{/if}
            </td>
            <td>
                {if $pagenation|@count>1}
                <div style="margin-left:20px;">
                    {if $pgsPrev|default:''}<a href="#" onclick="{$pgsPrev}; return false;">&lt;&lt;</a>{/if}
                    {foreach from=$pagenation name=pagenation item=v key=k}
                        {if $v.active}<b>{$v.page}&nbsp;</b>
                        {else}<a href="#" onclick="{$v.link}; return false;">{$v.page}</a>&nbsp;{/if}
                    {/foreach}
                    {if $pgsNext|default:''}<a href="#" onclick="{$pgsNext}; return false;">&gt;&gt;</a>{/if}  
                </div>
                {/if}
            </td>
        </tr>                                                 
    </table>
</div>