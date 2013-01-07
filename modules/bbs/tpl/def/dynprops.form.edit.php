<?php

    $prefix = (isset($aData['prefix'])?$aData['prefix']:'dynprops');
    $prefix_id = 'edit_d_';
    $child_tpl_path  = PATH_MODULES.'bbs/tpl/def/';

    //echo '<pre>', print_r($aData, true), '</pre>'; exit;
    
    function buildCheckboxesBlocksForm(&$result, $data, $n, $values, callback $funcLI)
    {                                                        
        $cols = ($n<=6 ? 1 : 2);
        $inColumn = ceil($n/$cols);
        $specialColumns = round(( ($n/$cols) - intval($n/$cols)) * $cols);
        $c = 1.0;      
        $added = 1;
        $result .= '<ul class="left">';
        foreach($data as $k=>$v)
        {
            $res = $funcLI($k, $v, $values);
            if(empty($res)) continue;
            $result .= $res;
            if($added++ >= $inColumn && --$cols>0) {
                $result .= '</ul><ul class="left">';
                $added = 1;
                if($c++ == $specialColumns) {
                    --$inColumn;                    
                }                    
            }                      
        }
        $result .= '</ul><div class="clear"></div>';
    }
    
    foreach($aData['dynprops'] as $d)
    {
        $name = $prefix.'['.$d['id'].']';
        $extra = 'dyntype="'.$d['type'].'" id="'.$prefix_id.$d['id'].'"';
        
        if($d['type'] == dbDynprops::typeCheckbox) {
            $checkboxTitle = $d['title'];
        }
        $d['title'] .= (!empty($d['description'])?' '.$d['description']:'').':'.($d['req']?'<span class="req">*</span>':'');
        $adtxtClass = ($d['txt']?'adtxt':'');
        
        switch($d['type'])
        {
             case dbDynprops::typeRadioGroup:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <div class="padTop"><?= $d['title']; ?></div>
                    <div class="padTop">
                    <?php
                    
                        $sHTML = '';
                        buildCheckboxesBlocksForm($sHTML, $d['multi'], sizeof($d['multi']), $value, create_function('$k,$dm,$value', '
                            return \'<li><label><input type="radio" name="'.$name.'" class="'.$adtxtClass.'" '.$extra.' title="\'.$dm[\'name\'].\'" value="\'.$dm[\'value\'].\'" \'.($value == $dm[\'value\']?\' checked="checked"\':\'\').\' />\'.$dm[\'name\'].\'</label></li>\';'));
                        echo $sHTML;                     
                    
                        //foreach($d['multi'] as $dm) {
//                           echo '<label><input type="radio" name="'.$name.'" class="'.$adtxtClass.'" '.$extra.' title="'.$dm['name'].'" value="'.$dm['value'].'" '.($value == $dm['value'] ? 'checked="checked"' : '').'/>'.$dm['name'].'</label>';
//                        }
                    ?> 
                    </div>
                  </div> <?php
            }break; 
            case dbDynprops::typeRadioYesNo:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop"><?= '<label><input type="radio" name="'.$name.'" '.$extra.' class="'.$adtxtClass.'" title="Да" value="2" '.($value == 2?'checked="checked"':'').' />Да</label><label><input type="radio" name="'.$name.'" '.$extra.' title="Нет" class="'.$adtxtClass.'" value="1" '.($value == 1?'checked="checked"':'').' />Нет</label>'
                        ?></div>
                  </div> <?php
            }break;             
            case dbDynprops::typeCheckboxGroup:
            {            
                $value = ( isset($d['value']) && $d['value'] ? explode(';', $d['value']) : explode(';', $d['default_value']) );                
                ?><div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop" id="<?= $prefix_id.$d['id']; ?>">
                    <?php
                        $extra = 'dyntype="'.$d['type'].'"';
                        
                        $sHTML = '';
                        buildCheckboxesBlocksForm($sHTML, $d['multi'], sizeof($d['multi']), $value, create_function('$k,$dm,$value', '
                            return \'<li><label><input type="checkbox" name="'.$name.'[]" '.$extra.' class="'.$adtxtClass.'" title="\'.$dm[\'name\'].\'" \'.(in_array($dm[\'value\'], $value)?\' checked="checked"\':\'\').\' value="\'.$dm[\'value\'].\'" />\'.$dm[\'name\'].\'</label></li>\';'));
                        echo $sHTML;                        
                        
//                        foreach($d['multi'] as $dm) {
//                           echo '<label><input type="checkbox" name="'.$name.'[]" '.$extra.' class="'.$adtxtClass.'" title="'.$dm['name'].'" '.(in_array($dm['value'], $value)?'checked="checked"':'').' value="'.$dm['value'].'" />'.$dm['name'].'</label><br/>';
//                        }
                    ?>
                    </div> 
                  </div><?php
            }break; 
            case dbDynprops::typeCheckbox:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop">
                        <?= '<input type="checkbox" name="'.$name.'" '.$extra.' class="'.$adtxtClass.'" title="'.$checkboxTitle.'" value="1" '.($value?'checked="checked"':'').' />' ?>
                    </div>
                  </div> <?php
            }break; 
            case dbDynprops::typeSelect:
            {   
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                $class = 'inputText2';
                if($d['parent']) {
                   $extra .= ' ';
                   $class = '';
                ?><div class="padTop">
                    <div class="left padRight"><?= $d['title']?>
                        <div class="padTop">
                        <?= '<select name="'.$name.'" '.$extra.' onchange="editDynpropsParentSelect('.$d['id'].', this.value, \''.$prefix.'\', this);" class="inputText2 '.$adtxtClass.($d['req']?' req':'').'">' ?>
                    <?php
                        foreach($d['multi'] as $dm) {
                           echo '<option value="'.$dm['value'].'" '.($value == $dm['value'] ? 'selected="selected"' : '').'>'.$dm['name'].'</option>';
                        }
                        $extra = 'dyntype="'.$d['type'].'"';
                    ?> 
                        </select>
                        </div>
                    </div>
                    <div class="left"><?= $d['child_title'].':'.($d['req']?'<span class="req">*</span>':'')?>
                        <div class="padTop">
                            <?
                               if(!empty($value) && isset($aData['children'][$d['id']])) {  
                                   $aChildDynprop = current($aData['children'][$d['id']]);
                                   $aChildDynprop['default_value'] = (!empty($d['child_default']) ? $d['child_default'] : false);
                                   $childSelectOpts = $this->formChildEdit($aChildDynprop, 'dynprops.form.child.php', $child_tpl_path);
                                   ?> <select class="inputText2 <?= $adtxtClass.($d['req']?' req':'') ?>" id="<?= $prefix_id.$d['id']; ?>_child" <?= $extra; ?> name="<?= $prefix.'['.$childSelectOpts['id'].']'; ?>"><?= $childSelectOpts['form']; ?></select> <?                                   
                               } else {
                                   echo '<select disabled="disabled" name="'.$name.'" '.$extra.' id="'.$prefix_id.$d['id'].'_child" class="inputText2 '.$adtxtClass.($d['req']?' req':'').'"></select>';
                               }
                            ?>                            
                        
                        </div>
                    </div>                    
                  </div> 
                  <div class="clear"></div>
              <?php } else { ?>
                  <div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop"><?= '<select name="'.$name.'" '.$extra.' class="inputText2 '.$adtxtClass.'">' ?>
                        <?php
                        foreach($d['multi'] as $dm) {
                           echo '<option value="'.$dm['value'].'" '.($value == $dm['value'] ? 'selected="selected"' : '').'>'.$dm['name'].'</option>';
                        } ?> 
                        </select>
                    </div>
                  </div> <?php
                  }
            }break; 
            case dbDynprops::typeInputText:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop"><?= '<input type="text" name="'.$name.'" '.$extra.' value="'.$value.'" class="inputText2 '.$adtxtClass.($d['req']?' req':'').'" style="width:427px;"/>' ?></div>
                  </div> <?php
            }break; 
            case dbDynprops::typeTextarea:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop"><?= '<textarea type="text" name="'.$name.'" '.$extra.' class="inputText2 '.$adtxtClass.($d['req']?' req':'').'" style="width:652px; height:80px;">'.$value.'</textarea>' ?></div>
                  </div> <?php
            }break; 
            case dbDynprops::typeNumber:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop">
                        <?= '<input type="text" name="'.$name.'" '.$extra.' value="'.$value.'" class="inputText2 '.$adtxtClass.($d['req']?' req':'').'" style="width:127px;"/>' ?>
                        <?php
                            if($d['parent'] && isset($aData['children'][$d['id']])) {
                                $childSelectOpts = $this->formChildEdit(current($aData['children'][$d['id']]), 'dynprops.form.child.php', $child_tpl_path);
                                ?> <select class="<?= $adtxtClass ?>" id="<?= $prefix_id.$d['id']; ?>_unit" name="<?= $prefix.'['.$childSelectOpts['id'].']'; ?>"><?= $childSelectOpts['form']; ?></select><?
                            }
                        ?>
                    </div>
                  </div> <?php
            }break; 
            case dbDynprops::typeRange:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <div class="padTop"><?= $d['title']?></div>
                    <div class="padTop"><?= '<select name="'.$name.'" '.$extra.' class="inputText2 '.$adtxtClass.($d['req']?' req':'').'">' ?>
                        <?php
                        if(is_string($value)) {
                            echo '<option value="0">'.$value.'</option>';
                        }
                        for($i=$d['start']; $i<=$d['end'];$i+=$d['step']) {
                           echo '<option value="'.$i.'" '.($value == $i ? 'selected="selected"' : '').'>'.$i.'</option>';
                        } ?> 
                        </select>
                        <?php            
                            if($d['parent'] && isset($aData['children'][$d['id']])) {
                                $childSelectOpts = $this->formChildEdit(current($aData['children'][$d['id']]), 'dynprops.form.child.php', $child_tpl_path);
                                if($childSelectOpts['id']) {
                                    ?> <select class="<?= $adtxtClass ?>" id="<?= $prefix_id.$d['id']; ?>_unit" name="<?= $prefix.'['.$childSelectOpts['id'].']'; ?>"><?= $childSelectOpts['form']; ?></select><?
                                }
                            }
                        ?>                        
                    </div>
                  </div> <?php
            }break; 
        }
    }
