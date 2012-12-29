<form action="" method="post">
	<input type="hidden" name="all" id="send_to_all" value="0" />
	<table cellpadding="0" cellspacing="0" class="admtbl">
		<tr>
			<td class="row1">Отправитель:</td>
			<td class="row2"><b>Главный администратор [{$user_info.login}]</b></td>
		</tr>
		<tr>                       
			<td class="row1">Текст сообщения:</td>
			<td class="row2"><textarea style="width:550px; height:100px" name="message">{$message|default:''}</textarea></td>
		</tr>
        <tr>
		<td colspan="2" class="row1" align="left">
			<input type="submit" class="admButton" value="Отправить" />
            <input type="submit" class="admButton" value="Отправить всем" onclick="javascript: document.getElementById('send_to_all').value='1'; " />
		</td>
        </tr>	
	</table>

	<br>
	<br>
	
	<table class="admtbl" id="receiversTable">
	<tr align="center">
		<th width="110">Получатель</th>
        <th width="50">ID</th>
        <th>Логин</th>  
	    <th>E-mail</th>
	    <th>Тип пользователя</th> 
	</tr>
	{foreach from=$aData item=v key=k}
	<tr align="center">
	    <td class="row{$k%2} alignCenter"><input name="recipients[]" value="{$v.id}" type="checkbox" /></td>
        <td class="row{$k%2} alignCenter">{$v.id|string_format:'%03d'}</td>
	    <td class="row{$k%2}">{$v.login}</td>
	    <td class="row{$k%2}"><a href="mailto:{$v.email}">{$v.email}</a></td>
	    <td class="row{$k%2} alignCenter">{$v.group_title}</td>  
	</tr>
	{/foreach}
    {if $aData|@count>0 && $pgFromTo}
    <tr>
        <td colspan="5">
           <br />
           {$pagenation_template}   
        </td>
    </tr>
    {/if}
    
	</table>

</form>