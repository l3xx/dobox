<form method="post" action="" name="moduleForm">
<table class="admtbl tbledit">
<tr>
	<td class="row1 field-title" style="width:150px;">Название модуля:</td>
	<td class="row2"><input type="text" name="title" id="title" size="50" value="{$aData.title|default:''}" /></td>
</tr>
<tr>
    <td class="row1 field-title">Языки:</td>
    <td class="row2"><input type="text" name="languages" size="50" value="{$aData.languages|default:''}" /> <span class="desc">&nbsp;&nbsp;формат: [def,ru,en]</span></td>
</tr>
<tr class="footer">
	<td colspan="2" class="row1">
        <input type="submit" class="button submit" value="Создать" />
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
var aModules = new Array('{$aData.modules|@implode:"','"}');
{literal}
//<![CDATA[

function indexOf(ar, item, i) {
  i || (i = 0);
  var length = ar.length;
  if (i < 0) i = length + i;
  for (; i < length; i++)
    if (ar[i] === item) return i;
  return -1;
}

$(function(){ 
    $('#title').bind('keyup', function(){ 
        if(indexOf(aModules, this.value)!=-1)
            this.value = '';
    }).focus();
});
//]]> 
{/literal}
</script>