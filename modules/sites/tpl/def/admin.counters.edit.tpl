<form action="" method="post" name="countersForm">
<input type="hidden" name="rec" value="{$aData.id}" />
    <table class="admtbl tbledit">
        <tr class="required">
            <td class="row1 field-title" style="white-space:nowrap; width:90px;">Название<span class="required-mark">*</span>:</td>
            <td class="row2"> <input type="text" name="title" value="{$aData.title|default:''}" class="stretch" /> </td>
        </tr>
        <tr class="required">
            <td class="row1 field-title">Код счетчика<span class="required-mark">*</span>:</td>
            <td class="row2"><textarea name="code" style="height:130px;">{$aData.code|default:''}</textarea></td>
        </tr>
        <tr class="footer">
            <td colspan="2" class="row1">
                <input type="submit" class="button submit" value="Сохранить" />
                <input type="button" class="button delete" value="Удалить" onclick="bff.redirect('index.php?s={$class}&amp;ev=counters_action&amp;act=delete&amp;rec={$aData.id}', 'Удалить счетчик?');" />
                <input type="button" class="button cancel" value="Отмена" onclick="history.back();" />
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    {literal}
    var helper = new bff.formChecker( document.forms.countersForm, {}, function(){
            document.forms.countersForm['title'].focus();
        } );
    {/literal}
</script>