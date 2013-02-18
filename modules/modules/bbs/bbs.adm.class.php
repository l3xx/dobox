<?php

class BBS extends BBSBase
{            
    function init()
    {
        parent::init();
        $this->tplAssignByRef('bbs', $this); 
    }
    
    //-------------------------------------------------------------------------------------------------------------------------------
    // объявления    

    function items_listing()
    {
        if( !$this->haveAccessTo('items-listing') )
            return $this->showAccessDenied();
 
        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'press':
                {
                    if(!$this->haveAccessTo('items-press'))
                        $this->ajaxResponse(Errors::ACCESSDENIED);
                        
                    $nItemID = $this->input->get('rec', TYPE_UINT);
                    
                    $aData = $this->db->one_array('SELECT I.id, I.press, U.login as email 
                                                   FROM '.TABLE_BBS_ITEMS.' I
                                                        LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id
                                                   WHERE I.id = '.$nItemID);
                    if(empty($aData) || $aData['press']!=BBS_PRESS_PAYED) $this->ajaxResponse(Errors::IMPOSSIBLE);

                    $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET press = '.BBS_PRESS_PUBLICATED.' WHERE id = '.$nItemID);
                    if($res && !empty($aData['email'])) {
                        bff::sendMailTemplate(array('item_url'=>SITEURL.'/item/'.$nItemID, 'email'=>$aData['email']), 
                            'member_bbs_press_publicated', $aData['email']);
                        $this->ajaxResponse(Errors::SUCCESSFULL);
                    }
                } break;
                case 'delete':
                {
                    if(!$this->haveAccessTo('items-edit'))
                        $this->ajaxResponse(Errors::ACCESSDENIED);
                        
                    $nItemID = $this->input->id('rec', 'p');
                    if($nItemID) {
                        
                        $aItemData = $this->db->one_array('SELECT user_id FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID);
                        if(empty($aItemData)) break;
                        
                        $res = $this->itemDelete($nItemID, $aItemData['user_id']);
                        $this->ajaxResponse( ($res ? Errors::SUCCESSFULL : Errors::IMPOSSIBLE) );
                    }
                } break; 
            }
            $this->ajaxResponse( Errors::IMPOSSIBLE );
        } 

        $this->input->getm(array(
                'search'  => TYPE_STR,
                'cat_id'  => TYPE_UINT,
                'svc'     => TYPE_UINT,
                'page'    => TYPE_UINT,
                'perpage' => TYPE_UINT, 
                'uid'     => TYPE_UINT,
                'mod'     => TYPE_UINT, 
                'press'   => TYPE_UINT,
            ), $aData);
        
        $sqlWhere = array();
        if(!empty($aData['search'])) 
        {
            if(intval($aData['search'])>0) {
                $sqlWhere[] = 'I.id = '.intval($aData['search']);
            } else {
                $sqlWhere[] = 'I.descr LIKE '.$this->db->str2sql('%'.$aData['search'].'%');
            }
        }

        if(!$this->security->isSuperAdmin())
        {
            $aCatsAllowed = $this->security->getAllowedBBSCategories();
            if(empty($aCatsAllowed)) {
                $sqlWhere[] = 'I.cat1_id = -1';
            } else {
                $sqlWhere[] = 'I.cat1_id IN ('.join(',', $aCatsAllowed).')';
            }
        }
        
        if($aData['cat_id']>0 && (!isset($aCatsAllowed) || in_array($aData['cat_id'], $aCatsAllowed)) ) {
            $sqlWhere[] = '(I.cat_id = '.$aData['cat_id'].' OR I.cat1_id = '.$aData['cat_id'].' OR I.cat2_id = '.$aData['cat_id'].')';
        }

        if($aData['mod'])
        {
            $sqlWhere[] = 'I.moderated = 0 AND I.status!='.BBS_STATUS_PUBLICATED_OUT;
        } elseif($aData['press']) {
            $sqlWhere[] = 'I.press > 0';
        } else {
            $sqlWhere[] = 'I.status IN ('.BBS_STATUS_PUBLICATED.','.BBS_STATUS_PUBLICATED_OUT.')';
        }

        if($aData['svc']>0) {
            $sqlWhere[] = 'I.svc = '.$aData['svc'];
        }
        
        if($aData['uid']>0) {
            $aData['uinfo'] = $this->db->one_array('SELECT name, email FROM '.TABLE_USERS.' WHERE user_id = '.$aData['uid']);
            if(!empty($aData['uinfo'])) {
                $sqlWhere[] = 'I.user_id = '.$aData['uid'];
            }
        }        

        $sqlWhere = (!empty($sqlWhere) ? 'WHERE '.join(' AND ', $sqlWhere) : '');
           
        $nCount = $this->db->one_data('SELECT COUNT(I.id) FROM '.TABLE_BBS_ITEMS.' I '.$sqlWhere);
        $this->prepareOrder($orderBy, $orderDirection, 'I.created,desc', array('I.created'));
                                                                            
        $aPerpage = $this->preparePerpage($aData['perpage'], array(20,40,60) );   
        
        $aData['order'] = "$orderBy,$orderDirection";
        $sFilter = http_build_query($aData); unset($aData['page']);
        $this->generatePagenation($nCount, $aData['perpage'], "index.php?s={$this->module_name}&ev=items_listing&$sFilter&{pageId}", $sqlLimit);
        $aData['f'] = $sFilter;
        
        $aData['items'] = $this->db->select('SELECT I.id, I.status, I.press, I.status_prev, I.user_id, I.descr, I.price, CL.id as claims
                    FROM '.TABLE_BBS_ITEMS.' I
                        LEFT JOIN '.TABLE_BBS_ITEMS_CLAIMS.' CL ON I.id = CL.item_id
                    '.$sqlWhere."
                    GROUP BY I.id
                    ORDER BY $orderBy $orderDirection $sqlLimit");
                   
        $aData['cats'] = $this->getCategoriesOptions($aData['cat_id'], 'все категории', 0, (isset($aCatsAllowed) ? $aCatsAllowed : false));
        $aData['svcs'] = bff::i()->GetModule('Services')->getItemsSvcFilterOptions($aData['svc'], 'все услуги');
        $aData['perpage'] = $aPerpage;
        $this->tplAssign('curr_sign', $this->items_currency['sign']);
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.items.listing.tpl');
    }
    
    function items_modlisting()
    {
        if( !$this->haveAccessTo('items-modlisting') )
            return $this->showAccessDenied();

        $_GET['mod'] = $_POST['mod'] = 1;
        $this->adminIsListing();
        return $this->items_listing();
    }
    
    function items_presslisting()
    {
        if( !$this->haveAccessTo('items-press') )
            return $this->showAccessDenied();
                                              
        $_GET['press'] = $_POST['press'] = 1;
        $this->adminIsListing();
        return $this->items_listing();
    }
    
    function items_add()
    {
        if( !$this->haveAccessTo('items-edit') )
            return $this->showAccessDenied();

        $sFilter   = $this->input->get('f', TYPE_STR);// listing filter
        $sRedirect = 'items_listing&'.$sFilter; 
            
        $this->processItemData($aData, 0);
        
        if(bff::$isPost)
        {
            if($this->errors->no())
            {
                $this->db->prepareInsertQuery($sqlFields, $sqlValues, $aData);                

                $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS.'
                            (created, modified'.(!empty($sqlFields) ? ','.$sqlFields : '').')
                            VALUES ('.$this->db->getNOW().', '.$this->db->getNOW().(!empty($sqlValues) ? ','.$sqlValues : '').')');
      
                $nItemID = $this->db->insert_id(TABLE_BBS_ITEMS, 'id');
                if($nItemID)
                {
                    if($this->items_images && !empty($_FILES))
                    {                                                           
                        $pImages = $this->initImages();
                        $sImages = $pImages->uploadImages($nItemID);
                        $pImages->setImages($nItemID, $sImages);
                    }
                    
                    $this->adminRedirect(Errors::SUCCESSFULL, $sRedirect);
                }
            }
            $aData = $_POST;
        }
        
        $aData['cat_id_options'] = $this->getCategoriesOptions( $aData['cat_id'], ($aData['cat_id']!=0?false:'не указана'), 2);
        if(empty($aData['cat_id_options']))
            $this->errors->set('create_category_first');
        
        $aData = array_merge($aData, array('edit'=>false, 'id'=>0));

        $this->items_images_limit = config::get('bbs_images_limit');    
        
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.items.form.tpl');
    }
           
    function items_edit()
    {
        if( !$this->haveAccessTo('items-edit') )
            return $this->showAccessDenied();

        if(bff::$isAjax)
        {
            $this->input->postm(array(
                'id'         => TYPE_UINT, 
                'reg'        => TYPE_ARRAY_UINT,      
                'contacts'   => TYPE_ARRAY_STR, 
                'descr'      => TYPE_STR, 
                'info'       => TYPE_STR,
                'price'      => TYPE_NUM,
                'price_torg' => TYPE_BOOL,
                'price_bart' => TYPE_BOOL,
                'video'      => TYPE_STR, 
                'mkeywords'  => TYPE_STR,
                'mdescription' => TYPE_STR,
            ), $p);
            
            $this->input->clean_array($p['reg'], array(
                1=>TYPE_UINT,2=>TYPE_UINT,3=>TYPE_UINT
            ));
            
            $aDynpropsData = $this->input->post('dp', TYPE_ARRAY);
            if(!empty($aDynpropsData)) {    
                $dp = $this->initDynprops();
                $aDynpropsData = $dp->prepareSaveDataByID( $aDynpropsData, $dp->getByID( array_keys($aDynpropsData) ), 'update' );
            }

            $sRegionsTitle = '';
            if(!empty($p['reg'])) {
                $aRegions = $this->db->select('SELECT title, numlevel FROM '.TABLE_BBS_REGIONS.' WHERE id IN('.join(',', $p['reg']).') ORDER BY numlevel');
                if(!empty($aRegions)) {
                    $aRegions = func::array_transparent($aRegions, 'numlevel', true);
                    $nRegionStart = 1;
                    if(sizeof($aRegions)==1) $sRegionsTitle = $aRegions[$nRegionStart]['title'];
                    else { 
                        if(sizeof($aRegions)==3) $nRegionStart = 2;
                        $sRegionsTitle = $aRegions[$nRegionStart]['title'].($aRegions[$nRegionStart+1] ? ', '.$aRegions[$nRegionStart+1]['title'] : '');
                    }
                }
            } 
            
            if($this->errors->no())
            {   
                if(!empty($p['contacts']['site']) && $p['contacts']['site'] == 'http://') {
                    $p['contacts']['site'] = '';
                }

                $adtxtLimit = config::get('bbs_adtxt_limit');
                if(!empty($adtxtLimit)) {
                    $p['descr'] = mb_substr($p['descr'], 0, $adtxtLimit);
                }
                
                $sqlNOW = $this->db->getNOW(); 
                
                $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET
                    country_id = '.($p['reg'][1]).', region_id = '.($p['reg'][2]).', city_id = '.($p['reg'][3]).',  
                    price = '.$p['price'].', price_torg = '.$p['price_torg'].', price_bart = '.$p['price_bart'].',   
                    contacts_name = :c_name, contacts_email = :c_email, contacts_phone = :c_phone, contacts_skype = :c_skype, contacts_site = :c_site, video = :video, 
                    descr = :descr, descr_regions = :descr_regions, info = :info, mkeywords = :mkeywords, mdescription = :mdescription, modified = '.$sqlNOW.'
                    '.(!empty($aDynpropsData) ? $aDynpropsData : '').'
                    WHERE id = '.$p['id'].'              
                ', array(       
                    array(':c_name', (isset($p['contacts']['name']) ? $p['contacts']['name'] : ''), PDO::PARAM_STR),
                    array(':c_email', (isset($p['contacts']['email']) ? $p['contacts']['email'] : ''), PDO::PARAM_STR),
                    array(':c_phone', (isset($p['contacts']['phone']) ? $p['contacts']['phone'] : ''), PDO::PARAM_STR),
                    array(':c_skype', (isset($p['contacts']['skype']) ? $p['contacts']['skype'] : ''), PDO::PARAM_STR),
                    array(':c_site', (isset($p['contacts']['site']) ? $p['contacts']['site'] : ''), PDO::PARAM_STR),
                    array(':video', $p['video'], PDO::PARAM_STR),
                    array(':descr', $p['descr'], PDO::PARAM_STR), 
                    array(':descr_regions', $sRegionsTitle, PDO::PARAM_STR),
                    array(':info', $p['info'], PDO::PARAM_STR), 
                    array(':mkeywords', $p['mkeywords'], PDO::PARAM_STR),
                    array(':mdescription', $p['mdescription'], PDO::PARAM_STR),
                ));
                
                $this->ajaxResponse(array( 'res'=>($res === 1) ));  
            }
            
            $this->ajaxResponse(null);
                      
        }
            
