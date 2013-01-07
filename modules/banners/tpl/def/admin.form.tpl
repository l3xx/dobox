<script type="text/javascript">
var bannersShowDateMin = new Date({$aData.date_min});
{literal}
    $(function (){
        $('#banners_show_start').attachDatepicker({minDate: bannersShowDateMin, yearRange: '-2:+2'});
        $('#banners_show_finish').attachDatepicker({minDate: bannersShowDateMin, yearRange: '-2:+2'});
        
        var checks = $('input.catcheck');
        checks.click(function(){
            var checked = $(this).is(':checked');
            if($(this).hasClass('all')) {
                if(checked) {
                    checks.filter(':not(.all)').removeAttr('checked');
                }
            } else {
                checks.filter('.all').removeAttr('checked');
            }
        });
    });
function showInput(val)
{ 
  $('[id^=cont_]').hide(); 
  $('#'+val).show(); 
} 
    
{/literal} 
</script>
<form method="post" action="" enctype="multipart/form-data">
<input type="hidden" name="errno" value="" />
<input type="hidden" name="rec" value="{$aData.id|default:''}" />
<table class="admtbl tbledit">
<tr>
    <td class="row1" width="150">Позиция:</td>
    <td class="row2">
        <select name="position" style="width: 280px;">
            {foreach from=$aPosOptions item=v key=k}
                <option value="{$v.keyword}" {if $aData.position == $v.keyword} selected="selected"{/if}>{$v.title} (ширина {$v.width}px; высота {$v.height}px;)</option>
            {/foreach}
        </select>  
    </td>
</tr>
<tr>
    <td class="row1">Раздел:</td>
    <td class="row2">
        {$aCategories.checks}
    </td>
</tr>
<tr>
    <td class="row1">Дата начала показа:</td>
    <td class="row2">
        <input type="text" name="show_start" id="banners_show_start" value="{$aData.show_start|date_format:"%d-%m-%Y %H:%M"}" style="width:100px;" />
    </td>
</tr>
<tr>
    <td class="row1">Дата окончания показа:</td>
    <td class="row2">
        <input type="text" name="show_finish" id="banners_show_finish" value="{$aData.show_finish|date_format:"%d-%m-%Y %H:%M"}" style="width:100px;" />
    </td>
</tr>
<tr>
    <td class="row1">Лимит показов <span class="desc">(число)</span>:</td>
    <td class="row2">
        <input type="text" name="show_limit" value="{if $aData.show_limit==0}нет лимита{else}{$aData.show_limit}{/if}" style="width:100px;" />
    </td>
</tr>
<tr>
    <td class="row1">Тип баннера:</td>
    <td class="row2">
        <label><input type="radio" name="banner_type" value="1" {if $aData.banner_type==$smarty.const.BANNERS_TYPE_IMG} checked="checked"{/if} onclick="showInput('cont_image');" />&nbsp;Изображение </label> <br />
        <label><input type="radio" name="banner_type" value="2" {if $aData.banner_type==$smarty.const.BANNERS_TYPE_FLASH} checked="checked"{/if} onclick="showInput('cont_flash');"/>&nbsp;Flash </label> <br />
        <label><input type="radio" name="banner_type" value="3" {if $aData.banner_type==$smarty.const.BANNERS_TYPE_CODE} checked="checked"{/if} onclick="showInput('cont_code');" />&nbsp;Код </label> <br />
    </td>
</tr>
<tr id="cont_image" {if $aData.banner_type!=$smarty.const.BANNERS_TYPE_IMG}style="display:none;"{/if} >
    <td class="row1">Изображение:</td>
    <td class="row2">
        {if $aData.img_big|default:''}
            <a href="{$aData.img_big|default:''}" target="_blank"><img src="{$aData.img_small|default:''}" alt="" title="original size" /></a><br /><br />
        {/if}
        <input size="30" type="file" name="bnrimg" />&nbsp;&nbsp;
        <label><input type="checkbox" value="1" name="resize_img" {if $aData.resize_img==1} checked="checked"{/if} />&nbsp;&nbsp;уменьшать изображение</label>
    </td>
