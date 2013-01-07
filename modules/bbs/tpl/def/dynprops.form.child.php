<?php

    switch($aData['type'])
    {
        case dbDynprops::typeSelect:
        {   
            $value = (isset($aData['value'])? $aData['value'] : 0);
            if(!isset($aData['value']) && $aData['default_value']!==false) {
                echo '<option value="0">'.$aData['default_value'].'</option>';
            }
            foreach($aData['multi'] as $dm) {
               echo '<option value="'.$dm['value'].'" '.($value == $dm['value'] ? 'selected="selected"' : '').'>'.$dm['name'].'</option>';
            }   
        }break;
    }  