        $sFilter   = $this->input->get('f', TYPE_STR);// listing filter
        $sRedirect = 'items_listing&'.$sFilter; 
        $sFilter   = rawurlencode($sFilter);
        
        $nRecordID = $this->input->id();
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE, $sRedirect);
        
        $dp = $this->initDynprops();  
        
        $aData = $this->db->one_array('SELECT I.*, C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                                CT.title as cat_type_title
                              FROM '.TABLE_BBS_ITEMS.' I
                                LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id,
                                '.TABLE_BBS_CATEGORIES.' C
                              WHERE I.id = '.$nRecordID.' AND I.cat_id = C.id');
            
        if(empty($aData))
            $this->adminRedirect(Errors::IMPOSSIBLE, $sRedirect);

        $aDynprops = $dp->form($aData['cat_id'], $aData, true, array(), 'dp', 'dynprops.form.edit.php', $this->module_dir_tpl);
        $aData['dp'] = (!empty($aDynprops['form']) ? $aDynprops['form'] : 0); unset($aDynprops);

        $aData['regions'] = $this->db->select('SELECT R.id, R.pid, R.title
                                       FROM '.TABLE_BBS_REGIONS.' R, '.TABLE_BBS_REGIONS.' R2
                                       WHERE R.numlevel IN(1,2) AND R.enabled = 1 AND (R.pid = 0 OR (R.pid = R2.id AND R2.enabled = 1)) 
                                       ORDER BY R.main DESC, R.num, R.title');
        $aData['regions'] = $this->db->transformRowsToTree($aData['regions'], 'id', 'pid', 'sub');
        
        if($aData['country_id'] && $aData['region_id'] && $aData['city_id']) {
            $aData['cities'] = $this->db->select('SELECT R.id, R.title
                                       FROM '.TABLE_BBS_REGIONS.' R 
                                       WHERE R.pid = '.$aData['region_id'].' AND R.enabled = 1 
                                       ORDER BY R.main DESC, R.num, R.title');
        }
        
        if($this->items_images) {    
            $aData['img'] = ( !empty($aData['img'])?explode(',', $aData['img']):array() );
            $this->items_images_limit = config::get('bbs_images_limit'.($aData['user_id']>0?'_reg':''));    
        }
        
        $aData['cats_path'] = '';
        $aParentsID = $this->tree_getNodeParentsID($aData['cat_id']);
        $aParentsID[] = $aData['cat_id']; 
        if(!empty($aParentsID)) {
            $sQuery = 'SELECT C.title
                       FROM '.TABLE_BBS_CATEGORIES.' C
                       WHERE '.$this->db->prepareIN('C.id', $aParentsID).'
                       ORDER BY C.numleft';
            $aData['cats_path'] = join('&nbsp;&nbsp;<img src="/img/arrowRightSmall.png" />&nbsp;&nbsp;', $this->db->select_one_column($sQuery) );
        }
        
        $aData['f'] = &$sFilter; 
        $this->includeJS(array('bbs.txt','admin.bbs.edit'), false, false);  
        $this->includeJS(array('swfupload/swfupload'));  
        return $this->tplFetchPHP($aData, 'admin.items.edit.php');
    }      

    function items_comments()
    {
        if(!$this->haveAccessTo('items-comments'))
            return $this->showAccessDenied();

        $nItemID = $this->input->getpost('rec', TYPE_UINT);
        if(!$nItemID) $this->adminRedirect(Errors::IMPOSSIBLE, 'items_listing');

        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'comment-delete':
                {   
                    if(($nCommentID = Func::POSTGET('comment_id', false, true))<=0 || !$nItemID) 
                        $this->ajaxResponse(Errors::IMPOSSIBLE);                        
                    
                    // статусы: 0 - непромодерирован, 1 - виден всем(промодерирован), 2 - удален модератором, 3 - удален автором
                    $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS_COMMENTS.' 
                                   SET deleted = 2 
                                   WHERE id='.$nCommentID.' AND item_id='.$nItemID.' AND deleted = 0');
                    if( !empty($res) ) {
                        $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_COMMENTS_ENOTIFY.' WHERE comment_id = '.$nCommentID.' AND item_id = '.$nItemID);
                    }
                                   
                    $this->ajaxResponse( ( $res ? Errors::SUCCESSFULL : Errors::IMPOSSIBLE ) ); 
                } break;            
            }
            
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        }
        
        $aData = $this->db->one_array('SELECT I.id, I.user_id, I.status,
                              I.cat_id, C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                              I.cat_type, CT.title as cat_type_title, 
                              I.views_total, 
                              I.img, I.imgfav, I.imgcnt, I.descr, I.descr_regions,
                              I.contacts_name, I.contacts_email, I.contacts_phone, I.contacts_skype, I.contacts_site                              
                              FROM '.TABLE_BBS_ITEMS.' I
                                    LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id,
                                    '.TABLE_BBS_CATEGORIES.' C
                              WHERE I.id = '.$nItemID.'
                                 AND I.cat_id = C.id
                              ');  

        if(empty($aData)) $this->adminRedirect(Errors::IMPOSSIBLE, 'items_listing');        
        $aData = Func::array_2_htmlspecialchars($aData, array('title')); 
                              
        //получаем комментарии
        $aData['comments'] = $this->getItemComments($nItemID, false); 
        $aData['curuid']   = $this->security->getUserID();
                                  
        $this->includeJS(array('admin.bbs.comments'), false, false);
        $this->includeCSS('admin.bbs.comments');
                                          
        $this->adminCustomCenterArea();
        $this->tplAssignByRef('aData', $aData); 
        $this->tplAssignByRef('itemID', $nItemID);         
        return  $this->tplFetch('admin.items.comments.tpl');
    } 
    
    function items_comments_all()
    {
        if(!$this->haveAccessTo('items-comments'))
            return $this->showAccessDenied();

        $aData = array('sort'=>array(), 'comments'=>array(), 'prev'=>0, 'next'=>0);
 
        $nLimit = 15;                                         
       
        //prepare order 
        $aData['order'] = array('created'=>'desc');
        $this->prepareOrder($orderBy, $orderDirection, 'created,desc', $aData['order']); 
        
        $aData['offset'] = $this->input->getpost('offset', TYPE_UINT);
        if($aData['offset']<=0) $aData['offset'] = 0;

        $sql = '';
        if(!$this->security->isSuperAdmin())
        {
            $aCatsAllowed = $this->security->getAllowedBBSCategories();
            if(empty($aCatsAllowed)) {
                $sql = ' AND I.cat1_id = -1';
            } else {
                $sql = ' AND I.cat1_id IN ('.join(',', $aCatsAllowed).')';
            }
        }        
        
        $aData['comments'] = $this->db->select('SELECT C.*, I.cat_id, CC.title as cat_title, U.name as user_name, U.blocked as user_blocked, U.blocked_reason as user_blocked_reason
                              FROM '.TABLE_BBS_ITEMS_COMMENTS.' C
                                   LEFT JOIN '.TABLE_USERS.' U ON C.user_id = U.user_id,
                                   '.TABLE_BBS_ITEMS.' I,
                                   '.TABLE_BBS_CATEGORIES.' CC
                              WHERE C.deleted = 0 AND C.item_id = I.id AND I.cat_id = CC.id '.$sql.'
                              ORDER BY '." $orderBy $orderDirection ".
                              $this->db->prepareLimit($aData['offset'], $nLimit+1)); 

        //prev, next
        $aData['prev'] = ( $aData['offset'] ? $aData['offset']-$nLimit : 0);
        if(count($aData['comments']) > $nLimit)
        {
            $aData['next'] = $aData['offset']+$nLimit;
            array_pop($aData['comments']);
        } else {
            $aData['next'] = 0;
        }
        
        $this->adminIsListing();
        $this->tplAssignByRef('aData', $aData);
        $this->tplAssign('pagenation_template', $this->tplFetch('admin.items.comments.all.pages.tpl'));
        return  $this->tplFetch('admin.items.comments.all.tpl');
    }
    
    function items_complaints()
    {
        if( !$this->haveAccessTo('items-complaints-listing') )
            return $this->showAccessDenied();
 
        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'delete':
                {
                    if(!$this->haveAccessTo('items-complaints-edit'))
                        $this->ajaxResponse(Errors::ACCESSDENIED);
                        
                    $nClaimID = $this->input->id('id', 'p');
                    if($nClaimID) 
                    {
                        $aData = $this->db->one_array('SELECT id, viewed FROM '.TABLE_BBS_ITEMS_CLAIMS.' WHERE id = '.$nClaimID);
                        if(empty($aData)) $this->ajaxResponse(Errors::IMPOSSIBLE);
                        
                        $aResponse = array('counter_update'=>false);
                        $res = $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_CLAIMS.' WHERE id = '.$nClaimID);
                        if($res && !$aData['viewed']) {
                            config::saveCount('bbs_items_claims', -1);
                            $aResponse['counter_update'] = true;
                        }    
                        $aResponse['res'] = $res;
                        $this->ajaxResponse( $aResponse );
                    }
                } break; 
                case 'view':
                {
                    if(!$this->haveAccessTo('items-complaints-edit'))
                        $this->ajaxResponse(Errors::ACCESSDENIED);
                        
                    $nClaimID = $this->input->id('id', 'p');
                    if($nClaimID) {
                        $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS_CLAIMS.' SET viewed = 1 WHERE id = '.$nClaimID);
                        if($res) config::saveCount('bbs_items_claims', -1);
                        $this->ajaxResponse( Errors::SUCCESSFULL );
                    }
                } break;                 
            }
            $this->ajaxResponse( Errors::IMPOSSIBLE );
        } 

        $this->input->getm(array(
                'item'       => TYPE_UINT,      
                'page'    => TYPE_UINT,
                'perpage' => TYPE_UINT,
            ), $aData);
        
        $sqlWhere = array();
        if($aData['item']) {
            $sqlWhere[] = 'CL.item_id = '.$aData['item'];
        }    
        
        $sqlWhere = (!empty($sqlWhere) ? 'WHERE '.join(' AND ', $sqlWhere) : '');

        $nCount = $this->db->one_data('SELECT COUNT(CL.id) FROM '.TABLE_BBS_ITEMS_CLAIMS.' CL '.$sqlWhere);
        $this->prepareOrder($orderBy, $orderDirection, 'CL.created,desc', array('CL.created'=>'desc'));
                                                                            
        $aPerpage = $this->preparePerpage($aData['perpage'], array(20,40,60) );   
        
        $aData['order'] = "$orderBy,$orderDirection";
        $sFilter = http_build_query($aData); unset($aData['page']);
        $this->generatePagenation($nCount, $aData['perpage'], "index.php?s={$this->module_name}&ev=items_complaints&$sFilter&{pageId}", $sqlLimit);
        $aData['f'] = $sFilter;

        if($nCount>0) {
            $aData['complaints'] = $this->db->select('SELECT CL.*, 0 as other
                        FROM '.TABLE_BBS_ITEMS_CLAIMS.' CL
                        '.$sqlWhere."
                        ORDER BY $orderBy $orderDirection $sqlLimit");
        } else {
            $aData['complaints'] = array();
        }
        
        $compl = &$aData['complaints'];
        $reasons = $this->getItemClaimReasons();
        if(!empty($compl) && !empty($reasons)) 
        {
            foreach($compl as $k=>$v) {                          
                $r = $v['reasons'];                              
                if($r==0) continue;

                $r_text = array();
                foreach($reasons as $rk=>$rv) {
                    if($rk!=32 && $rk & $r) {
                        $r_text[] = $rv;
                    }
                }
                $compl[$k]['reasons_text'] = join(', ', $r_text);
                $compl[$k]['other'] = $r & 32;
            }
        }

        $aData['perpage'] = $aPerpage;
        $this->adminIsListing();
        $this->tplAssignByRef('aData', $aData); 
        return $this->tplFetch('admin.items.complaints.listing.tpl');
    }

    function ajax()
    {
        switch(func::GET('act'))
        {
            case 'item-info':
            {
                if(!$this->haveAccessTo('items-edit'))
                    return $this->showAccessDenied();
                    
                $nItemID = $this->input->id();
                if($nItemID) {
                    $aData = $this->db->one_array('SELECT I.id, I.user_id, I.created, I.status, I.status_prev, I.svc,
                              I.blocked_num, I.blocked_reason, I.moderated,
                              I.cat_id, C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                              I.cat_type, CT.title as cat_type_title,
                              I.descr_regions,
                              I.img, I.imgfav, I.imgcnt, I.descr, I.price, I.price_torg, I.price_bart, I.video,
                              I.contacts_name, I.contacts_email, I.contacts_phone, I.contacts_skype, I.contacts_site,
                              I.publicated, I.publicated_to, I.publicated_order, I.marked_to, I.premium_to,
                              U.email as user_email, U.name as user_name, CL.id as claims
                        FROM '.TABLE_BBS_ITEMS.' I 
                            LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                            LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id
                            LEFT JOIN '.TABLE_BBS_ITEMS_CLAIMS.' CL ON I.id = CL.item_id,
                             '.TABLE_BBS_CATEGORIES.' C
                        WHERE I.id = '.$nItemID);
                    if(empty($aData)) break;

                    $aData['cats_path'] = '';
                    $aParentsID = $this->tree_getNodeParentsID($aData['cat_id']);
                    $aParentsID[] = $aData['cat_id']; 
                    if(!empty($aParentsID)) {
                        $sQuery = 'SELECT C.title
                                   FROM '.TABLE_BBS_CATEGORIES.' C
                                   WHERE '.$this->db->prepareIN('C.id', $aParentsID).'
                                   ORDER BY C.numleft';
                        $aData['cats_path'] = join('&nbsp;&nbsp;<img src="/img/arrowRightSmall.png" />&nbsp;&nbsp;', $this->db->select_one_column($sQuery) );
                    }                    
                    
                    $aData['iltype'] = $this->input->get('iltype', TYPE_STR);
                    echo $this->tplFetchPHP($aData, 'admin.items.info.php'); 
                    exit;
                }
            } break; 
            case 'item-block':
            {   
                if(!$this->haveAccessTo('items-edit'))
                    return $this->showAccessDenied();
                         
                $sReason  = mb_strcut( Func::POSTGET('blocked_reason',true), 0, 300);
                $nBlocked = $this->input->post('blocked', TYPE_UINT);
                $nItemID = $this->input->post('id', TYPE_UINT);
                if(!$nItemID) break;
                $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
                               SET blocked_reason = '.$this->db->str2sql($sReason).',
                                   moderated = 1
                                   '.( ! $nBlocked ? ',
                                   blocked_num = blocked_num + 1,
                                   status_prev = status,
                                   status = '.BBS_STATUS_BLOCKED.'
                                   ':'').'
                               WHERE id = '.$nItemID); 
                $this->ajaxResponse( Errors::SUCCESSFULL );               
            } break;            
            case 'item-approve':
            {   
                if(!$this->haveAccessTo('items-edit'))
                    return $this->showAccessDenied();
                         
                $nItemID = $this->input->post('id', TYPE_UINT);
                if(!$nItemID) break;
                $aItemData = $this->db->one_array('SELECT status, publicated, publicated_to FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID);
                if(empty($aItemData) || $aItemData['status'] == BBS_STATUS_NEW) break;
                
                $sql = '';
                if($aItemData['status'] == BBS_STATUS_BLOCKED) {
                    $newStatus = BBS_STATUS_PUBLICATED_OUT;
                    $now = time();
                    $from = strtotime($aItemData['publicated']);
                    $to = strtotime($aItemData['publicated_to']);
                    if(!empty($from) && !empty($to) && $now>=$from && $now<$to) {
                        $newStatus = BBS_STATUS_PUBLICATED;
                    }
                    $sql = ', status_prev = status, status = '.$newStatus;
                }
                
                $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
                               SET moderated = 1'.$sql.'
                               WHERE id = '.$nItemID); 
                $this->ajaxResponse( Errors::SUCCESSFULL );               
            } break; 
            case 'item-publicate2':
            {                                                    
                if(!$this->haveAccessTo('items-edit'))
                    return $this->showAccessDenied();
                    
                $nItemID = $this->input->post('id', TYPE_UINT); 
                if(!$nItemID) { $this->ajaxResponse(Errors::IMPOSSIBLE); }
                
                $aItem = $this->db->one_array('SELECT id, status, moderated, publicated, publicated_to,
                             cat_id, cat1_id, cat2_id, cat_type 
                        FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID.' AND status != '.BBS_STATUS_NEW);
                if(empty($aItem)){ $this->ajaxResponse(Errors::IMPOSSIBLE); }
        
                if($aItem['status'] == BBS_STATUS_BLOCKED) {
                    $this->errors->set('Невозможно продлить публикацию, поскольку объявление '.
                        ($aItem['moderated'] == 0 ? 'ожидает проверки' : 'отклонено') );
                    $this->ajaxResponse( null );
                }
                
                if($aItem['status'] == BBS_STATUS_PUBLICATED) {
                    $this->errors->set('Невозможно продлить публикацию, поскольку объявление опубликовано');
                    $this->ajaxResponse( null );
                }
            
                $nPeriod = $this->input->post('period', TYPE_UINT);
                //проверяем корректность периода публикации
                if( ! ($nPeriod>=1 && $nPeriod<=6) ) {
                    $this->errors->set('wrong_publicated_period');
                    $this->ajaxResponse( null );
                }
                       
                $publicateTo = $this->preparePublicatePeriodTo( $nPeriod, 
                                ( $aItem['status'] == BBS_STATUS_PUBLICATED_OUT ? time() : strtotime($aItem['publicated_to']) ) );
                    
                if( $aItem['status'] == BBS_STATUS_PUBLICATED_OUT )
                {
                    $toOld = strtotime($aItem['publicated_to']);
                    /* если разница между датой снятия с публикации и текущей датой
                     * более 3 дней, тогда поднимаем объявление вверх.
                     * в противном случае: оставлем дату старта публикации(pulicated) и дату порядка публикации(publicated_order) прежними
                     */
                    $bUpdatePublicatedOrder = ((time() - $toOld) > 259200); //60*60*24*3
                    $sqlNOW = $this->db->getNOW();
                    $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
                        SET publicated_to = '.$this->db->str2sql( $publicateTo ).',
                            '.($bUpdatePublicatedOrder ? ' publicated = '.$sqlNOW.', publicated_order = '.$sqlNOW.',' : '').'
                            status_prev = status,
                            status = '.BBS_STATUS_PUBLICATED.',
                            moderated = 1
                        WHERE id = '.$nItemID.'
                    '); 
                    
                    if(!empty($res)){
                        # накручиваем счетчики кол-ва опубликованных объявлений:
                        # в категориях и типах:   
                        $this->itemsCounterUpdate(array($aItem['cat1_id'], $aItem['cat2_id'], $aItem['cat_id']),
                                                  (!empty($aItem['cat_type']) ? array($aItem['cat_type']) : array()), 
                                                  true, true);
                        $this->ajaxResponse(Errors::SUCCESS);
                    }
                                            
                } else {
                    // продление опубликованных пока НЕ делаем
//                        $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
//                            SET publicated_to = '.$this->db->str2sql( $publicateTo ).'
//                            WHERE id = '.$nItemID.'
//                        '); 
                }                  
            } break;
            case 'item-unpublicate':
            {
                if(!$this->haveAccessTo('items-edit'))
                    return $this->showAccessDenied();
                    
                $nItemID = $this->input->post('id', TYPE_UINT); 
                if(!$nItemID) { $this->ajaxResponse(Errors::IMPOSSIBLE); }
                
                $aItem = $this->db->one_array('SELECT id, status, moderated, publicated, publicated_to,
                             cat_id, cat1_id, cat2_id, cat_type 
                        FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID.' AND status = '.BBS_STATUS_PUBLICATED);
                if(empty($aItem)){ $this->ajaxResponse(Errors::IMPOSSIBLE); }
                
                $sqlDateEmpty = $this->db->str2sql('0000-00-00 00:00:00');
                $res = (int)$this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
                            SET status_prev = status, status = '.BBS_STATUS_PUBLICATED_OUT.', moderated = 1, 
                                svc = 0, premium_to = '.$sqlDateEmpty.', marked_to = '.$sqlDateEmpty.',
                                publicated_to = '.$this->db->getNOW().' 
                        WHERE id='.$nItemID);
                if(!empty($res)){
                    # укручиваем)) счетчики кол-ва опубликованных объявлений:
                    # в категориях и типах:   
//                    $this->itemsCounterUpdate(array($aItem['cat1_id'], $aItem['cat2_id'], $aItem['cat_id']),
//                                              (!empty($aItem['cat_type']) ? array($aItem['cat_type']) : array()), 
//                                              false, true);
                    $this->ajaxResponse(Errors::SUCCESS);
                }
            } break;
            case 'item-img-upload':
            {
                $nItemID = $this->input->post('id', TYPE_UINT); 
                
                if(!$this->haveAccessTo('items-edit') || !$nItemID) {
                    $this->ajaxResponse(array('success' => false, 'error'=>'В доступе отказано'), 1);
                }
                
                $uploadResult = Upload::swfuploadStart(true);
                if(!is_array($uploadResult)) {
                    $sErrorMessage = $uploadResult;
                    $this->ajaxResponse(array('success' => false, 'error' => $uploadResult), 1);
                }
                
                $sFilename = $this->initImages()->saveImageFileCustom($this->items_images_path, $nItemID, $uploadResult);
                if(!empty($sFilename) && $nItemID>0) 
                {
                    $aData = $this->db->one_array('SELECT img, imgcnt FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID);
                    if(!empty($aData)) {
                        $aData['img'] .= (!empty($aData['img']) ? ',' : '').$sFilename;
                        $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET imgcnt = imgcnt+1, img = '.$this->db->str2sql($aData['img']).'
                                            WHERE id = '.$nItemID);
                    }
                }

                $this->ajaxResponse(array('success' => true, 'filename' => $sFilename, 'id'=>$nItemID), 1);  
            } break;
            case 'item-img-delete':
            {
                if(!$this->haveAccessTo('items-edit'))
                    return $this->showAccessDenied();
                
                $nItemID = $this->input->post('id', TYPE_UINT); 
                $sFilename = $this->input->post('filename', TYPE_STR);
                
                if(empty($sFilename) || !$nItemID) {
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                }
                
                $this->initImages()->deleteImageFileCustom($this->items_images_path, $nItemID, $sFilename);
                
                $this->ajaxResponse(Errors::SUCCESS);
            } break;
        }         
        $this->ajaxResponse( Errors::IMPOSSIBLE ); 
    }
    
    //-------------------------------------------------------------------------------------------------------------------------------
    // категории
      
    function categories_listing()
    {
        if( !$this->haveAccessTo('categories-listing') )
            return $this->showAccessDenied();
 
        $aData = array();
        if(bff::$isAjax)
        {
            $sAct = func::GET('act');
            if(!$this->haveAccessTo('categories-edit') && $sAct!='subs-list')
                  $this->ajaxResponse(Errors::ACCESSDENIED);
                
            switch($sAct)
            {
                case 'subs-list':
                {
                    $nCategoryID = $this->input->id('category', 'pg');
                    if(!$nCategoryID) $this->ajaxResponse(Errors::UNKNOWNRECORD);

                    $aData['cats'] = $this->db->select('SELECT C.id, C.pid, C.enabled, C.regions, C.prices, C.numlevel, IF(C.numright-C.numleft>1,1,0) as node, C.title, COUNT(I.id) as items 
                                               FROM '.TABLE_BBS_CATEGORIES.' C
                                                    LEFT JOIN '.TABLE_BBS_ITEMS.' I ON C.id IN(I.cat1_id, I.cat2_id, I.cat_id)
                                               WHERE C.pid='.$nCategoryID.' 
                                               GROUP BY C.id
                                               ORDER BY C.numleft');                  
                    
                    $this->tplAssignByRef('aData', $aData);
                    $this->ajaxResponse( array( 'cats'=> $this->tplFetch('admin.categories.listing.ajax.tpl') ) );
                } break; 
                case 'toggle':
                {
                    $nCategoryID = $this->input->get('rec');
                    if($this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.' SET enabled = (1-enabled) WHERE id = '.$nCategoryID))
                        $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;   
                case 'prices-form':
                {
                    $p = $this->input->getm(array(
                        'rec'    => TYPE_UINT,
                        'save'   => TYPE_BOOL,
                    ));
                    
                    if($p['save']) {  
                        $this->input->postm(array(
                            'prices' => TYPE_BOOL,
                            'sett'   => TYPE_ARRAY,
                        ), $p);
                        $this->input->clean_array($p['sett'], array(
                            'ranges'=> TYPE_ARRAY,
                            'torg'  => TYPE_BOOL,
                            'bart'  => TYPE_BOOL,
                        ));
                        $p['sett']['ranges'] = $this->prepareCategoryPricesRanges( $p['sett']['ranges'] );
                        
                        $res = $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.' SET 
                                prices = '.$p['prices'].', prices_sett = ? 
                                WHERE id = '.$p['rec'], array( serialize($p['sett']) ));
                        $this->ajaxResponse(array('prices'=>$p['prices'], 'cat_id'=>$p['rec']));
                    }
                    
                    $aData = $this->db->one_array('SELECT prices, prices_sett as sett FROM '.TABLE_BBS_CATEGORIES.' WHERE id = '.$p['rec']);
                    $aData['sett'] = (!empty($aData['sett']) ? unserialize($aData['sett']) : array());
                    if($aData['sett'] === false) $aData['sett'] = array();
                    if(!isset($aData['sett']['ranges'])) $aData['sett']['ranges'] = array();
                    
                    $aData['form'] = $this->tplFetchPHP($aData, 'admin.prices.form.php');
                    $aData['cat_id'] = $p['rec'];
                    $this->ajaxResponse($aData);
                } break;
                case 'regions':
                {
                    $nCategoryID = $this->input->get('rec');
                    if($this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.' SET regions = (1-regions) WHERE id = '.$nCategoryID))
                        $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;                 
                case 'rotate':
                {                            
                    if($this->tree_rotateTablednd())
                        $this->ajaxResponse(Errors::SUCCESSFULL);
                } break; 
                case 'delete':
                {
                    $nCategoryID = $this->input->id('rec', 'p');
                    if($nCategoryID) {
                        $nSubcats  = $this->tree_getChildrenCount($nCategoryID);
                        $nItems =  $this->db->one_data('SELECT COUNT(I.id) FROM '.TABLE_BBS_ITEMS.' I WHERE I.cat_id = '.$nCategoryID);
                        if(empty($nSubcats) && empty($nItems)) { //удаляем только пустую категорию
                            $aDeleteID = $this->tree_deleteNode($nCategoryID);
                            if(!$aDeleteID) $this->adminRedirect(Errors::IMPOSSIBLE); 
                                
                            $this->db->execute('DELETE FROM '.TABLE_BBS_CATEGORIES.' WHERE id IN ('.join(',', $aDeleteID).')');
                            $this->ajaxResponse( Errors::SUCCESSFULL );
                        }
                    }
                } break; 
            }
            
            $this->ajaxResponse( Errors::IMPOSSIBLE );
        } 
 
        $sCatState = func::getCOOKIE(BFF_COOKIE_PREFIX.'bbs_cats_state');     
        $aCatExpandedID = (!empty($sCatState) ? explode(',', $sCatState) : array());
        $aCatExpandedID[] = 1;
        $sqlWhere = ' AND '.$this->db->prepareIN('C.pid', $aCatExpandedID);

        $aData['cats'] = $this->db->select('SELECT C.id, C.pid, C.enabled, C.regions, C.prices, C.numlevel, IF(C.numright-C.numleft>1,1,0) as node, C.title, COUNT(I.id) as items
                    FROM '.TABLE_BBS_CATEGORIES.' C
                        LEFT JOIN '.TABLE_BBS_ITEMS.' I ON C.id IN(I.cat1_id, I.cat2_id, I.cat_id)
                    WHERE 1=1 '.$sqlWhere.'
                    GROUP BY C.id
                    ORDER BY C.numleft ASC');
                                        
        $this->includeJS('tablednd');
        $this->tplAssignByRef('aData', $aData);
        $aData['cats_html'] = $this->tplFetch('admin.categories.listing.ajax.tpl'); 
        return $this->tplFetch('admin.categories.listing.tpl');
    }
               
    function categories_add()
    {
        if( !$this->haveAccessTo('categories-edit') )
            return $this->showAccessDenied();                       
            
        $this->processCategoryData($aData, 0);
            
        if(bff::$isPost)
        {
            if($this->errors->no())
            {
                $nCategoryID = $this->tree_insertNode($aData['pid']);

                if($nCategoryID) {
                    unset($aData['pid']);
                    $this->db->prepareUpdateQuery($sqlUpdate, $aData);                      
                    $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.'
                                   SET '.(!empty($sqlUpdate) ? $sqlUpdate.', ' : '').'  
                                       created  = '.$this->db->getNOW().',
                                       modified = '.$this->db->getNOW().'
                                   WHERE id='.$nCategoryID );
                    
                    $this->adminRedirect(Errors::SUCCESSFULL, 'categories_listing');                        
                }
            }
            $aData = $_POST;
        }
                                            
        $aData['pid_options'] = $this->getCategoriesOptions($aData['pid'], false, 1);      

        $aData['edit'] = false;
        $this->adminCustomCenterArea();    
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.categories.form.tpl');
    }        

    function categories_edit()
    {
        if( !$this->haveAccessTo('categories-edit') )
            return $this->showAccessDenied();
        
        $nRecordID = $this->input->id('rec');
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');
                              
        $aData = $this->db->one_array('SELECT C.* FROM '.TABLE_BBS_CATEGORIES.' C WHERE C.id='.$nRecordID);
        if(!$aData) $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');

        if(bff::$isPost)
        {
            $this->processCategoryData($aData, $nRecordID);
            $this->db->prepareUpdateQuery($sqlData, $aData);

            if($this->errors->no())
            {                                 
                $this->db->execute('UPDATE '.TABLE_BBS_CATEGORIES.'
                               SET '.(!empty($sqlData) ? $sqlData.', ' : '').'  
                                   modified = '.$this->db->getNOW().'
                               WHERE id='.$nRecordID );
                
                $this->adminRedirect(Errors::SUCCESSFULL, 'categories_listing');                                          
            }
            $aData = $_POST;
        }

        $aData['pid_options'] = '';
        $aParentsID = $this->tree_getNodeParentsID($nRecordID, '');
        if(!empty($aParentsID)) {
            $sQuery = 'SELECT C.title
                       FROM '.TABLE_BBS_CATEGORIES.' C
                       WHERE '.$this->db->prepareIN('C.id', $aParentsID).'
                       ORDER BY C.numleft';
            $aData['pid_options'] = join(' > ', $this->db->select_one_column($sQuery) );
        }

        $aData['edit'] = true;
        $this->adminCustomCenterArea();
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.categories.form.tpl');
    }

    //-------------------------------------------------------------------------------------------------------------------------------
    // типы категории   
    
    function types()
    {
        if( !$this->haveAccessTo('types') )
            return $this->showAccessDenied();
 
        $nCategoryID = $this->input->get('cat_id', TYPE_UINT);
        if(!$nCategoryID) $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');
 
        $aData = array();
        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'toggle':
                {
                    $tableName = TABLE_BBS_CATEGORIES_TYPES;
                    $nTypeID = $this->input->get('type_id');

                    if(!$nTypeID) {
                        $nTypeID = $this->input->id('subtype_id');
                        $tableName = TABLE_BBS_CATEGORIES_SUBTYPES;
                    }

                    if($this->db->execute('UPDATE '.$tableName.' SET enabled = (1-enabled) WHERE id = '.$nTypeID))
                        $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;  
                case 'rotate':
                {                            
                    if($this->db->rotateTablednd(TABLE_BBS_CATEGORIES_TYPES))
                        $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;  
                case 'form':
                {
                    $nTypeID = $this->input->get('type_id');
                    $tableName = TABLE_BBS_CATEGORIES_TYPES;

                    if ($nTypeID === NULL) {
                        $nTypeID = $this->input->get('subtype_id');
                        $tableName = TABLE_BBS_CATEGORIES_SUBTYPES;
                    }

                    if($nTypeID) {
                        $aData = $this->db->one_array('SELECT id, title FROM '.$tableName.' WHERE id = '.$nTypeID);
                    } else {
                        $aData['id'] = 0;
                    }
                    
                    $aData['form'] = $this->tplFetchPHP($aData, 'admin.types.form.php');
                    $this->ajaxResponse($aData);
                } break; 
                case 'delete':
                {
                    $tableName = TABLE_BBS_CATEGORIES_TYPES;
                    $nTypeID = $this->input->id('type_id');

                    if(!$nTypeID) {
                        $nTypeID = $this->input->id('subtype_id');
                        $tableName = TABLE_BBS_CATEGORIES_SUBTYPES;
                    }
                    if($nTypeID) {
                        $nItems =  $this->db->one_data('SELECT COUNT(I.id) FROM '.TABLE_BBS_ITEMS.' I WHERE I.cat_type = '.$nTypeID);
                        if(empty($nItems)) { //удаляем только "свободный" тип
                            $res = $this->db->execute('DELETE FROM '.$tableName.' WHERE id = '.$nTypeID);
                            if($res) $this->ajaxResponse( Errors::SUCCESS );
                        }
                    }
                } break; 
            }
            $this->ajaxResponse( Errors::IMPOSSIBLE );
        } 
        
        if(bff::$isPost)
        {                                                      
            switch(func::POSTGET('action')) {
                case 'add': {
                    $this->input->postm(array(
                        'title' => TYPE_STR,
                    ), $aData, array('title'));

                    $tableName = TABLE_BBS_CATEGORIES_TYPES;
                    if (func::POSTGET('subtype_id') !== false)  $tableName = TABLE_BBS_CATEGORIES_SUBTYPES;

                    if($this->errors->no())
                    {
                        $nNum = (integer)$this->db->one_data('SELECT MAX(num) FROM '.$tableName.' WHERE cat_id = '.$nCategoryID);

                        $res = $this->db->execute('INSERT INTO '.$tableName.' (cat_id, title, num)
                        VALUES('.$nCategoryID.', ?, '.($nNum+1).')
                    ', array($aData['title']));

                        $this->adminRedirect(($res ? Errors::SUCCESS : Errors::IMPOSSIBLE), 'types&cat_id='.$nCategoryID);
                    }
                } break;
                case 'edit': {

                    $name = 'type_id';
                    $tableName = TABLE_BBS_CATEGORIES_TYPES;

                    if (func::POSTGET('subtype_id') !== false) {
                        $tableName = TABLE_BBS_CATEGORIES_SUBTYPES;
                        $name = 'subtype_id';
                    }

                    $this->input->postm(array(
                        'title' => TYPE_STR,
                        $name => TYPE_UINT,
                    ), $aData, array('title'));

                    if((isset($aData['type_id']) && !$aData['type_id']) && (isset($aData['subtype_id']) && !$aData['subtype_id'])) {
                        $this->adminRedirect(Errors::IMPOSSIBLE, 'types&cat_id='.$nCategoryID);
                    }
                    
                    if($this->errors->no()) 
                    {
                        $res = $this->db->execute('UPDATE '.$tableName.'
                            SET title = ? WHERE id = '.$aData[$name], array($aData['title']));
                        
                        $this->adminRedirect(($res ? Errors::SUCCESS : Errors::IMPOSSIBLE), 'types&cat_id='.$nCategoryID);
                    } 
                } break;
            }
        }
        
        $aParentID = $this->tree_getNodeParentsID( $nCategoryID, ' AND numlevel>0 ' );
        if(empty($aParentID)) $aParentID = array();
        $aParentID[] = $nCategoryID;

        $aData['cats'] = $this->db->select('SELECT C.id, C.title
                    FROM '.TABLE_BBS_CATEGORIES.' C
                    WHERE '.$this->db->prepareIN('C.id', $aParentID).'
                    ORDER BY C.numleft');
        
        $aData['types'] = $this->db->select('SELECT T.*, C.title as cat_title
                    FROM '.TABLE_BBS_CATEGORIES_TYPES.' T, '.TABLE_BBS_CATEGORIES.' C
                    WHERE '.$this->db->prepareIN('T.cat_id', $aParentID).' AND T.cat_id = C.id
                    ORDER BY C.numleft, T.num ASC');

        $aData['subtypes'] = $this->db->select('SELECT T.*, C.title as cat_title
                    FROM '.TABLE_BBS_CATEGORIES_SUBTYPES.' T, '.TABLE_BBS_CATEGORIES.' C
                    WHERE '.$this->db->prepareIN('T.cat_id', $aParentID).' AND T.cat_id = C.id
                    ORDER BY C.numleft, T.num ASC');

        $aData['url_listing'] = $this->adminCreateLink('types');
        $aData['url_action'] = $this->adminCreateLink('types&cat_id='.$nCategoryID.'&act=');
        $aData['cat_id'] = $nCategoryID;
        
        $this->includeJS('tablednd');
        $this->adminCustomCenterArea();           
        return $this->tplFetchPHP($aData, 'admin.types.php') . $this->tplFetchPHP($aData, 'admin.subtypes.php');
    }
    
    function settings()
    {
        if(!$this->haveAccessTo('settings'))
            return $this->showAccessDenied();  
         
        $configPrefix = $this->module_name.'_';
         
        $sCurrentTab = func::POSTGET('tab'); 
        if(empty($sCurrentTab))
            $sCurrentTab = 'general';
        
        if(bff::$isPost && func::POST('save')==1)
        {
            $confTmp = func::POST('config', false);
            $this->input->clean_array($confTmp, array(
                //general
                'items_perpage' => TYPE_UINT,
                'items_freepubl_category_limit' => TYPE_UINT, 
                'items_freepubl_category_limit_reg' => TYPE_UINT,
                'adtxt_limit' => TYPE_UINT,
                //svc 
                'svc_up_price'      => TYPE_NUM,
                'svc_mark_price'    => TYPE_NUM,
                'svc_premium_price' => TYPE_NUM,
                'svc_press_price'   => TYPE_NUM,
                'svc_up_desc'       => TYPE_STR,
                'svc_mark_desc'     => TYPE_STR,
                'svc_premium_desc'  => TYPE_STR,
                'svc_press_desc'    => TYPE_STR,
                //files 
                'images_limit' => TYPE_UINT,
                'images_limit_reg' => TYPE_UINT,
                //add_instruction
                'add_instruct1' => TYPE_STR, 
                'add_instruct2' => TYPE_STR, 
                'add_instruct3' => TYPE_STR, 
                'add_instruct4' => TYPE_STR, 
            ));
            
            $conf = array();
            foreach($confTmp as $k=>$v) {
                $conf[$configPrefix.$k] = $v;
            }
   
            bff::i()->Sites_saveConfig($conf, false); //в БД    
            $configAll = config::getAll();
            bff::i()->Sites_saveConfig(array_merge($configAll, $conf), true); //в файл
            
            $this->adminRedirect(Errors::SUCCESS, 'settings&tab='.$sCurrentTab );
        }
            
        $aConfig = config::getWithPrefix($this->module_name.'_');
        $aConfig = array_map('stripslashes', $aConfig);        
        
        $aConfig['options'] = array();
        $aConfig['options']['limit10'] = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15);

        $aData = $aConfig;
        $aData['tabs'] = array(
            'general'=>array('t'=>'Общие настройки','a'=>0),   
            'files'=>array('t'=>'Загрузка файлов','a'=>0), 
            'add_instruction'=>array('t'=>'Инструкция при добавлении','a'=>0),       
        ); 
        
        $aData['tabs'][$sCurrentTab]['a'] = 1;
        $this->tplAssign('tab', $sCurrentTab);        
        $this->tplAssignByRef('aData', $aData);  
        $this->adminCustomCenterArea();   
        $this->includeJS('wysiwyg');    
        return $this->tplFetch('admin.settings.tpl');  
    }
    
    //-------------------------------------------------------------------------------------------------------------------------------
    // регионы
    
    function regions_country()
    {
        if(!$this->haveAccessTo('regions'))
            return $this->showAccessDenied();
        
        $aData = $this->db->select('SELECT R.*, COUNT(I.id) as items
                               FROM '.TABLE_BBS_REGIONS.' R
                                 LEFT JOIN '.TABLE_BBS_ITEMS.' I ON I.country_id = R.id
                               WHERE R.pid = 0
                               GROUP BY R.id
                               ORDER BY R.num');

        $this->adminIsListing();
        $this->includeJS('tablednd');
        $this->tplAssign('pid', 0); $this->tplAssign('numlevel', 1);
        $this->tplAssign('rotate', 1);   
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.regions.country.tpl');
    }
    
    function regions_region()
    {                       
        if( !$this->haveAccessTo('regions') )
            return $this->showAccessDenied();
        
        $nCountryID = func::GET('pid', false, true);
        if(empty($nCountryID)) $nCountryID = BBS_COUNTRY_DEFAULT;
        
        $aData['main'] = func::GET('main', false, true);     

        if($aData['main'])
        {
            $aData['regions'] = $this->db->select('SELECT R.*, COUNT(I.id) as items
                                             FROM '.TABLE_BBS_REGIONS.' R
                                                LEFT JOIN '.TABLE_BBS_ITEMS.' I ON I.region_id = R.id
                                             WHERE R.main = 1 AND R.pid = '.$nCountryID.'
                                             GROUP BY R.id
                                             ORDER BY R.num');
            $this->includeJS('tablednd');
        }
        else
        {
            $nCount = $this->db->one_data('SELECT COUNT(*) FROM '.TABLE_BBS_REGIONS.' WHERE pid = '.$nCountryID);
            $this->generatePagenation( $nCount, 20, "index.php?s=".$this->module_name."&ev=".bff::$event."&pid=$nCountryID&{pageId}", $sqlLimit );
            
            $aData['regions'] = $this->db->select('SELECT R.*, COUNT(I.id) as items
                                             FROM '.TABLE_BBS_REGIONS.' R
                                                LEFT JOIN '.TABLE_BBS_ITEMS.' I ON I.region_id = R.id
                                             WHERE R.pid = '.$nCountryID.'
                                             GROUP BY R.id
                                             ORDER BY R.main DESC, R.num, R.title '.$sqlLimit);
        }
                                                     
        $this->tplAssign('pid', $nCountryID); 
        $this->tplAssign('numlevel', 2);
        $this->tplAssign('rotate', $aData['main']);
        
        $aData['country_options'] = $this->regionsOptions($nCountryID, 0, false);

        $this->adminIsListing();
        $this->tplAssignByRef('aData', $aData);                   
        return $this->tplFetch('admin.regions.region.tpl');
    }
    
    function regions_city()
    {
        if( !$this->haveAccessTo('regions') )
            return $this->showAccessDenied();
        
        $nCountryID = func::GET('cid', false, true);
        $nRegionID = func::GET('pid', false, true);
                                                            
        $aData['main'] = func::GET('main', false, true);
        $aData['city'] = strip_tags(func::GET('city', true));
                
        if($aData['main'])
        {
            $aData['cities'] = $this->db->select('SELECT R.*, COUNT(I.id) as items
                                             FROM '.TABLE_BBS_REGIONS.' R
                                                LEFT JOIN '.TABLE_BBS_ITEMS.' I ON I.region_id = R.id
                                             WHERE R.main = 1 AND R.pid = ' . $nRegionID . '
                                             GROUP BY R.id
                                             ORDER BY R.num');
            $this->includeJS('tablednd');
        }
        else
        {
            $nCount = $this->db->one_data('SELECT COUNT(*) FROM ' . TABLE_BBS_REGIONS . ' WHERE pid = ' . $nRegionID . ($aData['city'] ? ' AND title LIKE (' . $this->db->str2sql($aData['city'] . '%') . ')' : ''));
            $this->generatePagenation( $nCount, 20, "index.php?s=".$this->module_name."&ev=".bff::$event."&cid=$nCountryID&pid=$nRegionID&{pageId}", $sqlLimit );
            
            $aData['cities'] = $this->db->select('SELECT R.*, COUNT(I.id) as items
                                             FROM '.TABLE_BBS_REGIONS.' R
                                                LEFT JOIN '.TABLE_BBS_ITEMS.' I ON I.city_id = R.id
                                             WHERE R.pid = '.$nRegionID.'
                                             GROUP BY R.id
                                             ORDER BY R.main DESC, R.num, R.title ' . $sqlLimit);
        }
        $this->tplAssign('cid', $nCountryID);                                               
        $this->tplAssign('pid', $nRegionID); $this->tplAssign('numlevel', 3);
        $this->tplAssign('rotate', $aData['main']);      
        
        $aData['country_options'] = $this->regionsOptions($nCountryID, 0, false);
        $aData['region_options']  = $this->regionsOptions($nRegionID, $nCountryID, false);
        $this->adminIsListing();
        $this->tplAssignByRef('aData', $aData);     
        return $this->tplFetch('admin.regions.city.tpl');
    }
    
    function regions_ajax()
    {
        if( !$this->haveAccessTo('regions') )
            return $this->showAccessDenied();
        
        
        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'region-add': //добавление региона: страна, регион, город/станция метро
                {
                    $this->input->postm(array(
                        'pid'      => TYPE_UINT,
                        'numlevel' => TYPE_UINT,
                        'title'    => TYPE_STR,
                    ), $aData, array('title'));                  

                    if($this->errors->no())
                    {
                        $nNum = (integer)$this->db->one_data('SELECT MAX(num) FROM '.TABLE_BBS_REGIONS.' WHERE pid = '.$aData['pid']);
                        $res = $this->db->execute('INSERT INTO '.TABLE_BBS_REGIONS.' (title, pid, numlevel, num)
                                            VALUES('.$this->db->str2sql($aData['title']).', '.$aData['pid'].', '.$aData['numlevel'].', '.($nNum + 1).')');
                        
                        if($res) $this->ajaxResponse(Errors::SUCCESS);
                    }    
                } break;
                case 'region-delete': //удаление региона: регион, город/станция
                {   
                    $nRecordID = Func::POSTGET('rec', false, true);
                    if(!$nRecordID) break;
                    
                    $res = $this->db->execute('DELETE FROM '.TABLE_BBS_REGIONS.' WHERE (id = '.$nRecordID.' OR pid = '.$nRecordID.')');
                    if($res) $this->ajaxResponse(Errors::SUCCESS);
                } break;                 
                case 'region-toggle': //включение/выключение региона: страна, регион, город/станция
                {   
                    $nRecordID = Func::POSTGET('rec', false, true);
                    if(!$nRecordID) break;
                    
                    $res = $this->db->execute('UPDATE '.TABLE_BBS_REGIONS.' 
                                   SET enabled = (1 - enabled) WHERE id='.$nRecordID);
                    if($res) $this->ajaxResponse(Errors::SUCCESS); 
                } break;            
                case 'region-toggle-main': //переключатель статуса "главный": регион, город/станция
                {   
                    $nRecordID = Func::POSTGET('rec', false, true);
                    if(!$nRecordID) break; 
                    
                    $res = $this->db->execute('UPDATE '.TABLE_BBS_REGIONS.' SET main=(1-main) WHERE id='.$nRecordID.' AND pid>0 LIMIT 1');
                    if($res) $this->ajaxResponse( Errors::SUCCESS ); 
                } break;
                case 'region-save': //обновление региона: страна, регион, город/станция
                {
                    $this->input->postm(array(    
                        'rec'   => TYPE_UINT,
                        'title' => TYPE_STR,
                    ), $aData, array('title'));   

                    if($aData['rec'] && $this->errors->no())
                    {
                        $this->db->execute('UPDATE '.TABLE_BBS_REGIONS.' SET title = '.$this->db->str2sql($aData['title']).' WHERE id = '.$aData['rec']);
                        $this->ajaxResponse(array('title' => $aData['title'], 'id' => $aData['rec']));
                    }
                } break;               
                case 'region-rotate': //регион, город/станция
                {
                    $res = $this->db->rotateTablednd(TABLE_BBS_REGIONS, ' AND main = 1');
                    if($res) $this->ajaxResponse(Errors::SUCCESS);        
                } break;
                case 'country-rotate': //страна
                {
                    $res = $this->db->rotateTablednd(TABLE_BBS_REGIONS, ' AND pid = 0');
                    if($res) $this->ajaxResponse(Errors::SUCCESS);
                } break;
//                case 'get-cities': // autocomplete
//                {
//                    $nCountryID = func::SESSION('cid');
//                    $arr['query'] = func::GET('query', true);
//                    
//                    $aData = $this->db->select('SELECT R.id, R.title
//                                           FROM ' . TABLE_REGION . ' R
//                                           LEFT JOIN ' . TABLE_REGION . ' R ON R.id = C.region_id
//                                           WHERE C.country_id = ' . $nCountryID . ' AND C.main = 0 AND C.title LIKE(' . $this->db->str2sql($arr['query'] . '%') . ')
//                                           ORDER BY title');
//                    
//                    if($aData)
//                    {
//                        foreach($aData as $key => $value)
//                        {
//                            $arr['suggestions'][] = $value['title'] . ($value['region'] ? ' (' . $value['region'] . ')' : '');
//                            $arr['data'][]        = $value['id'];
//                        }
//                    }
//                    else
//                    {
//                        $arr['suggestions'] = array();
//                        $arr['data'] = array();
//                    }
//                                    
//                    echo json_encode($arr);
//                    exit;
//                }break;
            }
        }
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
        
    //-------------------------------------------------------------------------------------------------------------------------------
    // dev
    
    function treevalidate()
    {
        $this->adminIsListing();
        return $this->tree_validate(true);
    }        

}
