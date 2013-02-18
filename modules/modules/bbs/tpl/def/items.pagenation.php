<?php

//echo '<pre>', print_r($aData, true), '</pre>', '<br /><br />';
if($aData['total_pages'] > 1) { ?>
<!--<div class="navigation">
    <div class="nav">
        <ul>
            <li class="selected"><b><span>1</span></b></li>
            <li><a href="#" title=""><span>2</span></a></li>
            <li><a href="#" title=""><span>3</span></a></li>
            <li><a href="#" title=""><span>4</span></a></li>
            <li><a href="#" title=""><span>5</span></a></li>
            <li><a href="#" title=""><span>6</span></a></li>
            <li><a href="#" title=""><span>7</span></a></li>
            <li><a href="#" title=""><span>8</span></a></li>
            <li><a href="#" title=""><span>9</span></a></li>
            <li><a href="#" title=""><span>10</span></a></li>
            <li><a href="#" title=""><span>11</span></a></li>
            <li><a href="#" title=""><span>12</span></a></li>
            <li><a href="#" title=""><span>13</span></a></li>
            <li><a href="#" title=""><span>14</span></a></li>
            <li><a href="#" title=""><span>15</span></a></li>
            <li><a href="#" title=""><span>16</span></a></li>
            <li class="clearLi">...</li>
            <li><a href="#" title=""><span>250</span></a></li>
        </ul><br/>
        <div class="clear"></div>
        <div class="scrollBar">
            <div class="scroll_thumb"><div class="scroll_knob"></div></div>
        </div>
    </div>
    <div class="arrowsBlock">
      <a href="#" title="" class="arrowLeft">назад</a>
      <a href="#" title="" class="arrowRight">вперед</a>
    </div>
</div>   -->
                                                                                                                   
<div class="navigation">
    <div class="nav" id="bbs_items_paginator"></div>
</div>
<script type="text/javascript">
//<![CDATA[
    $('#bbs_items_paginator').paginator({ pagesTotal:<?= $aData['total_pages'] ?>, pageCurrent: <?= $aData['page_current'] ?>, baseUrl: '<?= $aData['href'] ?>', 
        clickHandler: function(page){ 
            <? if(!empty($aData['jshandler'])) {
                echo 'return '.$aData['jshandler'].'(page);';
            } ?>
            return true;
        }
    });
//]]>
</script>
<? }
