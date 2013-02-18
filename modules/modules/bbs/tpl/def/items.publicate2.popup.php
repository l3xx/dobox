<?php
    extract( $aData );
?>

<div class="popupCont" id="ipopup-item-publicate2" style="display:none;"><div class="popup"><div class="top"></div><div class="center">
<div class="close"><a href="#" title="" rel="close"><img src="/img/close.png" alt=""/></a></div>
<h1 class="ipopup-title">Продлить публикацию объявления</h1>
<div class="ipopup-content">
    <form action="/ajax/bbs?act=item-publicate2">
        <input type="hidden" name="item" value="<?= $id; ?>" />    
        <input type="hidden" name="save" value="1" />
        <div class="error hidden"></div>
        <div class="padTop">Укажите период публикации <span class="req">*</span>:<div class="progress" style="margin:7px 0 0 12px; display:none;"></div></div>
        <div class="padTop">
            <script type="text/javascript"> 
            <?  
                $week = (60*60 * 24 * 7);
                $from = ($status == BBS_STATUS_PUBLICATED ? strtotime($publicated_to) : time());
                $periods = bff::getPublicatePeriods( $from );
                 ?>                        
            var bbsPublicate2Periods = <?= func::php2js( $periods['data'] );  ?>;
            </script>
            <select class="inputText2" tabindex="1" name="period" onchange="$('#publicated2-till').html( bbsPublicate2Periods[this.value] );">
                <?= $periods['html'] ?> 
            </select>
        </div>
        <div class="padTop">
            Срок публикации:
            <div class="term"><?= ($status == BBS_STATUS_PUBLICATED_OUT ? 'с '.date('d.m.Y').' по ' : ' до ' ) ?><span id="publicated2-till"><?= date('d.m.Y', $from+$week ); ?></span></div>
        </div>        
        <div class="padTop">
            <div class="button left">
                <span class="left">&nbsp;</span>
                <input type="submit" tabindex="2" value="ПРОДЛИТЬ"/> 
            </div>
            <div style="margin: 4px 0 0 10px;" class="left"><a rel="close" href="javascript:void(0);" class="greyBord">отмена</a></div>
            
            <div class="clear"></div>
        </div>
    </form>       
</div>
</div><div class="bottom"></div></div></div>
