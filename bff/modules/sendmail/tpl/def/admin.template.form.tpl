<script type="text/javascript">
{literal}

var tpl_input_focused = null;
$(function(){
    
    $('textarea.expanding').autogrow({  
        minHeight: 250,
        lineHeight: 16
    });
    
    $('.tpl_input').focus(function(){
        tpl_input_focused = this;
    });
                            
});

{/literal}
</script>

<form method="post" action="">

<table class="admtbl tbledit">
<tr class="row2">
    <td colspan="2">
        Редактирование шаблона '<b>{$aData.title|default:''}</b>'<br />
        <span class="admDescription">{$aData.description|default:''}</span>           
    </td>
</tr>
<tr>
    <td colspan="2">
        <div class="desc">Заголовок:</div>
        <input type="text" name="tpl_subject" id="tpl_subject" value="{$aData.tpl.subject|default:''|escape}" class="tpl_input stretch" />
    </td>
</tr>
<tr class="row1">
    <td width="500" style="vertical-align: top;">
        <span class="description">Текст:</span><br />
        <textarea class="expanding tpl_input stretch" name="tpl_body" style="min-height:250px;" id="tpl_body">{$aData.tpl.body|default:''}</textarea>
    </td>
    <td style="vertical-align:top;">
        Код для вывода данных в шаблон:
        <hr size="1" style="color:#ccc" />
        <table>
            {foreach from=$aData.vars name=templateVars item=v key=k}
            <tr>
                <td style="width:50px;"><a href="#" onclick="bff.textInsert(tpl_input_focused, '{$k}'); return false;">{$k}</a></td>
                <td> - {$v}</td>
            </tr>
            {/foreach}
        </table>
    </td>
</tr>
<tr class="footer">
    <td colspan="2">
        <input type="submit" class="submit button" value="Сохранить" />
        <input type="button" class="cancel button" value="К списку шаблонов" onclick="bff.redirect('index.php?s=sendmail&ev=template_listing');" />
    </td>
</tr>

</table>
</form>