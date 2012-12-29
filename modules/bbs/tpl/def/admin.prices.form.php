<tr>                          
    <td style="white-space:nowrap; width:100px;" class="row1">
        <span class="field-title"><b>Цена доступна</b></span>:
    </td>
    <td class="row2">
         <input type="checkbox" name="prices" id="bbs-cat-prices-check" <?= ($aData['prices']?'checked="checked"':''); ?> />
    </td>
</tr>
<tbody id="bbs-cat-prices-sett" class="<? if(!$aData['prices']): ?>hidden<? endif; ?>">
<tr>                          
    <td class="row1"><span class="field-title">Возможен торг</span>:</td>
    <td class="row2">
         <input type="checkbox" name="sett[torg]" <?= (!empty($aData['sett']['torg'])?'checked="checked"':''); ?>  />
    </td>
</tr>
<tr>                          
    <td class="row1"><span class="field-title">Возможен бартер</span>:</td>
    <td class="row2">
         <input type="checkbox" name="sett[bart]" <?= (!empty($aData['sett']['bart'])?'checked="checked"':''); ?>  />
    </td>
</tr>
<tr>                          
    <td class="row1"><span class="field-title">Диапазоны поиска</span>:</td>
    <td class="row2">
        <table>
            <tbody id="bbs-cat-prices-ranges">
            <?php 
            $i=1;
            foreach($aData['sett']['ranges'] as $v){ ?>
            <tr class="range-<?= $i; ?>"><td>от <input name="sett[ranges][<?= $i ?>][from]" value="<?= ($v['from']>0?$v['from']:''); ?>" type="text" style="width: 80px;" />&nbsp;&nbsp; до <input name="sett[ranges][<?= $i ?>][to]" type="text" value="<?= ($v['to']>0?$v['to']:''); ?>" style="width: 80px;" /><a class="but cross price-range-del" href="#" style="margin-left:7px;"></a></td></tr>
            <?php $i++; } ?>
            </tbody>
        </table>
        <a href="#" class="but add but-text">добавить диапазон</a>
    
        <script type="text/javascript">  
        var bbsCatPricesRanges = (function(){
            var $block, iterator = <?= count($aData['sett']['ranges']); ?>;
            function init()
            {
                $block = $('#bbs-cat-prices-ranges');
                
                $('>a', $block.parent().parent()).click(function(e){
                    nothing(e);
                    addRange(++iterator);
                    initRotate(true);
                    $.fancybox.resize();
                });            
                
                $('.price-range-del', $block).live('click', function(e){
                    nothing(e);
                    $(this).parent().remove();
                    $.fancybox.resize();
                });
                
                initRotate(false);
            }
            
            function initRotate(update)
            {
                if(update === true) {
                    $block.parent().tableDnDUpdate();
                } else {
                    $block.parent().tableDnD({onDragClass: 'rotate'/*, dragHandle: tdClass*/});
                }
            }
            
            function addRange(i)
            {                  
                $block.append('<tr class="range-'+i+'"><td>от <input name="sett[ranges]['+i+'][from]" type="text" style="width: 80px;" />&nbsp;&nbsp; до <input name="sett[ranges]['+i+'][to]" type="text" style="width: 80px;" /><a class="but cross price-range-del" href="#" style="margin-left:7px;"></a></td></tr>');
                $('.range-'+i+' > td > input:first', $block).focus(); 
            }
            
            return { init: init };
        }());  
              
        $(function(){
            $('#bbs-cat-prices-check').click(function(){
                $('#bbs-cat-prices-sett').toggleClass('hidden');
            });
            bbsCatPricesRanges.init();
        });
        </script>        
    </td>
</tr>
</tbody>