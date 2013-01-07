<?php /* Smarty version 2.6.7, created on 2012-12-29 13:41:03
         compiled from admin.items.form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'admin.items.form.tpl', 2, false),array('modifier', 'filesize', 'admin.items.form.tpl', 47, false),array('function', 'math', 'admin.items.form.tpl', 94, false),)), $this); ?>
<script type="text/javascript">
var itemID = <?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['id'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)); ?>
;
<?php echo '
//<![CDATA[ 
    function bbsItemAct(act, extra) {  
        return false;    
    }
//]]> 
'; ?>

</script>

<form method="post" action="" name="modifyInfo" enctype="multipart/form-data">
<input type="hidden" name="action" value="info" />
<input type="hidden" name="rec" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['id'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)); ?>
" />
<table class="admtbl tbledit">
<tr>
    <td class="row1" width="90">Категория:</td>
    <td class="row2"><select name="cat_id"><?php echo $this->_tpl_vars['aData']['cat_id_options']; ?>
</select></td>
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
    <td class="row1"><span class="field-title">Цена</span>:</td>
    <td class="row2"><input type="text" maxlength="9" name="price" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['aData']['price'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0')); ?>
" style="width:150px;" /> <?php echo $this->_tpl_vars['bbs']->items_currency['short']; ?>
</td>
</tr>
<tr>
    <td class="row1"><span class="field-title">Описание</span>: </td>
    <td class="row2"><textarea name="descr" style="height: 110px;" class="stretch"><?php echo $this->_tpl_vars['aData']['descr']; ?>
</textarea></td>
</tr>
<?php if (! $this->_tpl_vars['aData']['edit'] && $this->_tpl_vars['bbs']->items_images): ?>
<tr>
    <td class="row1">Изображения:</td>
    <td class="row2">
        <?php unset($this->_sections['fileinputs']);
$this->_sections['fileinputs']['name'] = 'fileinputs';
$this->_sections['fileinputs']['loop'] = is_array($_loop=$this->_tpl_vars['bbs']->items_images_limit) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['fileinputs']['show'] = true;
$this->_sections['fileinputs']['max'] = $this->_sections['fileinputs']['loop'];
$this->_sections['fileinputs']['step'] = 1;
$this->_sections['fileinputs']['start'] = $this->_sections['fileinputs']['step'] > 0 ? 0 : $this->_sections['fileinputs']['loop']-1;
if ($this->_sections['fileinputs']['show']) {
    $this->_sections['fileinputs']['total'] = $this->_sections['fileinputs']['loop'];
    if ($this->_sections['fileinputs']['total'] == 0)
        $this->_sections['fileinputs']['show'] = false;
} else
    $this->_sections['fileinputs']['total'] = 0;
if ($this->_sections['fileinputs']['show']):

            for ($this->_sections['fileinputs']['index'] = $this->_sections['fileinputs']['start'], $this->_sections['fileinputs']['iteration'] = 1;
                 $this->_sections['fileinputs']['iteration'] <= $this->_sections['fileinputs']['total'];
                 $this->_sections['fileinputs']['index'] += $this->_sections['fileinputs']['step'], $this->_sections['fileinputs']['iteration']++):
$this->_sections['fileinputs']['rownum'] = $this->_sections['fileinputs']['iteration'];
$this->_sections['fileinputs']['index_prev'] = $this->_sections['fileinputs']['index'] - $this->_sections['fileinputs']['step'];
$this->_sections['fileinputs']['index_next'] = $this->_sections['fileinputs']['index'] + $this->_sections['fileinputs']['step'];
$this->_sections['fileinputs']['first']      = ($this->_sections['fileinputs']['iteration'] == 1);
$this->_sections['fileinputs']['last']       = ($this->_sections['fileinputs']['iteration'] == $this->_sections['fileinputs']['total']);
?>
            <input type="file" name="image<?php echo $this->_sections['fileinputs']['index']; ?>
" size="30" value="" /><br />
        <?php endfor; endif; ?>
        <span class="desc">Размер одного файла не должен превышать <b><?php echo ((is_array($_tmp=$this->_tpl_vars['bbs']->items_images_maxsize)) ? $this->_run_mod_handler('filesize', true, $_tmp) : smarty_modifier_filesize($_tmp)); ?>
</b></span>
    </td>
</tr>
<?php endif; ?>
<tr>
    <td class="row1"><span class="field-title">Meta Keywords</span>:</td>
    <td class="row2"><textarea name="mkeywords" style="height: 110px;" class="stretch"><?php echo $this->_tpl_vars['aData']['mkeywords']; ?>
</textarea></td>
</tr>
<tr>
    <td class="row1"><span class="field-title">Meta Description</span>:</td>
    <td class="row2"><textarea name="mdescription" style="height: 110px;" class="stretch"><?php echo $this->_tpl_vars['aData']['mdescription']; ?>
</textarea></td>
</tr>
<tr>
    <td class="row1" colspan="2">
        <input type="submit" class="button submit" value="<?php echo $this->_tpl_vars['aLang']['save']; ?>
" />
        <input type="button" class="button cancel" value="<?php echo $this->_tpl_vars['aLang']['cancel']; ?>
" onclick="history.back();" />
    </td>
</tr>
</table>
</form>

<?php if ($this->_tpl_vars['aData']['edit'] && $this->_tpl_vars['bbs']->items_images): ?>
<br/><hr class="cut"/><br/>       
<form method="post" action="" name="img" enctype="multipart/form-data">
<input type="hidden" name="action" value="image_add" />
<table class="admtbl tbledit">
<tr class="row1">
    <th colspan="2" align="center">изображения</th> 
</tr>
<tr class="row1">
    <td colspan="2">
        <div>
           <?php if (count($_from = (array)$this->_tpl_vars['aData']['img'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
               <?php if ($this->_tpl_vars['v']): ?>
                <div style="width:20%; float:left; text-align:center; padding-bottom:10px;">
                    <a href="javascript:$.fancybox('/files/images/items/<?php echo $this->_tpl_vars['aData']['id'];  echo $this->_tpl_vars['v']; ?>
', {type:'image'});">
                        <img src="/files/images/items/<?php echo $this->_tpl_vars['aData']['id']; ?>
s<?php echo $this->_tpl_vars['v']; ?>
" />
                    </a>
                    <div style="width:60px; margin: 0 auto;">
                        <a class="but del" href="index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=<?php echo $this->_tpl_vars['event']; ?>
&amp;action=image_del&amp;rec=<?php echo $this->_tpl_vars['aData']['id']; ?>
&amp;image=<?php echo $this->_tpl_vars['v']; ?>
&amp;f=<?php echo $this->_tpl_vars['aData']['f']; ?>
" onclick="<?php echo $this->_tpl_vars['aLang']['delete_confirm']; ?>
" title="<?php echo $this->_tpl_vars['aLang']['delete']; ?>
"></a>
                        <a class="but <?php if ($this->_tpl_vars['v'] != $this->_tpl_vars['aData']['imgfav']): ?>un<?php endif; ?>fav" href="index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=<?php echo $this->_tpl_vars['event']; ?>
&amp;action=image_fav&amp;rec=<?php echo $this->_tpl_vars['aData']['id']; ?>
&amp;image=<?php echo $this->_tpl_vars['v']; ?>
&amp;f=<?php echo $this->_tpl_vars['aData']['f']; ?>
" title="сделать эскизом"></a>
                    </div>
                </div>
                <?php endif; ?>
           <?php endforeach; endif; unset($_from); ?> 
        </div>
        <div class="clear"></div>                         
        <?php echo smarty_function_math(array('assign' => 'fileinputs_count','equation' => "x-y",'x' => $this->_tpl_vars['bbs']->items_images_limit,'y' => $this->_tpl_vars['aData']['imgcnt']), $this);?>

        <?php unset($this->_sections['fileinputs']);
$this->_sections['fileinputs']['name'] = 'fileinputs';
$this->_sections['fileinputs']['loop'] = is_array($_loop=$this->_tpl_vars['fileinputs_count']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['fileinputs']['show'] = true;
$this->_sections['fileinputs']['max'] = $this->_sections['fileinputs']['loop'];
$this->_sections['fileinputs']['step'] = 1;
$this->_sections['fileinputs']['start'] = $this->_sections['fileinputs']['step'] > 0 ? 0 : $this->_sections['fileinputs']['loop']-1;
if ($this->_sections['fileinputs']['show']) {
    $this->_sections['fileinputs']['total'] = $this->_sections['fileinputs']['loop'];
    if ($this->_sections['fileinputs']['total'] == 0)
        $this->_sections['fileinputs']['show'] = false;
} else
    $this->_sections['fileinputs']['total'] = 0;
if ($this->_sections['fileinputs']['show']):

            for ($this->_sections['fileinputs']['index'] = $this->_sections['fileinputs']['start'], $this->_sections['fileinputs']['iteration'] = 1;
                 $this->_sections['fileinputs']['iteration'] <= $this->_sections['fileinputs']['total'];
                 $this->_sections['fileinputs']['index'] += $this->_sections['fileinputs']['step'], $this->_sections['fileinputs']['iteration']++):
$this->_sections['fileinputs']['rownum'] = $this->_sections['fileinputs']['iteration'];
$this->_sections['fileinputs']['index_prev'] = $this->_sections['fileinputs']['index'] - $this->_sections['fileinputs']['step'];
$this->_sections['fileinputs']['index_next'] = $this->_sections['fileinputs']['index'] + $this->_sections['fileinputs']['step'];
$this->_sections['fileinputs']['first']      = ($this->_sections['fileinputs']['iteration'] == 1);
$this->_sections['fileinputs']['last']       = ($this->_sections['fileinputs']['iteration'] == $this->_sections['fileinputs']['total']);
?>
            <input type="file" name="image<?php echo $this->_sections['fileinputs']['index']; ?>
" size="30" value="" /><br />
        <?php endfor; endif; ?>
        <?php if (((is_array($_tmp=@$this->_tpl_vars['fileinputs_count'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0')) > 0): ?>
            Размер одного файла не должен превышать <?php echo ((is_array($_tmp=$this->_tpl_vars['bbs']->items_images_maxsize)) ? $this->_run_mod_handler('filesize', true, $_tmp) : smarty_modifier_filesize($_tmp)); ?>
<br /><br />
            <input type="submit" class="button submit" value="Загрузить изображения" />
        <?php endif; ?> 
            <input type="button" class="button delete" value="Удалить все изображения" onclick="if(confirm('Удалить все изображения?')) document.location='index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=<?php echo $this->_tpl_vars['event']; ?>
&amp;action=image_del_all&amp;rec=<?php echo $this->_tpl_vars['aData']['id']; ?>
&amp;f=<?php echo $this->_tpl_vars['aData']['f']; ?>
';" />
    </td>
</tr>
</table>
</form>
<?php endif; ?>