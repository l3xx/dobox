<?php

  extract($aData, EXTR_REFS);

  $amountTo = $this->input->get('a', TYPE_UINT);
  if($amountTo<=0) $amountTo = '';
?>

<div class="bill">
    <div class="pay left">
        <h1>пополнение счета</h1>
        <div id="uBillManage">
            
            <div style="margin: 10px 10px;"> 
                <div class="error hidden"></div>
                
                <input type="text" class="inputText" id="uBillAmount" style="width: 70px" maxlength="7" value="<?= $amountTo ?>" /> $.
                <br /><br />
                <div class="button">
                    <span class="left">&nbsp;</span>
                    <input type="button" class="submit" id="uBillAmountPay" value="ПОПОЛНИТЬ СЧЕТ" tabindex="2" />
                </div>

                <div id="bill-progress" class="progress" style="margin-left:7px; display:none;"></div>
            </div>
            
            <div id="uBillProcessBlock"></div>
        </div>
    </div>
    <div class="history left" style="width: 511px;">
        <h1>история операций</h1>
        <form action="/bill?act=history" id="uBillHistoryForm">
        <input type="hidden" name="offset" value="<?= $offset; ?>" />
        <table style="width: 100%;">
            <tr><th width="95">Дата</th><th>Сумма</th><th>Операция</th><th>Статус</th></tr>
            <tbody id="uMyBillsList"><? require ('history.ajax.php'); ?></tbody>
            <tr>
                <td colspan="4" class="pagenation" id="uBillHistoryPgn">
                    <?= $pgn; ?>
                    <!--<span class="prev prevnactive"><i>&nbsp;</i>&nbsp;<a href="#">назад</a></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="next"><a href="#">вперед</a>&nbsp;<i>&nbsp;</i></span>-->
                </td>
            </tr>
        </table>
        </form>
    </div>
</div>
<div class="clear"></div>

<script type="text/javascript">
var pgnClass = function(){ }, pgn;
pgnClass.prototype = {
    initialize: function(form, options)
    {
        this.form = $(form).get(0);
        this.process = false;    
        this.options  = { progress: false, ajax: false };
        
        if(options) { for (var o in options) { 
            this.options[o] = options[o]; } }
        
        this.options.targetList = $(options.targetList);
        this.options.targetPagenation = $(options.targetPagenation);
    },
    prev: function(offset)
    {
        if(this.process) return;
        this.form['offset'].value = offset;
        this.update();
    },
    next: function(offset)
    {
        if(this.process) return;
        this.form['offset'].value = offset;  
        this.update();
    },
    update: function()
    {
        if(!this.options.ajax) {
            this.form.submit();
            return;
        }
        
        if(this.process)
            return;                            
            
        this.process = true;
        
        this.options.targetList.animate({'opacity': 0.65}, 300);
        bff.ajax($(this.form).attr('action'), $(this.form).serializeArray(), function(data){
            if(data) {
                this.options.targetList.animate({'opacity': 1}, 100).html(data.list);
                this.options.targetPagenation.html(data.pgn); 
            }    
            this.process = false;
        }.bind(this), this.options.progress);
    }
};
    
$(function(){
    var $block = $('#uBillManage'), $err = $('.error', $block),
        $amount = $('#uBillAmount', $block),
        $processBlock = $('#uBillProcessBlock', $block),
        processing = false;
    
    $('#uBillAmountPay', $block).click(function(e){
        nothing(e);
        var a = intval($amount.val());
        if(a<=1){
            $amount.focus();
            return;
        } else {
            if(processing) return false;
            processing = true;            
            bff.ajax('/bill?act=pay', {amount: a}, function(data, errors){
                if(data && data.res) {
                    app.pay( data.form );
                    return;
                } else {
                    app.showError($err, errors);
                }
                processing = false;
            });
        }
        
    });    
    
    pgn = new pgnClass();
    pgn.initialize('#uBillHistoryForm', {targetList:'#uMyBillsList', targetPagenation:'#uBillHistoryPgn', ajax: true});
    
    $(".greyLine").corner("8px");
});
</script>