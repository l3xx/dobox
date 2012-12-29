

<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Разблокировать доступ или удалить из белого списка</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
            {if $aData.bans}
            <div class="actionBar">
                <b>жирным</b> - белый список
            </div>
            <form action="" method="post">
            <input type="hidden" name="action" value="massdel" />   
            <table class="admtbl tblhover">  
            <tr class="header">
                {if $fordev}<th style="width:40px;">ID</th>{/if}
                <th class="small" style="width:40px;"><input type="checkbox" id="сheckAll" value="0" onclick="{literal} if(this.value == '1') {onCheck(0); this.value = '0';} else {onCheck(1); this.value = '1';} {/literal}" /></th>
                <th class="small" style="width:200px;" align="left">Блокировка</th>
                <th class="small" style="width:100px;" align="center">Тип</th>
                <th class="small" align="left">Описание</th>
                <th class="small" style="width:90px;">Период</th>
                <th style="width:35px;"></th> 
            </tr>
            {foreach from=$aData.bans item=v key=k}
            <tr class="row{$k%2}" id="ban{$v.id}">
                {if $fordev}<td class="small">{$v.id}</td>{/if}
                <td><input type="checkbox" class="сheckBan" value="{$v.id}" title="блокировка администратора" name="banid[]" /></td>
                <td align="left"><span class="{if $v.exclude} bold{/if}">{$v.ip|default:$v.email}</span></td>
                <td class="description">{if $v.ip}ip{else}email{/if}</td> 
                <td><a class="description" href="javascript:void(0);" title="для пользователя: {$v.reason|truncate:50}&shy;sad">{$v.description|truncate:50}</a></td>
                <td class="small">{$v.till}<br />{if $v.finished} до {$v.finished_formated|date_format:'%d.%m.%Y %H:%M'}{/if}</td>
                <td>                                                                                                                                                                                                     
                    <a class="but del" title="Удалить" href="javascript:void(0);" onclick="bff.ajaxDelete('Удалить блокировку?', {$v.id}, 'index.php?s={$class}&ev=ajax&action=delete', this); return false;" ></a>
                </td>
            </tr>
            {/foreach}
            <tr class="footer">
                <td colspan="7">
                    <input type="submit" class="button delete" onclick="if(!confirm('Продолжить?')) return false;" value="Удалить выделенные" />
                </td>
            </tr>
            </table>
           
            </form>
            {else}
            <table class="admtbl"> 
            <tr class="norecords">
                <td>нет блокировок</td>
            </tr>
            </table>
            {/if}            
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>

