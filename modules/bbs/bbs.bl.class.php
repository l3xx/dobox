<?php
                                                                         
define('TABLE_BBS_CATEGORIES',                DB_PREFIX.'bbs_categories');
define('TABLE_BBS_CATEGORIES_DYNPROPS',       DB_PREFIX.'bbs_categories_dynprops');
define('TABLE_BBS_CATEGORIES_DYNPROPS_MULTI', DB_PREFIX.'bbs_categories_dynprops_multi');    
define('TABLE_BBS_CATEGORIES_IN_DYNPROPS',    DB_PREFIX.'bbs_categories_in_dynprops'); 
define('TABLE_BBS_CATEGORIES_TYPES',          DB_PREFIX.'bbs_categories_types');
define('TABLE_BBS_ITEMS',                     DB_PREFIX.'bbs_items');
define('TABLE_BBS_ITEMS_CLAIMS',              DB_PREFIX.'bbs_items_claims');
define('TABLE_BBS_ITEMS_COMMENTS',            DB_PREFIX.'bbs_items_comments');
define('TABLE_BBS_ITEMS_COMMENTS_ENOTIFY',    DB_PREFIX.'bbs_items_comments_enotify');
define('TABLE_BBS_ITEMS_VIEWS',               DB_PREFIX.'bbs_items_views');   
define('TABLE_BBS_ITEMS_FAV',                 DB_PREFIX.'bbs_items_fav');
define('TABLE_BBS_REGIONS',                   DB_PREFIX.'bbs_regions');

define('BBS_COUNTRY_DEFAULT', 1); //Россия  
                                                      
define('BBS_STATUS_NEW',            1); //новое
define('BBS_STATUS_PUBLICATED',     3); //опубликованное
define('BBS_STATUS_PUBLICATED_OUT', 4); //истекший срок публикации
define('BBS_STATUS_BLOCKED',        5); //заблокированное

define('BBS_FAV_COOKIE_EXPIRE', 2); //в днях

define('BBS_PRESS_PAYED',      1); //публикация в прессе оплачена
define('BBS_PRESS_PUBLICATED', 3); //опубликовано в прессе


abstract class BBSBase extends Module
{
    var $securityKey = 'f59d7393ccf683db2a63b795f75cd9e3'; 
    
    var $items_images           = 1;       //используются ли изображения 
    var $items_images_limit     = 1;       //лимит изображений объявления
    var $items_images_maxsize   = 5242880; //5 мб (в байтах)
    var $items_images_path      = '';      //путь к изображениям
    var $items_images_url       = '';      //url к изображениям
    var $items_images_watermark = false;   //путь к watermark изображению

    // максимальная глубина подкатегорий
    var $category_deep = 3;                          
    // возможно ли наличие объявлений и подкатегорий на одном уровне  
    var $category_mixed = false; 
    // название валюты
    var $items_currency = array('sign'=>'р.', 'short'=>'руб.', 'forms'=>'рубль;рубля;рублей');
    // используются ли URL keywords
    var $url_keywords = false;
                 
    public function init()
    {
        parent::init();
        
        $this->items_images_path = bff::buildPath('items', 'images');
        $this->items_images_url  = bff::buildUrl('items', 'images');      
        
        # nested sets tree
        $tree = $this->attachComponent('tree', new dbNestedSetsTree(TABLE_BBS_CATEGORIES) );  

        bff::i()->GetModule('Services');
        
        if(bff::$isFrontend) return; # !                          
        
        # проверяем наличие корневого элемента
        if( $tree->checkRootNode( $nRootID ) )
        {
            # если только-что создан, создаем категорию
            $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.' 
                                SET title = '.$this->db->str2sql('Корневой раздел').', created = '.$this->db->getNOW().'
                                WHERE id = '.$nRootID);
        }  
        
        $this->initDynprops();                             
    }
    
