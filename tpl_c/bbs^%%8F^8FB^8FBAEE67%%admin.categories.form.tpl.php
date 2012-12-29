<?php /* Smarty version 2.6.7, created on 2012-12-26 01:05:31
         compiled from admin.categories.form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'admin.categories.form.tpl', 34, false),array('modifier', 'jwysiwyg', 'admin.categories.form.tpl', 34, false),)), $this); ?>
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
            <td class="row2"><?php if (! $this->_tpl_vars['aData']['edit']): ?><select name="pid" style="width:350px;"><?php echo $this->_tpl_vars['aData']['pid_options']; ?>
</select><?php else: ?><div style="height:18px;" class="bold"><?php echo $this->_tpl_vars['aData']['pid_options']; ?>
</div><input type="hidden" name="pid" value="<?php echo $this->_tpl_vars['aData']['pid']; ?>
" /><?php endif; ?></td>
        </tr>
        <tr class="required">
            <td class="row1"><span class="field-title">Название</span>:</td>
            <td class="row2"><input type="text" name="title" value="<?php echo $this->_tpl_vars['aData']['title']; ?>
" class="stretch" /></td>
        </tr>
        <?php if ($this->_tpl_vars['bbs']->url_keywords): ?>
        <tr>
            <td class="row1"><span class="field-title">Keyword</span>:</td>   
            <td class="row2">                   
                <div class="left relative" style="width:100%">
                    <label for="keyword" class="placeholder">keyword для URL</label>  
                    <input class="stretch" type="text" maxlength="90" name="keyword" id="keyword" placeholder="keyword для URL" value="<?php echo $this->_tpl_vars['aData']['keyword']; ?>
" />
                </div> 
                <div class="clear-all"></div>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="row1"><span class="field-title">Краткое описание</span>:</td>
            <td class="row2"><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['aData']['textshort'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')))) ? $this->_run_mod_handler('jwysiwyg', true, $_tmp, 'textshort', 600, 90) : smarty_modifier_jwysiwyg($_tmp, 'textshort', 600, 90)); ?>
</td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Подробное описание</span>:</td>
            <td class="row2"><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['aData']['textfull'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')))) ? $this->_run_mod_handler('jwysiwyg', true, $_tmp, 'textfull', 600, 160) : smarty_modifier_jwysiwyg($_tmp, 'textfull', 600, 160)); ?>
</td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Title</span>:</td>
            <td class="row2"><input type="text" name="mtitle" value="<?php echo $this->_tpl_vars['aData']['mtitle']; ?>
" class="stretch" /></td>
        </tr>        
        <tr>
            <td class="row1"><span class="field-title">Meta Keywords</span>:<br /><span class="desc">перечень ключевых слов через запятую;<br />максимум 255 символов</span></td>
            <td class="row2"><textarea name="mkeywords" style="height: 150px;" class="stretch"><?php echo $this->_tpl_vars['aData']['mkeywords']; ?>
</textarea></td>
        </tr>
        <tr>
            <td class="row1"><span class="field-title">Meta Description</span>:<br /><span class="desc">описание содержимого страницы;<br />максимум 255 символов</span></td>
            <td class="row2"><textarea name="mdescription" style="height: 150px;" class="stretch"><?php echo $this->_tpl_vars['aData']['mdescription']; ?>
</textarea></td>
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