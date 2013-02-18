<?php

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

    switch($aData['type'])
    {
        case dbDynprops::typeSelect:
        {   
            $id = $aData['id'];
            $res = '';   
            
//            if(isset($aData['multi'][0])) {
//                unset($aData['multi'][0]);
//            }
            $sHTML = '';
            buildCheckboxesBlocks($sHTML, $aData['multi'], sizeof($aData['multi']), array(), create_function('$k,$i', '
                $k = $i[\'value\'];
                return \'<li><label><input type="checkbox" name="fc['.$aData['data_field'].']['.$id.'][\'.$k.\']" id="d'.$id.'_c\'.$k.\'" value="\'.$k.\'" /><label for="d'.$id.'_c\'.$k.\'"> <span>\'.$i[\'name\'].\'</span></label></li>\';
            '));
            echo $sHTML;            
                                                 
//            foreach($aData['multi'] as $k=>$i) 
//            { 
//                $k = $i['value'];
//                if($k == 0) continue;
//                $res .= '<li><label><input type="checkbox" name="fc['.$aData['data_field'].']['.$id.']['.$k.']" id="d'.$id.'_c'.$k.'" value="'.$k.'" /><label for="d'.$id.'_c'.$k.'"> <span>'.$i['name'].'</span></label></li>';
//            } 
//            echo '<ul>', $res, '</ul>';
        }break;
    }  