    /**
    * @return dbDynprops объект
    */
    function initDynprops()
    {
        static $dynprops = null;
        if(isset($dynprops)) return $dynprops;
        
        # подключаем "Динамические свойства"
        $dynprops = $this->attachComponent('dynprops', 
            new dbDynprops('cat_id', TABLE_BBS_CATEGORIES, 
                                     TABLE_BBS_CATEGORIES_DYNPROPS, 
                                     TABLE_BBS_CATEGORIES_DYNPROPS_MULTI
                                     , 1, TABLE_BBS_CATEGORIES_IN_DYNPROPS
                                     ) );
                                     
        $dynprops->setSettings(array(
            'module_name'=>'bbs',
            'ownerTableType'=>'ns', 
            'typesAllowed'=>array(
                dbDynprops::typeCheckboxGroup,
                dbDynprops::typeRadioGroup,
                dbDynprops::typeRadioYesNo, 
                dbDynprops::typeCheckbox,
                dbDynprops::typeSelect,     
                dbDynprops::typeInputText,
                dbDynprops::typeTextarea, 
                dbDynprops::typeNumber, 
                dbDynprops::typeRange,
            ),
            'typesAllowedParent'=>array(dbDynprops::typeSelect, dbDynprops::typeNumber, dbDynprops::typeRange)
        ));
        
        return $dynprops;        
    }
            
    /**
    * @return BBSItemsImages объект
    */
    function initImages()
    {
        require_once($this->module_dir.'bbs.items.images.class.php');
        return new BBSItemsImages();
    }    
    
    /**
    * Обработка данный категории
    * @param array ref данные
    * @param integer ID категории
    */
    function processCategoryData(&$aData, $nCategoryID)
    {
        $aData['pid'] = $this->input->postget('pid', TYPE_UINT);
        
        $aParams = array(              
            'title'        => TYPE_STR,
            'keyword'      => TYPE_STR,
            'textshort'    => TYPE_STR,
            'textfull'     => TYPE_STR,
            'mtitle'       => TYPE_STR,
            'mkeywords'    => TYPE_STR,
            'mdescription' => TYPE_STR,
        );
        $this->input->postm($aParams, $aData, (bff::$isPost ? array('title') : false) );

        if(bff::$isPost) {

            if(!$aData['pid']) {
                $this->errors->set('empty:category');
            }
            
            if($this->url_keywords) {
                $aData['keyword'] = $this->getKeyword($aData['keyword'], $aData['title'], TABLE_BBS_CATEGORIES, $nCategoryID);
            }
            
            if($this->errors->no())
            {
                //generate meta-data           
                if(BFF_GENERATE_META_AUTOMATICALY && !empty($aData['textfull']) && (empty($aData['mkeywords']) || empty($aData['mdescription'])) )
                {
                   Func::generateMeta($aData['textfull'], $aMeta);
                   if(empty($aData['mdescription']))
                        $aData['mdescription'] = $aMeta['mdescription'];
                   if(empty($aData['mkeywords']))
                        $aData['mkeywords'] = $aMeta['mkeywords'];
                }
            }
        }
        
    }
    
    /**
    * Обработка данных объявления
    * @param array ref данные
    * @param integer ID товара
    */                  
    function processItemData(&$aData, $nItemID)
    {
        $aParams = array(                 
            'keyword'      => TYPE_STR,
            'descr'        => TYPE_STR,   
            'mkeywords'    => TYPE_STR,
            'mdescription' => TYPE_STR,  
            'price'        => TYPE_UINT,  
        );            
        
        $this->input->postm($aParams, $aData, array() );

        if(bff::$isPost) 
        {
            if( !$nItemID ) //добавление объявления 
            {
                $aData['cat_id'] = $this->input->postget('cat_id', TYPE_UINT);
                
                if(!$aData['cat_id']) {
                    $this->errors->set('empty:category');
                } else {
                    if(!$this->category_mixed &&
                        $this->tree_getChildrenCount($aData['cat_id']) > 0 ) 
                    {
                        $this->errors->set('no_mixed_category_content');
                    }
                }
            }
            
            if($this->url_keywords) {       
                $aData['keyword'] = $this->getKeyword($aData['keyword'], $aData['title'], TABLE_BBS_ITEMS, $nItemID);
            }            
        }         
    }
    
