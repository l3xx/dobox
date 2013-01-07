<div class="blueblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Объявления / Редактирование категории</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
        
        <form method="post" action="">
        <table class="admtbl tbledit">
        <tr>
            <td class="row1" width="125"><span class="field-title">Основная категория</span>:</td>
            <td class="row2">{if !$aData.edit}<select name="pid" style="width:350px;">{$aData.pid_options}</select>{else}<div style="height:18px;" class="bold">{$aData.pid_options}</div><input type="hidden" name="pid" value="{$aData.pid}" />{/if}</td>
        </tr>
        <tr class="required">
            <td class="row1"><span class="field-title">Название</span>:</td>
            <td class="row2"><input type="text" name="title" value="{$aData.title}" class="stretch" /></td>
        </tr>
        {if $bbs->url_keywords}
        <tr>
            <td class="row1"><span class="field-title">Keyword</span>:</td>   
            <td class="row2">                   
                <div class="left relative" style="width:100%">
                    <label for="keyword" class="placeholder">keyword для URL</label>  
                    <input class="stretch" type="text" maxlength="90" name="keyword" id="keyword" placeholder="keyword для URL" value="{$aData.keyword}" />
                </div> 
                <div class="clear-all"></div>
            </td>
        </tr>
        {/if}
        <tr>
            <td class="row1"><span class="field-title">Краткое описание</span>:</td>
            <td class="row2">{$aData.textshort|default:''|jwysiwyg:'textshort':600:90}</td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Подробное описание</span>:</td>
            <td class="row2">{$aData.textfull|default:''|jwysiwyg:'textfull':600:160}</td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Title</span>:</td>
            <td class="row2"><input type="text" name="mtitle" value="{$aData.mtitle}" class="stretch" /></td>
        </tr>        
        <tr>
            <td class="row1"><span class="field-title">Meta Keywords</span>:<br /><span class="desc">перечень ключевых слов через запятую;<br />максимум 255 символов</span></td>
            <td class="row2"><textarea name="mkeywords" style="height: 150px;" class="stretch">{$aData.mkeywords}</textarea></td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Meta Description</span>:<br /><span class="desc">описание содержимого страницы;<br />максимум 255 символов</span></td>
            <td class="row2"><textarea name="mdescription" style="height: 150px;" class="stretch">{$aData.mdescription}</textarea></td>
        </tr>
        <tr class="footer">
            <td class="row1" colspan="2">
                <input type="submit" class="button submit" value="Сохранить" />        
                <input type="button" class="button cancel" value="Отмена" onclick="history.back();" />
            </td>
        </tr>
        </table>
        </form>
        
        </div>
    </div>
</div>