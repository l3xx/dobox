<?php 
    extract($aData, EXTR_REFS);

?>
<div class="blueblock whiteblock">
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Счета / Список</span>
        <span class="rightC"></span>
    </div>
    <div class="content clear">
        <div class="text">

            <form action="" method="get" name="filters" id="j-bills-filters" onsubmit="return false;">
            <input type="hidden" name="offset" value="<?= $f['offset'] ?>" />
            <input type="hidden" name="order" value="<?= $f['order'] ?>" />
            <div class="actionBar">
                <span class="left bold" style="margin-right:5px;">
                    <input type="text" name="id" value="<?= ($f['id']>0?$f['id']:'') ?>" placeholder="ID счета" style="width: 45px;" />
                </span>
                <span class="left bold" style="margin-right:5px;">
                    <input type="text" name="item" value="<?= ($f['item']>0?$f['item']:'') ?>" placeholder="ID объявления" style="width: 80px;" />
                </span>                
                <span class="left bold" style="margin-right:5px;"> 
                    <input type="text" name="p_from" value="<?= $f['p_from'] ?>" placeholder="от" style="width: 60px;" id="j-bills-period-from" />&nbsp;<input type="text" name="p_to" value="<?= $f['p_to'] ?>" placeholder="до" style="width: 60px;" id="j-bills-period-to" />
                </span>
                <div class="left relative" style="margin-right:5px;">
                   <input type="hidden" name="uid" id="j-bills-user-id" value="<?= $f['uid'] ?>" />
                   <input type="text" class="autocomplete" id="j-bills-user-login" style="width: 130px;" placeholder="пользователь" value="<?= ($f['uid']>0 ? $user['login'] : '') ?>" />
                </div>      
                <select onchange="jBills.submit(true);" style="width: 110px;" name="type" class="left">
                    <option class="bold" value="0">- тип -</option>
                    <option <? if($f['type']==Bills::typeInPay) { ?>selected<? } ?> value="<?= Bills::typeInPay ?>">пополнение счета</option>
                    <option <? if($f['type']==Bills::typeInGift){ ?>selected<? } ?> value="<?= Bills::typeInGift ?>">подарок</option> 
                    <option <? if($f['type']==Bills::typeOutService){ ?>selected<? } ?> value="<?= Bills::typeOutService ?>">оплата услуги</option> 
                </select>
                <select onchange="jBills.submit(true);" style="width: 95px; margin:0 7px;" name="status" class="left">
                    <option class="bold" value="0">- статус -</option>
                    <?= $status_options ?>
                </select>
                <input type="button" class="button cancel" onclick="jBills.submit(true);" value="найти" />
                <div class="progress" style="display:none; margin-left:2px;" id="j-bills-progress"></div>
                <div class="clear"></div>

            </div>
            </form>
            
            <table class="admtbl tblhover">
            <tr class="header">
                <th width="50">
                    <a href="javascript: jBills.onOrder('id');" class="ajax">ID</a>
                    <div class="order-<?= $f['order_dir'] ?>" <? if($f['order_by']!='id') { ?>style="display:none;"<? } ?> id="j-order-id"></div>                
                </th>
                <th width="95">Дата</th> 
                <th width="135">Пользователь</th>
                <th width="75">Баланс</th>
                <th width="75">Сумма</th>
                <th>Описание</th>
                <th width="95">Статус</th> 
            </tr>                   
            <tbody id="j-bills-list" style="font-size: 11px;">
            <? require ('admin.listing.ajax.php'); ?>
            </tbody>
            </table>
            <div id="j-bills-pgn" align="center" style="margin-top: 5px;"><?= $aData['pgn']; ?></div>
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>               

