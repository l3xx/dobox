<?php

abstract class SitesBase extends Module
{   
	var $securityKey = 'bc7ce37bb936e4f217b7ae2a940f5b3e';
    
    //Функции работы с счетчиками посещаемости сайта
    function getSiteCounters($bEnabled = false)
    {
        return $this->db->select('SELECT * FROM '.TABLE_COUNTERS.' '.($bEnabled?' WHERE enabled = 1 ':'').' ORDER BY num');                
    }

    function getSubscribesCount()
    {
        return $this->db->one_data('SELECT COUNT(id) FROM '.TABLE_SUBSCRIBES);
    }

    function getOptionsSizetype(&$nValue = false, &$sSizeType = '')
    {
        $aSizeTypesText = array('байт', 'Кб', 'Мб', 'Гб');
        $aSizeTypes = array('b', 'kb', 'mb', 'gb');
        $sSizeTypeSelected = 'b';
        
        $sResult = '';  
        
        if($nValue!==false)
        {
            $sSizeTypeSelected = ($nValue >= 1073741824) ? 'gb' : (($nValue >= 1048576) ? 'mb' : (($nValue >= 1024) ? 'kb' : 'b'));
            $nValue = Func::getfilesize($nValue, false);
        }

        for ($i = 0, $total = sizeof($aSizeTypes); $i < $total; $i++)
        {   
            if($sSizeTypeSelected == $aSizeTypes[$i])
                $sSizeType = $aSizeTypesText[$i];
                
            $selected = ($sSizeTypeSelected == $aSizeTypes[$i]) ? ' selected="selected"' : '';
            $sResult .= '<option value="' . $aSizeTypes[$i] . '"' . $selected . '>' . $aSizeTypesText[$i] . '</option>';
        }
        
        return $sResult;
    } 
    
