<script type="text/javascript">
{literal}

var updating  = false;
var sending   = false;  
function updateList(url)
{
    if(updating)
        return;
    
    updating = true; 
    
    var params = {};
    params.clients   = ($('#s_clients').attr('checked')?1:0);
    params.employees = ($('#s_employees').attr('checked')?1:0);
    params.users     = ($('#s_users').attr('checked')?1:0);
    params.usersall  = ($('#s_usersall').attr('checked')?1:0);
    params.blocked   = ($('#s_blocked').attr('checked')?1:0);
                       
    bff.ajax(url, params, function(data){
        if(data)
        {  
            $('#exists').html(data.exists);
            $('#receivers').html('');
        }                 
        updating = false;
    }, '#progress-employees');
}

function onFilter(n, type)
{
    if(n==1)
    {
        $('#s_users').attr('checked', false);
        $('#s_usersall').attr('checked', false);
    }
    if(n==2)
    {
        $('#s_clients').attr('checked', false);
        $('#s_employees').attr('checked', false);
    }
    
    if(type == 'users')
    {
        if(!$('#s_users').attr('checked'))
            $('#s_usersall').attr('checked', false);
    } else if(type == 'usersall') {
        if($('#s_usersall').attr('checked'))
            $('#s_users').attr('checked', 'checked');
    }
}

function doSend(url)
{
    if(updating || sending)
        return false;
    
    //есть ли получатели
    if(!bff.formSelects.hasOptions('receivers'))
        return false;

    //указан ли отправитель
    var f = $('#msg-from');
    if(f.val()=='')
    {
        f.focus();
        return false;
    }
    
    //указан текст сообщения
    var b = $('#msg-body');
    if(b.val()=='')
    {
        b.focus();
        return false;
    }
    
    bff.formSelects.SelectAll('receivers');
    
    sending = true;
    bff.ajax(url, $('#massend_form').serializeArray(), function(data){
        if(data)
        {  
            //$('#exists').html(data.exists);
            //$('#receivers').html('');
        }   
        sending = false;
    });
    
    return true;
}  

$(document).ready (function() {
    $('textarea.expanding').autogrow({  
        minHeight: 150,
        lineHeight: 16
    });                                    
});



{/literal}
</script>

<form method="post" action="" id="massend_form">
<table class="admtbl">
<thead>
<tr class="row1">
    <td colspan="2">
        <fieldset style="height:90px; width:620px;" class="fieldset">
            <legend>Настройки рассылки (<a href="#" onclick="updateList('index.php?s={$class}&amp;ev=ajax&amp;action=massend-filter'); return false;">обновить</a>) 
                    <span id="progress-employees" style="display:none;"  class="progress"></span>
            </legend>
            <table border="0">
                <tr>
                    <td style="vertical-align:top;"><input type="checkbox" onclick="onFilter(1, 'clients');" checked="checked" name="clients" id="s_clients" /></td>
                    <td style="vertical-align:top;"><label for="s_clients">клиентам</label></td>
                    
                    <td style="vertical-align:top;"><input type="checkbox" name="blocked" checked="checked" id="s_blocked" /></td>
                    <td style="vertical-align:top; width:290px;" rowspan="2"><label for="s_blocked">исключая заблокированные аккаунты<br /><b>(клиентов, сотрудников, пользователей)</b></label></td>
                </tr>
                <tr>
                    <td style="vertical-align:top;"><input type="checkbox" onclick="onFilter(1, 'employees');" name="employees" id="s_employees" /></td>
                    <td style="vertical-align:top;"><label for="s_employees">сотрудникам</label></td>
                </tr>
                <tr>
                    <td style="vertical-align:top;"><input type="checkbox" onclick="onFilter(2, 'users');" name="users" id="s_users" /></td>
                    <td style="vertical-align:top;"><label for="s_users">пользователям (подписавшимся на рассылку)</label></td>

                    <td style="vertical-align:top;"><input type="checkbox" onclick="onFilter(2, 'usersall');" name="usersall" id="s_usersall" /></td>
                    <td style="vertical-align:top;"><label for="s_usersall" class="clr-error">включая запретивших корреспонденцию или неподписавшихся пользователей</label></td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>
</thead>
<tbody>
<tr class="row2">
	<td colspan="2"> 
	    <table>     
	        <tr>
	            <td>
                    <strong>Список:</strong>
	                <select multiple name="exists" id="exists" style="width:291px; height:300px;">{$aData.exists}</select><br />
	            </td>
	            <td align="center" valign="middle" style="border:0;"><br />
                     <div style="width:33px; height:2px;">&nbsp;</div>
                     <div class="button">
                        <span class="left"></span>
                        <input type="button" style="width: 25px;" value="&gt;&gt;" onclick="bff.formSelects.MoveAll('exists', 'receivers');" />
                    </div>
                     <div class="button">
                        <span class="left"></span>
                        <input type="button" style="width: 25px;" value="&gt;" onclick="bff.formSelects.MoveSelect('exists', 'receivers');" />
                    </div>
                     <div class="button">
                        <span class="left"></span>
                        <input type="button" style="width: 25px;" value="&lt;" onclick="bff.formSelects.MoveSelect('receivers', 'exists');" />
                    </div>
                     <div class="button">
                        <span class="left"></span>
                        <input type="button" style="width: 25px;" value="&lt;&lt;" onclick="bff.formSelects.MoveAll('receivers', 'exists');" />
                    </div>
	            </td>
	            <td>
                    <strong>Получатели:</strong>
                    <select multiple name="receivers[]" id="receivers" style="width:292px; height:300px;">{$aData.receivers}</select>
                </td>
	        </tr>
	    </table>
	</td>
</tr>
<tr class="row1">
    <td>От:</td>
    <td>
        <div class="text-field">
            <input type="text" name="from" id="msg-from" value="{$config.mail_admin|default:''}" />
        </div>
    </td>
</tr>
<tr class="row1">
    <td>Тема:</td>
	<td>
        
        <div class="input left">
            <span class="left"></span>
                <input style="width:539px;" type="text" name="subject" id="subject" value="{$aData.subject|default:''}" />
            <span class="right"></span>
        </div>
    </td>        
</tr>
<tr class="row2">
	<td style="vertical-align:top;">Сообщение:<br /><br />
        <span class="description">
            {literal}
            <a href="#" class="description" onclick="bff.textInsert('#msg-body', '{name}'); return false;">{name}</a><br />
            <a href="#" class="description" onclick="bff.textInsert('#msg-body', '{login}'); return false;">{login}</a><br />
            <a href="#" class="description" onclick="bff.textInsert('#msg-body', '{email}'); return false;">{email}</a><br />
            <a href="#" class="description" onclick="bff.textInsert('#msg-body', '{password}'); return false;">{password}</a>
            {/literal}
        </span>
        </td>
	<td><textarea name="body" id="msg-body" class="expanding" cols="0" rows="0" style="min-height: 150px; width:550px; height:150px">{$aData.body|default:''}</textarea></td>
</tr>
<tr class="footer">
	<td colspan="2" align="center">
        <div class="button">
            <span class="left"></span>
            <input type="button" style="width: 85px;" value="Отправить" onclick="return doSend('index.php?s={$class}&amp;ev=ajax&amp;action=massend');" />
        </div>
        {if $smarty.server.REQUEST_METHOD == 'POST'}
        <div class="button-separator"></div>
        <div class="button">
            <span class="left"></span>
            <input type="button" style="width: 85px;" value="Отмена" onclick="bff.redirect('index.php?s={$class}&amp;ev={$event}');" />
        </div>
        {/if}
	</td>
</tr> 
</form>
</tbody>
</table>
