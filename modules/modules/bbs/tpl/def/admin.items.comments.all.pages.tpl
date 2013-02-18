
    <div style="margin-top:7px;" id="pagenation">
        <table>
            <tr>
                <td align="left" colspan="2">
                {if $aData.prev || $aData.offset>0}<a href="index.php?s=bbs&amp;ev=items_comments_all&offset={$aData.prev}" id="pagenation_prev">&larr; Назад</a>{else}<span class="desc">&larr; Назад</span>{/if}
                <span class="desc">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                {if $aData.next}<a href="index.php?s=bbs&amp;ev=items_comments_all&offset={$aData.next}" id="pagenation_next">Вперёд  &rarr;</a>{else}<span class="desc">Вперёд  &rarr;</span>{/if}
                </td>
            </tr>                                                 
        </table>
    </div>