    /**
    * Получаем список категорий
    * @param integer ID выбранной категории
    * @param mixed не выьранное значение
    * @param тип списка: 0 - все категории(кроме корневой), 1 - список при добавлении категории, 2 - список при добавлении товара
    * @return string <option></option>...
    */
    function getCategoriesOptions($nSelectedID = 0, $mEmptyOpt = false, $nType = 0, $aOnlyID = array())
    {                                              
        $sqlWhere = '';
        if($nType == 1) {
            if($this->category_deep!==false) {
                 $sqlWhere .= ' AND C.numlevel < '.$this->category_deep;
            }
        }
        else {
            $sqlWhere .= ' AND C.numlevel>0'; 
        }
        if(!empty($aOnlyID)) {
            $sqlWhere .= ' AND (C.id IN ('.join(',', $aOnlyID).') OR C.pid IN('.join(',', $aOnlyID).'))';
        }
        
        $bUsePadding = (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'chrome')!==false ? false : true);
        
        $sQuery = 'SELECT C.id, C.title, C.numlevel, C.numleft, C.numright, COUNT(I.id) as items
                   FROM '.TABLE_BBS_CATEGORIES.' C
                        LEFT JOIN '.TABLE_BBS_ITEMS.' I ON C.id = I.cat_id
                   WHERE 1=1 '.$sqlWhere.'
                   GROUP BY C.id
                   ORDER BY C.numleft';     

        $sOptions = '';  
        $aCategories = $this->db->select($sQuery);
        foreach($aCategories as $v) {      
            $nl = &$v['numlevel'];          
            $disable = $nType>0 && !$this->category_mixed && ($nType == 2 ? ($v['numright']-$v['numleft']) != 1 : 
                            ( $nl>0 && $v['items']>0 ) ); //$v['numright']-$v['numleft']) > 1 ||

            $sOptions .= '<option value="'.$v['id'].'" '.
                            ($bUsePadding && $nl>1 ? 'style="padding-left:'.($nl*10).'px;" ':'').
                            ($v['id'] == $nSelectedID?' selected':'').
                            ($disable ? ' disabled' : '').
                            '>'.(!$bUsePadding && $nl>1 ? str_repeat('&nbsp;&nbsp;', $nl) :'').$v['title'].'</option>';
        }
        
        if($mEmptyOpt!==false) {
            $nValue = 0;
            if(is_array($mEmptyOpt)) {
                $nValue = key($mEmptyOpt);
                $mEmptyOpt = current($mEmptyOpt);
            }
            $sOptions = '<option value="'.$nValue.'" class="bold">'.$mEmptyOpt.'</option>'.$sOptions;
        }
        