    function geoCityOptions($nCurrentCityID = 0, $sPosType = 'edit', $opts = array())
    {                 
        $sOptions = '';
        switch($sPosType)
        {
            case 'edit': {
                if(!$nCurrentCityID) { //показываем невыбранный вариант только если еще не выбрали
                    $sOptions .= '<option class="bold" value="0">'.(!empty($opts['empty_title'])?$opts['empty_title']:'не указан').'</option>';
                }
            } break;
            case 'list': {                                                 
                if(!$nCurrentCityID && GEO_CITY_BOX_TYPE!=3) {
                    $sOptions .= '<option class="bold" value="0">'.(!empty($opts['empty_title'])?$opts['empty_title']:'все города').'</option>';
                }                
            } break;
        }
        
        $bExpand = (!empty($opts['expand']) ? true : false);

        /*
         * Ограничение по работе с городами:
         * _type: 0 = все города(свернутые до списка основных), 1 = основные города, 2 = города района, 3 = один город
         * _id: 0=0, 1=0, 2=id района, 3=id города
         */
        $sqlSelected = ($nCurrentCityID>0?' OR city_id = '.$nCurrentCityID:'');
        switch(GEO_CITY_BOX_TYPE)
        {
            case 0: //все города (свернутые до списка основных), включая выбранный
            {
                if(!$bExpand) {
                    $aCities = $this->db->select('SELECT city_id as id, title 
                            FROM '.TABLE_CITY.'
                            WHERE enabled=1 AND (main = 1'.$sqlSelected.')
                            ORDER BY num');
                } else {
                    $aCities = $this->db->select('SELECT city_id as id, title 
                            FROM '.TABLE_CITY.'
                            WHERE enabled=1
                            ORDER BY title');
                }
            } break;
            case 1: //основные города, включая выбранный
            {
                if(!$bExpand) {
                    $aCities = $this->db->select('SELECT city_id as id, title 
                        FROM '.TABLE_CITY.'
                        WHERE enabled=1 AND (main = 1'.$sqlSelected.')
                        ORDER BY num');                  
                } else {
                    $aCities = $this->db->select('SELECT city_id as id, title 
                        FROM '.TABLE_CITY.'
                        WHERE enabled=1 AND main = 1
                        ORDER BY num');
                }                    
                
            } break;
            case 2: //города района (свернутые до списка основных), включая выбранный
            {
                if(!$bExpand) {
                    $aCities = $this->db->select('SELECT city_id as id, title 
                            FROM '.TABLE_CITY.'
                            WHERE enabled=1 AND region_id = '.GEO_CITY_BOX_ID.' AND (main = 1'.$sqlSelected.')
                            ORDER BY numreg');                    
                } else {
                    $aCities = $this->db->select('SELECT city_id as id, title 
                            FROM '.TABLE_CITY.'
                            WHERE enabled=1 AND region_id = '.GEO_CITY_BOX_ID.'
                            ORDER BY numreg');
                }
            } break;
            case 3: //один город, включая выбранный
            {
                $aCities = $this->db->select('SELECT city_id as id, title 
                        FROM '.TABLE_CITY.'
                        WHERE enabled=1 AND (city_id = '.GEO_CITY_BOX_ID.$sqlSelected.')
                        ORDER BY numreg');
            } break;
        }
        
        for($i=0, $n = count($aCities); $i<$n; $i++) {
            $sOptions .= '<option value="'.$aCities[$i]['id'].'"'.($nCurrentCityID == $aCities[$i]['id']?' selected="selected"':'').'>'.$aCities[$i]['title'].'</option>'; 
        }
        
        if( !$bExpand && (GEO_CITY_BOX_TYPE == 0 || GEO_CITY_BOX_TYPE == 2) ) {
            $sOptions .= '<option value="-1" class="desc">Другой</option>'; 
        }
        
        return $sOptions;            
        
//        $cache = Cache::singleton('apc', true); $cacheKey = 'geo-сities-'.($bOnlyMain?'main':'all');
//        if(!($aCities = $cache->get($cacheKey))) {
//            $aCities = $this->db->select('SELECT city_id, title  FROM '.TABLE_CITY.'
//                WHERE enabled=1 '.($bOnlyMain? ' AND main = 1 '.($nCurrentCityID?' OR city_id='.$nCurrentCityID:''):'').'
//                ORDER BY '.($bOnlyMain?'num':'title'));
//            $cache->add($cacheKey, $aCities);
//        }        
//        if(!empty($aCities))
//        {
//            foreach($aCities as $v)
//                $sResultOptions .= '<option value="'.$v['city_id'].'"'.($nCurrentCityID == $v['city_id']?' selected ':'').'>'.$v['title'].'</option>';
//        }
//        
//        if($bOnlyMain)
//            $sResultOptions .= '<option value="-1" class="admDescription">Другой</option>';
//            
//        
//        return $sResultOptions;    
    }
        
    function geoOblastOptions($nCurrentID = 0, &$aRegions=array())
    {
        $sResultOptions = '';
        $aRegions = $this->db->select('SELECT * FROM '.TABLE_REGION.' WHERE city_id=0 ORDER BY title');
        if(!empty($aRegions))
        {
            foreach($aRegions as $v)
                $sResultOptions .= '<option value="'.$v['region_id'].'"'.($nCurrentID == $v['region_id']?' selected ':'').'>'.$v['title'].'</option>';
        }
        
        return $sResultOptions;       
    }
    
    function geoRegionsCacheDelete( $nCityID )
    {
        $cache = Cache::singleton();
        $cache->delete('geo-regions-'.$nCityID);
        $cache->delete('geo-regions-'.$nCityID.'+ydata');
    }

    function geoRegionOptions($nCityID = 0, $nCurrentRegionID=0, $bReturnNoIfEmpty = false, $mEmptyOption = 'не указан', $bGetYData = true)
    {
        $sResultOptions = '';
        if($nCityID)
        {
            //$cache = Cache::singleton('apc', true); $cacheKey = 'geo-regions-'.$nCityID.($bGetYData?'+ydata':''); 
            
            //if(!($aRegions = $cache->get($cacheKey))) {  
                $aRegions = $this->db->select('SELECT region_id as id, title'.($bGetYData?', ybounds, ypoly':'').' FROM '.TABLE_REGION.'
                    WHERE city_id='.$nCityID.'
                    ORDER BY title');                                      
            //    $cache->add($cacheKey, $aRegions);
            //}
            
            if(!empty($aRegions))
            {
                $sResultOptions .= ($mEmptyOption!==false ? '<option class="bold" value="0">'.$mEmptyOption.'</option>' : '');
                foreach($aRegions as $v)
                    $sResultOptions .= '<option value="'.$v['id'].'"'.($nCurrentRegionID == $v['id']?' selected ':'').'>'.$v['title'].'</option>';
            }
        }
       
        return array( 
                   'options'=>($bReturnNoIfEmpty && empty($sResultOptions) ? 'no' : $sResultOptions),
                   'regdata'=>$aRegions
                   );
    } 
    
    function saveConfig($aConfig, $bSave2File = false)
    {
        if($bSave2File) 
        {
            $aFind    = array("'\r'", "'\n'");
            $aReplace = array('', '');

            $rHandler = fopen(PATH_BASE.'config/site.config.php', "w");
            $bMagicQuotes = get_magic_quotes_gpc();
            fwrite($rHandler, "<?php \n\n return array (\n");
            foreach($aConfig as $sName => $sValue)
            {
                if(in_array($sName, array('offline_reason', 'copyright', 'contactaddress'
                    , 'ps_rbkmoney_info', 'ps_zpay_info', 'ps_robox_info', 'ps_webmoney_info', 'ps_checkout_info'
                    ))) 
                {
                    $sValue = trim($sValue);    
                    if (!$bMagicQuotes) {
                        $sValue = addslashes($sValue);
                    }                                                                             
                    $sValue = preg_replace($aFind,$aReplace,$sValue);
                }
                else
                {
                    $sValue = trim($sValue);
                    if (!$bMagicQuotes) {
                        $sValue = addslashes($sValue);
                    }                        
                    $sValue = htmlentities($sValue, ENT_QUOTES, 'UTF-8');
                }                
                fwrite($rHandler, "'{$sName}' => \"{$sValue}\",\n");
            }
            fwrite($rHandler, ");\n\n");
            fclose($rHandler);
        } else {
            
            foreach($aConfig as $config_name=>$config_value) {
                config::save($config_name, $config_value);
            }
            
        }
    }
   

} 