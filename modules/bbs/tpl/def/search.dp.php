<?php

    $child_tpl_path  = PATH_MODULES.'bbs/tpl/def/';

    $inputSource = bff::$isAjax ? 'postm' : 'getm';
    $this->input->$inputSource(array(
        'f' => TYPE_ARRAY_ARRAY,
        'fc' => TYPE_ARRAY_ARRAY,
    ), $p);
    
    //echo '<pre>', print_r($p, true), '</pre>'; exit;
    //echo '<pre>', print_r($aData, true), '</pre>'; exit;
    
    function buildCheckboxesBlocks(&$result, $data, $n, $values, callback $funcLI)
    {                                                        
        $cols = ($n<=16 ? 2 : ($n<=30 ? 3 : 4));
        $inColumn = ceil($n/$cols);
        $specialColumns = round(( ($n/$cols) - intval($n/$cols)) * $cols);
        $c = 1.0;      
        $added = 1;
        $result .= '<ul>';
        foreach($data as $k=>$v)
        {
            $res = $funcLI($k, $v, $values);
            if(empty($res)) continue;
            $result .= $res;
            if($added++ >= $inColumn && --$cols>0) {
                $result .= '</ul><ul>';
                $added = 1;
                if($c++ == $specialColumns) {
                    --$inColumn;                    
                }                    
            }                      
        }
        $result .= '</ul>';
    }
    
    foreach($aData['dynprops'] as $d)
    {
        $id = $d['id'];
        $fid = $d['data_field'];
        $name = 'f['.$fid.']';
        $type = &$d['type'];

        $d['parent'] = ( in_array($type, $this->typesAllowedParent) && $d['parent'] ? 1 : 0 );
        
        switch($type)
        {
            case dbDynprops::typeNumber:
            case dbDynprops::typeRange:
            {
                if(empty($p['f'][$fid])) { 
                    $values = array(); $from = 0; $to = 0;
                } else {
                    $values = &$p['f'][$fid];
                    $from = (!empty($values['f'])?intval($values['f']):0); 
                    $to = (!empty($values['t'])?intval($values['t']):0);
                    unset($values['f'], $values['t']);
                }
                
                $sel = false; 
                $custom = !empty($d['search_range_user']);
                foreach($d['search_ranges'] as $k=>$i){ 
                    $d['search_ranges'][$k]['title'] = ($i['from']&&$i['to'] ? $i['from'].'...'.$i['to'] : ($i['from'] ? '> '.$i['from'] : '< '.$i['to']));
                    if($sel===false && isset($values[$k])) {
                        $sel = $d['search_ranges'][$k]['title'];
                    }
                }                                 
                
                $active = (($custom && ($from||$to)?1:0) + sizeof($values));
                $sel = ($custom && ($from||$to)?($from&&$to?$from.' - '.$to.' ':(($from?'от '.$from:'до '.$to))):($sel === false ? 'не важно': $sel) ); 
                $unit = '';
                ?>
                <div class="selectBlock d<?= $id; ?><? if($active): ?> active<? endif; ?>">
                    <span class="left">&nbsp;</span>
                    <a class="right" href="#"><span class="pad"><b><?= $d['title'] ?></b><br/><span class="sel<? if($active>1): ?> plus<? endif; ?>"><?= $sel; ?></span></span> <span class="drop">&nbsp;</span></a>
                    <div class="clear"></div>
                    <div class="dropdown hidden">
                        <? if($custom){ ?>
                        <div>
                            <label>от <input name="<?= $name; ?>[f]" value="<?= $from>0?$from:''; ?>" class="from" type="text" /></label>&nbsp;
                            <label>до <input name="<?= $name; ?>[t]" value="<?= $to>0?$to:''; ?>" class="to" type="text" /></label>
                            <? if(!empty($aData['children'][$d['id']][0]['multi'])) { $u = current($aData['children'][$d['id']][0]['multi']); $unit = tpl::escape($u['name']); echo '<span class="unit">'.$unit.'</span>'; } ?>
                        </div>
                        <div class="clear"></div>
                        <? }
                        if(!empty($d['search_ranges'])) 
                        {                                       
                            $sHTML = '<ul><li '.(empty($values) ? 'class="select"':'').'><input '.(empty($values)?' checked="checked" disabled="disabled"':'').'class="checkAll" type="checkbox" id="d'.$id.'_0" /><label for="d'.$id.'_0"> <span>не важно</span></label></li></ul><div class="clear"></div>';
                            buildCheckboxesBlocks($sHTML, $d['search_ranges'], sizeof($d['search_ranges']), $values, create_function('$k,$i,$values', '
                                return \'<li><input type="checkbox" name="'.$name.'[\'.$k.\']" id="d'.$id.'_\'.$k.\'" 
                                    \'.(isset($values[$k])?\' checked\':\'\').\' value="1" /><label for="d'.$id.'_\'.$k.\'"> <span>\'.$i[\'title\'].\'</span></label></li>\';
                            '));
                            echo $sHTML;                        
                        /*
                         ?><ul><li<? if(empty($values)): ?> class="select"<? endif; ?>><input type="checkbox"<? if(empty($values)): ?> checked="checked" disabled="disabled"<? endif; ?> class="checkAll" id="d<?= $id.'_0'; ?>" /><label for="d<?= $id.'_0'; ?>"> <span>не важно</span></label></li>
                        <? foreach($d['search_ranges'] as $k=>$i){ ?>
                            <li><input type="checkbox" name="<?= $name; ?>[<?= $k; ?>]" id="d<?= $id.'_'.$k; ?>" <?= isset($values[$k])?'checked':'' ?> value="1" /><label for="d<?= $id.'_'.$k; ?>"> <span><?= $i['title']; ?></span></label></li>
                        <? } */ ?> 
                        <? } ?>
                        <div class="clear"></div>                     
                        <div class="buttonsCont">
                            <div class="button">
                                <span class="left">&nbsp;</span>
                                <input type="button" class="submit" value="отфильтровать" onclick="bbsSearch.filter(<?= $type.', '.$id.', \'.d'.$id.'\', 0, \'\', this' ?>);" style="width:114px;" />
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>  
                <?     
            } break; 
            case dbDynprops::typeSelect:
            case dbDynprops::typeSelectMulti:
            case dbDynprops::typeRadioGroup:
            case dbDynprops::typeCheckboxGroup:
            {
                if(empty($p['f'][$fid])) $p['f'][$fid] = array();
                $values = &$p['f'][$fid];
                
                $sel = 'не важно'; 
                $active = (sizeof($values));

                if($active>=1) { 
                    $valFirst = key($values); 
                    for($i = 0; $i<sizeof($d['multi']) ; $i++) {
                        if($d['multi'][$i]['value'] == $valFirst) {
                            $sel = $d['multi'][$i]['name'];
                            break;
                        }
                    }
                }
                ?>
                <div class="selectBlock d<?= $id; ?><? if($active): ?> active<? endif; ?>">
                    <span class="left">&nbsp;</span>
                    <a class="right" href="#"><span class="pad"><b><?= $d['title'] ?></b><br/><span class="sel<? if($active>1): ?> plus<? endif; ?>"><?= $sel; ?></span></span> <span class="drop">&nbsp;</span></a>
                    <div class="clear"></div>
                    <div class="dropdown hidden">                               
                        <? 
                        if(!empty($d['multi'])) 
                        { 
                            $dMultiFirst = current($d['multi']);
                            $sHTML = '<ul><li '.(empty($values) ? 'class="select"':'').'><input '.(empty($values)?' checked="checked" disabled="disabled"':'').($d['parent'] ? ' class="checkAll p" rel="'.$d['id'].'"' : 'class="checkAll"').' type="checkbox" id="d'.$id.'_0" /><label for="d'.$id.'_0"> <span>не важно</span></label></li></ul><div class="clear"></div>';
                            buildCheckboxesBlocks($sHTML, $d['multi'], (!empty($d['multi']) && $dMultiFirst['value']==0 ? sizeof($d['multi'])-1 : sizeof($d['multi']) ), $values, create_function('$k,$i,$values', '
                                if($i[\'value\']==0){ return \'\'; } $k = $i[\'value\'];
                                return \'<li><input type="checkbox"'.($d['parent'] ? ' class="p" rel="'.$d['id'].'"':'').' name="'.$name.'[\'.$k.\']" id="d'.$id.'_\'.$k.\'" 
                                    \'.(isset($values[$k])?\' checked\':\'\').\' value="\'.$k.\'" /><label for="d'.$id.'_\'.$k.\'"> <span>\'.$i[\'name\'].\'</span></label></li>\';
                            '));
                            echo $sHTML;
                            ?>
                        <? /* foreach($d['multi'] as $k=>$i){ if($i['value']==0) continue;  $k = $i['value'];?>
                            <li><input type="checkbox" <? if($d['parent']): ?>class="p" rel="<?= $d['id'] ?>"<? endif; ?> name="<?= $name; ?>[<?= $k; ?>]" id="d<?= $id.'_'.$k; ?>" <?= isset($values[$k])?'checked':'' ?> value="<?= $k; ?>" /><label for="d<?= $id.'_'.$k; ?>"> <span><?= $i['name']; ?></span></label></li>
                        <? }*/ ?>
                        <? } ?>
                        <div class="clear"></div>                     
                        <div class="buttonsCont">
                            <div class="button">
                                <span class="left">&nbsp;</span>
                                <input type="button" class="submit" value="отфильтровать" onclick="bbsSearch.filter(<?= $type.', '.$id.', \'.d'.$id.'\', '.$d['parent'].', \'\', this' ?>);" style="width:114px;" />
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>  
                <?  
                if($d['parent']) {
                    $active_c = 0;
                    $sel = false;
                    if($active) { 
                        $pairs = array();
                        foreach($values as $k=>$v) {
                            $pairs[] = array('parent_id'=>$d['id'],'parent_value'=>$k);
                        }
                        $child = $this->getByParentIDValuePairs($pairs);        
                        $child = (!empty($child[$d['id']]) ? $child[$d['id']] : array()); 
                        foreach($child as $k=>$dc) {
                            if(isset($p['fc'][$dc['data_field']][$dc['id']])) 
                            {
                                for($i = 0; $i<sizeof($dc['multi']) ; $i++) {
                                    if(isset($p['fc'][$dc['data_field']][$dc['id']][$dc['multi'][$i]['value']]) ) {
                                        $child[$k]['multi'][$i]['active'] = 1;
                                        if($sel===false) $sel = $child[$k]['multi'][$i]['name'];
                                        $active_c++;
                                    }
                                }
                            }
                        }
                    }
                ?>
                <div class="selectBlock d<?= $id; ?>_child<?= (!$active?' hidden':'').($active_c?' active':'') ?>">
                    <span class="left">&nbsp;</span>
                    <a class="right" href="#"><span class="pad"><b><?= $d['child_title'] ?></b><br/><span class="sel<? if($active_c>1): ?> plus<? endif; ?>"><?= ($sel!==false?$sel:'не важно'); ?></span></span> <span class="drop">&nbsp;</span></a>
                    <div class="clear"></div>
                    <div class="dropdown hidden"><div class="values">
                        <? if($active && !empty($child)) {  
                             foreach($d['multi'] as $k=>$m) {
                             if(isset($values[$m['value']])) {
                                 $dd = $child[$m['value']];
                                 $id2 = $dd['id'];
                                 $fid2 = $dd['data_field'];
                            ?> 
                            <div id="<?= $id ?>_c<?= $m['value'] ?>">
                                <div class="childseparator"><b><?= $m['name'] ?></b></div>
                                <div><?     
                                
//                                if(isset($dd['multi'][0])) {
//                                    unset($dd['multi'][0]);
//                                }
                                $sHTML = '';                                
                                buildCheckboxesBlocks($sHTML, $dd['multi'], sizeof($dd['multi']), array(), create_function('$k,$i', '
                                    $k = $i[\'value\'];
                                    return \'<li><input type="checkbox" name="fc['.$fid2.']['.$dd['id'].'][\'.$k.\']" id="d'.$id2.'_c\'.$k.\'" \'.(isset($i[\'active\'])?\' checked\':\'\').\' value="\'.$k.\'" /> <label for="f'.$id2.'_c\'.$k.\'"><span>\'.$i[\'name\'].\'</span></label></li>\';
                                '));
                                echo $sHTML;                                
                                                                                 
//                                foreach($dd['multi'] as $k=>$i) {
//                                    $k = $i['value'];
//                                    if($k == 0) continue; 
//                                    echo '<li><input type="checkbox" name="fc['.$fid2.']['.$dd['id'].']['.$k.']" id="d'.$id2.'_c'.$k.'"'.(isset($i['active'])?' checked':'').' value="'.$k.'" /><label for="f'.$id2.'_c'.$k.'"> <span>'.$i['name'].'</span></label></li>';
//                                } 
                                ?>
                                </div>
                                <div class="clear"></div>
                            </div><script type="text/javascript">$(function(){ bbsSearch.saveChildCache(<?= $d['id']; ?>, '<?= $m['value'] ?>', '<?= tpl::escape($m['name']) ?>'); });</script> 
                        <?  } }
                        } ?></div>                     
                        <div class="clear"></div>                     
                        <div class="buttonsCont">
                            <div class="button">                           
                                <span class="left">&nbsp;</span>
                                <input type="button" class="submit" value="отфильтровать" onclick="bbsSearch.filter(<?= (dbDynprops::typeSelect).', 0, \'.d'.$id.'_child\', 0, \'\', this' ?>);" style="width:114px;" />
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>  
                <?
                }   
            }break;
            case dbDynprops::typeRadioYesNo: 
            case dbDynprops::typeCheckbox:
            {
                if(empty($p['f'][$fid])) $p['f'][$fid] = array();
                $values = &$p['f'][$fid];
                
                $sel = 'не важно'; 
                $active = (sizeof($values));

                if($active>=1) { 
                    $valFirst = key($values); 
                    if($type == dbDynprops::typeRadioYesNo) {
                        $sel = ($valFirst == 2 ? 'да' : 'нет');
                    } else {
                        $sel = ($valFirst == 1 ? 'есть' : 'не важно');
                    }
                }

                ?>
                <div class="selectBlock d<?= $id; ?><? if($active): ?> active<? endif; ?>">
                    <span class="left">&nbsp;</span>
                    <a class="right" href="#"><span class="pad"><b><?= $d['title'] ?></b><br/><span class="sel<? if($active>1): ?> plus<? endif; ?>"><?= $sel; ?></span></span> <span class="drop">&nbsp;</span></a>
                    <div class="clear"></div>
                    <div class="dropdown hidden">                               
                        <ul>
                        <? if($type == dbDynprops::typeRadioYesNo) { ?>   
                            <li<? if(empty($values)): ?> class="select"<? endif; ?>><input<? if(empty($values)): ?> checked="checked" disabled="disabled"<? endif; ?> class="checkAll" type="checkbox" id="d<?= $id.'_0'; ?>" /><label for="d<?= $id.'_0'; ?>"> <span>не важно</span></label></li>
                            <li><input type="checkbox" name="<?= $name; ?>[2]" id="d<?= $id.'_2'; ?>" <?= isset($values[2])?'checked':'' ?> value="2" /><label for="d<?= $id ?>_2"> <span>да</span></label></li>
                            <li><input type="checkbox" name="<?= $name; ?>[1]" id="d<?= $id.'_1'; ?>" <?= isset($values[1])?'checked':'' ?> value="1" /><label for="d<?= $id ?>_1"> <span>нет</span></label></li>
                        <? } else { ?>
                            <li<? if(empty($values)): ?> class="select"<? endif; ?>><input<? if(empty($values)): ?> checked="checked" disabled="disabled"<? endif; ?> value="0" class="checkAll" type="checkbox" id="d<?= $id.'_0'; ?>" /><label for="d<?= $id.'_0'; ?>"> <span>не важно</span></label></li>
                            <li><input type="checkbox" name="<?= $name; ?>[1]" id="d<?= $id ?>_1" <?= isset($values[1])?'checked':'' ?> value="1" /><label for="d<?= $id ?>_1"> <span>есть</span></label></li>
                        <? } ?>
                        </ul>
                        <div class="clear"></div>                     
                        <div class="buttonsCont">
                            <div class="button">
                                <span class="left">&nbsp;</span>
                                <input type="button" class="submit" value="отфильтровать" onclick="bbsSearch.filter(<?= $type.', '.$id.', \'.d'.$id.'\', '.$d['parent'].', \'\', this' ?>);" style="width:114px;" />
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>  <?
            } break;
        }
    }
?>