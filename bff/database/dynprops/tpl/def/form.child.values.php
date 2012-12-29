<?php

    switch($aData['type'])
    {
        case dbDynprops::typeSelect:
        {   
            $value = (isset($aData['value'])? $aData['value'] : $aData['default_value']);
            foreach($aData['multi'] as $dm) {
               echo '<option value="'.$dm['value'].'" '.($value == $dm['value'] ? 'selected="selected"' : '').'>'.$dm['name'].'</option>';
            }   
        }break;
    }  
