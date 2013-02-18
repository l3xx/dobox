<?php

/**
* Компонент для работы с динамическими свойствами
*/

class dbDynprops extends Module 
{   
    # типы динамических свойств
    const typeInputText     = 1;  // Однострочное текстовое поле
    const typeTextarea      = 2;  // Многострочное текстовое поле
    const typeWysiwyg       = 3;  // Текстовый редактор
    const typeRadioYesNo    = 4;  // Выбор Да/Нет
    const typeCheckbox      = 5;  // Флаг
    const typeSelect        = 6;  // Выпадающий список                                                    
    const typeSelectMulti   = 7;  // Выпадающий список с мультивыбором (ctrl)
    const typeRadioGroup    = 8;  // Группа св-в с единичным выбором
    const typeCheckboxGroup = 9;  // Группа св-в с множественным выбором
    const typeNumber        = 10; // Число
    const typeRange         = 11; // Диапазон
    const typeCountry       = 12; // Страна
    const typeState         = 13; // Штат
    const typeDate          = 14; // Дата
    
    const datePatternJS     = 'yy-mm-dd'; // Формат даты для Javascript
    const datePatternPHP    = 'Y-m-d';    // Формат даты для PHP
        
    # настройки работы с владельцами
    protected $ownerColumn = 'cat_id';
    protected $ownerTable;
    protected $ownerTable_ID = 'id';
    protected $ownerTableType = 'ns'; //integer - adjacency list, с указанием максимальной глубины вложенности; 'ns' - nested sets
    // ?? protected $ownerOneLevelRecords = 1; //максимальное кол-во записей на одном уровне владельцев
    
    
    /** @var string таблица параметров свойств */    
    protected $tblDynprops;
    /** @var string таблица значений свойств с множественным выбором */    
    protected $tblMulti;
    /** @var string таблица связи свойств с владельцем (используется при частичном наследовании) */
    protected $tblIn;     
         
    protected $inherit = false; //false|0 - без наследования, 1 - полное, 2 - выборочное                                 
            
    public $datafield_prefix     = 'f';
    public $datafield_int_first  = 1;
    public $datafield_int_last   = 10;
    public $datafield_text_first = 11;
    public $datafield_text_last  = 14;
                                           
    public $act_listing = 'dynprops_listing';
    public $act_action  = 'dynprops_action';
                               
    public $typesAllowed = array();
    public $typesAllowedParent = array( self::typeCheckbox, self::typeSelect, self::typeRadioGroup );
    public $typesAllowedChild = array( self::typeSelect );
    
    /**
    * @param string название столбца владельца
    * @param string таблица владельцев
    * @param string таблица параметров свойств
    * @param string таблица значений свойств полей с множественным выбором
    * @param mixed наследование: false|0 - без наследования, true|1 - полное, 2 - выборочное
    * @param string таблица связывающая свойства с владельцем (при включенном наследовании)
    */
    public function __construct($ownerColumn, $tableOwners, $tableDynprops, $tableDynpropsMulti, $mInherit = false, $tableDynpropsIn = false)
    {   
        $this->ownerColumn = $ownerColumn;  
        $this->ownerTable = $tableOwners;  
        
        $this->tblDynprops = $tableDynprops;
        $this->tblMulti = $tableDynpropsMulti;
        
        $this->inherit = $mInherit;
        if($this->isInheritParticular()) {
            $this->tblIn = $tableDynpropsIn;   
        }
        $this->module_dir = PATH_CORE.'database'.DIRECTORY_SEPARATOR.'dynprops'.DIRECTORY_SEPARATOR;
    }

