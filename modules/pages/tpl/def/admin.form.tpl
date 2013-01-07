<form method="post" action="" name="pagesForm"> 
<table class="admtbl tbledit">
<tr class="required">
	<td class="row1" style="width:135px;"><span class="field-title">Заголовок</span>:</td>
	<td class="row2">
         <input class="stretch" type="text" name="title" value="{$aData.title}" />
    </td>
</tr>
<tr class="required">
	<td class="row1"><span class="field-title">Имя файла</span>:</td>
	<td class="row2">
    {if $event=='edit'}
        <div class="bold" style="height:25px;">{$aData.filename}{$smarty.const.PAGES_EXTENSION}</div> 
    {else}  
        <input type="text" name="filename" value="{$aData.filename}" maxlength="30" class="text-field" />&nbsp;<b>{$smarty.const.PAGES_EXTENSION}</b>
    {/if}
    </td>
</tr>
<tr>                                                                   
	<td class="row1"><span class="field-title">Содержание</span>:</td>
	<td class="row2">{$aData.content|default:''|wysiwyg:'content':'100%':300}</td>
</tr>
<tr>
	<td class="row1"><span class="field-title">Meta Keywords</span>:<br /><span class="desc">{$aLang.meta_keywords}</span></td>
	<td class="row2"><textarea name="mkeywords" id="mkeywords" onkeyup="bff.textLimit('mkeywords', 255);" onkeydown="bff.textLimit('mkeywords', 255);" style="height: 85px;">{$aData.mkeywords|default:''}</textarea></td>
</tr>
<tr>
	<td class="row1"><span class="field-title">Meta Description</span>:<br /><span class="desc">{$aLang.meta_description}</span></td>
	<td class="row2"><textarea name="mdescription" id="mdescription" onkeyup="bff.textLimit('mdescription', 255);" onkeydown="bff.textLimit('mdescription', 255);" style="height: 85px;">{$aData.mdescription|default:''}</textarea></td>
</tr>
<tr class="footer">
	<td colspan="2" class="row1">
        <input type="submit" class="button submit" value="Сохранить" />
        {if $event=='edit' && !$aData.issystem}
        <input type="button" class="button delete" value="Удалить" onclick="bff.redirect('index.php?s={$class}&amp;ev=action&amp;type=delete&amp;rec={$aData.id}', 'Удалить страницу?');" />
        {/if}
        <input type="button" class="button cancel" value="К списку страниц" onclick="bff.redirect('index.php?s={$class}&ev=listing');" />
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
{literal}
//<![CDATA[ 
var helper = new bff.formChecker( document.forms.pagesForm, {}, 
                function(){ document.forms.pagesForm['title'].focus();  } );     
//]]> 
{/literal}
</script>