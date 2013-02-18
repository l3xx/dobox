<script type="text/javascript" language="JavaScript">
{literal}
function MoveAll(source_id, destination_id)
{
	source = document.getElementById(source_id);
	destination = 	document.getElementById(destination_id);
	for(i=0; i<source.options.length; i++)
	{
		var opt;    
		var val = source.options[i].value;
			if(val=="")
				val = source.options[i].text;
			opt  = new  Option(source.options[i].text, val, false);
		
		destination.options.add(opt);
	}
	source.options.length =0;	
	sortSelect(source_id, true);
	sortSelect(destination_id, true);
}

function MoveSelect(source_id, destination_id)
{
	source = document.getElementById(source_id);
	destination = 	document.getElementById(destination_id);
	for(i=source.options.length-1; i>=0; i--)
	{
		if(source.options[i].selected==true)
		{
			var opt;  
			var val = source.options[i].value;
			if(val=="")
				val = source.options[i].text;
			opt  = new  Option(source.options[i].text, val, false);
			destination.options.add(opt);
			source.options[i] = null;
		}
	}
	sortSelect(source_id, true);
	sortSelect(destination_id, true);
	
}

function isEmail(item) {
var at="@";
var dot=".";
var lat=item.indexOf(at);
var litem=item.length;
var ldot=item.indexOf(dot);

   var reg= new RegExp ("^[0-9a-z_]+@[0-9a-z_^\\.]+\\.[a-z]{2,6}$", 'i');
   if (!reg.test(item)) {
           return false;
   }
if (item.indexOf(at)==-1) return false;
if (item.indexOf(at)==-1 || item.indexOf(at)==0 || item.indexOf(at)==litem) return false;
if (item.indexOf(dot)==-1 || item.indexOf(dot)==0 || item.indexOf(dot) >= litem - 2) return false;
if (item.indexOf(at,(lat+1))!=-1) return false;
if (item.substring(lat-1,lat)==dot || item.substring(lat+1,lat+2)==dot) return false;
if (item.indexOf(dot,(lat+2))==-1) return false;
if (item.indexOf(" ")!=-1)  return false;
 return true;
}

  // sort function - ascending (case-insensitive)
        function sortFuncAsc(record1, record2) {
            var value1 = record1.optText.toLowerCase();
            var value2 = record2.optText.toLowerCase();
            if (value1 > value2) return(1);
            if (value1 < value2) return(-1);
            return(0);
        }

        // sort function - descending (case-insensitive)
        function sortFuncDesc(record1, record2) {
            var value1 = record1.optText.toLowerCase();
            var value2 = record2.optText.toLowerCase();
            if (value1 > value2) return(-1);
            if (value1 < value2) return(1);
            return(0);
        }

        function sortSelect(selectToSort_id, ascendingOrder) {
            selectToSort = document.getElementById(selectToSort_id);

            if (arguments.length == 1) ascendingOrder = true;    // default to ascending sort

            // copy options into an array
            var myOptions = [];
            for (var loop=0; loop<selectToSort.options.length; loop++) {
                myOptions[loop] = { optText:selectToSort.options[loop].text, optValue:selectToSort.options[loop].value };
            }

            // sort array
            if (ascendingOrder) {
                myOptions.sort(sortFuncAsc);
            } else {
                myOptions.sort(sortFuncDesc);
            }

            // copy sorted options from array back to select box
            selectToSort.options.length = 0;
            for (var loop=0; loop<myOptions.length; loop++) {
                var optObj = document.createElement('option');
                optObj.text = myOptions[loop].optText;
                optObj.value = myOptions[loop].optValue;
                selectToSort.options.add(optObj);
            }
        }

function addtosel(input_id, select_id)
{
	val = document.getElementById(input_id).value;	
	if(val=="") return;
	if(!isEmail(val)) 
	{
		alert('Укажите корректный email!');
		return;
	}
	opt  = new  Option( val, val, false);
	document.getElementById(select_id).options.add(opt);
	document.getElementById(input_id).value = '';
}

function SelectAll(sel_id)
{
	sel = document.getElementById(sel_id);
	for(i=0; i<sel.options.length; i++)
	{
		sel.options[i].selected = true;
	}
	
}
function ShowDiv(div_id)
{
	document.getElementById(div_id).style.display='block';
}

function HideDiv(div_id)
{
	document.getElementById(div_id).style.display='none';
}

{/literal}

</script>

<form method="post" action="" enctype="multipart/form-data">
<table class="admtbl">
<tr>
	<th colspan="2">Рассылка писем</th>
</tr>
<tr class="row2">
	<td colspan="2" align="center">
	    <table>
	        <tr>
	            <td style="border:0;">
                    <center><strong>Существующие</strong></center>
	                <select multiple name="exists_users" id="exists_users" style="width:320px; height:300px;">{$exists_values}</select><br /><br />
	                <div id="divlinkadd">[<a href="#" onclick="ShowDiv('divadd_value');HideDiv('divlinkadd');">добавить новый email</a>]</div>
	                <div style="display:none;" id="divadd_value">
		                <input type="text" name="add_value" style="width:180px;" id="add_value" value="" />
                        <input type="button" class="admButton" value="добавить" onclick="addtosel('add_value', 'exists_users' );HideDiv('divadd_value');ShowDiv('divlinkadd');" />
                        <input type="button" class="admButton" value="отмена" onclick="HideDiv('divadd_value');ShowDiv('divlinkadd');" />
		            </div>
	            </td>
	            <td align="center" valign="middle" style="border:0;"><br />
	                <input type="button" value="&gt;&gt;" class="admButton" style="width: 35px;" onclick="MoveAll('exists_users', 'user_id')" /><br />
	                <input type="button" value="&gt;"     class="admButton" style="width: 35px;" onclick="MoveSelect('exists_users', 'user_id')"  /><br />
	                <input type="button" value="&lt;"     class="admButton" style="width: 35px;" onclick="MoveSelect('user_id', 'exists_users')" /><br />
	                <input type="button" value="&lt;&lt;" class="admButton" style="width: 35px;" onclick="MoveAll('user_id', 'exists_users')" /><br />
	            </td>
	            <td style="border:0;">
                    <center><strong>Адресат</strong></center>
                    <select multiple name="user_id[]" id="user_id" style="width:320px; height:300px;">{$sendtousers_options}</select>
                </td>
	        </tr>
	    </table>
	</td>
</tr>
<tr class="row1">
    <td>От кого:</td>
    <td><span style="font-weight:bold;">{$aData.from|default:''}</span></td>
</tr>
<tr class="row1">
    <td>Тема:</td>
	<td><input type="text" name="subject" value="{$aData.subject|default:''}" class="input" style="width:600px;"/></td>
</tr>
<tr class="row2">
	<td>Сообщение:</td>
	<td><textarea name="body" cols="0" rows="0" style="width:600px; height:300px">{$aData.body|default:''}</textarea></td>
</tr>
<tr class="row1">
	<td colspan="2" align="center">
		<input type="submit" value="Отправить" name="submit" class="btn admButton" onclick="SelectAll('user_id');" />
        {if $smarty.server.REQUEST_METHOD == 'POST'}
            <input type="button" value="Отмена" name="submit" class="btn admButton" onclick="bff.redirect('index.php?s={$class}&amp;ev={$event}');" />
        {/if}
	</td>
</tr>
</table>
</form>