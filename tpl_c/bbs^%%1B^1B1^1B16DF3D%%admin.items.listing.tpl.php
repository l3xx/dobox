<?php /* Smarty version 2.6.7, created on 2012-12-29 13:40:38
         compiled from admin.items.listing.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'admin.items.listing.tpl', 2, false),array('modifier', 'truncate', 'admin.items.listing.tpl', 61, false),)), $this); ?>
<script type="text/javascript">
var bbsPropFilter = '&f=<?php echo ((is_array($_tmp=$this->_tpl_vars['aData']['f'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
';
<?php echo '
function bbsItemAct(id, act, extra)
{
    switch(act)
    {                                                  
        case \'edit\':   { bff.redirect( \'index.php?s=bbs&ev=items_edit&rec=\'+id+bbsPropFilter); } break;
        case \'comm\':   { bff.redirect( \'index.php?s=bbs&ev=items_comments&rec=\'+id+bbsPropFilter); } break;
        case \'claims\': { bff.redirect( \'index.php?s=bbs&ev=items_complaints&item=\'+id); } break;
        case \'press\':  { 
            bff.ajax(\'index.php?s=bbs&ev=items_listing&act=press&rec=\'+id, {}, function(data){
                $(extra).attr(\'disabled\', \'disabled\');
                }, \'#progress-bbs-items\');
                return false;    
            } break;
        case \'del\':  { 
            bff.ajaxDelete(\'sure\', id, \'index.php?s=bbs&ev=items_listing&act=delete\', extra, 
                {progress: \'#progress-bbs-items\'});
                return false;    
            } break;
    }
    
    return false;
}
'; ?>

</script>

<div class="actionBar">
    <form action="" name="filter">
        <input type="hidden" name="s" value="<?php echo $this->_tpl_vars['class']; ?>
"/>
        <input type="hidden" name="ev" value="<?php echo $this->_tpl_vars['event']; ?>
"/>
        <input type="hidden" name="uid" value="<?php echo $this->_tpl_vars['aData']['uid']; ?>
"/>
        <div class="left">
            <label for="filter_id_title" class="placeholder">ID или Описание</label>
            <input type="text" name="search" style="width:170px;" id="filter_id_title" placeholder="ID или Описание" value="<?php echo $this->_tpl_vars['aData']['search']; ?>
" />
            <input type="submit" class="button submit" value="найти" />
            <select name="cat_id" onchange="document.forms.filter.submit();" style="margin:0 10px; width:240px;"><?php echo $this->_tpl_vars['aData']['cats']; ?>
</select>
            <select name="svc" onchange="document.forms.filter.submit();" style="margin-right: 10px;"><?php echo $this->_tpl_vars['aData']['svcs']; ?>
</select>
            по: <select name="perpage" onchange="document.forms.filter.submit();" style="margin-right:10px;"><?php echo $this->_tpl_vars['aData']['perpage']; ?>
</select>
            <?php if ($this->_tpl_vars['aData']['uid'] > 0): ?><div style="padding: 7px 0;">Объявления пользователя: <a class="ajax userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec=<?php echo $this->_tpl_vars['aData']['uid']; ?>
"><?php echo $this->_tpl_vars['aData']['uinfo']['name']; ?>
 (<?php echo $this->_tpl_vars['aData']['uinfo']['email']; ?>
)</a>&nbsp;<a href="#" onclick="document.forms.filter.uid.value = 0; document.forms.filter.submit(); return false;" class="del_s"></a></div><?php endif; ?>
        </div>
        <div class="right" style="margin:5px 0 0 5px;">
            <a href="index.php?s=<?php echo $this->_tpl_vars['class']; ?>
&amp;ev=items_add<?php if ($this->_tpl_vars['aData']['cat_id'] > 0): ?>&amp;cat_id=<?php echo $this->_tpl_vars['aData']['cat_id'];  endif; ?>" class="hidden">+ добавить объявление</a>
        </div>
        <div class="clear-all"></div>
    </form>
</div>

<table class="admtbl tblhover" id="bbs_items_listing">
<tr class="header nordrag nodrop">
    <th width="60">ID</th>
    <th align="left">Объявление<span id="progress-bbs-items" style="display:none;" class="progress"></span></th>
    <th width="70" align="left">Цена</th>
    <?php if ($this->_tpl_vars['aData']['press']): ?><th width="65">В прессе</th><?php endif; ?>
    <th width="135">Действие</th>
</tr>
<?php if (count($_from = (array)$this->_tpl_vars['aData']['items'])):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
    <tr id="i_list_<?php echo $this->_tpl_vars['v']['id']; ?>
">
        <td><a href="/item/<?php echo $this->_tpl_vars['v']['id']; ?>
" target="_blank"><?php echo $this->_tpl_vars['v']['id']; ?>
</a></td>
        <td align="left" style="padding-right:5px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['v']['descr'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 200) : smarty_modifier_truncate($_tmp, 200));  if ($this->_tpl_vars['v']['claims']): ?><br/><a href="#" class="clr-error" onclick="return bbsItemAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'claims');">есть жалобы</a><?php endif;  if ($this->_tpl_vars['v']['status'] == @BBS_STATUS_BLOCKED): ?><br/><span class="clr-error">из черного списка</span><?php endif; ?></td>
        <td align="left"><?php echo $this->_tpl_vars['v']['price']; ?>
 <?php echo $this->_tpl_vars['curr_sign']; ?>
</td>  
        <?php if ($this->_tpl_vars['aData']['press']): ?><td><input type="checkbox" <?php if ($this->_tpl_vars['v']['press'] != @BBS_PRESS_PUBLICATED): ?>onclick="bbsItemAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'press', this);"<?php else: ?>disabled checked<?php endif; ?> /></td><?php endif; ?>
        <td>  
            <a class="but <?php if ($this->_tpl_vars['v']['status'] == @BBS_STATUS_PUBLICATED): ?>un<?php endif; ?>block itemlink" href="index.php?s=bbs&ev=ajax&act=item-info&rec=<?php echo $this->_tpl_vars['v']['id']; ?>
&iltype=<?php if ($this->_tpl_vars['aData']['mod']): ?>mod<?php elseif ($this->_tpl_vars['aData']['press']): ?>press<?php else: ?>1<?php endif; ?>" onclick="return false; return bbsItemAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'publicate', this);"></a>
            <?php if ($this->_tpl_vars['v']['user_id'] > 0): ?><a class="but user userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec=<?php echo $this->_tpl_vars['v']['user_id']; ?>
"></a><?php else: ?><a href="javascript:void(0);" class="but user disabled"></a><?php endif; ?>
            <a class="but edit" href="#" onclick="return bbsItemAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'edit');"></a>
            <a class="but comments" href="#" onclick="return bbsItemAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'comm');"></a>
            <a class="but del" href="#" onclick="return bbsItemAct(<?php echo $this->_tpl_vars['v']['id']; ?>
, 'del', this);"></a>
        </td>
    </tr>
<?php endforeach; else: ?>
    <tr class="norecords">
        <td colspan="4">
            объявлений не найдено
        </td>
    </tr>
<?php endif; unset($_from); ?>
</table>
<?php echo $this->_tpl_vars['pagenation_template']; ?>