</tr>
<tr id="cont_flash" {if $aData.banner_type!=$smarty.const.BANNERS_TYPE_FLASH}style="display:none;"{/if} >
    <td>Flash:</td>
    <td>
        <table>  
            <tr>
                <td class="row2">
                    {if $aData.banner_type == $smarty.const.BANNERS_TYPE_FLASH}
                        <script type="text/javascript">
                        var fG = new Flash ();
                        var width = {$aData.width}*0.25;
                        var height = {$aData.height}*0.25;
                        fG.setSWF ('{$smarty.const.BANNERS_URL}/{$aData.id|default:''}_src_{$aData.banner}', width, height);
                        fG.setParam ('wmode', 'transparent');
                        fG.display ();
                        </script><br /><br />
                    {/if}
                    <input type="file" size="30" name="flash" />
                </td>
            </tr>
            <tr>
                <td class="row1" >
                    <input type="text" name="flash_width" value="{$aData.flash.width|default:''}" style="width:50px;" /> 
                    <span style="padding-top: 3px;">&nbsp;&nbsp;&nbsp;Ширина&nbsp;<span class="desc">(px)</span></span>
                </td>
            </tr>
            <tr >
                <td class="row2">
                   <input type="text" name="flash_height" value="{$aData.flash.height|default:''}" style="width:50px;" />
                   <span style="padding-top: 3px;">&nbsp;&nbsp;&nbsp;Высота&nbsp;<span class="desc">(px)</span></span>
                </td>
            </tr> 
            <tr >
                <td class="row2">
                    <input type="text" name="flash_key" value="{$aData.flash.key|default:''}" style="width:50px;" />
                    <span style="padding-top: 3px;">&nbsp;&nbsp;&nbsp;Ключ&nbsp;<span class="desc">(flashvars)<span></span>
                </td>
            </tr>
        </table>  
    </td>    
</tr>
<tr id="cont_code" {if $aData.banner_type!=$smarty.const.BANNERS_TYPE_CODE}style="display:none;"{/if} >
	<td class="row1">Код:</td>
	<td class="row2"><textarea name="code" rows="5" style="width:490px;">{if $aData.banner_type==$smarty.const.BANNERS_TYPE_CODE}{$aData.banner|default:''}{/if}</textarea></td>
</tr>
<tr>
    <td class="row1">Ссылка:</td>
    <td class="row2">
        <input type="text" name="clickurl" value="{$aData.clickurl|default:''}" style="width:480px;" />
    </td>
</tr>
{*
<tr>
    <td class="row1">Ссылка размещения:<br /> <span style="color:#999;">(на странице с таким адресом в заданной позиции будет показываться баннер)</span></td>
    <td class="row2">
        <input type="text" name="showurl" value="{$aData.showurl|default:''}" style="width:480px;" />
    </td>
</tr>
<tr >
    <td class="row1">Показывать на страницах нижнего уровня:<br /><span style="color:#999;">(относительно введенного url)</span></td>
    <td class="row2">
        <input type="checkbox" name="showurl_recursive" value="1" {if $aData.showurl_recursive ==1}checked='checked'{/if} />
    </td>
</tr>
*}
<tr >
    <td class="row1">Включен</td>
    <td class="row2">
        <input type="checkbox" name="enabled" value="1" {if $aData.enabled==1}checked="checked"{/if} />
    </td>
</tr>
<tr>
    <td class="row1">Title:</td>
    <td class="row2">
        <input type="text" name="title" value="{$aData.title|default:''}" style="width:480px;" />
    </td>
</tr>
<tr>
    <td class="row1">Alt:</td>
    <td class="row2">
        <input type="text" name="alt" value="{$aData.alt|default:''}" style="width:480px;" />
    </td>
</tr>
<tr>
    <td class="row1">Комментарий:</td>
    <td class="row2"><textarea name="description" style="width:490px; height:70px;">{$aData.description|default:''}</textarea></td>
</tr>
{if $fordev}
<tr>
    <td class="row1">Ссылка подсчета переходов:</td>
    <td class="row2">
        <div class="input left">
            <span class="left"></span>
                <input type="text" name="link" value="{$aData.link|default:''}" style="width:490px;" />
            <span class="right"></span>
        </div>
    </td>
</tr>
{/if}
<tr class="footer">
    <td colspan="2">
        <input class="button submit" type="submit" value="Сохранить" />
        <input class="button cancel" type="button" value="Назад" onclick="history.back();" />
    </td>
</tr>
</table>
</form>