?>
<script type="text/javascript">
function editDynpropsParentSelect(id, val, namePrefix, inputParent)
{
    bff.ajax('/ajax/bbs?act=dp-child', {dp_id: id, dp_value:val}, function(data){
        if(data) {
            var $inputChild = $('#<?= $prefix_id ?>'+id+'_child');
            $inputChild.html( ( data.form ? data.form : '') )
                  .attr('name', namePrefix+'['+data.id+']');
            if(intval(data.id)) {
                $inputChild.removeAttr('disabled');
            } else {
                $inputChild.attr('disabled', 'disabled');
            }
        }
    });
}

function txtDynprops(txt){
    with(bffDynpropsTextify) {
        <? foreach($aData['dynprops'] as $d)
        { 
            if($d['txt']) {
                echo 'txt = add(txt, '.$d['type'].', 0, \''.$d['id'].'\', \', \', \''.tpl::escape($d['txt_text'], 'javascript').'\');';
                if($d['parent'] && $d['type']==dbDynprops::typeSelect) {
                echo 'txt = add(txt, '.$d['type'].', 0, \''.$d['id'].'_child\', \' \', \''.tpl::escape($d['txt_text'], 'javascript').'\');';
                }
            }
        } ?>
    }
    return txt;    
}

</script>
                    
