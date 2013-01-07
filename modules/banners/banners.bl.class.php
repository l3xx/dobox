<?php

define('TABLE_BANNERS',      DB_PREFIX.'banners');
define('TABLE_BANNERS_STAT', DB_PREFIX.'banners_stat');

define('BANNERS_PATH', PATH_BASE.'files/bnnrs/' );
define('BANNERS_URL',  SITEURL.'/files/bnnrs' );

define('BANNERS_DEFAULT_IMAGE_PATH', BANNERS_PATH.'default.gif' );

define('BANNERS_MAKE_RESIZE', 0); 

define('BANNERS_TYPE_IMG',    1); 
define('BANNERS_TYPE_FLASH',  2); 
define('BANNERS_TYPE_CODE',   3); 

abstract class BannersBase extends Module
{
    public function init()
    {
        parent::init();
    }
    
    function savePositions($aPositions = array())
    {
        $rHandler = fopen($this->module_dir.'banners.positions.php', "w");
        fwrite($rHandler, "<?php \n\n return array (\n");
        foreach($aPositions as $sName => $sValue)
        {
            fwrite($rHandler, "'".$sName."'=> array(\n ");
            foreach($sValue as $key => $v)
            {
                $v = trim($v);
                if (!get_magic_quotes_gpc()) {
                    $v = addslashes($v);
                }                        
                $v = htmlentities($v,ENT_QUOTES,'UTF-8');
                fwrite($rHandler, "\t'{$key}' => \"{$v}\",\n");    
            }          
            fwrite($rHandler,"),\n");
        }
        fwrite($rHandler,");\n");
        fclose($rHandler);
    }
    
    function prepareClickURL($nBannerID)
    {
        return SITEURL.'/bn/click/'.$nBannerID;
    }

    function getBannerData($nBannerID, $bAddPositionInfo = false)
    {
        $res = $this->db->one_array('SELECT BN.*, SUM(BNST.shows) as shows 
                                 FROM '.TABLE_BANNERS.' BN
                                   LEFT JOIN '.TABLE_BANNERS_STAT.' BNST ON BN.id = BNST.id
                                 WHERE BN.id='.$nBannerID.' AND BN.enabled = 1
                                 GROUP BY BN.id');
        if($bAddPositionInfo) {
            
        }
        
        return $res;
    }
    
    function disableBanner($nBannerID)
    {
        return  $this->db->execute( 'UPDATE '.TABLE_BANNERS.' SET enabled=0 WHERE id='.$nBannerID );
    }
    
    function delImages($nRecordID, $aFilenames)
    {
        if(file_exists(BANNERS_PATH.$nRecordID.'_work_'.$aFilenames['banner']))
            unlink(BANNERS_PATH.$nRecordID.'_work_'.$aFilenames['banner']);
        if(file_exists(BANNERS_PATH.$nRecordID.'_th_'.$aFilenames['banner']))
            unlink(BANNERS_PATH.$nRecordID.'_th_'.$aFilenames['banner']);
    }
    
    function getBBSCategories($aSelectedID = array(), $bOptions = false)
    {
        if(!is_array($aSelectedID)) $aSelectedID = array($aSelectedID);
                          
        bff::i()->GetModule('bbs');
        
        $aCats = $this->db->select('SELECT id, title, 0 as disabled FROM '.TABLE_BBS_CATEGORIES.' WHERE numlevel=1 ORDER BY numleft');
        if($bOptions) 
        {
            $sOptions = '';   
            array_unshift($aCats, array('id'=>0, 'title'=>'любой', 'disabled'=>0),
                                  array('id'=>-2, 'title'=>'------------------------', 'disabled'=>1),
                                  array('id'=>1, 'title'=>'Все разделы сайта', 'disabled'=>0));
                                  
            foreach($aCats as $v) {
                $sOptions .= '<option value="'.$v['id'].'" class="'.($v['id']==0 || $v['id']==1?'bold':'').'" '.($v['id']==-2?'disabled':'').' '.(in_array($v['id'], $aSelectedID) ? ' selected="selected"' : '').'>'.$v['title'].'</option>';
            }
        } 
        else 
        {
            array_unshift($aCats, array('id'=>1, 'title'=>'Все разделы сайта'));
            $sCheckbox = '';
            foreach($aCats as $v) {
                $sCheckbox .= '<label><input type="checkbox" name="cat[]" class="catcheck '.($v['id']==1?'all bold':'cat').'" value="'.$v['id'].'"'.(in_array($v['id'], $aSelectedID) ? ' checked="checked"' : '').'/> '.$v['title'].'</label><br/>';
            }
        }
        
        $aCats = func::array_transparent($aCats, 'id', true);
        return array( 'cats'=>$aCats, 'options'=>(!empty($sOptions)?$sOptions:''), 'checks'=>(!empty($sCheckbox)?$sCheckbox:'') );
    }
}