    public function listing()
    {
        if(!$this->security->haveAccessToModuleToMethod($this->module_name, 'dynprops'))
            return $this->showAccessDenied();
            
        $nOwnerID = $this->input->id('owner');

        $aData = $this->db->one_array('SELECT O.id as owner_id, O.title as owner_title, '.($this->inherit ? 'O.pid': '0').' as owner_parent 
                                       FROM '.$this->ownerTable.' O 
                                       WHERE O.id = '.$nOwnerID);
        if($aData['owner_parent'] != 0) {      
            $aData['owner_parent'] = $this->db->one_array('SELECT O.id, O.title FROM '.$this->ownerTable.' O WHERE O.id = '.$aData['owner_parent']);
        }
                                                                 
        $aData['dynprops'] = $this->getByOwner($nOwnerID, true, false, false);
        
        $aData['url_listing'] = $this->adminCreateLink($this->act_listing);
        $aData['url_action'] = $this->adminCreateLink($this->act_action);
        $aData['url_action_owner'] = $this->adminCreateLink($this->act_action).'&owner='.$nOwnerID.'&act=';

        $this->includeJS('tablednd');
        $this->adminCustomCenterArea();
        return $this->tplFetchPHP($aData, 'listing.php');
    }
    
    public function action()
    {
        $nOwnerID = $this->input->id('owner');
        $sReturnLink = $this->act_listing.'&owner='.$nOwnerID;

        switch($this->input->get('act'))
        {
            case 'add':
            {   
                $aData = array('data' => array());
                if(bff::$isPost)
                {
                    $aData['data'] = $this->input->post('dynprop', TYPE_ARRAY);
                                                              
                    $res = $this->insert($aData['data'], $nOwnerID);
                    if($res) {
                        $this->adminRedirect(Errors::SUCCESSFULL, $sReturnLink);
                    }
                }
                  
                $aData['owner'] = $this->db->one_array('SELECT O.id, O.title, '.($this->inherit ? 'O.pid': '0').' as parent 
                                               FROM '.$this->ownerTable.' O WHERE O.id = ' . $nOwnerID);
                if($aData['owner']['parent'] != 0){
                    $aData['owner']['parent'] = $this->db->one_array('SELECT O.id, O.title FROM '.$this->ownerTable.' O WHERE O.id = '.$aData['owner']['parent']);    
                }                                 
                         
                $this->includeJS(array('dynprops', 'tablednd'));
                $aData['edit'] = false;
                $aData['url_listing'] = $this->adminCreateLink($this->act_listing).'&owner=';
                $aData['url_action_owner'] = $this->adminCreateLink($this->act_action).'&owner='.$nOwnerID.'&act=';
                return $this->tplFetchPHP($aData, 'manage.php');
            } break;
            case 'child': //ajax
            {
                $this->input->postm(array(
                    'parent_id'    => TYPE_UINT,
                    'parent_value' => TYPE_UINT,
                    'child_act'    => TYPE_STR,
                    'id'           => TYPE_UINT,                    
                ), $aData); extract($aData);
                
                if(!empty($aData['child_act']))
                {
                    switch($aData['child_act'])
                    {
                        case 'save': {
                            $aDynpropParams = $this->input->post('dynprop', TYPE_ARRAY);
                            if($aData['id']) {
                                $res = $this->update($aDynpropParams, $aData['id']);
                            } else {                                                       
                                $res = $this->insert($aDynpropParams, $nOwnerID, array('id'=>$parent_id, 'value'=>$parent_value));
                            }
                            if($res) $this->ajaxResponse( Errors::SUCCESSFULL );
                        } break;
                        case 'del': {
                            $res = $this->del($aData['id'], $nOwnerID); 
                            if($res) $this->ajaxResponse( Errors::SUCCESSFULL );
                        } break;
                    }
                    $this->ajaxResponse( Errors::IMPOSSIBLE );
                } else {  
                    $aData['data'] = $this->db->one_array('SELECT * FROM '.$this->tblDynprops.' WHERE parent_id = '.$parent_id.' AND parent_value='.$parent_value);
                    if(!empty($aData['data']))
                    {
                        $aData['id'] = $aData['data']['id'];
                        if($this->isMulti($aData['data']['type'])) {
                            $aData['data']['multi'] = $this->db->select('SELECT * FROM '.$this->tblMulti.' WHERE dynprop_id = ' . $aData['data']['id'] . ' ORDER BY num');
                        }
                    }
                    $aData['edit'] = !empty($aData['id']);
                }
                                                                                                                        
                //$aData['url_action_owner'] = $this->adminCreateLink($this->act_action).'&owner='.$nOwnerID.'&act=';  
                $this->typesAllowed = $this->typesAllowedChild;
                $this->ajaxResponse( array( 'form'=> $this->tplFetchPHP($aData, 'manage.child.php') ) );
            } break;            
            case 'inherit_list': //ajax
            {
                if(!$nOwnerID || !$this->isInheritParticular()) {
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                }
                
                $aData = $this->db->one_array('SELECT O.id as owner_id, O.title as owner_title, '.($this->inherit ? 'O.pid': '0').' as parent 
                                                FROM '.$this->ownerTable.' O WHERE O.id = '.$nOwnerID);
                if($aData['parent'] == 0) {
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                }                             
                
                $aOwnerParentID = $this->getOwnerParentsID( $nOwnerID );
                if(empty($aOwnerParentID)) {
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                }   
                
                $aData['dynprops'] = $this->db->select('SELECT D.id, D.title, D.'.$this->ownerColumn.', D.type, D.enabled, D.is_search, I.data_field, I2.'.$this->ownerColumn.' as inherited
                                                   FROM '.$this->tblDynprops.' D,
                                                        '.$this->tblIn.' I
                                                        LEFT JOIN '.$this->tblIn.' I2 ON I2.dynprop_id = I.dynprop_id AND I2.'.$this->ownerColumn.' = '.$nOwnerID.'
                                                   WHERE '.$this->db->prepareIN('I.'.$this->ownerColumn, $aOwnerParentID).' AND I.dynprop_id = D.id AND D.parent_id = 0
                                                   GROUP BY D.id
                                                   ORDER BY I.num');  
                                                               
                $aData['url_listing'] = $this->adminCreateLink($this->act_listing);
                $aData['url_action'] = $this->adminCreateLink($this->act_action);
                $this->ajaxResponse( $this->tplFetchPHP($aData, 'inherit.php') );         
            } break;
            case 'inherit_do': //ajax 
            {
                $nDynpropID = $this->input->id('dynprop');                 
                if(!$this->isInheritParticular() || !$nDynpropID || !$nOwnerID) 
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                        
                $res = $this->linkIN($nOwnerID, $nDynpropID, false, false);
                
                $this->ajaxResponse( ($res ? Errors::SUCCESSFULL : Errors::IMPOSSIBLE) );
            } break;
            case 'inherit_copy': //ajax
            {
                $nDynpropID = $this->input->id('dynprop');                 
                if(!$this->isInheritParticular() || !$nDynpropID || !$nOwnerID)
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                                    
                $res = $this->copy($nDynpropID, $nOwnerID);
                                
                $this->ajaxResponse( ($res ? Errors::SUCCESSFULL : Errors::IMPOSSIBLE) );
            } break;            
            case 'edit':
            {
                $nOwnerIDFrom = $this->input->id('owner_from');
                if($nOwnerIDFrom) {
                     $sReturnLink = $this->act_listing.'&owner='.$nOwnerIDFrom;
                }
                
                $nDynpropID = $this->input->id('dynprop');                 
                if(!$nDynpropID) $this->adminRedirect(Errors::IMPOSSIBLE, $sReturnLink);

                $aData = array();
                if(bff::$isPost)
                {    
                    $aData['data'] = $this->input->post('dynprop', TYPE_ARRAY);
                    
                    $res = $this->update($aData['data'], $nDynpropID);
                    if($res) {
                        $this->adminRedirect(Errors::SUCCESSFULL, $sReturnLink);
                    }
                } else {
                    $aData['data'] = $this->db->one_array('SELECT D.* FROM '.$this->tblDynprops.' D
                         WHERE D.id = '.$nDynpropID.' AND D.'.$this->ownerColumn.' = '.$nOwnerID);
                         
                    $data = &$aData['data'];
                    if($this->isMulti($data['type']))
                    {
                        $data['multi'] = $this->db->select('SELECT value, name FROM '.$this->tblMulti.' WHERE dynprop_id = ' . $nDynpropID . ' ORDER BY num');
                    }
                    
                    if($this->hasExtra($data['type']) || $data['parent'] || $data['txt']) {
                        $extra = unserialize( $data['extra'] );
                        if($extra!==false) {
                            $data = array_merge($data, $extra);
                        }
                    }
                } 
                
                if(empty($aData['data'])) $this->adminRedirect(Errors::IMPOSSIBLE, $sReturnLink);                     

                $aData['owner'] = $this->db->one_array('SELECT O.id, O.title, '.($this->inherit ? 'O.pid': '0').' as parent 
                                               FROM '.$this->ownerTable.' O WHERE O.id = '.$nOwnerID);
                if($aData['owner']['parent'] != 0){
                    $aData['owner']['parent'] = $this->db->one_array('SELECT O.id, O.title FROM '.$this->ownerTable.' O WHERE O.id = '.$aData['owner']['parent']);    
                } 
                
                $this->includeJS(array('dynprops', 'tablednd'));
                $aData['owner_from'] = $nOwnerIDFrom;    
                $aData['edit'] = true;
                $aData['url_listing'] = $this->adminCreateLink($this->act_listing).'&owner=';
                $aData['url_action_owner'] = $this->adminCreateLink($this->act_action).'&owner='.$nOwnerID.'&act=';
                return $this->tplFetchPHP($aData, 'manage.php');
            } break;
            case 'rotate':
            {
                if($this->isInheritParticular()) {
                    $res = $this->db->rotateTablednd( $this->tblIn, ' AND '.$this->ownerColumn.' = '.$nOwnerID, 'dynprop_id', 'num', true, $this->ownerColumn);
                } else {
                    $res = $this->db->rotateTablednd( $this->tblDynprops, ' AND '.$this->ownerColumn.' = '.$nOwnerID, 'id', 'num', true, $this->ownerColumn);
                }
                
                $this->ajaxResponse( $res ? Errors::SUCCESSFULL : Errors::IMPOSSIBLE );
            } break;
            case 'del':
            {
                $nDynpropID = $this->input->id('dynprop');
                if(!$nDynpropID) $this->adminRedirect(Errors::IMPOSSIBLE, $sReturnLink);

                $bRemoveOnlyInheritence = func::GET('inherit')?1:0;
                $res = $this->del($nDynpropID, $nOwnerID, $bRemoveOnlyInheritence==1);

                $this->adminRedirect( ($res? Errors::SUCCESSFULL : Errors::IMPOSSIBLE), $sReturnLink);
            } break;
        }
    }
    
    /**
    * @param mixed (integer|array) id владельца
    * @param array данные
    * @param mixed включая параметры наследуемых свойств (boolean - включать/не включать, 2 - включать с сохранением ключа реального владельца)
    * @param array id свойств, которые следует исключить из результата
    * @param string префикс
    * @param string тип формы
    * @param string путь к шаблону (false - путь указанный компонентом)
    * @param boolean только свойства отмеченные "для поиска"  
    * @return array 
    *   1 владелец: array('form'-html представление свойств, 'id'-id связанных свойств, 'i'-id наследуемых свойств)
    *   N владельцев: array(
    *       'form'=> array(
    *           'id владельца'=> html представление свойств, 
    *           ...
    *        ),
    *       'id'    => array( 'id свойства', ...), - id всех возвращаемых свойств
    *       'links' => array(
    *           'id владельца'=>array(
    *               'id' => array( 'id свойства', ... ), // id связанных с владельцем свойств
    *               'i'  => array( 'id свойства', ... )  // id наследуемых владельцем свойств
    *            ), ...
    *        )
    *    )
    */    
    public function form($mOwnerID, $aData = false, $bAddInherited = false, $aDynpropsExcludeID = array(), $sPrefix = 'dynprops', $sFormType = 'table', $sTemplateDir = false, $bSearchOnly = false)
    {
        if(empty($mOwnerID)) return '';

        $bMulti = true;
        
        if(is_array($mOwnerID)) 
        {             
            $aDynprops = $this->getByOwners($mOwnerID, $bAddInherited, $bMulti, $bSearchOnly);

            if(empty($aDynprops)) return '';
            if($aData===false) { $aData = array(); }   
            
            $aResult = array('form'=>array(),'id'=>array(),'links'=>array());
            
            foreach($aDynprops as $v) {
                $aResult['id'][] = $v['id'];
                if($this->inherit && $bAddInherited)
                    foreach(explode(',', $v['owners']) as $o) {
                        $aResult['links'][$o]['id'][] = $v['id'];
                        if(!in_array($v[$this->ownerColumn], $mOwnerID))
                            $aResult['links'][$o]['i'][] = $v['id'];
                    }
            }

            if($this->checkChildren()) {   
                $aDynpropsChildren = $this->getChildrenByParents($aDynprops, $aData, $bMulti, true, true);
            } 
            
            //в случае $bAddInherited = 2, идем по ключам $aDynprops, т.к. могут появиться паренты которых нет в $mOwnerID  
            $aDynprops = func::array_transparent($aDynprops, $this->ownerColumn);
            foreach( ($bAddInherited == 2 && $aData!==false ? array_keys($aDynprops) : $mOwnerID) as $ownerID) {
                if($aData!==false) {                     
                   $this->applyData($aDynprops[$ownerID], $aData[$ownerID]);
                } else {
                    $aData[$ownerID] = array();
                }
                $aParam = array('dynprops' => $aDynprops[$ownerID], 'prefix'=>$sPrefix, 'children'=>(isset($aDynpropsChildren)? $aDynpropsChildren : array()) );
                $aResult['form'][$ownerID] = $this->tplFetchPHP($aParam, ($sTemplateDir===false ? "form.$sFormType.php" : $sFormType), $sTemplateDir);
            }
            return $aResult;
        }
        else
        {                             
            $aDynprops = $this->getByOwner($mOwnerID, $bAddInherited, $bMulti, $bSearchOnly);
                                                                                
            if(empty($aDynprops)) return '';
            if($aData!==false) {
                $this->applyData($aDynprops, $aData);
            }
            
            if($this->checkChildren()) {   
                $aDynpropsChildren = $this->getChildrenByParents($aDynprops, $aData, $bMulti, true, true);
            }   
            
            $aDynpropsInheritedID = array();
            if($this->inherit && $bAddInherited) {
                foreach($aDynprops as $k=>$d) {
                    if($d[$this->ownerColumn]!=$mOwnerID)
                        $aDynpropsInheritedID[] = $k;
                }
            }
            $aResult = array('form'=>'', 
                             'id'=>array_keys($aDynprops), 
                             'i'=>$aDynpropsInheritedID);
            if(!empty($aDynpropsExcludeID)) {
                foreach($aDynpropsExcludeID as $id) {
                    if(isset($aDynprops[$id])) {
                        unset($aDynprops[$id]);
                    }
                }
            }
            $aParam = array('dynprops'=>$aDynprops, 'prefix'=>$sPrefix, 'children'=>(isset($aDynpropsChildren)? $aDynpropsChildren : array()) );
            $aResult['form'] = $this->tplFetchPHP($aParam, ($sTemplateDir===false ? "form.$sFormType.php" : $sFormType), $sTemplateDir);
            return $aResult;
        }
    }   

    public function formChildAdd($nParentDynpropID, $nParentDynpropValue, $sFormType = 'values', $sTemplateDir = false)
    {
        $aData = $this->db->one_array('SELECT C.*, P.extra as parent_extra FROM '.$this->tblDynprops.' C, '.$this->tblDynprops.' P 
                WHERE C.parent_id = '.$nParentDynpropID.' AND C.parent_value='.$nParentDynpropValue.' 
                    AND C.parent_id = P.id');
        if(!empty($aData))
        {
            if($this->isMulti($aData['type'])) {
                $aData['multi'] = $this->db->select('SELECT * FROM '.$this->tblMulti.' WHERE dynprop_id = ' . $aData['id'] . ' ORDER BY num');
            }
            $aParentExtra = unserialize($aData['parent_extra']);
            if($aParentExtra!==false && !empty($aParentExtra['child_default'])) {
                $aData['default_value'] = $aParentExtra['child_default'];
            } else {
                $aData['default_value'] = false;
            }
            return array('form'=>$this->tplFetchPHP($aData, ($sTemplateDir===false ? "form.child.$sFormType.php" : $sFormType), $sTemplateDir),
                         'id'=>$aData['id']);
        }
        return array('form'=>'','id'=>0);
    }
    
    public function formChildEdit($aDynprop, $sFormType = 'values', $sTemplateDir = false)
    {
        if(!empty($aDynprop)) {
            return array('form'=>$this->tplFetchPHP($aDynprop, ($sTemplateDir===false ? "form.child.$sFormType.php" : $sFormType), $sTemplateDir),
                         'id'=>$aDynprop['id']);
        }
        return array('form'=>'','id'=>0);
    }
    
    ############################################################################################################
    
    /**
    * Получаем параметры свойств по ID одного владельца
    * @param integer id владельца
    * @param boolean включая параметры наследуемых свойств (2 - )  
    * @param boolean получать значения свойств с множественным выбором
    * @param boolean только свойства отмеченные "для поиска"
    * @return array параметры свойств
    */
    public function getByOwner($nOwnerID, $bAddInherited = false, $bMulti = true, $bSearchOnly = false, $bOnlyBBSInTable = false)
    {
        $sqlExtra = '';
        if($bSearchOnly) {
            $sqlExtra .= ' AND D.is_search = 1';
        }
        if($bOnlyBBSInTable) {
            $sqlExtra .= ' AND D.in_table = 1';
        }
        $sqlExtra .= ' AND D.parent_id = 0'; 
                                                  
        $props = array();
        if($this->inherit)
        {
            if(!$bAddInherited)
            {   //возвращаем все свойства, кроме наследуемых
                if($this->isInheritParticular()) 
                {
                    $props = $this->db->select('SELECT D.*, DI.data_field, 0 as inherited
                                                FROM '.$this->tblDynprops.' D,
                                                     '.$this->tblIn.' DI
                                                WHERE DI.'.$this->ownerColumn.' = '.$nOwnerID.' AND DI.dynprop_id = D.id
                                                      AND D.'.$this->ownerColumn.' = DI.'.$this->ownerColumn.'
                                                    '.$sqlExtra.'                                                       
                                                ORDER BY DI.num');
                } else {
                    $props = $this->db->select('SELECT D.*, 0 as inherited
                            FROM '.$this->tblDynprops.' D
                            WHERE D.'.$this->ownerColumn.' = '.$nOwnerID.$sqlExtra.'                                                       
                            ORDER BY D.num');
                }
            } else {  
                //возвращаем все свойства, включая наследуемые
                if($this->isInheritParticular()) 
                {
                    $props = $this->db->select('SELECT D.*'.($bAddInherited!==2? ',DI.'.$this->ownerColumn : '').', DI.data_field,
                                                       (D.'.$this->ownerColumn.'!=DI.'.$this->ownerColumn.') as inherited
                                                FROM '.$this->tblDynprops.' D,
                                                     '.$this->tblIn.' DI
                                                WHERE DI.'.$this->ownerColumn.' = '.$nOwnerID.' AND DI.dynprop_id = D.id
                                                    '.$sqlExtra.'
                                                ORDER BY D.'.$this->ownerColumn.' ASC, DI.num');
                } else {    
                    $aOwnerParentsID = $this->getOwnerParentsID($nOwnerID);
                    $aOwnerParentsID[] = $nOwnerID;
                    $props = $this->db->select('SELECT D.*, (D.'.$this->ownerColumn.'!='.$nOwnerID.') as inherited'.($bAddInherited!==2? ',D.'.$this->ownerColumn : '').'
                                                FROM '.$this->tblDynprops.' D
                                                WHERE '.$this->db->prepareIN('D.'.$this->ownerColumn, $aOwnerParentsID).' '.$sqlExtra.'
                                                ORDER BY D.'.$this->ownerColumn.' ASC, D.num');
                }
            }
        } else {
            $props = $this->db->select('SELECT D.*, 0 as inherited
                                        FROM '.$this->tblDynprops.' D
                                        WHERE D.'.$this->ownerColumn.' = '.$nOwnerID.'
                                            '.$sqlExtra.'
                                        ORDER BY D.num');
        }
        
        if(!empty($props)) 
        {
            $props = func::array_transparent($props, 'id', true); 

            $aMultiID = array();
            foreach($props as $k=>&$v)
            {
                if( $bMulti && $props[$k]['multi'] = $this->isMulti($v['type']) ) {
                    $aMultiID[] = $v['id'];
                } 
                if($this->hasExtra($v['type']) || $v['parent'] || $v['txt']) {
                    $extra = unserialize( $v['extra'] );
                    if($extra!==false) {
                        $v = array_merge($v, $extra);
                    }
                }
            }
            
            if($bMulti && !empty($aMultiID)) 
            {
                $aMultiData = $this->db->select('SELECT * FROM '.$this->tblMulti.'
                                            WHERE dynprop_id IN(' . join(',', $aMultiID) . ') ORDER BY num');
                $aMultiData = func::array_transparent($aMultiData, 'dynprop_id');
                foreach($aMultiID as $id) {
                    $props[$id]['multi'] = $aMultiData[$id];
                }
                unset($aMultiData);
            }
            
            return $props;
        }
        return array();
    }

    /**
    * Получаем параметры свойств нескольких владельцев
    * @param mixed id владельцев
    * @param boolean включая параметры наследуемых свойств  
    * @param boolean получать значения свойств с множественным выбором
    * @param boolean только свойства отмеченные "для поиска"
    * @return array параметры свойств
    */
    public function getByOwners($aOwnerID, $bAddInherited = false, $bMulti = true, $bSearchOnly = false)
    {
        if(empty($aOwnerID))
            return false;
        
        if(!is_array($aOwnerID))
            $aOwnerID = array($aOwnerID);
        
        $sqlExtra = '';
        if($bSearchOnly) {
            $sqlExtra .= ' AND D.is_search = 1';
        }
        $sqlExtra .= ' AND D.parent_id = 0';
        
        $props = array();
        if($this->inherit)
        {
            if(!$bAddInherited)
            {   // возвращаем только уникальные свойства владельцев
                if($this->isInheritParticular()) 
                {
                    // ! MYSQL only (GROUP_CONCAT)
                    $props = $this->db->select('SELECT D.*, DI.data_field, 0 as multi,  
                                                       GROUP_CONCAT(DI.'.$this->ownerColumn.') as owners
                                                FROM '.$this->tblDynprops.' D,
                                                     '.$this->tblIn.' DI
                                                WHERE '.$this->db->prepareIN('D.'.$this->ownerColumn, $aOwnerID).' 
                                                      AND DI.dynprop_id = D.id
                                                    '.$sqlExtra.'
                                                GROUP BY D.id                   
                                                ORDER BY DI.num');
                } else {
                    $props = $this->db->select('SELECT D.*, 0 as multi, D.'.$this->ownerColumn.' as owners
                                                FROM '.$this->tblDynprops.' D
                                                WHERE '.$this->db->prepareIN('D.'.$this->ownerColumn, $aOwnerID).'
                                                    '.$sqlExtra.'
                                                ORDER BY D.num');
                }
            } else {
                //возвращаем все свойства владельцев (включая наследуемые)
                if($this->isInheritParticular()) 
                {
                    // ! MYSQL only (GROUP_CONCAT)
                     $props = $this->db->select('SELECT D.*'.($bAddInherited!==2? ',DI.'.$this->ownerColumn : '').', DI.data_field, 0 as multi,
                                                       GROUP_CONCAT(DI.'.$this->ownerColumn.') as owners
                                                FROM '.$this->tblDynprops.' D,
                                                     '.$this->tblIn.' DI
                                                WHERE '.$this->db->prepareIN('DI.'.$this->ownerColumn, $aOwnerID).' 
                                                      AND DI.dynprop_id = D.id
                                                    '.$sqlExtra.'
                                                GROUP BY D.id                   
                                                ORDER BY DI.num');
                } else {
                    $aOwnerParentsID = array();
                    foreach($aOwnerID as $ownerID) {
                        $parentsID = $this->getOwnerParentsID($ownerID);
                        if(!empty($parentsID)) {
                            $aOwnerParentsID = array_merge($aOwnerParentsID, $parentsID);
                        }
                        $aOwnerParentsID[] = $ownerID;
                    }                              
                    $props = $this->db->select('SELECT D.*, 0 as multi, D.'.$this->ownerColumn.' as owners
                                                FROM '.$this->tblDynprops.' D
                                                WHERE '.$this->db->prepareIN('D.'.$this->ownerColumn, $aOwnerParentsID).'
                                                    '.$sqlExtra.'
                                                ORDER BY D.num');                 
                }
            }
        } else {
            $props = $this->db->select('SELECT D.*, 0 as multi, D.'.$this->ownerColumn.' as owners
                                        FROM '.$this->tblDynprops.' D
                                        WHERE '.$this->db->prepareIN('D.'.$this->ownerColumn, $aOwnerID).'
                                            '.$sqlExtra.'
                                        ORDER BY D.num');
        }
        
        if(!empty($props)) 
        {
            
            $aMultiID = array();
            foreach($props as $k=>&$v)
            {
                if( $bMulti && $props[$k]['multi'] = $this->isMulti($v['type']) ) {
                    $aMultiID[] = $v['id'];
                } 
                if($this->hasExtra($v['type']) || $v['parent'] || $v['txt']) {
                    $extra = unserialize( $v['extra'] );
                    if($extra!==false) {
                        $v = array_merge($v, $extra);
                    }
                }
            }
                
            if($bMulti && !empty($aMultiID)) 
            {
                $aMultiID = array_unique($aMultiID);
                $aMultiData = $this->db->select('SELECT dynprop_id, name, value FROM '.$this->tblMulti.'
                                            WHERE dynprop_id IN(' . join(',', $aMultiID) . ') ORDER BY num');
                $aMultiData = func::array_transparent($aMultiData, 'dynprop_id');
                foreach($props as $k=>$x)
                {
                    if($x['multi']) {
                        $props[$k]['multi'] = $aMultiData[$x['id']];
                    } 
                }
                unset($aMultiData);
            }
            
            return $props;
        }
        return array();
    }
    
    /**
    * Получаем параметры свойств по ID
    * @param mixed id свойств(а)                    
    * @param boolean получать значения свойств с множественным выбором
    * @return array параметры свойств
    */
    public function getByID($aDynpropsID, $bMulti = true)
    {
        if(empty($aDynpropsID))
            return false;
        
        if(!is_array($aDynpropsID))
            $aDynpropsID = array($aDynpropsID);
       
        if($this->isInheritParticular())
        {   
            $props = $this->db->select('SELECT D.*, DI.data_field, 0 as multi
                                        FROM '.$this->tblDynprops.' D, '.$this->tblIn.' DI
                                        WHERE '.$this->db->prepareIN('D.id', $aDynpropsID).' AND DI.dynprop_id = D.id
                                        ORDER BY D.num');

        } else {
            $props = $this->db->select('SELECT D.*, 0 as multi
                                        FROM '.$this->tblDynprops.' D
                                        WHERE '.$this->db->prepareIN('D.id', $aDynpropsID).'
                                        ORDER BY D.num');            
        }
        
        if(!empty($props)) 
        {
            $props = func::array_transparent($props, 'id', true);
            
            $aMultiID = array();
            foreach($props as $k=>&$v)
            {
                if( $bMulti && $props[$k]['multi'] = $this->isMulti($v['type']) ) {
                    $aMultiID[] = $v['id'];
                } 
                if($this->hasExtra($v['type']) || $v['parent'] || $v['txt']) {
                    $extra = unserialize( $v['extra'] );
                    if($extra!==false) {
                        $v = array_merge($v, $extra);
                    }
                }
            }
                
            if($bMulti && !empty($aMultiID)) 
            {
                $aMultiID = array_unique($aMultiID);
                $aMultiData = $this->db->select('SELECT dynprop_id, name, value FROM '.$this->tblMulti.'
                                            WHERE dynprop_id IN(' . join(',', $aMultiID) . ') ORDER BY num');
                $aMultiData = func::array_transparent($aMultiData, 'dynprop_id');
                foreach($props as $k=>$x)
                {
                    if($x['multi']) {
                        $props[$k]['multi'] = $aMultiData[$x['id']];
                    } 
                }
                unset($aMultiData);
            }
            
            return $props;
        }
        return array();
    }
    
    /**
    * Получаем параметры свойств по parentID и parentValue
    * @param array пары array( array('parent_id'=>0, 'parent_value'=>0), ... )
    * @param boolean получать значения свойств с множественным выбором
    * @return array параметры свойств
    */
    public function getByParentIDValuePairs($aPairs, $bMulti = true)
    {      
        if(empty($aPairs))
            return array();
        
        $sqlPairs = array();
        for($i = 0, $cnt = sizeof($aPairs); $i<=$cnt; $i++) {
            if(!empty($aPairs[$i]['parent_id']))
                $sqlPairs[] = '(D.parent_id = '.$aPairs[$i]['parent_id'].(!empty($aPairs[$i]['parent_value']) ? ' AND D.parent_value = '.$aPairs[$i]['parent_value']:'').')';
        } 
        
        if(empty($sqlPairs)) return array();
        
        $sqlPairs = '('.join(' OR ', $sqlPairs).')';

        if($this->isInheritParticular())
        {   
            $props = $this->db->select('SELECT D.*, DI.data_field, 0 as multi
                                        FROM '.$this->tblDynprops.' D, '.$this->tblIn.' DI
                                        WHERE '.$sqlPairs.' AND D.id = DI.dynprop_id
                                        ORDER BY D.num');

        } else {
            $props = $this->db->select('SELECT D.*, 0 as multi
                                        FROM '.$this->tblDynprops.' D
                                        WHERE '.$sqlPairs.'
                                        ORDER BY D.num');            
        }
        
        if(!empty($props)) 
        {
            $props = func::array_transparent($props, 'id', true);
            
            $aMultiID = array();
            foreach($props as $k=>&$v)
            {
                if( $bMulti && $props[$k]['multi'] = $this->isMulti($v['type']) ) {
                    $aMultiID[] = $v['id'];
                } 
                if($this->hasExtra($v['type']) || $v['parent'] || $v['txt']) {
                    $extra = unserialize( $v['extra'] );
                    if($extra!==false) {
                        $v = array_merge($v, $extra);
                    }
                }
            }
            
            if($bMulti && !empty($aMultiID)) 
            {
                $aMultiID = array_unique($aMultiID);
                $aMultiData = $this->db->select('SELECT dynprop_id, name, value FROM '.$this->tblMulti.'
                                            WHERE dynprop_id IN(' . join(',', $aMultiID) . ') ORDER BY num');
                $aMultiData = func::array_transparent($aMultiData, 'dynprop_id');
                foreach($props as $k=>$x)
                {
                    if($x['multi']) {
                        $props[$k]['multi'] = $aMultiData[$k];
                    } 
                }
                unset($aMultiData);
            }

            $propsTmp = $props; $props = array();
            foreach($propsTmp as $p) {
                $props[$p['parent_id']][$p['parent_value']] = $p;
            } unset($propsTmp);
            
            return $props;
        }
        return array();
    }
    
    /**
    * Получаем параметры child-свойств по parentID
    * @param mixed параметры parent-свойств(а)
    * @param array данные
    * @param boolean получать значения свойств с множественным выбором
    * @param boolean получать child-свойства для parent'ов безпривязных типов (typeNumber, typeRange)
    * @param boolean добавлять datafield префикс к ключу при поиске в данных
    * @return array параметры child-свойств
    */
    private function getChildrenByParents($aDynprops, $aData = false, $bMulti = true, $bAddNoValueBindTypes = true, $bAddDatafieldPrefix = true)
    {
        if(empty($aDynprops) || !is_array($aDynprops)) return array();
        
        $aParentParamsPairs = array();
        $noBindValueTypes = ($bAddNoValueBindTypes ? array(self::typeNumber, self::typeRange) : array());
        $dataKey = '';
        foreach($aDynprops as $d) {
            $dataKey = ($bAddDatafieldPrefix ? $this->datafield_prefix : '').$d['data_field']; 
            $noValueBindType = in_array($d['type'], $noBindValueTypes);
            if($d['parent'] && ($noValueBindType || ($aData!==false && !empty($aData[$dataKey])) )  ) {
                if(is_array($aData[$dataKey]) && !$noValueBindType) {
                    foreach($aData[$dataKey] as $parent_value)
                        $aParentParamsPairs[] = array( 'parent_id'=>$d['id'], 'parent_value'=> $parent_value );   
                } else {
                    $aParentParamsPairs[] = array( 'parent_id'=>$d['id'], 'parent_value'=> ($noValueBindType ? 0 : $aData[$dataKey]) );
                }
            }
        }
        if(!empty($aParentParamsPairs)) {
           $aDynpropsChildren = $this->getByParentIDValuePairs($aParentParamsPairs);
           if($aData!==false) {
                $this->applyData($aDynpropsChildren, $aData, $bAddDatafieldPrefix);
           }
           return $aDynpropsChildren;
        }
        return array();
    }
    
    /**
    * Подставляем значения к свойствам
    * @param array параметры свойств
    * @param array значения array(id свойства=>значение, ...)
    * @param boolean добавлять datafield префикс к ключу при поиске в данных      
    */
    public function applyData(&$aDynprops, $aData, $bAddDatafieldPrefix = true)
    {
        if(empty($aDynprops)) return;
        
        foreach($aDynprops as $key=>&$d)
        {
            if(!isset($d['type']) && is_array($d)) {
                //вложенные свойств
                $this->applyData($d, $aData, $bAddDatafieldPrefix);
                continue;
            }
            $key = ($bAddDatafieldPrefix ? $this->datafield_prefix : '').$d['data_field'];
            $value = (isset($aData[$key]) ? $aData[$key] : '');
            
            if($this->isStoringBits($d['type']))
            {
                $d['value'] = join(';', $this->bit2source($value));
            }
            else if($this->isMulti($d['type']))
            {
                foreach($d['multi'] as $mk => $mv)
                {
                    if($mv['value'] == $value) {
                        $d['value'] = $mv['value'];
                        break;
                    }
                }
                if(!isset($d['value'])) $d['value'] = 0;
            }
            else
            {
                $d['value'] = $value;
            }
        }
    }
    
    /**
    * Подготовка к сохранению extra-данных свойства
    * @param array параметры свойства
    * @param integer тип свойства
    * @return array
    */
    protected function prepareExtra(&$aData, $nType)
    {
        $aExtra = array();
        
        if($nType == self::typeRange || $nType == self::typeNumber)
        {
            if($nType == self::typeRange) {
                $this->input->clean_array($aData, array(
                    'start' => TYPE_NUM,
                    'end'   => TYPE_NUM,
                    'step'  => TYPE_NUM,
                ), $aExtra);

                if(!$aExtra['end'])
                    $this->errors->set('enter_range_end');
                if(! $aExtra['step'])
                    $this->errors->set('enter_range_step');
            }
            
            # диапазоны для поиска 
            $this->input->clean_array($aData, array(
                'search_range_user' => TYPE_BOOL,  // пользовательский
                'search_ranges'     => TYPE_ARRAY, // предуказанные
            ), $aExtra);

            if(!empty($aExtra['search_ranges']))
            {
                foreach($aExtra['search_ranges'] as $k=>$v) 
                {   
                    $v['id']   = intval($v['id']);
                    $v['from'] = floatval( strip_tags($v['from']) );
                    $v['to']   = floatval( strip_tags($v['to']) );
                    
                    if(empty($v['from']) && empty($v['to'])) {
                        unset($aExtra['search_ranges'][$k]);
                        continue;
                    }
                }
            }
        }
        
        if($aData['txt']) {
            $aExtra['txt_text'] = $this->input->clean($aData['txt_text'], TYPE_NOHTML);
        }
        
        return $aExtra;
    }
    
    /**
    * Создание свойства
    * @param array параметры свойства
    * @param integer id владельца
    * @param array (id=>id парента, value=>value парента)
    */
    public function insert(&$aData, $nOwnerID, $aParentPropData = false)
    {
        $sqlFields = '';
        $sqlValues = '';

        $this->input->clean_array($aData, array(
            'type'      => TYPE_UINT,
            'req'       => TYPE_BOOL,
            'txt'       => TYPE_BOOL,
            'in_table'  => TYPE_BOOL,
            'is_search' => TYPE_BOOL,
            'parent'    => TYPE_BOOL,
        ), $aData);
        
        $type     = $aData['type'];
        $isBits   = $this->isStoringBits($type); 
        $isMulti  = $this->isMulti($type);
        $isParent = $aData['parent'];
        
        if( $isMulti )
        {
            $mdv = &$aData['multi_default_value'];
            
            $aDefValues     = (isset($mdv) ? array_flip((is_array($mdv) ? $mdv : array($mdv) )) : array());
            $aDefValuesBits = array(); 
            
            $i = 0;               
            foreach($aData['multi'] as $key => $value)
            {   
                $nValue =( !$key ? 0 : ($isBits ? pow(2, $i) : $key) );
                if(array_key_exists($key, $aDefValues))                
                    $aDefValuesBits[] = $nValue;
                
                $i++;
            }
            
            $aData['default_value'] = ( $isBits ? join(';', $aDefValuesBits) : $mdv);
        } else {
            if(!isset($aData['default_value'])) {
                $aData['default_value'] = '';
            }
        }
      
        if($isParent && !$aData['title'])
          $this->errors->set('empty:title');
        
        $aExtra = $this->prepareExtra($aData, $type);
        
        if($isParent) {
            $aExtra['child_title'] = $this->input->clean( $aData['child_title'], TYPE_STR );
            $aExtra['child_default'] = $this->input->clean( $aData['child_default'], TYPE_STR );
        }
        
        if($this->errors->no())
        {
            $datafieldCreated = false;
            
            if(!$this->isInheritParticular() || !empty($aParentPropData)) {
                $nDataField = $this->getDataField($nOwnerID, $type, $aParentPropData);
                if($nDataField===false) {
                    return false;
                }
                $nNum = $this->getNum($nOwnerID);
                
                $sqlFields .= ', data_field, num';
                $sqlValues .= ', '.$nDataField.','.$nNum;
                $datafieldCreated = true;
            }
            
            if(!empty($aParentPropData)) {
                $sqlFields .= ', parent_id, parent_value';
                $sqlValues .= ', '.$aParentPropData['id'].','.$aParentPropData['value'];
            }
            
            //сохраняем свойство
            $res = $this->db->execute('INSERT INTO '.$this->tblDynprops.' 
                                   ('.$this->ownerColumn.', title, description, type, parent, default_value, req, txt, in_table, is_search, extra'.$sqlFields.') 
                                  VALUES( '.$nOwnerID.', '.$this->db->str2sql($aData['title']).', '.$this->db->str2sql($aData['description']).', '.$type.', '.$isParent.',
                                          '.$this->db->str2sql($aData['default_value']).', '.$aData['req'].', '.$aData['txt'].', '.$aData['in_table'].', '.$aData['is_search'].',
                                          '.$this->db->str2sql(serialize($aExtra)).$sqlValues.')');

            $nDynpropID = $this->db->insert_id($this->tblDynprops, 'id');

            if($res !== false && $nDynpropID>0)
            {
                //сохраняем варианты выбора свойства
                if($isMulti) {
                    $i = 0;
                    $aInsert = array();
                    foreach($aData['multi'] as $key => $value)
                    {
                        $nValue =( !$key ? 0 : ($isBits ? pow(2, $i) : $key) );
                        $aInsert[] = '('.$nDynpropID.', '.$this->db->str2sql(trim($value)).', '.$nValue.', '.++$i.')';
                    }
                    if(!empty($aInsert)) {
                        $this->db->execute('INSERT INTO ' . $this->tblMulti.' 
                                        (dynprop_id, name, value, num) VALUES '.join(',', $aInsert));
                    }
                }

                //связываем свойство с владельцем (при частичном наследовании)
                if($this->isInheritParticular()) {
                    return $this->linkIN($nOwnerID, $nDynpropID, $type, !$datafieldCreated );
                } else {
                    return true;
                }
            }
        }
        
        // сохранение данных отправленных перед POST сабмитом
//        if($isBits)
//            $aData['default_value'] = join(';', $aDefValues);
//        
        if($isMulti) {                                                     
            $aMulti = $aData['multi']; $aData['multi'] = array();
            foreach($aMulti as $key => $value) {
                $aData['multi'][] = array('name' => $value, 'value' => $key);
            }
        }

        return false;
    }
    
    /**
    * Сохранение параметров свойства
    * @param array параметры свойства
    * @param integer id свойства
    */
    public function update(&$aData, $nDynpropID)
    {
        $sqlUpdate = '';  
        
        $this->input->clean_array($aData, array(
            'type'      => TYPE_UINT,
            'req'       => TYPE_BOOL,
            'txt'       => TYPE_BOOL, 
            'in_table'  => TYPE_BOOL,
            'is_search' => TYPE_BOOL,
            'parent'    => TYPE_BOOL,
        ), $aData);
        
        $type    = &$aData['type'];
        $isBits  = $this->isStoringBits($type); 
        $isMulti = $this->isMulti($type);  
        $isParent = &$aData['parent']; 

        if( $isMulti )
        {                 
            $mdv = &$aData['multi_default_value'];

            if($isBits) {
                $aData['default_value'] = join(';', (isset($mdv) ? array_flip((is_array($mdv) ? $mdv : array($mdv) )) : array()) );
            }  else {
                $aData['default_value'] = (isset($mdv) ? $mdv : '');
            } 
        }
      
        if($isParent && !$aData['title'])
           $this->errors->set('empty:title');
        
        $aExtra = $this->prepareExtra($aData, $type);

        if($isParent) {
            $aExtra['child_title'] = $this->input->clean( $aData['child_title'], TYPE_STR );
            $aExtra['child_default'] = $this->input->clean( $aData['child_default'], TYPE_STR );
        }

        if($this->errors->no())
        {
            //сохраняем дин. св-во
            $res = $this->db->execute('UPDATE '.$this->tblDynprops.' 
                                  SET title = '.$this->db->str2sql($aData['title']).',
                                      description = '.$this->db->str2sql($aData['description']).',
                                      type  = '.$type.', parent = '.$isParent.',
                                      default_value = '.$this->db->str2sql( (isset($aData['default_value']) ? $aData['default_value'] : '') ).',
                                      req = '.$aData['req'].', txt = '.$aData['txt'].', in_table = '.$aData['in_table'].',
                                      is_search = '.$aData['is_search'].',
                                      extra = '.$this->db->str2sql( serialize($aExtra) ).'
                                      '.$sqlUpdate.'
                                  WHERE id = '.$nDynpropID);               

            if($res !== false)
            {                
                if($isMulti) 
                {
                    $aMultiAdded = ( !empty($aData['multi_added']) ? explode(',', $aData['multi_added']) : array() );
                    $aMultiAdded = array_map('intval', $aMultiAdded);
                    
                    //обновляем варианты выбора дин. св-ва
                    $i = 1;
                    foreach($aData['multi'] as $key => $value)
                    {
                        if(!in_array($key, $aMultiAdded)) {
                            $this->db->execute('UPDATE ' . $this->tblMulti.' 
                                       SET name = '.$this->db->str2sql($value).',
                                           num =  '.($i++).'
                                       WHERE dynprop_id = '.$nDynpropID.' AND value = '.$key);
                        } else {
                            $title = trim($value);
                            if($title!='') {
                                $aData['multi'][$key] = array(
                                    'title'=>$title,
                                    'num'=>$i++
                                );
                            } else {
                                unset($aData['multi'][$key]);
                            }
                        }
                    }
                    
                    //получаем максимальное значение перед удалением, дабы добавляемые получили еще неиспользуемые значения
                    $maxValue = $this->db->one_data('SELECT MAX(value) as value FROM '.$this->tblMulti.' WHERE dynprop_id = '.$nDynpropID);
                    
                    if(!empty($aData['multi_deleted']))
                    {
                        $aData['multi_deleted'] = join(',', array_map('intval', explode(',', $aData['multi_deleted'])) );
                        //удаляем прикрепленные свойства
                        $aChildDynpropID = $this->db->select_one_column('SELECT id FROM '.$this->tblDynprops.' 
                                       WHERE parent_id = '.$nDynpropID.' AND parent_value IN('.$aData['multi_deleted'].')');
                        if(!empty($aChildDynpropID)) {
                            $this->db->execute('DELETE FROM '.$this->tblMulti.' 
                                           WHERE '.$this->db->prepareIN('dynprop_id', $aChildDynpropID));
                            $this->db->execute('DELETE FROM '.$this->tblDynprops.' 
                                           WHERE '.$this->db->prepareIN('id', $aChildDynpropID));
                        }
                        $this->db->execute('DELETE FROM '.$this->tblMulti.' 
                                       WHERE dynprop_id = '.$nDynpropID.' AND value IN('.$aData['multi_deleted'].')');
                    }
                    
                    if(!empty($aMultiAdded)) 
                    {
                        $i = $maxValue;
                        $b = $i;                       
                        $aInsert = array(); 
                        foreach($aData['multi'] as $key => $v)
                        {
                            if(in_array($key, $aMultiAdded)) 
                            {
                                $nValue = ($isBits ? ($b*=2) : ++$i);
                                $aInsert[] = '('.$nDynpropID.', '.$this->db->str2sql(trim($v['title'])).', '.$nValue.', '.$v['num'].')';
                            }
                        }
                        if(!empty($aInsert)) {
                            $this->db->execute('INSERT INTO '.$this->tblMulti.'
                                            (dynprop_id, name, value, num) VALUES '.join(',', $aInsert));
                        }
                    }
                }                
                return true; 
            }
        } else {
            if($isMulti) {                                                     
                $aMulti = $aData['multi']; $aData['multi'] = array();
                foreach($aMulti as $key => $value) {
                    $aData['multi'][] = array('name' => $value, 'value' => $key);
                }
            }
            return false;
        }
    }
    
    /**
    * Копирование свойства
    * @param integer id свойства
    * @param integer id владельца
    */
    public function copy($nDynpropID, $nOwnerID) //...
    {
        $aDynprop = $this->db->one_array('SELECT * FROM '.$this->tblDynprops.' WHERE id = '.$nDynpropID);
        if(empty($aDynprop)) return false; 

        $aParentPropData = false;
        if(!empty($aDynprop['parent'])) {
            $aParentPropData = array('id'=>$aDynprop['parent'], 'value'=>$aDynprop['parent_value']);
        }
        
        $type = $aDynprop['type'];           
        unset($aDynprop['id']);
        $aDynprop[$this->ownerColumn] = $nOwnerID;
        
        $datafieldCreated = false;
        
        if(!$this->isInheritParticular() || !empty($aParentPropData))
        {   /** формируем data_field и num сразу, либо при связывании (@see linkIn) */
            $nDataField = $this->getDataField($nOwnerID, $type, $aParentPropData);
            if($nDataField===false) {
                return false;
            }  
            $nNum = $this->getNum($nOwnerID);
            
            $aDynprop['data_field'] = $nDataField;
            $aDynprop['num'] = $nNum;
            $datafieldCreated = true;  
        }       
                
        //сохраняем свойство
        $this->db->prepareInsertQuery($sInsertFields, $sInsertValues, $aDynprop);
        $this->db->execute('INSERT INTO '.$this->tblDynprops.' ('.$sInsertFields.') VALUES('.$sInsertValues.')');
        $nDynpropNewID = $this->db->insert_id($this->tblDynprops, 'id');
        
        if($nDynpropNewID > 0) 
        {
            //копируем значения свойства с множественным выбором
            if($this->isMulti($type))
            {
                $aMulti = $this->db->select('SELECT * FROM '.$this->tblMulti.' WHERE dynprop_id = '.$nDynpropID);
                if(!empty($aMulti)) {
                    $aInsert = array();
                    foreach($aMulti as $v) {
                        $aInsert[] = array(
                            'dynprop_id'=> $nDynpropNewID,
                            'name'      => $v['name'],
                            'value'     => $v['value'],
                            'num'       => $v['num'],
                        );
                    }                        
                    $this->db->multiInsert($this->tblMulti, $aInsert);
                }
            }
            
            //связываем свойство с владельцем
            if($this->isInheritParticular()) {
                return $this->linkIN($nOwnerID, $nDynpropNewID, $type, !$datafieldCreated);
            } else {
                return true;
            }
        }
    }
    
    /**
    * Удаление свойства
    * @param integer id свойства
    * @param integer id владельца
    * @param boolean удалять только связь потомков владельца со свойством
    */
    public function del($nDynpropID, $nOwnerID, $bOnlyIn = false) //ok
    {
        if($this->isInheritParticular() && $bOnlyIn) {    
            //при наследовании, удаляем только связь со всеми потомками владельца
            $aOwnersID = ($this->inherit ? $this->getOwnerChildrensID($nOwnerID) : array());
            $aOwnersID[] = $nOwnerID;
            return $this->db->execute('DELETE FROM '.$this->tblIn.' WHERE dynprop_id = '.$nDynpropID.' AND '.$this->db->prepareIN($this->ownerColumn, $aOwnersID));                
        } else {
            $aDynpropData = $this->db->one_array('SELECT * FROM '.$this->tblDynprops.' WHERE id = '.$nDynpropID.' AND '.$this->ownerColumn.' = '.$nOwnerID);
            if(empty($aDynpropData)) return false;
            
            $res = $this->db->execute('DELETE FROM '.$this->tblDynprops.' WHERE id = '.$nDynpropID.' AND '.$this->ownerColumn.' = '.$nOwnerID);
            if(!empty($res)) {
                $multi = $this->isMulti( $aDynpropData['type'] );
                if(!empty($aDynpropData['parent'])) {                                                      
                    //удаляем прикрепленные свойства
                    $aChildDynpropID = $this->db->select_one_column('SELECT id FROM '.$this->tblDynprops.' WHERE parent_id = '.$nDynpropID);
                    if(!empty($aChildDynpropID)) {
                        $this->db->execute('DELETE FROM '.$this->tblMulti.' WHERE '.$this->db->prepareIN('dynprop_id', $aChildDynpropID));
                        $this->db->execute('DELETE FROM '.$this->tblDynprops.' WHERE '.$this->db->prepareIN('id', $aChildDynpropID));
                    }                    
                }
                
                if($multi) 
                    $this->db->execute('DELETE FROM '.$this->tblMulti.' WHERE dynprop_id = '.$nDynpropID);
                
                if($this->isInheritParticular()) {     
                    // удаляем связь со всеми владельцами(реальными и виртуальными)
                    return $this->db->execute('DELETE FROM '.$this->tblIn.' WHERE dynprop_id = '.$nDynpropID);
                }
            }
            return $res;  
        }
    }
    
    /**
    * Удаление всех свойств связанных с владельцем
    * @param integer id владельца
    * @param boolean удалять только связь потомков владельца со свойством
    * @return boolean 
    */
    public function delAll($nOwnerID, $bOnlyIn = false) //ok
    {                                   
        $aOwnerDynpropsID = $this->db->select_one_column('SELECT id FROM '.$this->tblDynprops.' WHERE '.$this->ownerColumn.' = '.$nOwnerID);
        foreach($aOwnerDynpropsID as $dynpropID) {
            $this->del($dynpropID, $nOwnerID, $bOnlyIn);
        }
        
        return true;
    }
    
    /**
    * Связывание свойства с владельцем
    * @param integer id владельца
    * @param integer id свойства
    * @param integer тип свойства
    * @param boolean генерировать ли data_field
    * @param boolean 
    */
    public function linkIN($nOwnerID, $nDynpropID, $nType = false, $bGenerateDataField = true)
    {
        if(!$this->isInheritParticular())
            return false;
        
        if($bGenerateDataField)
        {
            if($nType === false) {
                $nType = $this->db->one_data('SELECT type FROM '.$this->tblDynprops.' WHERE id = '.$nDynpropID);
                if(!$nType) return false;
            }

            $nDataField = $this->getDataField($nOwnerID, $nType);  
            if($nDataField===false) {
                return false;
            }
        } else {
            $nDataField = (int)$this->db->one_data('SELECT data_field FROM '.$this->tblIn.' WHERE dynprop_id = '.$nDynpropID);
            if(empty($nDataField)) {
                return false;
            }            
        }
        
        return $this->db->execute('INSERT INTO '.$this->tblIn.' (dynprop_id, '.$this->ownerColumn.', data_field, num) 
                VALUES('.$nDynpropID.', '.$nOwnerID.', '.$nDataField.', '.$this->getNum($nOwnerID).')
            ');
    }
    
    /**
    * Подготовка запроса сохранения значений свойств по ID владельцев
    * @param mixed id владельца
    * @param array значения свойств: array(id владельца=>значения, ...)
    * @param array параметры свойств: без группировки по id владельца
    * @param string тип запроса: 'insert', 'update'
    * @return array @see prepareSaveDataByOwner
    */
    public function prepareSaveDataByOwner($mOwnerID, $aDynpropsData, $aDynprops, $sQueryType = 'insert'/*update*/)
    {   
        if(!empty($mOwnerID) && !empty($aDynpropsData) && !empty($aDynprops))
        {
            if(!is_array($mOwnerID)) {
                $mOwnerID = array($mOwnerID);
            }
            
            $aDynprops = func::array_transparent($aDynprops, $this->ownerColumn);
            
            $aResult = array();
            foreach($mOwnerID as $ownerID){
                if($ownerID>0) {
                    $aResult[$ownerID] = $this->prepareSaveData( (isset($aDynpropsData[$ownerID]) ? $aDynpropsData[$ownerID] : array()), 
                                                                 (isset($aDynprops[$ownerID]) ? $aDynprops[$ownerID] : array()),
                                                                 $sQueryType );
                }
            }       
                        
            return $aResult;
        }
        return array();
    }

    /**
    * Подготовка запроса сохранения значений свойств по их ID
    * @param mixed id свойств
    * @param array значения свойств: array(id свойства=>значение, ...)
    * @param array параметры свойств
    * @param string тип запроса: 'insert', 'update'
    * @return array @see prepareSaveData
    */
    public function prepareSaveDataByID($aDynpropsData, $aDynprops, $sQueryType = 'insert'/*update*/, $sKey = 'id')
    {   
        if(!empty($aDynpropsData) && !empty($aDynprops))
        {
            return $this->prepareSaveData( (!empty($aDynpropsData) ? $aDynpropsData : array()), 
                                           (!empty($aDynprops) ? $aDynprops : array()),
                                           $sQueryType, $sKey);
        }
        return array();
    }
    
    /**
    * Подготовка запроса поиска сущностей по значениям свойств
    * @param array значения свойств: array(id | data_field свойства=>значение, ...)
    * @param array параметры свойств
    * @param string тип ключа: 'id', 'data_field'
    * @return array @see prepareSaveData
    */
    public function prepareSearchQuery($aData, $aDataChildren = false, $aDynprops, $sTablePrefix, $sKey = 'data_field')
    {                                            
        //echo 'input: <pre>', print_r($aData, true), '</pre>     ';
        //echo '<pre>', print_r($aDynprops, true), '</pre>'; exit;
        if(!empty($aDynprops) && !empty($aData))
        {
            $sqlResult = array();

            if($this->checkChildren()) {   
                $aDynpropsChildren = $this->getChildrenByParents($aDynprops, $aData, true, false, false);
            }  
            
            foreach($aDynprops as $key=>$d) 
            {
                $type = &$d['type'];
                $val = (isset($aData[$d[$sKey]]) ? $aData[$d[$sKey]] : false);
                if($val===false) continue;
                
                $field = $sTablePrefix.$this->datafield_prefix.$d['data_field'];

                switch($d['type'])
                {
                    case self::typeSelect:
                    case self::typeRadioGroup:  
                    {
                        $this->input->clean($val, TYPE_ARRAY_UINT);
                        if(empty($val) || empty($d['multi'])) continue;

                        $sql = array();
                        
                        if($d['parent']) {       
                            foreach($val as $pv) {
                                if(!empty($aDynpropsChildren[$d['id']][$pv])){
                                    $dc = &$aDynpropsChildren[$d['id']][$pv];
                                    if(!empty($aDataChildren[$dc['data_field']][$dc['id']])) {
                                        $cval = &$aDataChildren[$dc['data_field']][$dc['id']];
                                        $this->input->clean($cval, TYPE_ARRAY_UINT);
                                        if(!empty($cval)) {             
                                            $sql[] = "($field = {$pv} AND ".($sTablePrefix.$this->datafield_prefix.$dc['data_field'])." IN (".join(',', $cval)."))";
                                        }
                                    } else {
                                        $sql[] = "($field = {$pv})";
                                    }
                                } else {
                                    $sql[] = "($field = {$pv})";
                                }
                            }
                        } else {
                            $sql[] = $field.(sizeof($val)>1 ? ' IN ('.join(',', $val).')' : ' = '.current($val));
                        }
                        if(!empty($sql)) {
                            $sqlResult[] = ( sizeof($sql) == 1 ? current($sql) : '(('.join(') OR (', $sql).'))');
                        }
                    } break;
                    case self::typeCheckboxGroup:
                    case self::typeSelectMulti:
                    {
                        $this->input->clean($val, TYPE_ARRAY_UINT);
                        if(empty($val) || empty($d['multi'])) continue;

                        $sqlResult[] = '('.$field.' & '.intval(array_sum($val)).')';                         
                    } break;
                    case self::typeNumber:
                    case self::typeRange:    
                    {
                        $sql = array();     
                        $this->input->clean($val, TYPE_ARRAY_NUM);
                        if($d['search_range_user']) {       
                            if(!empty($val['f']) || !empty($val['t'])) {
                                $sql[] = ( !empty($val['f'])?" $field >= ".$val['f'].(!empty($val['t'])?" AND $field <= ".$val['t']:''):"$field <= ".$val['t'] );
                                unset($val['f'], $val['t']);
                            }
                        }
                        if(!empty($d['search_ranges']) && !empty($val)) 
                        {
                            foreach($d['search_ranges'] as $k=>$r) {
                                if(isset($val[$k])) {
                                    $sql[] = ($r['from']?" $field >= ".$r['from'].($r['to']?" AND $field <= ".$r['to']:''):"$field <= ".$r['to']);
                                }
                            }    
                        }                   
                        if(!empty($sql)) {                                  
                            $sqlResult[] = ( sizeof($sql) == 1 ? current($sql) : '(('.join(') OR (', $sql).'))');
                        }
                    } break;       
                    case self::typeCheckbox:
                    {
                        $sqlResult[] = $field.' = 1';
                    } break;
                    case self::typeRadioYesNo:
                    {      
                        if(isset($val[1]) || isset($val[2])) {
                            if(sizeof($val)==2)
                                $sqlResult[] = $field.' IN (1,2)';
                            else {
                                $sqlResult[] = $field.' = '.(isset($val[1])?1:2);
                            }
                        }
                    } break;  
                }
            }  
            return (!empty($sqlResult) ? join(' AND ', $sqlResult) : '' );
        }
        return '';
    }
    
    /**
    * Подготовка запроса сохранения значений свойств
    * @param array значения свойств
    * @param array параметры свойств
    * @param string тип запроса: 'insert', 'update'
    * @return array insert: (id владельца=>array('fields'=>string, values=>string), ...), update: (id владельца=>string, ...)
    */    
    protected function prepareSaveData( $aDynpropsData, $aDynprops, $sQueryType, $sKey = 'id')
    {
        $isUpdateQuery = ($sQueryType == 'update');
        $sqlFields = ''; $sqlValues = '';

        foreach($aDynprops as $key=>$d) 
        {
            $type = &$d['type'];
            $value = (isset($aDynpropsData[$d[$sKey]]) ? $aDynpropsData[$d[$sKey]] : false);
            $fieldName = $this->datafield_prefix.$d['data_field'];
                    
            $sqlFields .= ", $fieldName";
            
            $sql = ($isUpdateQuery ? ", $fieldName = " : ', ');
            
            if($this->isStoringBits($type))
            {
                $sqlValues .= $sql.(!$value?0:array_sum($value));
            }
            else if($this->isMulti($type))
            {
                if($value) {
                    foreach($d['multi'] as $key2 => $value2)
                    {                                
                        if($value2['value'] == $value) {
                            $sqlValues .= $sql.$value2['value'];
                            break;
                        }
                    }
                } else {
                    $sqlValues .= $sql.'0';
                }
            }
            else if($type == self::typeRange)
            {
                if($value)
                {
                    if(!is_numeric($value))
                    {
                        $this->errors->set('invalid_number_field_content', null, false, $d['title']);
                        break;  
                    }                                                       
                    if($value < $d['start'] || $value > $d['end'])
                    {                                                                                                        
                        $this->errors->set('number_field_out_of_range', null, false, $d['title']);
                    }
                }                           
                $sqlValues .= $sql.$this->db->str2sql((is_array($value) ? intval(array_sum($value)) : strip_tags($value)));
            }
            else
            {
                $sqlValues .= $sql. $this->db->str2sql((is_array($value) ? intval(array_sum($value)) : strip_tags($value)));
            } 
        }
        
        return ($isUpdateQuery ? $sqlValues : array('fields'=> $sqlFields, 'values'=>$sqlValues) );
    }
     
    /**
    * Формирование номера ячейки для хранения значения свойства
    * @param integer id владельца 
    * @param integer тип свойства
    * @param array инорфмация о парент-свойстве (id=>id парента, value=>value парента)
    * @return integer 
    */
    protected function getDataField($nOwnerID, $nType, $aParentPropData = false)
    {
        $nDataField = false;

        $isText  = $this->isStoringText($nType);
        $sqlType = ' AND D.type '.($isText ? '' : 'NOT').' IN('.join(',', $this->getTextTypes()).')';
        
        if(!empty($aParentPropData))
        {
            $sQueryLast = '';    

            //пытаемся получить data_field уже ранее прикрепляемого свойства
            if($this->isInheritParticular()) {
                $sQueryLast = 'SELECT DI.data_field 
                            FROM '.$this->tblDynprops.' D, '.$this->tblIn.' DI 
                            WHERE D.parent_id='.$aParentPropData['id'].' AND D.id = DI.dynprop_id
                            ';
            } else {               
                $sQueryLast = 'SELECT D.data_field 
                    FROM '.$this->tblDynprops.' D
                    WHERE D.parent_id='.$aParentPropData['id'];
            }
            
            if( count($this->typesAllowedChild)!==1 ) {
                $sQueryLast .= $sqlType;    
            }
            $nLastID = $this->db->one_data($sQueryLast);
            if(!empty($nLastID)) {
                return $nLastID; 
            }
        }
        
        if($this->inherit) {   
            if ($this->isOwnerTableNestedSets())
            {
                $aOwnerNodeInfo = $this->db->one_array('SELECT O.numleft, O.numright FROM '.$this->ownerTable.' O WHERE O.id = '.$nOwnerID);
                if(!empty($aOwnerNodeInfo)) {
                    //получаем всех парентов и детей 
                    $aOwnersID = $this->db->select_one_column('SELECT O.id FROM '.$this->ownerTable.' O
                                                    WHERE (O.numleft < '.$aOwnerNodeInfo['numleft'].' AND O.numright > '.$aOwnerNodeInfo['numright'].') OR
                                                          (O.numleft > '.$aOwnerNodeInfo['numleft'].' AND O.numright < '.$aOwnerNodeInfo['numright'].') ');
                }
                if(empty($aOwnersID)) $aOwnersID = array();
                $aOwnersID[] = $nOwnerID;
                
                if($this->isInheritParticular()) {
                    $sQuery = ' SELECT DN.data_field
                                FROM '.$this->tblDynprops.' D,
                                     '.$this->tblIn.' DN
                                WHERE DN.'.$this->ownerColumn.' IN ('.join(',', $aOwnersID).') AND DN.dynprop_id = D.id '.$sqlType.'
                                GROUP BY DN.data_field 
                                ORDER BY DN.data_field ASC';   
                } else {
                    $sQuery = ' SELECT D.data_field
                                FROM '.$this->tblDynprops.' D
                                WHERE D.'.$this->ownerColumn.' IN ('.join(',', $aOwnersID).') '.$sqlType.'
                                GROUP BY D.data_field 
                                ORDER BY D.data_field ASC';                           
                }
                
            } 
            else //adjacency list (id - pid)
            {   
                    //получаем всех парентов и детей
                    $deep = intval($this->ownerTableType);
                    $aParentID = $this->db->getAdjacencyListParentsID($this->ownerTable, $nOwnerID, $deep);
                    $aChildrenID = $this->db->getAdjacencyListChildrensID($this->ownerTable, $nOwnerID, $deep);
                    $aOwnersID = array_merge($aParentID, $aChildrenID);
                    $aOwnersID[] = $nOwnerID;
                    
                    if($this->isInheritParticular()) {
                        $sQuery = ' SELECT DN.data_field
                                    FROM '.$this->tblDynprops.' D,
                                         '.$this->tblIn.' DN
                                    WHERE DN.'.$this->ownerColumn.' IN ('.join(',', $aOwnersID).') AND DN.dynprop_id = D.id '.$sqlType.'                                      
                                    GROUP BY DN.data_field 
                                    ORDER BY DN.data_field ASC';   
                    } else {
                        $sQuery = ' SELECT D.data_field
                                    FROM '.$this->tblDynprops.' D
                                    WHERE D.'.$this->ownerColumn.' IN ('.join(',', $aOwnersID).') '.$sqlType.'
                                    GROUP BY D.data_field 
                                    ORDER BY D.data_field ASC'; 
                    }
            }
        } else {
            $sQuery = ' SELECT D.data_field
                        FROM '.$this->tblDynprops.' D
                        WHERE D.'.$this->ownerColumn.' = '.$nOwnerID.' '.$sqlType.'
                        ORDER BY D.data_field ASC';            
        }

        $aCurDataFields = $this->db->select_one_column($sQuery);
        $aCurDataFields = array_unique($aCurDataFields);

        if($isText)
        {
            if(!$aCurDataFields)
            {
                $nDataField = $this->datafield_text_first;
            }
            else
            {
                $i = $this->datafield_text_first;

                foreach($aCurDataFields as $value)
                {
                    if($value == $i)
                    {
                        $i++;
                        continue;
                    }
                    else
                    {
                        $nDataField = $i;
                        break;
                    }
                }
                
                if(!$nDataField)
                    $nDataField = intval(max($aCurDataFields) + 1);
                

                $num = $this->datafield_text_first - 1;
                $num += sizeof($aCurDataFields) + 1;

                if($num > $this->datafield_text_last) {
                    $this->errors->set('to_much_text_fields');
                    return false;
                }
            }
        }
        else
        {                 
            if(!$aCurDataFields)
            {
                $nDataField = $this->datafield_int_first;
            }
            else
            {
                $i = $this->datafield_int_first;

                foreach($aCurDataFields as $value)
                {
                    if($value == $i)
                    {
                        $i++;
                        continue;
                    }
                    else
                    {
                        $nDataField = $i;
                        break;
                    }
                }
                
                if(!$nDataField)
                    $nDataField = max($aCurDataFields) + 1;
                
                $num = sizeof($aCurDataFields) + 1;

                if($num > $this->datafield_int_last) {
                    $this->errors->set('to_much_int_fields');
                    return false;
                }
            }
        }

        return $nDataField;
    }
    
    protected function getOwnerParentsID($nOwnerID, $aOwnerNodeInfo = false) 
    {
        if($this->isOwnerTableNestedSets())
        {
            if(empty($aOwnerNodeInfo)) {
                $aOwnerNodeInfo = $this->db->one_array('SELECT O.numleft, O.numright FROM '.$this->ownerTable.' O WHERE O.id = '.$nOwnerID);
            }
            if(!empty($aOwnerNodeInfo)) {
                return $this->db->select_one_column('SELECT O.id FROM '.$this->ownerTable.' O
                                                WHERE O.numleft < '.$aOwnerNodeInfo['numleft'].' AND O.numright > '.$aOwnerNodeInfo['numright']);
            }
            return ( !empty($aOwnersID) ? $nOwnerID : array() );
        }
        else //adjacency list (id - pid)
        {   
            $deep = intval($this->ownerTableType);
            return $this->db->getAdjacencyListParentsID($this->ownerTable, $nOwnerID, $deep);  
            
        } 
    }
    
    protected function getOwnerChildrensID($nOwnerID, $aOwnerNodeInfo = false) 
    {
        if($this->isOwnerTableNestedSets())
        {   
            if(empty($aOwnerNodeInfo)) {
                $aOwnerNodeInfo = $this->db->one_array('SELECT O.numleft, O.numright FROM '.$this->ownerTable.' O WHERE O.id = '.$nOwnerID);
            }
            
            if(!empty($aOwnerNodeInfo)) {
                return $this->db->select_one_column('SELECT O.id FROM '.$this->ownerTable.' O
                                                WHERE O.numleft > '.$aOwnerNodeInfo['numleft'].' AND O.numright < '.$aOwnerNodeInfo['numright']);
            }
            return ( !empty($aOwnersID) ? $nOwnerID : array() );            
        } 
        else //adjacency list (id - pid)
        {
            return $this->db->getAdjacencyListChildrensID($this->ownerTable, $nOwnerID, intval($this->ownerTableType) );  
        }
    }
    
    /**
    * Получает порядковый номер свойства
    * @param integer id владельца      
    * @return integer
    */   
    protected function getNum($nOwnerID)
    {
        if($this->isInheritParticular()) {
            $nNum = (int)$this->db->one_data('SELECT MAX(num) FROM '.$this->tblIn.' WHERE '.$this->ownerColumn.' = '.$nOwnerID);
        } else {
            $nNum = (int)$this->db->one_data('SELECT MAX(num) FROM '.$this->tblDynprops.' WHERE '.$this->ownerColumn.' = '.$nOwnerID);
        }
        return ($nNum+1);
    }
    
    /**
    * Возвращает название свойства
    * @param integer тип свойства
    * @return string
    */   
    static public function getTypeTitle($nType)
    {
        switch($nType)
        {
            case self::typeInputText:      return 'Однострочное текстовое поле'; break;
            case self::typeTextarea:       return 'Многострочное текстовое поле'; break;
            case self::typeWysiwyg:        return 'Текстовый редактор'; break;
            case self::typeRadioYesNo:     return 'Выбор Да/Нет'; break;
            case self::typeCheckbox:       return 'Флаг'; break;
            case self::typeSelect:         return 'Выпадающий список'; break;
            case self::typeSelectMulti:    return 'Список с мультивыбором (ctrl)'; break;
            case self::typeRadioGroup:     return 'Группа св-в с единичным выбором'; break;
            case self::typeCheckboxGroup:  return 'Группа св-в с множественным выбором'; break;
            case self::typeRange:          return 'Диапазон'; break;
            case self::typeNumber:         return 'Число'; break;
        }
        return '';
    }

    /** 
     * Возвращает массив чисел из которых была составлена сумма битов
     * @param int $nSum: сумма битов
     * @return array
     */
    public function bit2source($nSum)
    {
        $str = strrev(decbin($nSum));
        $nLength = strlen($str);
        $arr = array();            
        for($i = 0; $i < $nLength; $i++)
        {
            if($str[$i]) {
                $arr[] = pow(2, $i);
            }
        }
        return $arr;
    }
    
    public function isStoringBits($nType)
    {
        //типы дин.свойств значения которых хранятся при помощи битов
        return in_array($nType, array(self::typeCheckboxGroup, self::typeSelectMulti) );
    }

    protected function isStoringText($nType)
    {
        //типы дин.свойств значения которых хранятся в поле TEXT
        return in_array($nType, $this->getTextTypes() );
    }
    
    protected function isMulti($nType)
    {
        //типы дин.свойств значения которых хранятся в таблице _multi
        return in_array($nType, array(self::typeCheckboxGroup, self::typeSelectMulti, self::typeRadioGroup, self::typeSelect) );
    }

    protected function isParent($nType)
    {
        //типы дин.свойств c возможностью прикрепления
        return in_array($nType, array( self::typeCheckbox, self::typeSelect, self::typeRadioGroup, self::typeCountry, self::typeState ) );
    }
    
    protected function checkChildren() 
    {
        return !empty($this->typesAllowedParent);
    }
    
    protected function hasExtra($nType)
    {                  
        return in_array($nType, array( self::typeNumber, self::typeRange ));
    }
    
    protected function getTextTypes()
    {
        return array( self::typeInputText, self::typeTextarea, self::typeWysiwyg );
    }
    
    protected function isOwnerTableNestedSets()
    {
        static $ns;
        return ( $ns!==null ? $ns : ( $ns = ($this->ownerTableType == 'ns') ) );
    } 
    
    public function isInheritParticular()
    {
        return $this->inherit === 2;
    }  
    
}
