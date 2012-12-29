                                
<div class="actionBar"> 
    <? if($aData['owner']['parent'] != 0){ ?>
    <a href="<?= $aData['url_listing'].$aData['owner']['parent']['id']; ?>"><?= $aData['owner']['parent']['title']; ?></a>
    &rarr;&nbsp;&nbsp;
    <? } ?>      
    <a href="<?= $aData['url_listing'].$aData['owner']['id']; ?>" class="<? if($aData['edit'] && $aData['owner_from']>0): ?> bold clr-error<? endif; ?>"><?= $aData['owner']['title']; ?></a>
    &rarr;&nbsp;&nbsp;
    <a href="<?= $aData['url_listing'].$aData['owner']['id']; ?>">динамические свойства</a>
    &rarr;&nbsp;&nbsp;
    <span class="bold">управление</span>
</div>      

<script type="text/javascript">
var bffDynpropsMain;
$(function(){
    bffDynpropsMain = bffDynprops.init(false,
        {                                         
            edit: <?= ($aData['edit']?'true':'false'); ?>,
            data: <?= ( !empty($aData['data']) ? func::php2js($aData['data']) : 'null' ); ?>,
            types_allowed: [<?= join(',', $this->typesAllowed); ?>],
            types_allowed_parent: [<?= join(',', $this->typesAllowedParent); ?>],
        }, 
        {
            url_action_owner: '<?= $aData['url_action_owner']; ?>', 
            date: {
                dateFormat: "<?= dbDynprops::datePatternJS; ?>",
                onSelect: function(dateText, inst) {
                    $("#"+this.id+"_timestamp").val((new Date($(this).datepicker('getDate')).getTime())/1000);
                }
            } 
        }
    );
});

</script>

<form method="post" id="bffDynpropsMainForm" action="">
    <input type="hidden" name="dynprop[multi_deleted]" value="" class="multi-deleted" />
    <input type="hidden" name="dynprop[multi_added]" value="" class="multi-added" />
    <table class="admtbl tbledit dynprop-block">
        <tr>
            <td class="row1" width="100"><span class="field-title">Тип</span>:</td>
            <td class="row2">
                <select class="dynprop-type-select" style="width:240px;" name="dynprop[type]"></select>
                <label style="display:none; margin-left: 10px;"><input class="dynprop-parent" type="checkbox" name="dynprop[parent]" /> с прикреплением</label>
            </td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Название</span>:</td>
            <td class="row2"><input class="dynprop-title" type="text" maxlength="150" name="dynprop[title]" value="" style="width: 233px;" /></td>
        </tr>  
        <tr>
            <td class="row1"><span class="field-title">Уточнение к названию</span>:</td>
            <td class="row2"><input class="dynprop-description" type="text" maxlength="150" name="dynprop[description]" value="" style="width: 233px;" /></td>
        </tr>
        <tbody class="dynprop-parent-block" style="display:none;">
        <tr>
            <td class="row1"><span class="field-title">Название</span>:<br/><span class="desc">для прикрепления</span></td>
            <td class="row2"><input class="dynprop-child-title" type="text" maxlength="150" name="dynprop[child_title]" value="" style="width: 233px;" /></td>
        </tr>  
        <tr>
            <td class="row1"><span class="field-title">Значение<br/>по-умолчанию</span>:<br/><span class="desc">для прикрепления</span></td>
            <td class="row2"><input class="dynprop-child-default" type="text" maxlength="150" name="dynprop[child_default]" value="" style="width: 233px;" /></td>
        </tr>  
        </tbody>   
        <tr>
            <td class="row1"><span class="field-title">Значение<br/>по-умолчанию</span>:<br/><a href="#" class="ajax desc multi-default-clear">сбросить</a></td>
            <td class="row2">
                <div class="dynprop-params"></div>
            </td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Обязательное</span>:<br/><span class="desc">для ввода</span></td>
            <td class="row2">
                <label><input type="checkbox" name="dynprop[req]" class="dynprop-req" /></label>
            </td>
        </tr>   
        <tr>
            <td class="row1"><span class="field-title">Текст объявления</span>:</td>
            <td class="row2">
                <label><input type="checkbox" name="dynprop[txt]" class="dynprop-txt" /></label>
                <span class="dynprop-txt-block hidden">
                    <input type="text" placeholder="дополняющий текст" class="dynprop-txt-text" name="dynprop[txt_text]" style="margin-left:5px;" />
                </span>
            </td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Отображать в таблице</span>:</td>
            <td class="row2">
                <label><input type="checkbox" name="dynprop[in_table]" class="dynprop-in_table" /></label>
            </td>
        </tr>    
        <tr class="dynprop-search-block hidden">
            <td class="row1"><span class="field-title">Поле поиска</span>:</td>
            <td class="row2">
                <label><input type="checkbox" name="dynprop[is_search]" class="dynprop-search" /></label>
            </td>
        </tr>   
        <tr class="footer">
            <td class="row1" colspan="2">
                <input type="submit" class="button submit" value="Сохранить" />
                <input type="button" class="button cancel " value="Отмена" onclick="bff.redirect('<?= $aData['url_listing'].($aData['edit'] && $aData['owner_from']>0?$aData['owner_from']:$aData['owner']['id']); ?>');" />                
            </td>
        </tr>      
    </table>     
</form>
