<script type="text/javascript">
{literal}

function selectType(type)
{ 
    switch(type)
    {
        case 'page':{
             $('#divmenuname, #divlink').hide();    
             $('#divpage').show();  
             $('#actiontype').val('page');
        }break;
        case 'link':{
             $('#divmenuname, #divpage').hide(); 
             $('#divlink').show();  
        }break;
        case 'menu_type':{
             $('#divlink, #divpage').hide();
             $('#divmenuname').show();
        }break;
    }
    document.getElementById('divbtn').style.display='inline';  
}
{/literal}
</script>

<form method="post" action="">
<input type="hidden" name="actiontype" id="actiontype" value="" />

<table class="admtbl tbledit">
<tr>
    <td class="row1 field-title" width="100">Создать в:</td>
    <td class="row2"><select name="pid" style="width:300px;">{$pid_options}</select></td>
</tr>
<tr>
    <td class="row1 field-title">Keyword:</td>
    <td class="row2"><input type="text" name="keyword" maxlength="15" value="{$aData.keyword|default:''}" style="width:184px;" /></td>
</tr>  
<tr>
    <td class="row1 field-title">Открывать:</td>
    <td class="row2"><select name="target" style="width:190px;">{$target_options}</select></td>
</tr>
<tr>                    
    <td rowspan="3" class="field-title" style="vertical-align:top;">Тип:</td>               
    <td class="row2" colspan="2">
        <label><input value="page" type="radio" name="type" onclick="selectType('page');" id="type-page" /> Ссылка на статическую страницу</label>
    </td>
</tr>      
<tr>   
    <td class="row2">
        <label><input value="link" type="radio" name="type" onclick="selectType('link');" id="type-link" /> Добавить ссылку</label>
    </td>
</tr>

<tr>
    <td class="row2">
        <label><input value="menu_type" type="radio" name="type" onclick="selectType('menu_type');" id="type-menu" /> Новое меню</label>
    </td>
</tr> 

{* TYPE: PAGE *}
<tbody id="divpage" style="display:none;">   
    <tr>
        <td class="row1 field-title" width="100">Страница:</td>
        <td class="row2">
            <div style="display:block;" id="divexistpage" class="desc">
                <select name="page_id" id="page_id" style="width:300px;" onchange="document.getElementById('menu_title3').value = this.options[this.selectedIndex].text;">
                    {$pages_options}
                </select>
                [<a href="index.php?s=pages&amp;ev=add" target="_blank">добавить страницу</a>]
            </div>
        </td>
    </tr>
    <tr>
        <td class="row1 field-title">Название:</td>
        <td class="row2"><input type="text" name="menu_title3" id="menu_title3" value="" style="width:560px;" />
        <script type="text/javascript">
            var elem = document.getElementById('page_id');
            if(elem.options[0])
                document.getElementById('menu_title3').value=elem.options[0].text;
        </script>
        </td>
    </tr>  
</tbody>

{* TYPE: LINK *}
<tbody id="divlink" style="display:none;">     
    <tr>
        <td class="row1 field-title" width="100">Название:</td>
        <td class="row2">
            <input type="text" name="menu_title2"  value="{$aData.title}" class="stretch" />
        </td>
    </tr>
    <tr>
        <td class="row1 field-title">Меню URL:</td>
        <td class="row2"><input type="text" name="menu_link2" value="{$aData.menu_link|default:''}" class="stretch" /></td>
    </tr>
    <tr>
        <td class="row1 field-title">Meta Keywords:<br /><span class="desc">{$aLang.meta_keywords}</span></td>
        <td class="row2"><textarea name="mkeywords2" style="height: 85px;">{$aData.mkeywords|default:''}</textarea></td>
    </tr>
    <tr>
        <td class="row1 field-title">Meta Description:<br /><span class="desc">{$aLang.meta_description}</span></td>
        <td class="row2"><textarea name="mdescription2" style="height: 85px;">{$aData.mdescription|default:''}</textarea></td>
    </tr>     
</tbody>

{* TYPE: MENU *} 
<tbody id="divmenuname" style="display:none;">  
    <tr>
        <td class="row1 field-title" width="100">Название:</td>
        <td class="row2">
            <input type="text" name="menu_title4" value="{$aData.title}" class="stretch" />
        </td>
    </tr>
    <tr>
        <td class="row1 field-title">Meta Keywords:<br /><span class="desc">{$aLang.meta_keywords}</span></td>
        <td class="row2"><textarea name="mkeywords4" style="height: 85px;">{$aData.mkeywords|default:''}</textarea></td>
    </tr>
    <tr>
        <td class="row1 field-title">Meta Description:<br /><span class="desc">{$aLang.meta_description}</span></td>
        <td class="row2"><textarea name="mdescription4" style="height: 85px;">{$aData.mdescription|default:''}</textarea></td>
    </tr>        
</tbody>                                  
    

<tr class="footer">
    <td class="row1" colspan="3">
        <span id="divbtn" style="display:none;">
        	<input type="submit" class="button submit" value="Создать" />        
        </span>
        <input type="button" class="button cancel" value="Отмена" onclick="document.location='index.php?s={$class}&amp;ev=listing'" />
    </td>
</tr>
</table>
</form>