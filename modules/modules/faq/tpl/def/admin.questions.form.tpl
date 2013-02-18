
<form action="" name="clientQuestionsForm" method="post">   
<table cellpadding="0" cellspacing="0" class="admtbl tbledit">
<tr class="required check-select">
    <td class="row1 field-title" width="80">Категория:</td>
    <td class="row2">
    <div class="left"> 
       <select name="category" id="category" style="width:198px;">
             {$aData.category_options|default:''} 
       </select> 
    </div>
    <div class="right desc">
        {if $aData.edit && $aData.modified!='0000-00-00 00:00:00'}
            последние изменения: {$aData.modified|date_format2:true}
        {/if}
    </div>
    </td>
</tr>
<tr class="required">
	<td class="row1 field-title">Вопрос:</td>
	<td class="row2">
        <input type="text" id="question" name="question" class="stretch" value="{$aData.question|default:''}" />
    </td>
</tr>
<tr>
    <td class="row1 field-title">Ответ:</td>
    <td class="row2">{$aData.answer|default:''|wysiwyg:'answer':'100%':400}</td>
</tr>
<tr>
    <td class="row1 field-title">Выводить в подсказках:</td>
    <td class="row2">
        <label><input type="checkbox" name="pos_add" {if $aData.pos_add}checked="checked"{/if} /> разместить объявление</label><br/>
        <label><input type="checkbox" name="pos_up" {if $aData.pos_up}checked="checked"{/if} /> продвинуть объявление</label>
    </td>
</tr>
<tr class="footer">
    <td colspan="2">
        <input type="submit" class="button submit" value="Сохранить" />
        <input type="button" class="button cancel" value="К списку вопросов" onclick="bff.redirect('index.php?s={$class}&ev=questions_listing');" />
    </td>
</tr>

</table>
</form>
                                                                                             
<script type="text/javascript">
{literal}
//<![CDATA[        
    var fchecker = new bff.formChecker( document.forms.clientQuestionsForm, {}, function(){
         document.forms.clientQuestionsForm['category'].focus();
    } ); 
//]]>
{/literal}
</script> 