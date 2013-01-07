<?

$prefix = (isset($aData['prefix'])?$aData['prefix']:'dynprops');
foreach($aData['dynprops'] as $d)
{                     
    $name = $prefix.'['.$d[$this->ownerColumn].']'.'['.$d['id'].']';
    $extra = 'dyntype="'.$d['type'].'"';
    $tr_class = 'class="dynprop-owner-'.$d[$this->ownerColumn].' dynprop-id-'.$d['id'].'"';
    switch($d['type'])
    {
         case dbDynprops::typeRadioGroup:
        {
            $value = (isset($d['value'])? $d['value'] : $d['default_value']);
            ?><tr <?= $tr_class; ?>>
                <td class="row1"><?= $d['title']; ?>:</td><td>
                <?  foreach($d['multi'] as $dm) {
                       echo '<label><input type="radio" name="'.$name.'" '.$extra.' value="'.$dm['value'].'" '.($value == $dm['value'] ? 'checked="checked"' : '').'/> '.$dm['name'].'</label><br/>';
                    }
                ?> 
              </td></tr> <?
        }break; 
        case dbDynprops::typeRadioYesNo:
        {
            $value = (isset($d['value'])? $d['value'] : $d['default_value']);
            ?><tr <?= $tr_class; ?>>
                <td class="row1"><?= $d['title']?>:</td><td>
                <?= '<label><input type="radio" name="'.$name.'" '.$extra.' value="2" '.($value == 2?'checked="checked"':'').' /> Да</label>&nbsp;
                     <label><input type="radio" name="'.$name.'" '.$extra.' value="1" '.($value == 1?'checked="checked"':'').' /> Нет</label>'
                    ?>
              </td></tr><?
        }break;             
        case dbDynprops::typeCheckboxGroup:
        {            
            $value = ( isset($d['value']) && $d['value'] ? explode(';', $d['value']) : explode(';', $d['default_value']) );                
            ?><tr <?= $tr_class; ?>>
                <td class="row1"><?= $d['title']?>:</td><td>
                <?  foreach($d['multi'] as $dm) {
                       echo '<label><input type="checkbox" name="'.$name.'[]" '.$extra.' '.(in_array($dm['value'], $value)?'checked="checked"':'').' value="'.$dm['value'].'" /> '.$dm['name'].'</label><br/>';
                    }
                ?> 
              </td></tr><?
        }break; 
        case dbDynprops::typeCheckbox:
        {
            $value = (isset($d['value'])? $d['value'] : $d['default_value']);
            ?><tr <?= $tr_class; ?>>
                <td class="row1"><?= $d['title']?>:</td><td>
                <?= '<label><input type="hidden" name="'.$name.'" value="0" /><input type="checkbox" name="'.$name.'" '.$extra.' value="1" '.($value?'checked="checked"':'').' /> Да</label>' ?>
              </td></tr> <?
        }break; 
        case dbDynprops::typeSelect:
        {
            $value = (isset($d['value'])? $d['value'] : $d['default_value']);
            ?><tr <?= $tr_class; ?>>
                <td class="row1"><?= $d['title']?>:</td><td>
                <?= '<select name="'.$name.'" '.$extra.'>' ?>
                <?  foreach($d['multi'] as $dm) {
                       echo '<option value="'.$dm['value'].'" '.($value == $dm['value'] ? 'selected="selected"' : '').'>'.$dm['name'].'</option>';
                    }
                ?> 
                    </select>
              </td></tr> <?
        }break; 
        case dbDynprops::typeInputText:
        {
            $value = (isset($d['value'])? $d['value'] : $d['default_value']);
            ?><tr <?= $tr_class; ?>>
                <td class="row1"><?= $d['title']?>:</td><td>
                <?= '<input type="text" name="'.$name.'" '.$extra.' class="stretch" value="'.$value.'" />' ?>
              </td></tr> <?
        }break; 
    }
}
