<?php

if(bff::$isAjax) {
    extract($aData, EXTR_REFS);
}

foreach($bills as $k=>$v) 
{ ?>
<tr>
    <td class="date"><?= tpl::date_format3($v['created'], 'd.m.Y в H:i'); ?></td>
    <td class="summ">
        <?= ($v['type'] == Bills::typeOutService ? '<b class="red">–&nbsp;'.$v['amount'].'</b>' : '<b class="green">+&nbsp;'.$v['amount'].'</b>') ?> <span class="f10Up">$</span>
    </td>
    <td class="type">
        <? if(!empty($v['details'])){ ?><a href="#" class="ajaxLink" onclick="$(this).next().toggle(); return false;"><?= $v['description'] ?></a><span style="display: none;" class="desc"><br /><?= $v['details'] ?></span><? } else { echo $v['description']; } ?>    
    </td>
    <td id="tr<?= $v['id'] ?>_status">
        <? if($v['status'] == Bills::statusWaiting) { ?><span class="clr-error">незавершен</span>
        <? } elseif($v['status'] == Bills::statusCompleted) { ?><span style="color:green;">завершен<br /><span style="font-size: 11px;"><?= tpl::date_format2($v['created'], true) ?></span></span>
        <? } elseif($v['status'] == Bills::statusProcessing) { ?><span style="color:darkorange;">обрабатывается</span>
        <? } elseif($v['status'] == Bills::statusCanceled) { ?><span style="color:#666;">отменен</span><? } ?>    
    </td>
</tr>
<? 
}
if(empty($bills)) 
{ ?>
<tr class="norecords">
    <td colspan="4" style="text-align: center"><div style="margin:45px 0;" class="grey">операций не найдено</div></td>
</tr>
<? }