        return $sOptions;
    }      
    
    function getItemStatusParams()
    {
        return array(
                BBS_ITEM_STATUS_NEW       => array('t'=>'Незавершен', 'c'=>'#000'), 
                BBS_ITEM_STATUS_COMPLETED => array('t'=>'Завершен', 'c'=>'green'),
                BBS_ITEM_STATUS_POSTPONED => array('t'=>'Отложен', 'c'=>'#000'), 
                BBS_ITEM_STATUS_SENDED    => array('t'=>'Отправлен', 'c'=>'#000'), 
                BBS_ITEM_STATUS_CANCELED  => array('t'=>'Отменен', 'c'=>'#aaa'),
                BBS_ITEM_STATUS_PAYED     => array('t'=>'Оплачен', 'c'=>'#000'), 
                BBS_ITEM_STATUS_WAITS     => array('t'=>'Ждет комплектации', 'c'=>'#000')
            );
    }
    
    function getItemStatusOptions($nSelectedID = 0, $mEmptyOpt = false, $aExcludeStatus = array())
    {   
        $aStatus = $this->getItemStatusParams();
        if(!empty($aExcludeStatus)) {
            $aStatus = array_diff_key($aStatus, $aExcludeStatus); 
        }
                   
        $sOptions = '';
        foreach($aStatus as $k=>$v) {
            $sOptions .= '<option value="'.$k.'" style="color:'.$v['c'].'"'.
                            ($k == $nSelectedID?' selected':'').
                            '>'.$v['t'].'</option>';
        }
        
        if($mEmptyOpt!==false) {
            $nValue = 0;
            if(is_array($mEmptyOpt)) {
                $nValue = key($mEmptyOpt);
                $mEmptyOpt = current($mEmptyOpt);
            }
            $sOptions = '<option value="'.$nValue.'" class="bold">'.$mEmptyOpt.'</option>'.$sOptions;
        }
        
        return $sOptions;
    }

    function getSvcPublicatePrice()
    {
        return doubleval(config::get('bbs_svc_publicate_price', 0));
    }

    /**
     * Проверка существования ключа
     * @param string ключ
     * @param array исключая записи (ID)
     * @returns integer
     */ 
    function isKeywordExists($sKeyword, $sTable, $nExceptRecordID = null)
    {
        return $this->db->one_data('SELECT id
                               FROM '.$sTable.'
                               WHERE '.( !empty($nExceptRecordID)? ' id!='.intval($nExceptRecordID).' AND ' : '' ).'
                                  keyword='.$this->db->str2sql($sKeyword).'  LIMIT 1');
    }
    
    /**
     * Проверка ключа / Формирование ключа исходя из заголовка
     * @param string заголовок     
     * @param array исключая записи (ID)
     * @returns string ключ
     */ 
    function getKeyword($sKeyword='', $sTitle = '', $sTable, $nExceptRecordID = null)
    {                                            
        if(empty($sKeyword) && !empty($sTitle)) {
            $sKeyword = mb_strtolower( func::translit( $sTitle ) );
        }
        
        $sKeyword = preg_replace('/[^a-zA-Z0-9_\-\']/','',$sKeyword);
        if(empty($sKeyword)){
             $this->errors->set('empty:keyword');
        } else {
            if($this->isKeywordExists($sKeyword, $sTable, $nExceptRecordID))
                $this->errors->set('exists:keyword');
        }
        
        return $sKeyword;              
    }
    
    function prepareCategoryPricesRanges($data)
    {
        if(!empty($data) && is_array($data))
        {
            foreach($data as $k=>&$v) 
            {
                $v['from'] = floatval( trim(strip_tags($v['from'])) );
                $v['to']   = floatval( trim(strip_tags($v['to'])) );
                
                if(empty($v['from']) && empty($v['to'])) {
                    unset($data[$k]);
                    continue;
                }
            }
            return $data;
        }
        return array();
    }

    # регионы
    function regionsOptions($nSelectedID = false, $nParentRegionID = 0, $bOnlyEnabled = true, $sEmptyOpt = false)
    {                  
        $aRegions = $this->db->select('SELECT id, title FROM ' . TABLE_BBS_REGIONS . ' WHERE '.($bOnlyEnabled?'enabled = 1 AND':'').' pid = '.$nParentRegionID.' ORDER BY main DESC, num');

        $sOptions = ($sEmptyOpt!==false ? '<option value="0">'.$sEmptyOpt.'</option>':'');
        foreach($aRegions as $v) {
            $sOptions .= '<option value="'.$v['id'].'"'.($v['id'] == $nSelectedID?' selected="selected"':'').'>'.$v['title'].'</option>';
        }
        return $sOptions;
    }
    
    function periodSelect($nSelectedID = 0)
    {
        $periods = array(
            0=>'за весь период',
            1=>'за сегодня',
            2=>'за последнюю неделю',
            3=>'за последний месяц',
        );
        
        $sOprions = '';
        for($i=0; $i<4; $i++) {
            $sOprions .= '<option value="'.$i.'"'.($i == $nSelectedID ? ' selected':'').'>'.$periods[$i].'</option>';
        }
        
        return $sOprions;
    }
    
    function getTotalItemsCount()
    {
        return $this->db->one_data('SELECT COUNT(id) FROM '.TABLE_BBS_ITEMS.' WHERE status = '.BBS_STATUS_PUBLICATED);
    }
    
    function getFavorites( $bResaveFromCookie = false )
    {
        static $cache;
        if(isset($cache)) return $cache;
        
        $cookieKey = BFF_COOKIE_PREFIX.'bbs_fav';
        if( ( $userID = $this->security->getUserID() ) )
        {
            $res = $this->db->select_one_column('SELECT item_id FROM '.TABLE_BBS_ITEMS_FAV.' WHERE user_id = '.$userID.' GROUP BY item_id');
            if(empty($res)) $res = array();
            
            if($bResaveFromCookie) {             
                $resCookie = func::getCOOKIE($cookieKey);
                if(!empty($resCookie)) {
                    $resCookie = explode(',', $resCookie);
                    if(!empty($resCookie)) $this->input->clean($resCookie, TYPE_ARRAY_UINT);
                    if(!empty($resCookie)) 
                    {
                        $sqlInsert = array();       
                        foreach($resCookie as $id) {
                            if(!in_array($id, $res)) {
                                $sqlInsert[] = "($id, $userID)";
                                $res[] = $id;
                            }
                        }
                        if(!empty($sqlInsert)) {
                            $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS_FAV.' (item_id, user_id) 
                                VALUES '.join(',', $sqlInsert));
                        }
                    }
                    func::setCOOKIE($cookieKey, '', BBS_FAV_COOKIE_EXPIRE, '/', '.'.SITEHOST);
                }
            }
        } else {
            $res = func::getCOOKIE($cookieKey);
            if(empty($res)) $res = 0;
            else {
                $res = explode(',', $res);
                if(empty($res)) $res = 0;
                else {
                    $this->input->clean($res, TYPE_ARRAY_UINT);
                }
            }
        }
        $cache = $res = (!empty($res) ? array('id'=>$res, 'total'=>count($res)) : array('id'=>array(), 'total'=>0) ); 
        config::set('bbs_favs', $res);
        return $res;
    }
    
    function itemDelete( $mItemID, $nUserID )
    {
        if(empty($mItemID)) return 0;
        if(!is_array($mItemID)) {
            $mItemID = array($mItemID);
        }
                                
        $aData = $this->db->select('SELECT I.id, I.cat_id, I.cat1_id, I.cat2_id, I.cat_type, I.img, I.imgcnt 
                                    FROM '.TABLE_BBS_ITEMS.' I WHERE '.$this->db->prepareIN('I.id', $mItemID).' AND I.user_id = '.$nUserID);
        if(empty($aData)) return 0;
        
//        $aCatsItemsDecrement = array();
//        $aCatsTypesItemsDecrement = array();
        
        $aDelID = array();
        $oImages = $this->initImages();
        foreach($aData as $item) {
            if($item['imgcnt']>0) {
                $aFilenames = explode(',', $item['img'], $item['imgcnt']);
                if(!empty($aFilenames))
                foreach($aFilenames as $filename) {
                    $oImages->deleteImageFileCustom($this->items_images_path, $item['id'], $filename);
                }
            }
            $aDelID[] = $item['id'];
            
//            $catsUpdate = array_unique(array($item['cat_id'], $item['cat1_id'], $item['cat2_id']));
//            foreach($catsUpdate as $c) {
//                if($c == 0) continue;
//                if(!isset($aCatsItemsDecrement[$c])) {
//                    $aCatsItemsDecrement[$c] = 1;
//                } else {
//                    $aCatsItemsDecrement[$c] += 1;  
//                }
//            }
//            if(!empty($item['cat_type'])) {
//                if(!isset($aCatsTypesItemsDecrement[$item['cat_type']])) {
//                    $aCatsTypesItemsDecrement[$item['cat_type']] = 1;
//                } else {
//                    $aCatsTypesItemsDecrement[$item['cat_type']] += 1;  
//                }
//            }
        }
        
        if(!empty($aDelID)) {
            $sqlIN = $this->db->prepareIN('id', $aDelID);
            $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_FAV.' WHERE item_'.$sqlIN);
            $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_VIEWS.' WHERE item_'.$sqlIN);
            $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_COMMENTS.' WHERE item_'.$sqlIN);
            $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_CLAIMS.' WHERE item_'.$sqlIN);
            $res = $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS.' WHERE '.$sqlIN);
            if(!empty($res) && $nUserID>0) {
                $this->db->execute('UPDATE '.TABLE_USERS.' SET items = items - '.sizeof($aDelID).' WHERE user_id = '.$nUserID);
            }
            
