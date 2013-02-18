<script type="text/javascript">
var itemID = {$aData.id|default:0};
{literal}
//<![CDATA[ 
    function bbsItemAct(act, extra) {  
        return false;    
    }
//]]> 
{/literal}
</script>

<form method="post" action="" name="modifyInfo" enctype="multipart/form-data">
<input type="hidden" name="action" value="info" />
<input type="hidden" name="rec" value="{$aData.id|default:0}" />
<table class="admtbl tbledit">
<tr>
    <td class="row1" width="90">Категория:</td>
    <td class="row2"><select name="cat_id">{$aData.cat_id_options}</select></td>
</tr>
{if $bbs->url_keywords}
<tr>
    <td class="row1"><span class="field-title">Keyword</span>:</td>   
    <td class="row2">                   
        <div class="left relative" style="width:100%">
            <label for="keyword" class="placeholder">keyword для URL</label>  
            <input class="stretch" type="text" maxlength="90" name="keyword" id="keyword" placeholder="keyword для URL" value="{$aData.keyword}" />
        </div> 
        <div class="clear-all"></div>
    </td>
</tr>
{/if}
<tr>
    <td class="row1"><span class="field-title">Цена</span>:</td>
    <td class="row2"><input type="text" maxlength="9" name="price" value="{$aData.price|default:'0'}" style="width:150px;" /> {$bbs->items_currency.short}</td>
</tr>
<tr>
    <td class="row1"><span class="field-title">Описание</span>: </td>
    <td class="row2"><textarea name="descr" style="height: 110px;" class="stretch">{$aData.descr}</textarea></td>
</tr>
{if !$aData.edit && $bbs->items_images}
<tr>
    <td class="row1">Изображения:</td>
    <td class="row2">
        {section name=fileinputs loop=$bbs->items_images_limit}
            <input type="file" name="image{$smarty.section.fileinputs.index}" size="30" value="" /><br />
        {/section}
        <span class="desc">Размер одного файла не должен превышать <b>{$bbs->items_images_maxsize|filesize}</b></span>
    </td>
</tr>
{/if}
<tr>
    <td class="row1"><span class="field-title">Meta Keywords</span>:</td>
    <td class="row2"><textarea name="mkeywords" style="height: 110px;" class="stretch">{$aData.mkeywords}</textarea></td>
</tr>
<tr>
    <td class="row1"><span class="field-title">Meta Description</span>:</td>
    <td class="row2"><textarea name="mdescription" style="height: 110px;" class="stretch">{$aData.mdescription}</textarea></td>
</tr>
<tr>
    <td class="row1" colspan="2">
        <input type="submit" class="button submit" value="{$aLang.save}" />
        <input type="button" class="button cancel" value="{$aLang.cancel}" onclick="history.back();" />
    </td>
</tr>
</table>
</form>

{if $aData.edit && $bbs->items_images}
<br/><hr class="cut"/><br/>       
<form method="post" action="" name="img" enctype="multipart/form-data">
<input type="hidden" name="action" value="image_add" />
<table class="admtbl tbledit">
<tr class="row1">
    <th colspan="2" align="center">изображения</th> 
</tr>
<tr class="row1">
    <td colspan="2">
        <div>
           {foreach from=$aData.img item=v key=k}
               {if $v}
                <div style="width:20%; float:left; text-align:center; padding-bottom:10px;">
                    <a href="javascript:$.fancybox('/files/images/items/{$aData.id}{$v}', {ldelim}type:'image'{rdelim});">
                        <img src="/files/images/items/{$aData.id}s{$v}" />
                    </a>
                    <div style="width:60px; margin: 0 auto;">
                        <a class="but del" href="index.php?s={$class}&amp;ev={$event}&amp;action=image_del&amp;rec={$aData.id}&amp;image={$v}&amp;f={$aData.f}" onclick="{$aLang.delete_confirm}" title="{$aLang.delete}"></a>
                        <a class="but {if $v!=$aData.imgfav}un{/if}fav" href="index.php?s={$class}&amp;ev={$event}&amp;action=image_fav&amp;rec={$aData.id}&amp;image={$v}&amp;f={$aData.f}" title="сделать эскизом"></a>
                    </div>
                </div>
                {/if}
           {/foreach} 
        </div>
        <div class="clear"></div>                         
        {math assign='fileinputs_count' equation="x-y" x=$bbs->items_images_limit y=$aData.imgcnt}
        {section name=fileinputs loop=$fileinputs_count}
            <input type="file" name="image{$smarty.section.fileinputs.index}" size="30" value="" /><br />
        {/section}
        {if $fileinputs_count|default:'0' > 0}
            Размер одного файла не должен превышать {$bbs->items_images_maxsize|filesize}<br /><br />
            <input type="submit" class="button submit" value="Загрузить изображения" />
        {/if} 
            <input type="button" class="button delete" value="Удалить все изображения" onclick="if(confirm('Удалить все изображения?')) document.location='index.php?s={$class}&amp;ev={$event}&amp;action=image_del_all&amp;rec={$aData.id}&amp;f={$aData.f}';" />
    </td>
</tr>
</table>
</form>
{/if}