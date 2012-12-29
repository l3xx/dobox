
<form action="" name="faqCategoriesForm" method="post">   
<table class="admtbl tbledit">
<tr class="required">
	<td class="row1" width="55"><span class="field-title">Название</span>:</td>
	<td class="row2">
        <input class="stretch" type="text" maxlength="100" name="title" value="{$aData.title|default:''}" />
    </td>
</tr>
<tr class="footer">
    <td colspan="2">
        <input type="submit" class="button submit" value="Сохранить" />
        <input type="button" class="button cancel" value="Отмена" onclick="return history.back();" />
    </td>
</tr>

</table>
</form>

<script type="text/javascript">
{literal}
//<![CDATA[ 
var helper = new bff.formChecker( document.forms.faqCategoriesForm );     
//]]> 
{/literal}
</script>