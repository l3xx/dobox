
<div style="margin-top:7px;" id="pagenation">
    <table class="admtbl">
        <tr>
            <td align="center" class="desc">
                {if $pgPrev|default:''}<a href="#" onclick="{$pgPrev}; return false;" id="previous_page">&larr; Назад</a>{else}&larr; Назад{/if}
                <span style="margin:0 10px;">
                {if $pagenation|@count>1}  
                    {if $pgsPrev|default:''}<a href="#" onclick="{$pgsPrev}; return false;">&lt;&lt;</a>{/if}
                    {foreach from=$pagenation name=pagenation item=v key=k}
                        {if $v.active}<b>{$v.page}&nbsp;</b>
                        {else}<a href="#" onclick="{$v.link}; return false;">{$v.page}</a>&nbsp;{/if}
                    {/foreach}
                    {if $pgsNext|default:''}<a href="#" onclick="{$pgsNext}; return false;">&gt;&gt;</a>{/if}
                    {else}|  
                {/if} 
                </span>
                {if $pgNext|default:''}<a href="#" onclick="{$pgNext}; return false;" id="next_page">Вперёд &rarr;</a>{else}Вперёд &rarr;{/if}
            </td>
        </tr>                                                 
    </table>
</div>