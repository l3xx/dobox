<?php
         
class Banners extends BannersBase 
{
    var $positions = array();
            
    function initPositions()
    {
        $this->positions = require_once($this->module_dir.'banners.positions.php');
    }

    /* переход по ссылке указанной в настройках баннера +1 к счетчику показов */
    function click()
    {
        $nID = $this->input->id('id');
        if(!$nID) Func::JSRedirect(SITEURL);
        
        $aData = $this->getBannerData($nID);
        if(empty($aData)) Func::JSRedirect(SITEURL);
        
        # +1 к кликам  (MySQL ONLY)
        $this->db->execute('INSERT INTO '.TABLE_BANNERS_STAT.' (id, clicks, period) 
                       VALUES('.$nID.',1, '.$this->db->str2sql(date('Y-m-d')).') 
                       ON DUPLICATE KEY UPDATE clicks=clicks+1');
        
        if(empty($aData['clickurl']) || $aData['clickurl']=='#')
            Func::JSRedirect(SITEURL);
            
        Func::JSRedirect('http://'.$aData['clickurl']);
    }
    
    function show()
    {
        $nID = $this->input->id('id');
        $sFilePath = '';

        do 
        {
            if(!$nID) break;
            
            $aData = $this->getBannerData($nID);  
            if(empty($aData)) break;

            /* заблокировать если превышено допустимое количество показов */
            if($aData['show_limit']!=0)
            {
                if($aData['show_limit']<=$aData['shows']) {
                   $this->disableBanner($nID); 
                }    
            }
            // если flash|code баннер
            if($aData['banner_type'] == BANNERS_TYPE_FLASH || $aData['banner_type']== BANNERS_TYPE_CODE) {
                $sFilePath = BANNERS_DEFAULT_IMAGE_PATH;    
            }
            else{
                if(!empty($aData['banner'])) 
                {
                    $sFilePath = BANNERS_PATH.$nID.'_work_'.$aData['banner'];
                    if(!file_exists($sFilePath)) {
                        $sFilePath = BANNERS_PATH.$nID.'_th_'.$aData['banner'];
                        if(!file_exists($sFilePath))
                            $sFilePath  = BANNERS_DEFAULT_IMAGE_PATH;
                    }
                }
            }
           
            // +1 к показам  (MySQL ONLY)
            $this->db->execute('INSERT INTO '.TABLE_BANNERS_STAT.' (id, shows, period) 
                           VALUES('.$nID.', 1, '.$this->db->str2sql(date('Y-m-d')).') 
                           ON DUPLICATE KEY UPDATE shows=shows+1');
        
        } while(false);

        if(empty($sFilePath))
            $sFilePath = BANNERS_DEFAULT_IMAGE_PATH;
        
        $res = getimagesize($sFilePath);
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        // always modified
        header("Last-Modified: " .date("r", filectime($sFilePath)) );
        header('Content-type: '.$res['mime']);
        readfile($sFilePath);    
        exit();
    }
    
    function assignFrontendBanners($tplVariableName='aBanners', $aPositions=array())
    {
        $this->initPositions();
        $aBanners = $this->getFrontendBanners($aPositions);

        $aBannersHTML = array();
        $bStatus = $this->security->isLogined();
        foreach($aBanners as $pos=>$v) 
        { 
            $this->tplAssign('aBannerData', $aBanners[$pos]);
            $aBannersHTML[$pos] = $this->tplFetch('frontend.banners.tpl');
            continue;
                    
            /*если пользователь не зарегистрирован или не установлен флаг (отключать для зарег пользователей)*/
            if(( $bStatus && $v['positioninfo']['show_to_reguser'] == 0) || (!$bStatus && $v['positioninfo']['show_to_reguser'] == 1))
            {
                 
            }
        } 
        
        $this->tplAssign($tplVariableName, $aBannersHTML);
    }
    
    private function getFrontendBanners($aPositions = array())
    {
        if(count($aPositions)==0) {
            foreach($this->positions as $key=>$v)
                if($v['enabled'])
                    $aPositions[] = $v['keyword'];
        } else {
            foreach($aPositions as $key) {
                if( ! (isset($this->positions[$key]) && $this->positions[$key]['enabled']) )
                    unset($aPositions[$key]);
            }
        }
        
        $aBanners = $this->getBannersByPosition( $aPositions );
        
        $URL = $_SERVER['REQUEST_URI'];
        $nCategoryID = (int)config::get('bbsCurrentCategory', 0);

        $aBannersResult = array();  
        foreach($aPositions as $pos)
        {
            if(!empty($aBanners[$pos])) 
            {
                $aMatchedByURL = $aWithEmptyURL = $aMatchedByCategory = array();
                foreach($aBanners[$pos] as $key=>$v)
                {
                    if($nCategoryID>0 && !empty($v['cat']) && intval($v['cat'])!=1)
                    {
                        $v['cat'] = explode(',', $v['cat']);
                        if(in_array($nCategoryID, $v['cat'])) {
                            $aMatchedByCategory[$key] = $key;
                            continue;
                        } else {
                            continue;
                        }
                    }
                    
//                    if(!empty($v['showurl']))
//                    {
//                        if($v['showurl_recursive'] == 1)
//                        {
//                            if( mb_strpos($URL, $v['showurl']) === 0 ) {
//                                $aMatchedByURL[$key] = $key;
//                                continue; 
//                            }
//                        }
//                        else{
//                            if($v['showurl'] == $URL)
//                            {
//                                # есть ли на данной позиции при данном url соответствующие баннеры
//                                $aMatchedByURL[$key] = $key;
//                                continue; 
//                            }    
//                        }
//                    } else {
                        $aWithEmptyURL[$key] = $key;
//                    }
                }
                
//                echo '<pre>', print_r(array(
//                    'cat'=>$aMatchedByCategory,
//                    'url'=>$aMatchedByURL,
//                    'emptyURL'=>$aWithEmptyURL,
//                ), true), '</pre>'; exit;
                
                if(!empty($aMatchedByCategory)) {
                    # если нашлись баннеры прикрепленные к текущей категории
                    # берем один из них
                    $aBannersResult[$pos] = $aBanners[$pos][ array_rand($aMatchedByCategory, 1) ];
                }
//                elseif(!empty($aMatchedByURL)) {
//                    # если для позиции существуют баннеры с текущим url,
//                    # берем один из них
//                    $aBannersResult[$pos] = $aBanners[$pos][ array_rand($aMatchedByURL, 1) ];
//                } 
                else {
                    # ищем среди баннеров, не учитывающих URL     
                    if(!empty($aWithEmptyURL)) {
                        $aBannersResult[$pos] = $aBanners[$pos][ array_rand($aWithEmptyURL, 1) ]; 
                    }
                }
            }
        } 
        
        //echo '<pre>', print_r($aBannersResult, true), '</pre>'; exit;

        return $aBannersResult;
    }

    private function getBannersByPosition($mPosition = null)
    {
        $sWhere = '';
        if(!empty($mPosition)) {
            if(is_array($mPosition)) {
                $sWhere = ' AND '.$this->db->prepareIN('position', $mPosition, false, true, false);
            } else {
                $sWhere = ' AND position = '. $this->db->str2sql($mPosition);
            }
        }

        $aData = $this->db->select('SELECT * FROM '.TABLE_BANNERS.' 
                               WHERE enabled = 1 '.$sWhere.' AND show_start <= DATE_FORMAT(NOW(),"%Y-%m-%d 00:00")
                               ORDER BY position');
       
        for ($i=0; $i<count($aData); $i++)
        {
            $aData[$i]['positioninfo'] = $this->positions[ $aData[$i]['position'] ];
            $aData[$i]['img'] = BANNERS_PATH.$aData[$i]['id'].'_thumb_'.$aData[$i]['banner'];
            $aData[$i]['img_work'] = SITEURL.'/bn/show/'.$aData[$i]['id'].'/';
            $aData[$i]['flash'] = unserialize($aData[$i]['flash']);

            if($aData[$i]['clickurl']) {
                $aData[$i]['clickurl'] = SITEURL.'/bn/click/'.$aData[$i]['id'];
            }
            else
                $aData[$i]['clickurl'] = '';
        }

        $aData = Func::array_transparent($aData, 'position');
        
        return $aData;
    }
}
