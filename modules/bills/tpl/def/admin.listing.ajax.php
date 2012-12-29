<?php   
    if(bff::$isAjax) {
        extract($aData, EXTR_REFS);
    }
?>
<? 
foreach($bills as $k=>$v) 
{
    $k = $k%2; ?>
<tr class="row<?= $k ?>" style="color: #666666;" id="tr<?= $v['id'] ?>">
    <td class="alignCenter small"><?= $v['id'] ?></td>
    <td class="alignCenter"><?= tpl::date_format2($v['created'], true) ?></td>
    <td class="alignCenter"><? if($v['user_id'] > 0){ ?><a href="#" onclick="return bff.userinfo(<?= $v['user_id'] ?>);"><?= $v['login'] ?></a><? } else { ?><span class="decs"><?= $v['ip'] ?></span><? } ?></td>
    <td class="alignCenter"><?= $v['user_balance'] ?></td>
    <td class="alignCenter"><?= ($v['type'] == Bills::typeOutService ? '<span class="clr-error">–&nbsp;'.$v['amount'].'</span>' : '<span class="bold green">+&nbsp;'.$v['amount'].'</span>') ?></td>
    <td class="alignCenter"><span class="bill"><? if(!empty($v['details'])){ ?><a href="#" class="ajax" onclick="$(this).next().toggle(); return false;"><?= $v['description'] ?></a><span style="display: none;" class="desc"><br /><?= $v['details'] ?></span><? } else { echo $v['description']; } ?></span></td>
    <td class="alignCenter" id="tr<?= $v['id'] ?>_status">
        <? if($v['status'] == Bills::statusWaiting) { ?>
            <a href="#" class="ajax" onclick="jBills.changeStatusShow(<?= $k ?>, '<?= $v['amount'] ?>', <?= $v['id'] ?>, <?= $v['user_id'] ?>, '<?= $v['login'] ?>'); return false;"><span class="clr-error">незавершен</span></a>
        <? } elseif($v['status'] == Bills::statusCompleted) { ?>
            <span style="color:green;">завершен<br /><?= tpl::date_format2($v['created'], true) ?></span>
        <? } elseif($v['status'] == Bills::statusProcessing) { ?>
            <a href="#" class="ajax" onclick="jBills.changeStatusShow(<?= $k ?>, '<?= $v['amount'] ?>', <?= $v['id'] ?>, <?= $v['user_id'] ?>, '<?= $v['login'] ?>'); return false;"><span style="color:darkorange;">обрабатывается</span> </a>
        <? } elseif($v['status'] == Bills::statusCanceled) { ?>
            <span style="color:#666;">отменен</span>
        <? } ?>
    </td>
</tr>
<? 
}
if(empty($bills)) 
{ ?>
<tr class="norecords">
    <td colspan="7"><div style="margin:15px 0;">ничего не найдено</div></td>
</tr>
<? } ?>
