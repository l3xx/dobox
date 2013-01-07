<?php
    foreach($aData['dynprops'] as $d)
    {                                                  
        switch($d['type'])
        {
             case dbDynprops::typeRadioGroup:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <b><?= $d['title']?>:</b>
                    <?php
                        foreach($d['multi'] as $dm) {
                           echo '<label><input type="radio" name="d'.$d['id'].'" dyntype="'.$d['type'].'" value="'.$dm['value'].'" '.($value == $dm['value'] ? 'checked="checked"' : '').'/>'.$dm['name'].'</label>';
                        }
                    ?> 
                  </div> <?php
            }break; 
            case dbDynprops::typeRadioYesNo:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <b><?= $d['title']?>:</b>
                    <?= '<label><input type="radio" name="d'.$d['id'].'" dyntype="'.$d['type'].'" value="2" '.($value == 2?'checked="checked"':'').' />Да </label>&nbsp;
                         <label><input type="radio" name="d'.$d['id'].'" dyntype="'.$d['type'].'" value="1" '.($value == 1?'checked="checked"':'').' />Нет</label>'
                        ?>
                  </div> <?php
            }break;             
            case dbDynprops::typeCheckboxGroup:
            {            
                $value = ( isset($d['value']) && $d['value'] ? explode(';', $d['value']) : explode(';', $d['default_value']) );                
                ?><div>
                    <b><?= $d['title']?>:</b>
                    <?php
                        foreach($d['multi'] as $dm) {
                           echo '<label><input type="checkbox" name="d'.$d['id'].'[]" dyntype="'.$d['type'].'" '.(in_array($dm['value'], $value)?'checked="checked"':'').' value="'.$dm['value'].'" />'.$dm['name'].'</label><br/>';
                        }
                    ?> 
                  </div><?php
            }break; 
            case dbDynprops::typeCheckbox:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <b><?= $d['title']?>:</b>
                    <?= '<label><input type="checkbox" name="d'.$d['id'].'" dyntype="'.$d['type'].'" value="1" '.($value?'checked="checked"':'').' />Да </label>&nbsp;' ?>
                  </div> <?php
            }break; 
            case dbDynprops::typeSelect:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <b><?= $d['title']?>:</b>
                    <?= '<select name="d'.$d['id'].'" dyntype="'.$d['type'].'" class="inputText">' ?>
                    <?php
                        foreach($d['multi'] as $dm) {
                           echo '<option value="'.$dm['value'].'" '.($value == $dm['value'] ? 'selected="selected"' : '').'>'.$dm['name'].'</option>';
                        }
                    ?> 
                        </select>
                  </div> <?php
            }break; 
            case dbDynprops::typeInputText:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div>
                    <b><?= $d['title']?>:</b>
                    <?= '<input type="text" name="d'.$d['id'].'" dyntype="'.$d['type'].'" value="'.$value.'" class="inputText">' ?>
                  </div> <?php
            }break; 
        }
    }