//            $this->itemsCounterUpdate( (!empty($aCatsItemsDecrement) ? $aCatsItemsDecrement : array()),
//                                       (!empty($aCatsTypesItemsDecrement) ? $aCatsTypesItemsDecrement : array()),
//                                       false );
        }
        
        return sizeof($aDelID);
    }
    
    function itemsCounterUpdate($aCatsID, $aCatTypesID, $bIncrement = true, $bOneItem = false)
    {
        $act = ($bIncrement?'+':'-');
        if(!empty($aCatsID)) 
        {
            if($bOneItem) {
                $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.' 
                    SET items = items '.$act.' 1
                    WHERE id IN('.join(',', $aCatsID).')
                ');
            } else {
                $aUpdateData = array();
                foreach($aCatsID as $k=>$i){
                    $aUpdateData[] = "WHEN $k THEN (items $act $i)";
                }
                if(!empty($aUpdateData)) {                                
                    $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.' 
                                        SET items = CASE id '.join(' ', $aUpdateData).' ELSE items END 
                                        WHERE id IN ('.join(',',array_keys($aCatsID)).')' );
                }       
            }         
        }
        if(!empty($aCatTypesID)) 
        {
            if($bOneItem) {
                $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES_TYPES.' 
                    SET items = items '.$act.' 1 WHERE id IN ('.join(',', $aCatTypesID).')
                ');
            } else {
                $aUpdateData = array();
                foreach($aCatTypesID as $k=>$i){
                    $aUpdateData[] = "WHEN $k THEN (items $act $i)";
                }
                if(!empty($aUpdateData)) {                                
                    $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES_TYPES.' 
                                        SET items = CASE id '.join(' ', $aUpdateData).' ELSE items END 
                                        WHERE id IN ('.join(',',array_keys($aCatTypesID)).')' );
                }       
            }         
        } 
    }
    
    function _buildCommentsRecursive($aComments, $bBegin=true) 
    {
        static $aResultComments, $iLevel, $iMaxIdComment;
        if ($bBegin) {
            $aResultComments = array();
            $iLevel = 0;
            $iMaxIdComment = 0;
        }        
        foreach($aComments as $aComment) 
        {
            $aTemp =& $aComment;
            if ($aComment['id']>$iMaxIdComment) {
                $iMaxIdComment = $aComment['id'];
            }
            $aTemp['level'] = $iLevel;  
            $aResultComments[] = $aTemp;            
            if(!empty($aComment['childnodes'])) {
                $iLevel++;
                $this->_buildCommentsRecursive($aComment['childnodes'], false);
            }
            unset($aTemp['childnodes']);
        }           
        $iLevel--;        
        return array('aComments'=>$aResultComments,'nMaxIdComment'=>$iMaxIdComment);
    }
    
    function getItemComments($nItemID, $nCommentLastID = 0)
    {
        $aComments = $this->db->select('SELECT C.*, ( CASE WHEN C.user_id != 0 THEN U.name ELSE C.name END) as name, U.blocked as user_blocked, U.blocked_reason as user_blocked_reason
                FROM '.TABLE_BBS_ITEMS_COMMENTS.' C
                    LEFT JOIN '.TABLE_USERS.' U ON C.user_id = U.user_id 
                WHERE C.item_id='.$nItemID.($nCommentLastID>0?' AND C.id>'.$nCommentLastID:' ').' 
                ORDER BY C.id ASC
                '); 
        if(!empty($aComments)){
            return $this->_buildCommentsRecursive( $this->db->transformRowsToTree($aComments,'id','pid','childnodes') ); 
        }
        return array('aComments'=>array(),'nMaxIdComment'=>0);
    }
    
    function getItemClaimReasons()
    {
        return array(
            1  => 'Неверная контактная информация',
            2  => 'Объявление не соответствует рубрике',
            4  => 'Объявление не отвечает правилам портала',
            8  => 'Некорректная фотография',
            16 => 'Антиреклама/дискредитация',
            32 => 'Другое',
        );        
    }
    
    function getItemClaimText($nReasons, $sComment)
    {
        $reasons = $this->getItemClaimReasons();
        if(!empty($nReasons) && !empty($reasons)) 
        {
            $r_text = array();
            foreach($reasons as $rk=>$rv) {
                if($rk!=32 && $rk & $nReasons) {
                    $r_text[] = $rv;
                }
            }
            $r_text = join(', ', $r_text);
            if($nReasons & 32 && !empty($sComment)) {
                $r_text .= ', '.$sComment;
            }
            return $r_text;
        }
        return '';
    }
    
    function isEditPassGranted($nItemID)
    {
        return (!empty($_SESSION['bbs-pass']) && isset($_SESSION['bbs-pass'][$nItemID]));
    }
    
    function grantEditPass($nItemID) 
    {
        if(!$this->isEditPassGranted($nItemID)) {
            if(empty($_SESSION['bbs-pass'])) {
                $_SESSION['bbs-pass'] = array();
            }
            $_SESSION['bbs-pass'][$nItemID] = 1;
        }
    }
    
    function preparePublicatePeriodTo($nPeriod, $nFrom)
    {
        $dateFormat = 'Y-m-d H:i:s';
        $dateWeek = (60*60 * 24 * 7);
        $dateFrom = $nFrom;
        switch( $nPeriod )
        {
            case 6: $dateTo = date($dateFormat, $dateFrom + (60*60 * 24 * 3) );  break; //3 дня
            case 1: $dateTo = date($dateFormat, $dateFrom+$dateWeek);  break;   //1 неделя
            case 2: $dateTo = date($dateFormat, $dateFrom+$dateWeek*2);  break; //2 недели
            case 3: $dateTo = date($dateFormat, $dateFrom+$dateWeek*3);  break; //3 недели 
            case 4: $dateTo = date($dateFormat, mktime(0,0,0,date('m', $dateFrom)+1,date('d', $dateFrom),date('Y', $dateFrom)) );  break; //1 месяц
            case 5: $dateTo = date($dateFormat, mktime(0,0,0,date('m', $dateFrom)+2,date('d', $dateFrom),date('Y', $dateFrom)) );  break; //2 месяц
        }
        return $dateTo;
    }
    
    function checkFreePublicationsLimit($nCategoryID, $nUserID, $sUID = '')
    {
        if(!$nCategoryID) return true;
        
        $sql = array();
        $sql[] = ( $nUserID > 0 ? '(user_id = '.$nUserID.(!empty($sUID)?' OR uid = '.$this->db->str2sql($sUID):'').')' : 
                                   'uid = '.$this->db->str2sql($sUID) );
        $sql[] = 'cat1_id = '.$nCategoryID;
        $sql[] = 'publicated >= '.$this->db->str2sql( date('Y-m-d', mktime(0,0,0,date('n')-1)) );
        
        // получаем кол-во опубликованных за последний месяц объявлений
        // 1) данным пользователем (на основе userID текущего юзера + его UID, т.е. все, что он надобавлял в неавторизованном виде, также посчитается)
        // 2) в указанной категории
        $nCount = $this->db->one_data('SELECT COUNT(id) FROM '.TABLE_BBS_ITEMS.' WHERE '.join(' AND ', $sql));
        
        $aConf = config::get(array('items_freepubl_category_limit_reg', 'items_freepubl_category_limit', ), false, 'bbs_');
        return ($nCount < $aConf['items_freepubl_category_limit'.($nUserID>0?'_reg':'')]);
    }
}
