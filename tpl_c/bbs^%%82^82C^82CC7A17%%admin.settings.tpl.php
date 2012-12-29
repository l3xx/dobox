<?php /* Smarty version 2.6.7, created on 2012-12-26 00:57:11
         compiled from admin.settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'admin.settings.tpl', 34, false),array('function', 'html_options', 'admin.settings.tpl', 140, false),)), $this); ?>
<script type="text/javascript">
//<![CDATA[ 
var helper = null;                        
<?php echo '    
function bbsSettingsHelper()
{   
    this.currentTab = \'general\';
    this.changeTab = function( tab )
    {   
        if(this.currentTab == tab)
            return;
                            
        $(\'div.conftab[id!="\'+tab+\'"]\').hide();
        $(\'#\'+tab).show();
        
        $(\'#tabs span.tab\').removeClass(\'tab-active\');
        $(\'#tabs span[rel="\'+tab+\'"]\').addClass(\'tab-active\');
        
        this.currentTab = document.getElementById(\'tab\').value = tab;
    } 
    
    this.editPS = function(ps)
    {
        $(\'div[id^="ps_"][id!="ps_\'+ps+\'"]\').slideUp(\'fast\');
        $(\'#ps_\'+ps).slideToggle(\'fast\', function(){ 
            //document.getElementById(\'psupdate\').value = ($(this).is(":visible")?ps:\'\');  
        });
        return false;
    } 
}

$(function(){
    helper = new bbsSettingsHelper();
    helper.changeTab("';  echo ((is_array($_tmp=@$this->_tpl_vars['tab'])) ? $this->_run_mod_handler('default', true, $_tmp, 'general') : smarty_modifier_default($_tmp, 'general'));  echo '", 0); 
});
//]]> 
'; ?>

</script>

<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span>Объявления / Настройки</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">  

<form method="post" action="">                        
<input type="hidden" name="save" value="1" />                 
<input type="hidden" name="tab" id="tab" value="" />


<div class="tabsBar" id="tabs">    
    <?php if (count($_from = (array)$this->_tpl_vars['aData']['tabs'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
        <span class="tab <?php if ($this->_tpl_vars['v']['a']): ?>tab-active<?php endif; ?>" onclick="helper.changeTab('<?php echo $this->_tpl_vars['k']; ?>
');" rel="<?php echo $this->_tpl_vars['k']; ?>
"><?php echo $this->_tpl_vars['v']['t']; ?>
</span>
    <?php endforeach; endif; unset($_from); ?>                     
</div>   

<!-- general -->
<div id="general" class="conftab" style="display:;">
    <table class="admtbl tbledit" style="margin:10px 0 10px 10px; border-collapse:separate;"> 
    <tr style="display:none;">
	    <td class="row1 field-title">Количество объявлений на странице:</td>
	    <td class="row2">
             <input style="width:95px;" type="text" name="config[items_perpage]" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['items_perpage'])) ? $this->_run_mod_handler('default', true, $_tmp, 10) : smarty_modifier_default($_tmp, 10)); ?>
" />
        </td>
    </tr>
    <tr>
        <td class="row1 field-title" width="350">Доступное кол-во бесплатных публикаций в одном разделе:<br/>для зарегистрированных</td>
        <td class="row2">
             <input style="width:95px;" type="text" name="config[items_freepubl_category_limit_reg]" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['items_freepubl_category_limit_reg'])) ? $this->_run_mod_handler('default', true, $_tmp, 10) : smarty_modifier_default($_tmp, 10)); ?>
" />
        </td>
    </tr>
    <tr>
        <td class="row1 field-title">Доступное кол-во бесплатных публикаций в одном разделе:<br/>для <b>не</b>зарегистрированных</td>
        <td class="row2">
             <input style="width:95px;" type="text" name="config[items_freepubl_category_limit]" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['items_freepubl_category_limit'])) ? $this->_run_mod_handler('default', true, $_tmp, 10) : smarty_modifier_default($_tmp, 10)); ?>
" />
        </td>
    </tr>   
    <tr>
        <td class="row1 field-title" style="width:170px;">Максимальный объем текста сообщения (символов):<br/></td>
        <td class="row2">
             <input style="width:95px;" type="text" name="config[adtxt_limit]" value="<?php echo $this->_tpl_vars['aData']['adtxt_limit']; ?>
" />
        </td>
    </tr>    
    </table>
</div>             

<!-- svc -->  
<div id="svc" class="conftab" style="display: none;">
   
   <table class="admtbl tbledit" style="margin:10px 0 10px 10px; border-collapse:separate;"> 
        <tr>
            <td style=" vertical-align:top;">Платное размещение:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_publicate_price]" value="<?php echo $this->_tpl_vars['aData']['svc_publicate_price']; ?>
" /> <span class="desc">&nbsp;руб.</span> </label>
                <textarea name="config[svc_publicate_desc]" id="svc_publicate_desc" class="svc_desc" style="height: 135px; width: 560px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['svc_publicate_desc'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea>
            </td>
        </tr>   
        <tr>
            <td style="width:130px; vertical-align:top;">Поднять объявление:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_up_price]" value="<?php echo $this->_tpl_vars['aData']['svc_up_price']; ?>
" /> <span class="desc">&nbsp;руб.</span> </label>
                <textarea name="config[svc_up_desc]" id="svc_up_desc" class="svc_desc" style="height: 135px; width: 560px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['svc_up_desc'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea>
            </td>
        </tr>
        <tr>
            <td style=" vertical-align:top;">Выделить объявления:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_mark_price]" value="<?php echo $this->_tpl_vars['aData']['svc_mark_price']; ?>
" /> <span class="desc">&nbsp;руб.</span> </label>
                <textarea name="config[svc_mark_desc]" id="svc_mark_desc" class="svc_desc" style=" height: 135px; width: 560px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['svc_mark_desc'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea>
            </td>
        </tr>
        <tr>
            <td style=" vertical-align:top;">Премиум:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_premium_price]" value="<?php echo $this->_tpl_vars['aData']['svc_premium_price']; ?>
" /> <span class="desc">&nbsp;руб.</span> </label>
                <textarea name="config[svc_premium_desc]" id="svc_premium_desc" class="svc_desc" style="height: 135px; width: 560px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['svc_premium_desc'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea>
            </td>
        </tr>
        <tr>
            <td style=" vertical-align:top;">Публикация в прессе:</td>
            <td>
                <label><input style="width:95px; margin-bottom:5px;" type="text" name="config[svc_press_price]" value="<?php echo $this->_tpl_vars['aData']['svc_press_price']; ?>
" /> <span class="desc">&nbsp;руб.</span> </label>
                <textarea name="config[svc_press_desc]" id="svc_press_desc" class="svc_desc" style="height: 135px; width: 560px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['svc_press_desc'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea>
            </td>
        </tr>
    </table> 
    
</div>

<!-- files -->  
<div id="files" class="conftab" style="display: none;">
   
   <table class="admtbl tbledit" style="margin:10px 0 10px 10px; border-collapse:separate;"> 
        <tr>
            <td style="width:150px;">Кол-во изображений<br />для зарегистрированных:</td>
            <td>
                <?php echo smarty_function_html_options(array('name' => 'config[images_limit_reg]','options' => $this->_tpl_vars['aData']['options']['limit10'],'style' => 'width:50px;','selected' => $this->_tpl_vars['aData']['images_limit_reg']), $this);?>

            </td>
        </tr>
        <tr>
            <td>Кол-во изображений<br />для <b>не</b>зарегистрированных:</td>
            <td>
                <?php echo smarty_function_html_options(array('name' => 'config[images_limit]','options' => $this->_tpl_vars['aData']['options']['limit10'],'style' => 'width:50px;','selected' => $this->_tpl_vars['aData']['images_limit']), $this);?>

            </td>
        </tr>
    </table> 
    
</div>

<!-- add_instruction -->
<div id="add_instruction" class="conftab" style="display: none;">
    <table class="admtbl tbledit"> 
    <tr>
        <td class="row1" style="width:45px;"><span class="field-title">ШАГ 1:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct1]" id="add_instruct1" class="add_instruct" style="height: 135px; width: 680px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['add_instruct1'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea></td>
    </tr>
    <tr>
        <td class="row1"><span class="field-title">ШАГ 2:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct2]" id="add_instruct2" class="add_instruct" style="height: 135px; width: 680px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['add_instruct2'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea></td>
    </tr>
    <tr>
        <td class="row1"><span class="field-title">ШАГ 3:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct3]" id="add_instruct3" class="add_instruct" style="height: 135px; width: 680px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['add_instruct3'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea></td>
    </tr>
    <tr>
        <td class="row1"><span class="field-title">ШАГ 4:</span></td>                            
        <td class="row2"><textarea name="config[add_instruct4]" id="add_instruct4" class="add_instruct" style="height: 135px; width: 680px;"><?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['add_instruct4'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</textarea></td>
    </tr>
    </table>

<script type="text/javascript">
//<![CDATA[                               
<?php echo '    
$(function(){
    $(\'textarea.add_instruct, textarea.svc_desc\').bffWysiwyg({autogrow: false});
});
//]]> 
'; ?>

</script>
    
</div>

<table class="admtbl">
<tr class="footer">  
    <td>
        <input type="submit" class="button submit" value="Сохранить" />
    </td>
</tr>
</table>
</form>



        </div>
    </div>                                    
    <div class="bottom"></div>   
    <div class="clear-all"></div>
</div>    