<div class="blueblock lightblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Заблокировать доступ</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
            <form method="post" action="" name="banlistForm">
            <input type="hidden" name="banmode" id="banmode" value="ip" />   
            <table class="admtbl tbledit">  
            <tr>          
                <td class="row1 actionBar" colspan="2">
                    <span class="bold field-title">Заблокировать:</span>&nbsp;&nbsp;<a onclick="return banType('ip');" class="clr-error ajax bold" href="#" id="typelnk_ip">доступ с IP-адресов</a>,
                    <a onclick="return banType('email');" href="#" id="typelnk_email" class="ajax">email адреса</a>
                    <br />
                    <span class="desc" id="type_ip_desc">
                        Вводите каждый IP-адрес или имя узла на новой строке. Для указания диапазона IP-адресов отделите его начало и конец дефисом (-), или используйте звёздочку (*) в качестве подстановочного знака.
                        <br /><u>Проверка IP-адреса производится</u>:<br />
                            <div>- при регистрации пользователей</div>
                            <div>- при авторизации администраторов в админ. панель</div>
                            <div>- при авторизации пользователей</div>
                    </span>
                    <span class="desc" id="type_email_desc" style="display:none;">
                        Вводите каждый адрес на новой строке. Используйте звёздочку (*) в качестве подстановочного знака для блокировки группы однотипных адресов. Например, *@gmail.com, *@*.example.com и т.д.
                        <br /><u>Проверка email-адреса производится</u>:<br />              
                            <div>- при регистрации пользователей</div> 
                    </span>
                </td>
            </tr>
            <tr id="type_ip_ban" class="required">
                <td class="row1 field-title" width="140">IP-адреса или хосты:</td>
                <td class="row2"><textarea name="ban_ip" style="height: 85px;"></textarea></td>
            </tr>
            <tr id="type_email_ban" class="required" style="display:none;">
                <td class="row1 field-title" width="140">Email адрес:</td>
                <td class="row2"><textarea name="ban_email" style="height: 85px;"></textarea></td>
            </tr>
            <tr>
                <td class="row1 field-title">Продолжительность блокировки:</td>
                <td class="row2">
                    <select name="banlength" onchange="{literal}if(this.value==-1){ $('#till').show(); }else{ $('#till').hide();}{/literal}" style="width:100px;">
                        <option value="0">Бессрочно</option>
                        <option value="30">30 минут</option>
                        <option value="60">1 час</option>
                        <option value="360">6 часов</option>
                        <option value="1440">1 день</option>
                        <option value="10080">7 дней</option>
                        <option value="20160">2 недели</option>
                        <option value="40320">1 месяц</option>
                        <option value="-1">До даты ... </option>
                    </select>
                </td>
            </tr> 
            <tr id="till" style="display:none;">
                <td class="row1 field-title">До даты:</td>
                <td class="row2"><input style="width:94px;" type="text" name="bandate" value="" />&nbsp;&nbsp;&nbsp; <span class="desc">ДД-ММ-ГГГГ</span>
                </td>
            </tr> 
            <tr>
                <td class="row1 field-title">Добавить в белый список:</td>
                <td class="row2">
                    <label><input type="radio" class="radio" value="1" name="exclude"/> Да</label>
                    <label><input type="radio" class="radio" checked value="0" name="exclude"/> Нет</label>
                    <span class="desc" style="padding-left:5px;" id="type_ip_exclude">(исключить введённые IP-адреса из чёрного списка.)</span>
                    <span class="desc" style="padding-left:5px; display:none;" id="type_email_exclude">(исключить введённые email адреса из чёрного списка.)</span>
                </td>
            </tr>
            <tr>
                <td class="row1 field-title">Причина блокировки доступа:</td>
                <td class="row2"><textarea name="description" id="description" onkeyup="bff.textLimit('description', 255);" onkeydown="bff.textLimit('description', 255);" style="height: 85px;"></textarea></td>
            </tr>
            <tr>
                <td class="row1 field-title">Причина, показываемая пользователю:</td>
                <td class="row2"><textarea name="reason" id="reason" onkeyup="bff.textLimit('reason', 255);" onkeydown="bff.textLimit('reason', 255);" style="height: 85px;"></textarea></td>
            </tr>
            <tr class="footer">
                <td colspan="2" class="row1">
                    <input type="submit" class="button delete" value="Заблокировать" />
                </td>
            </tr>
            </table>
            </form>    
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>

<script type="text/javascript">
{literal}
//<![CDATA[  

function banType(type)
{   
    type = type || 'ip';
    
    //links
    $('a[id^="typelnk"]').removeClass('clr-error bold');
    $('a[id^="typelnk_'+type+'"]').addClass('clr-error bold');
    
    $('*[id^="type_"]').hide();
    $('*[id^="type_'+type+'"]').show();
    
    $('#banmode').val(type);
    
    return false;
}

function onCheck(check)
{
    var cAll = $('#сheckAll');
    cAll.checked = false; cAll.value = 0;
    
    if(check)
    {
        cAll.checked = true; cAll.value = 1; 
        $('input.сheckBan').attr('checked', 1);
    } 
    else 
    { 
        $('input.сheckBan').attr('checked', 0); 
    }
    return false;
}

var fchecker = new bff.formChecker( document.forms.banlistForm );

//]]>   
{/literal}
</script>