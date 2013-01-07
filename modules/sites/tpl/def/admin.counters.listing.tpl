
<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Счетчики</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
            <table class="admtbl" id="listing">                 
                <tr class="header nodrag nodrop">
                    {if $fordev}<th width="60">ID</th>{/if}
                    <th width="150" align="left">Название</th>
                    <th width="130">Дата создания</th>
                    <th>Вид</th>
                    <th width="85">Действие</th>
                </tr> 
                {foreach from=$aData item=v key=k}
                <tr class="row{$k%2} {if !$v.enabled} desc{/if}" id="dnd-{$v.id}">
                    {if $fordev}<td >{$v.id}</td>{/if}
                    <td align="left">{$v.title}</td>
                    <td>{$v.created|date_format:'%d.%m.%Y'}</td>
                    <td>{$v.code}</td>
                    <td>
                        <a class="but {if $v.enabled}unblock{else}block{/if}" id="lnk_{$v.id}" onclick="bff.ajaxToggle({$v.id}, 'index.php?s=sites&ev=counters_listing&act=toggle', {ldelim}progress: '#progress-rotate'{rdelim});" href="javascript:void(0);"></a>
                        <a class="but edit" title="Редактировать" href="index.php?s={$class}&amp;ev=counters_edit&amp;rec={$v.id}"></a>
                        <a class="but del" title="Удалить"href="javascript:void(0);" onclick="bff.ajaxDelete('Удалить счетчик?', {$v.id}, 'index.php?s=sites&ev=counters_listing&act=delete', this, {ldelim}progress: '#progress-rotate'{rdelim}); return false;"></a>
                    </td>
                </tr>
                {foreachelse}
                <tr class="norecords">
                    <td colspan="{if $fordev}5{else}4{/if}">нет счетчиков</td>
                </tr>
                {/foreach}
            </table>
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

<div class="blueblock lightblock" id="addCounterDiv" style="display:none;">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Добавить счетчик</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
            <form action="" method="post" name="addCounterForm">
                <table class="admtbl tbledit">
                    <tr class="required">                          
                        <td style="white-space:nowrap; width:110px;" class="row1 field-title">Название<span class="required-mark">*</span>:</td>
                        <td class="row2"> <input type="text" name="title" maxlength="90" value="{$aDataAdd.title|default:''}" class="stretch" /> </td>
                    </tr>
                    <tr class="required">
                        <td class="row1 field-title">Код счетчика<span class="required-mark">*</span>:</td>
                        <td class="row2"><textarea name="code" style="height:130px;">{$aDataAdd.code|default:''}</textarea></td>
                    </tr>
                    <tr class="footer">
                        <td colspan="2" class="row1">
                            <input type="submit" class="button submit" value="Добавить" />
                            <input type="reset"  class="button cancel" value="Отмена" onclick="$('#addCounterDiv').slideUp('fast', function(){ldelim} $('#addCounterLink').fadeIn('fast'); {rdelim} ); return;" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

<div id="addCounterLink">
    <div class="left">    
        <a class="but add" title="Добавить счетчик" href="#" onclick=" $('#addCounterLink').hide(); $('#addCounterDiv').slideDown('fast', function(){ldelim} helper.check(); document.forms.addCounterForm['title'].focus(); {rdelim}); return false;" ></a>
    </div>
    <div class="right desc" style="width:70px;">
        <div style="width:50px;" class="left">&nbsp;<span id="progress-rotate" style="display:none;" class="progress"></span></div>
        &darr; &uarr;
    </div> 
</div>

<script type="text/javascript">
    var add = {$isAddAction|default:0}; var countersCnt = {$aData|@count}; var helper = null;
    {literal}  
    $(function(){ 
        helper = new bff.formChecker( document.forms.addCounterForm ); 
        if (!add) {
            $('#addCounterDiv').hide();
            $('#addCounterLink').show();
        } else {
            $('#addCounterDiv').show('normal', function(){ 
                helper.check(); 
                document.forms.addCounterForm['title'].focus();
            });
            $('#addCounterLink').hide();
        } 
        if(countersCnt>1)
            bff.initTableDnD('#listing', 'index.php?s=sites&ev=counters_listing&act=rotate', '#progress-rotate');
    });
    {/literal}
</script>