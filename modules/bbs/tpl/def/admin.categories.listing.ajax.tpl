{foreach from=$aData.cats item=v key=k}
<tr id="dnd-{$v.id}" numlevel="{$v.numlevel}" pid="{$v.pid}">
    <td style="padding-left:{math equation="x*15-10" x=$v.numlevel}px;" align="left">
        <a onclick="return bbsCatAct({$v.id},'c',$(this).parent().parent());" class="but folder{if !$v.node}_ua{/if} but-text">{$v.title}</a>
    </td>
    <td><a href="index.php?s={$class}&amp;ev=items_listing&amp;cat_id={$v.id}">{$v.items}</a></td>
    <td><a class="but status{if !$v.regions} disabled{/if}" onclick="return bbsCatAct({$v.id}, 'regions', this);" href="#"></a></td>
    <td><a class="but price{if !$v.prices} disabled{/if}" id="prc{$v.id}" onclick="return bbsCatAct({$v.id}, 'prices', this);" href="#"></a></td>
    <td>
        <a class="but {if $v.enabled}un{/if}block" href="#" onclick="return bbsCatAct({$v.id}, 'toggle', this);"></a>
        <a class="but sett" onclick="return bbsCatAct({$v.id}, 'dyn');" href="#"></a>
        <a class="but sett disabled" onclick="return bbsCatAct({$v.id}, 'type');" href="#"></a>
        <a class="but edit" href="index.php?s={$class}&amp;ev=categories_edit&amp;rec={$v.id}" title="{$aLang.edit}"></a>
        {if $v.numlevel >= $bbs->category_deep}<a href="javascript:void(0);" class="but"></a>{elseif !$v.node && !$bbs->category_mixed && $v.items}<a href="javascript:void(0);" class="but add disabled"></a>{else}
            <a class="but add" href="index.php?s={$class}&amp;ev=categories_add&amp;pid={$v.id}" title="{$aLang.add}"></a>
        {/if}
        {if $v.node}<a class="but" href="#"></a>{else}
            <a class="but del" href="#" onclick="return bbsCatAct({$v.id}, 'del', this);" title="{$aLang.delete}"></a>
        {/if}
    </td>
</tr>
{/foreach}