<div class="blueblock whiteblock" <? if(!$f['uid'] || !$access_edit) { ?>style="display: none;"<? } ?> id="j-ubalance-block">
    <div class="title">
        <span class="leftC"></span>
        <span class="left" style="text-transform: none;">Операции со счетом пользователя</span>
        <span class="rightC"></span> 
        <div class="add"><div class="progress" style="display:none; margin-top: 10px;" id="j-ubalance-progress"></div></div>
    </div>
    <div class="content clear">
        <div class="text">

            <form action="" method="post" name="filters" id="j-ubalance-form">
                <input type="hidden" name="act" value="ubalance-add" />
                <input type="hidden" name="uid" value="<?= $f['uid'] ?>" />
                <table class="admtbl tbledit">
                    <tr><td colspan="2">Пополнить счет клиента <b id="j-ubalance-login"><? if($f['uid']>0){ ?><a href="#" onclick="return bff.userinfo(<?= $f['uid'] ?>);"><?= $user['login'] ?></a><? } ?></b> на указанную сумму:</td></tr>
                    <tr class="required">
                        <td width="60"><span class="field-title">Сумма</span>:</td>
                        <td><input type="text" name="amount" value="0" maxlength="5" />&nbsp;<span class="desc">$.</span></td>
                    </tr>
                    <tr class="required">
                        <td><span class="field-title">Описание</span>:</td>
                        <td><input type="text" name="description" maxlength="150" value="Подарок от администрации" style="width: 450px;" /></td>
                    </tr>                            
                    <tr class="footer"><td colspan="2"><input type="submit" class="button submit" value="Пополнить" /></td></tr>
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
var jBills = (function()
{
    var statusResult = Array();
    statusResult[<?= Bills::statusWaiting ?>]   = '<span style="color:red;">незавершен</span>';
    statusResult[<?= Bills::statusCompleted ?>] = '<span style="color:green;">завершен</span>'; 
    statusResult[<?= Bills::statusCanceled ?>]  = '<span style="color:#666;">отменен</span>';

    var $progress, $list, $listPgn, filters;
    var url = '<?= $this->adminCreateLink('listing'); ?>';
    var orders = <?= func::php2js($orders) ?>;
    var orderby = '<?= $f['order_by'] ?>';
    var status = <?= ($f['status']) ?>;
    var _processing = false; 
    
    $(function(){
        $progress = $('#j-bills-progress');
        $list     = $('#j-bills-list');
        $listPgn  = $('#j-bills-pgn');
        filters   = $('#j-bills-filters').get(0);
        hashHistory = (window.history && window.history.pushState);

        <? $this->includeJS('autocomplete'); ?>
        $('#j-bills-user-login').autocomplete( '<?= $this->adminCreateLink('ajax'); ?>&act=user-autocomplete', 
            {valueInput: '#j-bills-user-id', width: 154, placeholder: 'Пользователь', minChars: 2,
             onSelect: function(uid, text){ 
                 uid = intval(uid);
                 resetOffset(); 
                 updateList(); 
                 jUserBalance.onUser(uid, text);
            }});
        
        <? $this->includeJS('datepicker'); ?>
        $('#j-bills-period-from').attachDatepicker({yearRange: '-3:+3'});
        $('#j-bills-period-to').attachDatepicker({yearRange: '-3:+3'});      
    });
    
    function isProcessing()
    {
        return _processing;
    }

    function updateList(scroll)
    {
        if(isProcessing()) return;
        _processing = true;
        $list.addClass('disabled');
        bff.ajax(url, $(filters).serializeArray(), function(data){
            if(data) {
                $list.html( data.list );
                $listPgn.html( data.pgn );
                
                var f = $(filters).serialize();
                if(hashHistory) {
                    window.history.pushState({}, document.title, url + '&' + f);
                }
                if(scroll && scroll === true) {
                    $.scrollTo($list, {duration:600, offset:-100});
                }
            }
            $list.removeClass('disabled');
            _processing = false;
        }, $progress);
    }
    
    function setOffset(id)
    {
        filters.offset.value = intval(id);
    }
    
    function resetOffset()
    {
        setOffset(0);
    }
    
    function showCheckResult(msg)
    {
        alert(msg);
    }

    return {
        submit: function(bResetOffset){  
            if(isProcessing()) return false;
            if(bResetOffset === true) {
                resetOffset();
            }
            updateList();
        },
        prev: function (id)
        {
            if(isProcessing()) return false; setOffset(id); updateList(true);
        },
        next: function (id)
        {
            if(isProcessing()) return false; setOffset(id); updateList(true);
        },   
        changeStatus: function(bid, cls)
        {
            bff.ajax('index.php?s=bills&ev=ajax&act=status', $('#tr'+bid+'_form', $list).serialize(), function(data)
            {
                if(data){   
                    $('#tr'+bid+'_status', $list).html(statusResult[data.status]);
                    jBills.changeStatusCancel(bid, cls);
                }
            }, $progress);
        },        
        сhangeStatusCancel: function (bid, cls)
        {
            $('#tr'+bid+'_chng', $list).remove();
            if(cls == 0) {
                $('#tr'+bid).css('borderBottom', '1px solid #F3EEE8');
            }            
        },
        changeStatusShow: function(cls, money, bid, user_id, login)
        {
            if($('#tr'+bid+'_chng', $list).length>0)
            {  
                this.сhangeStatusCancel(bid, cls);
            } else {
                if(cls == 0)
                    $('#tr'+bid, $list).css('borderBottom', '0');
                
                $('#tr'+bid, $list).after('<tr class="row'+cls+'" style="border-top: 0;" id="tr'+bid+'_chng"><td colspan="7"><div style="padding: 10px; text-align: left; border: 1px dotted #ccc;"><form action="" method="post" id="tr'+bid+'_form"><input type="hidden" name="bid" value='+bid+' /> \
                    <div class="warning" style="height:40px;"></div> \
                    Изменить статус счета #'+bid+' на: <select name="status" style="width:100px;"> <option value="2">завершен</option> <option value="3">отменен</option> </select>&nbsp;&nbsp;<a href="#" class="bold" onclick="jBills.changeStatus('+bid+', '+cls+'); return false;">изменить</a>&nbsp;|&nbsp;<a href="#" class="ajax" onclick="jBills.check('+bid+'); return false;">проверить</a>&nbsp;|&nbsp;<a href="#" class="ajax" onclick="jBills.сhangeStatusCancel('+bid+', '+cls+'); return false;">отмена</a> <br /> \
                    <span class="description">После изменения статуса на <u>завершен</u> на счет пользователя <a href="#" onclick="return bff.userinfo('+user_id+');">'+login+'</a> будет зачислена сумма в размере '+money+' $. </span><br /> \
                    </form></div></td></tr>');
            }
        },
        showExtra: function(bid)
        {
            bff.ajax('index.php?s=bills&ev=ajax&act=extra', {bid: bid}, function(data)
            {
                if(data){   
                    if(!data.extra) data.extra = 'нет дополнительных данных';
                    alert(data.extra);
                }
            }, $progress);
        },
        check: function(bid)
        {
            bff.ajax('index.php?s=bills&ev=ajax&act=check', {bid: bid}, function(data)
            {
                if(data){
                   setTimeout(function(){ showCheckResult(data); }, 1);
                }
            }, $progress);
        },
        onOrder: function(by)
        {
            if(isProcessing() || !orders[by])
                return;
                
            orders[by] = (orders[by] == 'asc' ? 'desc' : 'asc');
            //hide prev order direction
            $('#j-order-'+orderby, $list.parent()).hide();
            //show current order direction
            orderby = by;
            $('#j-order-'+orderby, $list.parent()).removeClass('order-asc order-desc').addClass('order-'+orders[by]).show();
                
            filters.order.value = orderby+','+orders[by];
            resetOffset();
            
            updateList();
        }
    };
}());

var pgn = {
    prev: jBills.prev,
    next: jBills.next    
};

var jUserBalance = (function(){
    var $block, $form, $login;
    
    $(function(){
        $block = $('#j-ubalance-block');
        $form = $('#j-ubalance-form', $block); 
        $login = $('#j-ubalance-login', $block); 
    });
    
    return {
        onUser: function(uid, login)
        {
            <? if(!$access_edit){ ?>return false;<? } ?>
            
            if(uid>0) $block.show();
            else $block.hide();
            
            $login.html('<a href="#" onclick="bff.userinfo('+uid+'); return false;">'+login+'</a>');
            $form.get(0).uid.value = uid;
        }
    };
}());

</script>
