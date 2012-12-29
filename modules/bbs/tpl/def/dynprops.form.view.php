<?php

    $prefix = (isset($aData['prefix'])?$aData['prefix']:'dynprops');
    $prefix_id = 'edit_d_';
    
    foreach($aData['dynprops'] as $d)
    {
        $d['title'] = '<span>'.$d['title'].':&nbsp;</span>';
        
        if(empty($d['value'])) continue;
        switch($d['type'])
        {
            case dbDynprops::typeRadioGroup:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div class="padTop">
                    <?= $d['title']; ?>
                    <?php
                        foreach($d['multi'] as $dm) {
                           if($value == $dm['value']) {
                               echo $dm['name'];
                               break;
                           }
                        }
                    ?>
                  </div> <?php
            }break; 
            case dbDynprops::typeRadioYesNo:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div class="padTop">
                    <?= $d['title'].($value == 2 ? 'Да' : ($value == 1 ? 'Нет' : '')); ?>
                  </div> <?php
            }break;             
            case dbDynprops::typeCheckboxGroup:
            {            
                $value = ( isset($d['value']) && $d['value'] ? explode(';', $d['value']) : explode(';', $d['default_value']) );                
                ?><div class="padTop">
                    <?= $d['title']; ?>
                    <?php
                        $cbGroup = array();
                        foreach($d['multi'] as $dm) {
                           if(in_array($dm['value'], $value))
                               $cbGroup[] = $dm['name'];
                        }
                        echo join(', ', $cbGroup);
                    ?>
                  </div><?php
            }break; 
            case dbDynprops::typeCheckbox:
            {
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                ?><div class="padTop">
                    <?= $d['title'].($value ? 'Да':'Нет') ?>
                  </div> <?php
            }break; 
            case dbDynprops::typeSelect:
            {   
                $value = (isset($d['value'])? $d['value'] : $d['default_value']);
                if($d['parent']) {
                ?><div class="padTop">
                    <?= $d['title']; 
                        foreach($d['multi'] as $dm) {
                           if($value == $dm['value']){ echo $dm['name']; break; }
                        }
                    ?> 
                    </div>
                    <?
                    if(!empty($value) && isset($aData['children'][$d['id']])) {
                       $dmv = current( $aData['children'][$d['id']] );
                       foreach($dmv['multi'] as $dm) {
                           if($dm['value'] == $dmv['value']) {
                               echo '<div class="padTop"><span>'.$d['child_title'].':&nbsp;</span>'.$dm['name'].'</div>'; break;
                           }
                       }
                    }
                } else { ?>
                  <div class="padTop">
                    <? echo $d['title']; 
                       foreach($d['multi'] as $dm) {
                          if($value == $dm['value'] && $value!=0){ echo $dm['name']; break; }
                       } ?>
                  </div> <?php
                  }
            }break; 
            case dbDynprops::typeInputText:
            {
                ?><div class="padTop">
                    <?= $d['title'].(isset($d['value'])? $d['value'] : '') ?>
                  </div> <?php
            }break; 
            case dbDynprops::typeTextarea:
            {
                ?><div class="padTop">
                    <?= $d['title'].(isset($d['value'])? $d['value'] : '') ?>
                  </div> <?php
            }break; 
            case dbDynprops::typeNumber:
            {
                ?><div class="padTop">
                    <?= $d['title'].(isset($d['value'])? $d['value'] : '') ?>
                  </div> <?php
            }break; 
            case dbDynprops::typeRange:
            {
                ?><div class="padTop">
                    <?= $d['title'].(isset($d['value'])? $d['value'] : '') ?>
                  </div> <?php
            }break; 
        }
    }

                    
