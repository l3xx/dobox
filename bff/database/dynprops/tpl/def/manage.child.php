
<script type="text/javascript">                                       
var bffDynpropsChild;
$(function(){
    bffDynpropsChild = bffDynprops.init(true,
        {                                         
            edit: <?= ($aData['edit']?'true':'false'); ?>,
            data: <?= (!empty($aData['data']) ? func::php2js($aData['data']) : 'null'); ?>, 
            types_allowed: [<?= join(',', $this->typesAllowed); ?>]
        }, {}
    );
});

</script>

<div class="ipopup" style="width: 460px;">
    <div class="ipopup-wrapper">
        <div class="ipopup-title">Прикрепление свойства</div>
        <div class="ipopup-content">   

        <form method="post" id="bffDynpropsChildForm" action="">
            <input type="hidden" name="dynprop[multi_deleted]" value="" class="multi-deleted" />
            <input type="hidden" name="dynprop[multi_added]" value="" class="multi-added" /> 
            <input type="hidden" name="dynprop[description]" value="" />
            <input type="hidden" name="dynprop[parent]" value="0" /> 
            <input type="hidden" name="parent_id" value="<?= $aData['parent_id']; ?>" />
            <input type="hidden" name="parent_value" value="<?= $aData['parent_value']; ?>" />
            <input type="hidden" name="id" value="<?= (isset($aData['id']) ? $aData['id'] : 0); ?>" />
            <input type="hidden" name="child_act" value="save" />
            <table class="admtbl tbledit dynprop-block">
                <tr>
                    <td class="row1" width="80"><span class="field-title">Тип</span>:</td>
                    <td class="row2"><select class="dynprop-type-select" style="width:240px;" name="dynprop[type]"></select></td>
                </tr>
                <tr style="display: none;">
                    <td class="row1"><span class="field-title">Название</span>:</td>
                    <td class="row2"><input class="dynprop-title" type="text" maxlength="150" name="dynprop[title]" value="" style="display: none; width: 233px;" /></td>
                </tr>          
                <tr>
                    <td class="row1"><span class="field-title">Значение<br/>по-умолчанию:</span><br/><a href="#" class="ajax desc multi-default-clear">сбросить</a></td>
                    <td class="row2">
                        <div class="dynprop-params"></div>
                    </td>
                </tr>
                <tr class="hidden">
                    <td class="row1"><span class="field-title">Обязательное</span>:<br/><span class="desc">для ввода</span></td>
                    <td class="row2">
                        <label><input type="checkbox" name="dynprop[req]" class="dynprop-req" /></label>
                    </td>
                </tr>                     
                <tr class="dynprop-search-block hidden">
                    <td class="row1"><span class="field-title">Поле поиска</span>:</td>
                    <td class="row2">
                        <label><input type="checkbox" name="dynprop[is_search]" class="dynprop-search" /></label>
                    </td>
                </tr>   
                <tr class="footer">
                    <td class="row1" colspan="2" align="center">
                        <input type="submit" class="button submit" value="Сохранить" />
                        <? if($aData['edit']): ?><input type="button" class="button delete dynprop-delete" value="Удалить" /><?php endif; ?>
                        <input type="button" class="button cancel " value="Отмена" onclick="$.fancybox.close();" />                
                    </td>
                </tr>      
            </table>     
        </form>
        
        </div> 
    </div>
</div>