<?php

class BBS extends BBSBase
{
    function search()
    {   
        $inputSource = bff::$isAjax ? 'postm' : 'getm';
        $filter = $this->input->$inputSource(array(
            'q'     => TYPE_NOHTML,  // строка поиска
            'p'     => TYPE_UINT, // период публикации 
            'quick' => TYPE_BOOL, // быстрый поиск
            'c'     => TYPE_UINT, // категория  
            'pp'    => TYPE_UINT, //кол-во на страницу
            'page'  => TYPE_UINT, //номер страницы
        )); 
                          
        if(!$filter['pp'] || !in_array($filter['pp'], array(10,20,30))) { $filter['pp'] = 10; }
        if(!$filter['page']) $filter['page'] = 1;
        
        $sql = array();
        $sql[] = 'I.status = '.BBS_STATUS_PUBLICATED;

        # подготавливаем строку поиска 
        if(!empty($filter['q'])) 
        { 
            $sQ = &$filter['q'];
            if(mb_detect_encoding($sQ,'UTF-8, CP1251')!='UTF-8') {
                $sQ = iconv('CP1251','UTF-8', $sQ);
            }
            $sQ = trim( mb_substr($sQ, 0, 64) ); //урезаем до 64-х символов  
            $sQ = ereg_replace(" +", " ", $sQ); //сжимаем двойные пробелы
            
            $nQ_ID = intval( preg_replace('/[^0-9]+/', '', $sQ) );
            
            // полнотекстовый поиск
            $sql[] = '('.$this->db->prepareFulltextQuery($sQ, 'I.descr').' OR I.contacts_phone = '.$this->db->str2sql($sQ).''.(!empty($nQ_ID) ? ' OR I.contacts_phone LIKE '.$this->db->str2sql('%'.$sQ.'%').' OR I.id = '.$nQ_ID : '').')';
            //echo '<pre>', print_r($sql, true), '</pre>'; exit;
            
            //$sql[] = 'I.descr LIKE '.$this->db->str2sql('%'.$sQ.'%');
        }
        
        //период
        if($filter['p']>=1 && $filter['p']<=3) {
            switch($filter['p']) {
                case 1: $p_from = time(); break; //за сегодня 
                case 2: $p_from = mktime(0,0,0,date('m'), date('d')-7, date('Y')); break; //за последнюю неделю
                case 3: $p_from = mktime(0,0,0,date('m')-1, date('d'), date('Y')); break; //за последний месяц
            }        
            $sql[] = 'I.publicated >= '.$this->db->str2sql( date('Y-m-d', $p_from) );
        }

        $aData = array();
        $aData['f'] = &$filter;
        
        if(!$filter['quick']) 
        {
            //поиск с фильтрами
            if(!isset($_POST['fh']) && !isset($_GET['fh'])) {
                $_POST['fh'] = $_GET['fh'] = (!empty($_COOKIE[BFF_COOKIE_PREFIX.'fh']) ? 1 : 0);  
            }
            
            $this->input->$inputSource(array(   
                'f'   => TYPE_ARRAY, //дин. свойства ??
                'fe'  => TYPE_BOOL, //фильтр включен
                'fh'  => TYPE_BOOL, //фильтр свернут 
                'ph'  => TYPE_BOOL, //с фото
                'v'   => TYPE_UINT, //тип отображения, 0,1 - list, 2 - таблица
                'ct'  => TYPE_UINT, //тип категории
                'sct'  => TYPE_UINT, //подтип категории
                'r'   => TYPE_ARRAY, //регион
                //цена
                'prf' => TYPE_UNUM,
                'prt' => TYPE_UNUM,
                'pr'  => TYPE_ARRAY_UINT,
                //дин.свойства
                'f' => TYPE_ARRAY_ARRAY,
                'fc' => TYPE_ARRAY_ARRAY,
            ), $filter); 
                             
            //значения по-умолчанию
            if(!$filter['v']) { $filter['v'] = 1; }
                            
            $this->input->clean_array($filter['r'], array(
                1=>TYPE_UINT,2=>TYPE_UINT,3=>TYPE_UINT
            ));

            extract($filter);
            
            $cat = $this->db->one_array('SELECT * FROM '.TABLE_BBS_CATEGORIES.' WHERE id='.$c.' LIMIT 1');
            if(empty($cat)) func::JSRedirect('/');
            
            $cat['prices_sett'] = unserialize($cat['prices_sett']);
            
            $aData['items'] = array();
                        
            if($cat['items']>0) {
                //категория
                 if($cat['numlevel'] == 1) {
                    $sql[] = 'I.cat1_id = '.$c;
                 } elseif($cat['numlevel'] == 2) {
                    $sql[] = 'I.cat2_id = '.$c;
                 } elseif($cat['numlevel']==3 || ($cat['numright']-$cat['numleft']==1)) {
                    $sql[] = 'I.cat_id = '.$c;
                 }
                
                //тип категории
                if($ct>0) $sql[] = 'I.cat_type = '.$ct;
                
                if($fe) {
                    //цена
                    if(empty($cat['prices_sett']['ranges'])) {
                        unset($pr);
                    }
                    if($cat['prices'] && ($prf || $prt || !empty($pr) ) ) {
                        $sqlPrice = array();
                        if($prf||$prt) {
                            $sqlPrice[] = ($prf?' I.price>='.$prf.($prt?' AND I.price <= '.$prt:''):'I.price <= '.$prt);
                        }
                        if(!empty($pr)) {
                            foreach($cat['prices_sett']['ranges'] as $k=>$i) { if(isset($pr[$k])) {
                                $sqlPrice[] = ($i['from']?' I.price>='.$i['from'].($i['to']?' AND I.price <= '.$i['to']:''):'I.price <= '.$i['to']);
                            }}
                        }
                        if(!empty($sqlPrice)) {
                            $sql[] = '(('.join(') OR (', $sqlPrice).'))';
                        }
                    }
                    
                    //дин.свойства     
                    {
                        $dp = $this->initDynprops();   
                        $sqlDP = $dp->prepareSearchQuery($f, $fc, $dp->getByOwner($c, true, true, true), 'I.');
                        if(!empty($sqlDP)) {
                            $sql[] = $sqlDP;
                        }
                    }
                    
                    // регионы
                    if($cat['regions'] && $r[1]) {
                        if($r[1]) { $sql[] = 'I.country_id = '.$r[1];
                            if($r[2]) { $sql[] = 'I.region_id = '.$r[2];
                                if($r[3]) $sql[] = 'I.city_id = '.$r[3];
                            }
                        }                         
                    }
                    
                    //с фото
                    if($ph) $sql[] = 'I.imgcnt > 0';                     
                }
                
                $isTable = ($v == 2);
                if($isTable) {
                    $dp = $this->initDynprops();
                    $tableDynprops = $dp->getByOwner($c, true, true, false, true);
                    if(!empty($tableDynprops)) {
                        $sqlTableDF = array();
                        foreach($tableDynprops as $k=>$td) {
                            $sqlTableDF[] = 'I.f'.$td['data_field'];
                            if(!empty($td['multi'])) {
                                $tableDynprops[$k]['multi'] = func::array_transparent($td['multi'], 'value', true);
                            }
                        }
                        $aData['dynprops'] = &$tableDynprops;
                    } else {
                        $isTable = false;
                    }
                }
                
                $aData['total'] = $this->db->one_data('SELECT COUNT(I.id) FROM '.TABLE_BBS_ITEMS.' I 
                    '.(!empty($sql) ? 'WHERE '.join(' AND ', $sql):'')); 
                
                $aData['pagenation'] = $this->generatePagenation_Paginator3000($page, $aData['total'], $pp, '?page=%number%', 'bbsSearch.onPage', $sqlLimit);
                
                $sQuery = 'SELECT
                  I.id, I.user_id, I.status, I.press, I.svc,(I.svc = '.Services::typePremium.') as premium, 
                  I.cat1_id,   CAT1.title as cat1_title,
                  I.cat2_id,   CAT2.title as cat2_title,
                  I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                  I.cat_type,  CT.title as cat_type_title,
                  I.imgfav, I.imgcnt, I.price, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart,
                  I.publicated, U.blocked as user_blocked
                  '.($isTable ? ', '.join(', ', $sqlTableDF) : '').'
                  FROM '.TABLE_BBS_ITEMS.' I
                    LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
                    LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                    LEFT JOIN '.TABLE_BBS_ITEMS_VIEWS.' IV ON I.id = IV.item_id AND IV.views_date
                    LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id,
                    '.TABLE_BBS_CATEGORIES.' CAT1,
                    '.TABLE_BBS_CATEGORIES.' C         
                  WHERE '.(!empty($sql) ? join(' AND ', $sql).' AND ':'').'
                        C.id = I.cat_id
                    AND CAT1.id = I.cat1_id
                    AND (I.user_id = 0 OR U.blocked!=1)
                  GROUP BY I.id
                  ORDER BY premium DESC, I.premium_order DESC, I.publicated_order DESC       
                  '.$sqlLimit;

                $aData['items'] = $this->db->select( $sQuery );
            }
            
            //echo '<pre>', print_r($sql, true), '</pre>'; exit;
            //echo '<pre>', print_r($aData, true), '</pre>'; exit;
            
            if(bff::$isAjax) { 
                $this->ajaxResponse(array('list'=> $this->tplFetchPHP($aData, 'search.results.'.($v==1?'list':'table').'.php'), 'res'=>$this->errors->no()));
            }
                                                    
            $aData['cat'] = &$cat;  
            
            $aParentCatsID = $this->db->select_one_column('SELECT id FROM '.TABLE_BBS_CATEGORIES.'
                        WHERE ((numleft < '.$cat['numleft'].' AND numright > '.$cat['numright'].') OR id = '.$cat['id'].') AND numlevel>0
                        ORDER BY numleft');   
            $aData['cats'] = $this->db->select('SELECT id, pid, title 
                FROM '.TABLE_BBS_CATEGORIES.' 
                WHERE enabled = 1 AND (numlevel = 1 '.(!empty($aParentCatsID) ? ' 
                        OR pid IN ('.join(',', $aParentCatsID).') 
                        OR id IN ('.join(',', $aParentCatsID).')' : '').') 
                ORDER BY numleft');
            $aData['cats'] = $this->db->transformRowsToTree($aData['cats'], 'id', 'pid', 'sub');
            $aData['cats_active'] = $aParentCatsID;
                                                                                          
            $aData['types'] = $this->db->select('SELECT T.*
                        FROM '.TABLE_BBS_CATEGORIES_TYPES.' T, '.TABLE_BBS_CATEGORIES.' C
                        WHERE '.$this->db->prepareIN('T.cat_id', $aParentCatsID).' AND T.cat_id = C.id
                        ORDER BY C.numleft, T.num ASC');

            $aData['subtypes'] = $this->db->select('SELECT T.*
                        FROM '.TABLE_BBS_CATEGORIES_SUBTYPES.' T, '.TABLE_BBS_CATEGORIES.' C
                        WHERE '.$this->db->prepareIN('T.cat_id', $aParentCatsID).' AND T.cat_id = C.id
                        ORDER BY C.numleft, T.num ASC');

            $aData['types'] = func::array_transparent($aData['types'], 'id', true);   
            $aData['subtypes'] = func::array_transparent($aData['subtypes'], 'id', true);

            $dp = $this->initDynprops();
            $aData['dp'] = $dp->form($c, false, true, array(), 'f', 'search.dp.php', $this->module_dir_tpl, true);

            $aData['regions'] = $this->db->select('SELECT R.id, R.pid, R.title
                                           FROM '.TABLE_BBS_REGIONS.' R, '.TABLE_BBS_REGIONS.' R2
                                           WHERE R.numlevel IN(1'.($r[1]?',2':'').') AND R.enabled = 1 AND (R.pid = 0 OR (R.pid = R2.id AND R2.enabled = 1)) ORDER BY R.main DESC, R.num, R.title');
            $aData['regions'] = $this->db->transformRowsToTree($aData['regions'], 'id', 'pid', 'sub');
            
            if($r[1] && $r[2]) {
                $aData['cities'] = $this->db->select('SELECT R.id, R.title
                                           FROM '.TABLE_BBS_REGIONS.' R 
                                           WHERE R.pid = '.$r[2].' AND R.enabled = 1 
                                           ORDER BY R.main DESC, R.num, R.title');
            }

            config::set(array(
                    'title' => (!empty($cat['mtitle']) ? $cat['mtitle'] : $cat['title']) .' | '.config::get('title', ''),
                    'mkeywords' => $cat['mkeywords'],
                    'mdescription' => $cat['mdescription'],
                    'bbsCurrentCategory' => reset($aParentCatsID),
                ));
            
            $aData['period_select'] = $this->periodSelect($p); 
            $this->includeJS(array('history.packed', 'bbs.search', 'jquery.paginator'));  
            return $this->tplFetchPHP($aData, 'search.php');
        } else {
            //простой поиск

            extract($filter);
            
            if($c>0) {
                $sql[] = 'I.cat1_id = '.$c;    
            }

            $sql[] = '(I.user_id = 0 OR U.blocked!=1)';
            
            $aData['total_items'] = $this->db->one_data('SELECT COUNT(I.id) FROM '.TABLE_BBS_ITEMS.' I 
                    LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id
                    '.(!empty($sql) ? 'WHERE '.join(' AND ', $sql):'')); 
                                                            
            $aData['pagenation'] = $this->generatePagenation_Paginator3000($page, $aData['total_items'], 15, '?'.http_build_query($filter).'&page=%number%', 'bbsSearchQuick.onPage', $sqlLimit);
            
            $aData['items'] = $this->db->select('SELECT
              I.id, I.status, I.press, (I.svc = '.Services::typePremium.') as premium, I.svc, I.user_id,
              I.cat1_id,   CAT1.title as cat1_title,
              I.cat2_id,   CAT2.title as cat2_title,
              I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
              I.cat_type,  CT.title as cat_type_title,
              I.imgfav, I.imgcnt, I.price, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart,
              I.publicated
              FROM '.TABLE_BBS_ITEMS.' I
                LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
                LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                LEFT JOIN '.TABLE_BBS_ITEMS_VIEWS.' IV ON I.id = IV.item_id AND IV.views_date
                LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id,
                '.TABLE_BBS_CATEGORIES.' CAT1,
                '.TABLE_BBS_CATEGORIES.' C
              WHERE '.(!empty($sql) ? join(' AND ', $sql).' AND ':'').'
                    C.id = I.cat_id
                AND CAT1.id = I.cat1_id 
              GROUP BY I.id
              ORDER BY premium DESC, I.premium_order DESC, I.publicated_order DESC
            '.$sqlLimit);
           
            if(bff::$isAjax) {
                $list = $this->tplFetchPHP($aData, 'search.results.list.php');
                $this->ajaxResponse(array('list'=> $list, 'res'=>$this->errors->no()));
            }

            $aData['cats'] = $this->db->select('SELECT
              CAT1.id, CAT1.title, COUNT(I.cat1_id) as items
              FROM '.TABLE_BBS_ITEMS.' I
                LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
                LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id, 
                '.TABLE_BBS_CATEGORIES.' CAT1,
                '.TABLE_BBS_CATEGORIES.' C
              WHERE '.(!empty($sql) ? join(' AND ', $sql).' AND ':'').'
                    C.id = I.cat_id
                AND CAT1.id = I.cat1_id
              GROUP BY CAT1.id
              ORDER BY CAT1.title    
            ');

            $aData['total'] = $this->getTotalItemsCount();
            
            $aData['period_select'] = $this->periodSelect($p); 
            $this->includeJS(array('jquery.paginator'));           
            return $this->tplFetchPHP($aData, 'search.quick.php');
        }
    }
    
    function add()
    {
        if(bff::$isAjax)
        {
            $this->input->postm(array(
                'id'         => TYPE_UINT, 
                'pass'       => TYPE_NOHTML,
                'cat'        => TYPE_ARRAY_UINT,
                'reg'        => TYPE_ARRAY_UINT,
                //'dp'         => TYPE_ARRAY_ARRAY, 
                'contacts'   => TYPE_ARRAY_NOHTML, 
                'descr'      => TYPE_NOTAGS, 
                'info'       => TYPE_NOTAGS,
                'img'        => TYPE_ARRAY_NOHTML,
                'imgfav'     => TYPE_NOHTML,
                'price'      => TYPE_NUM,
                'price_torg' => TYPE_BOOL,
                'price_bart' => TYPE_BOOL,
                'video'      => TYPE_NOHTML,     
                //publicate
                'period' => TYPE_UINT,          
            ), $p);

            $nUserID = $this->security->getUserID();
            
            $isEdit = false;
            if($p['id']>0 && !empty($p['pass']))
            {
                $sqlCheck = ' WHERE id = '.$p['id'].' AND status = '.BBS_STATUS_NEW.' AND pass = '.$this->security->encodeBBSEditPass($p['pass']);
                if($this->security->isMember())
                    $sqlCheck .= ' AND user_id = '.$nUserID;
                
                $res = $this->db->one_data('SELECT id FROM '.TABLE_BBS_ITEMS.$sqlCheck);
                if(empty($res)) {
                    $this->ajaxResponse(Errors::ACCESSDENIED);
                }
                $isEdit = true;
            }

            $sBannedByIP = $this->security->checkBan(false, func::getRemoteAddress(), false, true);
            if($sBannedByIP) {
                $this->errors->set(Errors::ACCESSDENIED); //не прошли бан-фильтр
            }  
            
            $this->input->clean_array($p['cat'], array(
                1=>TYPE_UINT,2=>TYPE_UINT,3=>TYPE_UINT,'type'=>TYPE_UINT
            ));
            $this->input->clean_array($p['reg'], array(
                1=>TYPE_UINT,2=>TYPE_UINT,3=>TYPE_UINT
            ));                
            
            $nCategoryID = ( $p['cat'][3] ? $p['cat'][3] : ($p['cat'][2] ? $p['cat'][2] : ($p['cat'][1] ? $p['cat'][1] : 0) ) );
            if(!$nCategoryID) {
                $this->errors->set('select:category');
            }
                       
            $p['cat']['type'] = (isset($p['cat']['type']) && $p['cat']['type']>0 ? abs(intval($p['cat']['type'])) : 0);

            $aDynpropsData = $this->input->post('dp', TYPE_ARRAY);
            if(!empty($aDynpropsData)) {    
                $dp = $this->initDynprops();
                $aDynpropsData = $dp->prepareSaveDataByID( $aDynpropsData, $dp->getByID( array_keys($aDynpropsData) ), ($isEdit?'update':'insert') );
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
                $p['contacts']['site'] = str_replace(array('http://','https://', 'ftp://'), '', $p['contacts']['site']);
                if(empty($p['contacts']['site'])) $p['contacts']['site'] = '';
                
                $adtxtLimit = config::get('bbs_adtxt_limit');
                if(!empty($adtxtLimit)) {
                    $p['descr'] = mb_substr($p['descr'], 0, $adtxtLimit);
                }
                
                $p['descr'] = func::cleanComment($p['descr']);
                $p['info'] = func::cleanComment($p['info']);
                
                $sqlNOW = $this->db->getNOW(); 

                $sUID = $this->security->getUID(false, 'post');

                // Превышен лимит бесплатных объявлений с одинаковой контактной информацией. Каждое последующее объявление становится платным.
                $bPayPublication = !$this->checkFreePublicationsLimit($p['cat'][1], $nUserID, $sUID);

                if($isEdit) 
                {
                    $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET
                        user_id = '.$nUserID.', 
                        cat1_id = '.$p['cat'][1].', cat2_id = '.$p['cat'][2].',
                        cat_id = '.$nCategoryID.', cat_type = '.$p['cat']['type'].', 
                        country_id = '.($p['reg'][1]).', region_id = '.($p['reg'][2]).', city_id = '.($p['reg'][3]).',  
                        img = :img, imgcnt = '.sizeof($p['img']).', imgfav = :imgfav, 
                        price = '.$p['price'].', price_torg = '.$p['price_torg'].', price_bart = '.$p['price_bart'].',   
                        contacts_name = :c_name, contacts_email = :c_email, contacts_phone = :c_phone, contacts_skype = :c_skype, contacts_site = :c_site, video = :video, 
                        descr = :descr, descr_regions = :descr_regions, info = :info, mkeywords = :mkeywords, mdescription = :mdescription, modified = '.$sqlNOW.'
                        '.(!empty($aDynpropsData) ? $aDynpropsData : '').'
                        WHERE id = '.$p['id'].'              
                    ', array(       
                        array(':img', join(',',$p['img']), PDO::PARAM_STR),
                        array(':imgfav', $p['imgfav'], PDO::PARAM_STR),
                        array(':c_name', (isset($p['contacts']['name']) ? $p['contacts']['name'] : ''), PDO::PARAM_STR),
                        array(':c_email', (isset($p['contacts']['email']) ? $p['contacts']['email'] : ''), PDO::PARAM_STR),
                        array(':c_phone', (isset($p['contacts']['phone']) ? $p['contacts']['phone'] : ''), PDO::PARAM_STR),
                        array(':c_skype', (isset($p['contacts']['skype']) ? $p['contacts']['skype'] : ''), PDO::PARAM_STR),
                        array(':c_site', (isset($p['contacts']['site']) ? $p['contacts']['site'] : ''), PDO::PARAM_STR),
                        array(':video', $p['video'], PDO::PARAM_STR),
                        array(':descr', $p['descr'], PDO::PARAM_STR), 
                        array(':descr_regions', $sRegionsTitle, PDO::PARAM_STR),
                        array(':info', $p['info'], PDO::PARAM_STR), 
                        array(':mkeywords', $p['descr'], PDO::PARAM_STR),
                        array(':mdescription', $p['descr'], PDO::PARAM_STR),
                    ));
                    
                    $this->ajaxResponse(array('res'=>($res === 1), 'pp'=>$bPayPublication));
                    
                } 
                else 
                {
                    $sPassword = func::generator(6);    
                    
                    $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS.' (
                        user_id, uid, status, cat1_id, cat2_id, cat_id, cat_type,
                        country_id, region_id, city_id,  
                        img, imgcnt, imgfav, 
                        price, price_torg, price_bart,        
                        contacts_name, contacts_email, contacts_phone, contacts_skype, contacts_site, video, pass,
                        descr, descr_regions, info, mkeywords, mdescription, created, modified'.(!empty($aDynpropsData['fields']) ? $aDynpropsData['fields'] : '').')
                        VALUES ('.$nUserID.', :uid, '.BBS_STATUS_NEW.', '.$p['cat'][1].', '.$p['cat'][2].', '.$nCategoryID.', '.$p['cat']['type'].', 
                            '.(isset($p['reg'][1]) ? $p['reg'][1] : 0).','.(isset($p['reg'][2]) ? $p['reg'][2] : 0).','.(isset($p['reg'][3]) ? $p['reg'][3] : 0).',
                            :img, '.sizeof($p['img']).', :imgfav,
                            '.$p['price'].', '.$p['price_torg'].', '.$p['price_bart'].',
                            :c_name, :c_email, :c_phone, :c_skype, :c_site, :video, '.$this->security->encodeBBSEditPass($sPassword).',
                            :descr, :descr_regions, :info, :mkeywords, :mdescription, '.$sqlNOW.', '.$sqlNOW.' 
                            '.(!empty($aDynpropsData['values']) ? $aDynpropsData['values'] : '').'
                        )              
                    ', array(       
                        array(':uid', $sUID, PDO::PARAM_STR),
                        array(':img', join(',',$p['img']), PDO::PARAM_STR),
                        array(':imgfav', $p['imgfav'], PDO::PARAM_STR),
                        array(':c_name', (isset($p['contacts']['name']) ? $p['contacts']['name'] : ''), PDO::PARAM_STR),
                        array(':c_email', (isset($p['contacts']['email']) ? $p['contacts']['email'] : ''), PDO::PARAM_STR),
                        array(':c_phone', (isset($p['contacts']['phone']) ? $p['contacts']['phone'] : ''), PDO::PARAM_STR),
                        array(':c_skype', (isset($p['contacts']['skype']) ? $p['contacts']['skype'] : ''), PDO::PARAM_STR),
                        array(':c_site', (isset($p['contacts']['site']) ? $p['contacts']['site'] : ''), PDO::PARAM_STR),
                        array(':video', $p['video'], PDO::PARAM_STR),
                        array(':descr', $p['descr'], PDO::PARAM_STR),
                        array(':descr_regions', $sRegionsTitle, PDO::PARAM_STR),
                        array(':info', $p['info'], PDO::PARAM_STR),
                        array(':mkeywords', $p['descr'], PDO::PARAM_STR),
                        array(':mdescription', $p['descr'], PDO::PARAM_STR),
                    ));
                    
                    $nItemID = $this->db->insert_id(TABLE_BBS_ITEMS, 'id');
                    
                    $aResponse = array();
                    
                    if($nItemID && $this->errors->no())
                    {
                        # накручиваем счетчики кол-во объявлений:
                        # у пользователя (владельца объявления)
                        if($nUserID) {
                            $this->db->execute('UPDATE '.TABLE_USERS.' 
                                SET items = items+1 WHERE user_id = '.$nUserID);
                        } else {
                            $this->grantEditPass( $nItemID ); // разрешаем редактирование по паролю для текущей сессии
                        }
                        
                        if(sizeof($p['img'])>0) {
                            $oImages = $this->initImages();
                            foreach($p['img'] as $sImgFilename) {
                                $oImages->renameImageFileCustom($this->items_images_path, $nItemID, $sImgFilename);
                            }
                        }
                    }
                    
                    $this->security->setSESSION('BBS_ITEM_PUBLISHED_ID', 0);
                    
                    $this->ajaxResponse(array('id'=>$nItemID, 'pass'=>$sPassword, 'res'=>$this->errors->no(), 'pp'=>$bPayPublication));
                }
            }
            
            $this->ajaxResponse(null);
        }          
        
        $aData['cats'] = $this->db->select('SELECT id, title FROM '.TABLE_BBS_CATEGORIES.' WHERE numlevel = 1 AND enabled = 1 ORDER BY numleft');
        
        $aData['regions'] = $this->db->select('SELECT R.id, R.pid, R.title
                                       FROM '.TABLE_BBS_REGIONS.' R, '.TABLE_BBS_REGIONS.' R2
                                       WHERE R.numlevel IN(1,2) AND R.enabled = 1 AND (R.pid = 0 OR (R.pid = R2.id AND R2.enabled = 1)) ORDER BY R.main DESC, R.num, R.title');
        $aData['regions'] = $this->db->transformRowsToTree($aData['regions'], 'id', 'pid', 'sub');
        
        $aData['contacts'] = array();
        if($this->security->isLogined()) { //берем контакты пользователя
            $aData['contacts'] = $this->security->getUserInfo('contacts');
            if(!empty($aData['contacts'])) {
                $c = $aData['contacts']['other']; unset($aData['contacts']['other']);
                if(is_array($c)) {
                    foreach($c as $v) {
                        if($v['type'] == 1) {
                            $aData['contacts']['skype'] = $v['data'];
                            break;
                        }
                    }
                }
            }
        }
        
        $this->input->clean_array($aData['contacts'], array(
            'name'=>TYPE_STR, 'phone'=>TYPE_STR, 'email2'=>TYPE_STR, 'skype'=>TYPE_STR
        ));
        
        $aConfig = array('add_instruct1', 'add_instruct2', 'add_instruct3', 'add_instruct4', 'adtxt_limit');
        $aConfig[] = 'images_limit'.($this->security->isMember() ? '_reg':'');
        $aData['config'] = config::get($aConfig, false, $this->module_name.'_');
        
        config::set('bbs_instruction', true);
        $this->tplAssign('bbsInstructions', array('i'=>$aData['config'], 'cur'=>1) );
        $this->includeJS(array('bbs.txt','bbs.add'));
        $this->includeJS(array('swfupload/swfupload'), false, true);
        return $this->tplFetchPHP($aData, 'item.add.php');
    }
    
    function publicate()
    {
        $sessionPublishedKey = 'BBS_ITEM_PUBLISHED_ID';
        $nUserID = $this->security->getUserID();
        
        if(bff::$isAjax)
        {
            $this->input->postm(array(
                'id'     => TYPE_UINT, // id объявления   
                'p'      => TYPE_STR,  // пароль
                'period' => TYPE_UINT, // период публикации
            ), $p);

            $nItemID = $p['id'];
            if(!$nItemID || empty($p['p'])) $this->ajaxResponse(Errors::ACCESSDENIED);

            $sqlCheck = ' WHERE id = '.$nItemID.' AND pass = '.$this->security->encodeBBSEditPass($p['p']);
            if($nUserID>0) $sqlCheck .= ' AND user_id = '.$nUserID;
            
            $aData = $this->db->one_array('SELECT id, uid, user_id, status, cat_id, cat1_id, cat2_id, cat_type FROM '.TABLE_BBS_ITEMS.$sqlCheck);
            if(empty($aData)) {
                $this->ajaxResponse(Errors::ACCESSDENIED);
            } else {
                if($aData['status'] != BBS_STATUS_NEW) {
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                }
            }
            
            //проверяем корректность периода публикации
            if( ! ($p['period']>=1 && $p['period']<=6) ) {
                $p['period'] = 1; // не корректная, ставим "1 неделю"
            }
            
            if($this->errors->no())
            {        
                $bActivatedFromUserBalance = false;
                
                // Превышен лимит бесплатных объявлений с одинаковой контактной информацией. Каждое последующее объявление становится платным.
                $bPayPublication = !$this->checkFreePublicationsLimit($aData['cat1_id'], $nUserID, $aData['uid']);
                if($bPayPublication) 
                {
                    /** @var Bills module */
                    $oBills    = bff::i()->GetModule('Bills');
                    $oServices = bff::i()->GetModule('Services');
                    $nSvcID    = Services::typePublicate;
                    $sDescription = $oServices->buildServiceBillDescription($nSvcID, $nItemID);
                    
                    $resp = array('pay'=>false);
                    do 
                    {
                        $svcPublicate = $oServices->getServiceSettings( $nSvcID );
                        if(empty($svcPublicate)) {
                            //не удалось получить информацию об услуге
                            $this->errors->set('Ошибка активации услуги');
                            break;
                        }

                        $price = $svcPublicate['price'];                        
                        
                        if($nUserID > 0) 
                        {
                            $balance = $this->security->getBalance(); 
                            if($price > $balance) {
                                // не достаточно денег на счету
                               $price = ($price - $balance);
                               $resp['pay'] = true; 
                            } else {
                                // оплачиваем услугу платной публикации, снимаем деньги со счета пользователя
                                $nBillID = $oBills->createBill_OutService( $nItemID, $nSvcID, $nUserID, 0, $price, Bills::statusProcessing, $sDescription);
                                if(!$nBillID) { 
                                    $this->errors->set('Ошибка формирования счета');
                                    break;
                                }
                                $bActivatedFromUserBalance = true;
                                break;
                            }
                        } else {
                            $balance = 0;
                            $resp['pay'] = true;
                        }
                             
                        if($resp['pay']) {
                            $nBillID = $oBills->createBill_InPay($nUserID, $balance, $price, $price, 'rur', Bills::psystemRobox, 
                                                              Bills::typeInPay, Bills::statusWaiting, 'Пополнение счета', $nItemID, $nSvcID);
                            if(!$nBillID) {
                                $this->errors->set('Ошибка при создании счета');
                            }
                            $resp['form'] = $oBills->buildPayForm( $price, Bills::psystemRobox, $nBillID, $nItemID, $nSvcID, $p['period'] );
                        }
                         
                    } while(false);
                    
                    if(!$bActivatedFromUserBalance) {
                        $resp['res'] = $this->errors->no();
                        $this->ajaxResponse($resp);
                    }
                }

                $p['to'] = $this->preparePublicatePeriodTo( $p['period'], time() );
                   
                $sqlNOW = $this->db->getNOW();
                $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
                    SET publicated = '.$sqlNOW.',
                        publicated_to = '.$this->db->str2sql($p['to']).',
                        publicated_order = '.$sqlNOW.',
                        status_prev = status,
                        status = '.BBS_STATUS_PUBLICATED.',
                        moderated = 0
                    WHERE id = '.$nItemID.' AND status = '.BBS_STATUS_NEW.' 
                ');

                if(!empty($res))
                {
                    # накручиваем счетчики кол-ва опубликованных объявлений:
                    # в категориях и типах:   
                    $this->itemsCounterUpdate(array($aData['cat1_id'], $aData['cat2_id'], $aData['cat_id']),
                                              (!empty($aData['cat_type']) ? array($aData['cat_type']) : array()), 
                                              true, true);
                              
                    $this->security->setSESSION($sessionPublishedKey, $nItemID);
                    
                    if($bActivatedFromUserBalance) 
                    {
                        // списываем с баланса пользователя
                        $res = $oBills->updateBalance($nUserID, $price, '-');
                        if($res) { 
                            $balance -= $price;
                            $this->security->setBalance( $balance );
                        }

                        //актуaлизируем информацию о счете
                        $oBills->updateBill($nBillID, $balance, false, Bills::statusCompleted);
                    }
                    
                    $this->ajaxResponse(array('res'=>$this->errors->no(), 'pay'=>false));
                }
            }
            
            $this->ajaxResponse(null);
        }

        $nItemID = (int)$this->security->getSESSION( $sessionPublishedKey );
        if(empty($nItemID) || $nItemID<0)
            func::JSRedirect('/items/add');

        $aData = $this->db->one_array('SELECT I.id, '.$this->security->decodeBBSEditPass('I.pass').',
                              I.cat1_id,   CAT1.title as cat1_title,
                              I.cat2_id,   CAT2.title as cat2_title,
                              I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                              I.cat_type,  CT.title as cat_type_title,
                              I.imgfav, I.imgcnt, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart
                              FROM '.TABLE_BBS_ITEMS.' I
                                LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
                                LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id,
                                '.TABLE_BBS_CATEGORIES.' CAT1,
                                '.TABLE_BBS_CATEGORIES.' C
                              WHERE I.id = '.$nItemID.' AND I.status = '.BBS_STATUS_PUBLICATED.'
                                AND CAT1.id = I.cat1_id
                                AND C.id = I.cat_id
                                ');
         
        if(empty($aData)) {
            func::JSRedirect('/items/add');
        }
                        
        /** @var Services module */
        $oServices = bff::i()->GetModule('Services');
        $aServices = $this->db->select('SELECT id, keyword, settings, description FROM '.TABLE_SERVICES.' ORDER BY id');
        $aServicesData = array();
        foreach($aServices as $v) {
            $sett = unserialize($v['settings']);
            $sett['desc'] = $v['description'];
            $aServicesData[$v['keyword']] = $sett;
        }
        $aData['svc'] = $aServicesData;

        config::set('bbs_instruction', true);
        $this->tplAssign('bbsInstructions', array('i'=>config::get($this->module_name.'_add_instruct4', ''), 'cur'=>4) );
        
        $aData['member'] = ($nUserID>0);
        
        return $this->tplFetchPHP($aData, 'item.add.success.php');
    }
    
    function edit()
    {
        if(bff::$isAjax)
        {
            $this->input->postm(array(
                'id'         => TYPE_UINT, 
                'reg'        => TYPE_ARRAY_UINT,      
                'contacts'   => TYPE_ARRAY_NOHTML, 
                'descr'      => TYPE_NOTAGS, 
                'info'       => TYPE_NOTAGS,
                'img'        => TYPE_ARRAY_NOHTML,
                'imgfav'     => TYPE_NOHTML,
                'imo'        => TYPE_NOHTML, // MD5 строка перечислений названий файлов изображений, до редактирования -- для сравнения
                'price'      => TYPE_NUM,
                'price_torg' => TYPE_BOOL,
                'price_bart' => TYPE_BOOL,
                'video'      => TYPE_NOHTML,     
                //publicate
                'period' => TYPE_UINT,
            ), $p);
            
            $sqlCheck = ' WHERE id = '.$p['id'];
            if($this->security->isMember())
                $sqlCheck .= ' AND user_id = '.$this->security->getUserID();
            else {
                if(!$this->isEditPassGranted($p['id'])) {
                    $this->ajaxResponse(Errors::ACCESSDENIED);
                }
            }
            
            $aData = $this->db->one_array('SELECT id, status, cat1_id, cat2_id, cat_id, cat_type, descr FROM '.TABLE_BBS_ITEMS.$sqlCheck);
            if(empty($aData)) {
                $this->ajaxResponse(Errors::ACCESSDENIED);
            }
        
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
                $p['contacts']['site'] = str_replace(array('http://','https://', 'ftp://'), '', $p['contacts']['site']);
                if(empty($p['contacts']['site'])) $p['contacts']['site'] = '';

                $adtxtLimit = config::get('bbs_adtxt_limit');
                if(!empty($adtxtLimit)) {
                    $p['descr'] = mb_substr($p['descr'], 0, $adtxtLimit);
                }

                $p['descr'] = func::cleanComment($p['descr']);
                $p['info'] = func::cleanComment($p['info']);
                
                $sqlNOW = $this->db->getNOW(); 
                                
                $sqlStatus = '';
                $isModified = ($p['imo']!=md5('(*&^%$+_)578'.join(',',$p['img'])) || $aData['descr']!=$p['descr']);
                if($isModified) {                                    
                    $sqlStatus = ', moderated = 0';
                }
                
                $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET
                    country_id = '.($p['reg'][1]).', region_id = '.($p['reg'][2]).', city_id = '.($p['reg'][3]).',  
                    img = :img, imgcnt = '.sizeof($p['img']).', imgfav = :imgfav, 
                    price = '.$p['price'].', price_torg = '.$p['price_torg'].', price_bart = '.$p['price_bart'].',   
                    contacts_name = :c_name, contacts_email = :c_email, contacts_phone = :c_phone, contacts_skype = :c_skype, contacts_site = :c_site, video = :video, 
                    descr = :descr, descr_regions = :descr_regions, info = :info, mkeywords = :mkeywords, mdescription = :mdescription, modified = '.$sqlNOW.'
                    '.(!empty($aDynpropsData) ? $aDynpropsData : '').$sqlStatus.'
                    WHERE id = '.$p['id'].'              
                ', array(       
                    array(':img', join(',',$p['img']), PDO::PARAM_STR),
                    array(':imgfav', $p['imgfav'], PDO::PARAM_STR),
                    array(':c_name', (isset($p['contacts']['name']) ? $p['contacts']['name'] : ''), PDO::PARAM_STR),
                    array(':c_email', (isset($p['contacts']['email']) ? $p['contacts']['email'] : ''), PDO::PARAM_STR),
                    array(':c_phone', (isset($p['contacts']['phone']) ? $p['contacts']['phone'] : ''), PDO::PARAM_STR),
                    array(':c_skype', (isset($p['contacts']['skype']) ? $p['contacts']['skype'] : ''), PDO::PARAM_STR),
                    array(':c_site', (isset($p['contacts']['site']) ? $p['contacts']['site'] : ''), PDO::PARAM_STR),
                    array(':video', $p['video'], PDO::PARAM_STR),
                    array(':descr', $p['descr'], PDO::PARAM_STR), 
                    array(':descr_regions', $sRegionsTitle, PDO::PARAM_STR),
                    array(':info', $p['info'], PDO::PARAM_STR), 
                    array(':mkeywords', $p['descr'], PDO::PARAM_STR),
                    array(':mdescription', $p['descr'], PDO::PARAM_STR),
                ));
                
                $success = ($res === 1);
                
                $this->ajaxResponse(array( 'res'=> $success));  
            }
            
            $this->ajaxResponse(null);
        }
        
        $nItemID = $this->input->id('id');
        if(!$nItemID) func::JSRedirect('/');
        
        $nUserID = $this->security->getUserID();
        
        $dp = $this->initDynprops();
        
        $aData = $this->db->one_array('SELECT I.id, '.$this->security->decodeBBSEditPass('I.pass').', I.user_id, I.status, I.moderated,
                              I.cat_id, C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                              I.country_id, I.region_id, I.city_id,
                              I.img, I.imgfav, I.imgcnt, I.descr, I.info, I.price, I.price_torg, I.price_bart, I.video,
                              I.contacts_name, I.contacts_email, I.contacts_phone, I.contacts_skype, I.contacts_site
                              , I.f'.join(', I.f', range($dp->datafield_int_first, $dp->datafield_text_last) ).'
                              FROM '.TABLE_BBS_ITEMS.' I, '.TABLE_BBS_CATEGORIES.' C
                              WHERE I.id = '.$nItemID.' AND I.cat_id = C.id
                              ');
        if(empty($aData)) func::JSRedirect('/');

        if($aData['user_id']==0) { //доступ только по паролю
            if(!$this->isEditPassGranted($nItemID)) {
                $aData = array('item_id'=>$nItemID,'user_id'=>$nUserID);
                return $this->tplFetchPHP($aData, 'item.edit.pass.php');
            }
        }  else if($aData['user_id'] != $nUserID) { //не является владельцем объявления
            return $this->showForbidden('Вы не является владельцем данного объявления.');
        }
        
        if($aData['status'] == BBS_STATUS_BLOCKED && $aData['moderated']==0) {
            return $this->showForbidden('Объявление ожидает проверки модератора.');
        }
                              
        $aDynprops = $dp->form($aData['cat_id'], $aData, true, array(), 'dp', 'dynprops.form.edit.php', $this->module_dir_tpl);

        $aData['dp'] = $aDynprops['form']; unset($aDynprops);

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
        
        if($nUserID) 
        {
            $aData['items_status'] = $this->db->one_array('SELECT 
                    SUM(I.status = '.BBS_STATUS_PUBLICATED.') as active,
                    SUM(I.status = '.BBS_STATUS_PUBLICATED_OUT.') as notactive,
                    SUM(I.status = '.BBS_STATUS_BLOCKED.') as blocked 
                  FROM '.TABLE_BBS_ITEMS.' I 
                  WHERE I.user_id = '.$nUserID.' AND I.status !='.BBS_STATUS_NEW);
        }
        
        $aConfig = array('adtxt_limit');
        $aConfig[] = 'images_limit'.($this->security->isMember() ? '_reg':'');
        $aData['config'] = config::get($aConfig, false, $this->module_name.'_');
        $aData['redirect'] = (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITEURL.'/item/'.$nItemID);
        
        config::get('tpl_bbs_item_edit', 1);
        config::set('userMenuCurrent', 2);
        config::set('bbsCurrentCategory', $aData['cat_id']);
        $this->includeJS(array('bbs.txt','bbs.edit'));
        $this->includeJS(array('swfupload/swfupload'), false, true);
        return $this->tplFetchPHP($aData, 'item.edit.php');
    }
    
    function view()
    {
        $nUserID = $this->security->getUserID();
        
        if(bff::$isAjax) 
        {
            $aResponse = array();
            switch(func::GET('act')) {
                case 'comment': 
                {
                    $p = $this->input->postm(array(
                        'id'      => TYPE_UINT,
                        'reply'   => TYPE_UINT,
                        'message' => TYPE_STR,
                        'name'    => TYPE_NOHTML,
                        'captcha' => TYPE_STR,
                    ));
                    
                    if(!$p['id']) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    
                    $p['name'] = func::cleanComment($p['name']);
                    $p['message'] = func::cleanComment($p['message']);
                    
                    if(empty($p['message'])) {
                        $this->errors->set('comm_message');
                    }
                    
                    if(!$nUserID) {
                        if(empty($p['name'])) $this->errors->set('comm_name');
                        
                        $oProtection = new CCaptchaProtection();
                        if( !$oProtection->valid((isset($_SESSION['c2'])?$_SESSION['c2']:''), $p['captcha']) ) {
                            $aResponse['captcha_wrong'] = 1;
                            $this->errors->set('comm_wrong_captcha');                
                        }   
                    }
                    
                    if($this->errors->no())
                    {
                        unset($_SESSION['c2']);
                        
                        $res = $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS_COMMENTS.' (pid, item_id, user_id, comment, name, ip, created) 
                            VALUES('.$p['reply'].', '.$p['id'].', '.$nUserID.', :comment, :name, :ip, :created)', 
                            array( ':comment'=>$p['message'], ':name'=>$p['name'], ':ip'=>func::getRemoteAddress(), ':created'=>$this->db->getNOW(false) ) );
                        
                        if(( $nCommentID = $this->db->insert_id(TABLE_BBS_ITEMS_COMMENTS, 'id')))
                        {   
                            $aData = $this->db->one_array('SELECT IC.*, ( CASE WHEN IC.user_id != 0 THEN U.name ELSE IC.name END) as name, 
                                        I.user_id as owner_id, I.contacts_email, U.blocked as user_blocked
                                    FROM '.TABLE_BBS_ITEMS_COMMENTS.' IC 
                                        LEFT JOIN '.TABLE_USERS.' U ON IC.user_id = U.user_id,
                                        '.TABLE_BBS_ITEMS.' I
                                    WHERE IC.id='.$nCommentID.' AND IC.item_id = I.id');
                            $aData['my'] = ($aData['owner_id']>0 && $aData['owner_id'] == $nUserID);
                            $aData['cur_user_id'] = $nUserID; 
                            $aResponse['comment'] = $this->tplFetchPHP($aData, 'item.view.comment.php');
                            
                            $sEnotifyEmail = false;
                            if($aData['owner_id']) {
                                if(!$nUserID || ($nUserID > 0 && $aData['owner_id']!=$nUserID)) { //комментатор > незарег. пользователь или не владелец объявления
                                    // для зарег. пользователей отправляем на email указанный при регистрации
                                    $sEnotifyEmail = $this->db->one_data('SELECT email FROM '.TABLE_USERS.' WHERE user_id = '.$aData['owner_id']);
                                }
                            } else {
                                // для незарег. пользователей отправляем на контактный email
                                $sEnotifyEmail = $aData['contacts_email']; 
                                if($this->isEditPassGranted($p['id'])) {
                                    $sEnotifyEmail = false; // есть доступ к редактированию, значит = владелец объявления
                                }
                            }
                            
                            if(!empty($sEnotifyEmail) && func::IsEmailAddress($sEnotifyEmail)) {
                                // отправляем уведомление о новом комментарии к объявлению
                               $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS_COMMENTS_ENOTIFY.' (item_id, comment_id, comment, email, created) 
                                    VALUES('.$p['id'].', '.$nCommentID.', :comment, :email, '.time().')', 
                                    array(':comment' => nl2br( tpl::truncate( $p['message'], 100, '...', true ) ),
                                          ':email'   => $sEnotifyEmail) );
                            }
                        }
                    }
                    
                } break;
                case 'comment_del':
                {
                    $p = $this->input->postm(array(
                        'id'         => TYPE_UINT,
                        'comment_id' => TYPE_UINT,
                    ));
                    
                    if(!$p['id'] || !$p['comment_id']) { 
                        $this->errors->set(Errors::IMPOSSIBLE); break; 
                    }
                    if(!$nUserID) {
                        $this->errors->set(Errors::ACCESSDENIED); break; 
                    }
                    
                    $isCommentOwner = $this->db->one_data('SELECT user_id FROM '.TABLE_BBS_ITEMS_COMMENTS.' WHERE id = '.$p['comment_id'].' AND user_id = '.$nUserID);
                    if($isCommentOwner) {
                        $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS_COMMENTS.' SET deleted = 3 WHERE id = '.$p['comment_id']);
                        $aResponse['success'] = !empty($res); 
                        $aResponse['by'] = 3;
                    } 
                    else 
                    {
                        $isOwner = $this->db->one_data('SELECT id FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$p['id'].' AND user_id = '.$nUserID);
                        if(empty($isOwner)) { $this->errors->set(Errors::ACCESSDENIED); break; }
                        
                        $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS_COMMENTS.' SET deleted = 1 WHERE id = '.$p['comment_id']);
                        
                        $aResponse['success'] = !empty($res);
                        $aResponse['by'] = 1;
                    }
                } break;
            }
            
            $aResponse['res'] = $this->errors->no();
            $this->ajaxResponse($aResponse);
        }

        $nItemID = $this->input->id('id');
        if(!$nItemID) func::JSRedirect('/');
        
        $sqlDate = $this->db->str2sql(date('Y-m-d'));
        
        $dp = $this->initDynprops();
        
        $aData = $this->db->one_array('SELECT I.id, I.user_id, I.status, I.press, I.svc, (I.svc = '.Services::typePremium.') as premium,
                              I.publicated, I.publicated_to, I.blocked_reason,
                              I.cat_id, C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                              I.cat_type, CT.title as cat_type_title,
                              I.views_total, IV.views as views_today,
                              I.img, I.imgfav, I.imgcnt, I.descr, I.descr_regions, I.info, I.price, I.price_torg, I.price_bart, I.video,
                              I.contacts_name, I.contacts_email, I.contacts_phone, I.contacts_skype, I.contacts_site,
                              I.mkeywords, I.mdescription, U.email2 as contacts_email2, U.blocked as user_blocked, U.blocked_reason as user_blocked_reason, 
                              I.f'.join(', I.f', range($dp->datafield_int_first, $dp->datafield_text_last) ).'
                              FROM '.TABLE_BBS_ITEMS.' I
                                    LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                                    LEFT JOIN '.TABLE_BBS_ITEMS_VIEWS.' IV ON I.id = IV.item_id AND IV.views_date = '.$sqlDate.'
                                    LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id,
                                    '.TABLE_BBS_CATEGORIES.' C
                              WHERE I.id = '.$nItemID.' 
                                 -- AND I.status = '.BBS_STATUS_PUBLICATED.'
                                 AND I.cat_id = C.id
                              ');

        if(empty($aData)) func::JSRedirect('/'); 
        else {
            if($aData['status'] != BBS_STATUS_PUBLICATED) {
                if($aData['status'] == BBS_STATUS_BLOCKED) {
                    return $this->showForbidden('Данное объявление отклонено.'.(!empty($aData['blocked_reason'])?' <br/><br/><b>Причина:&nbsp;</b>'.nl2br($aData['blocked_reason']):''), 'Объявление отклонено');
                }
                return $this->showForbidden('Данное объявление находится на модерации');
            }
        }
        
        if($aData['user_blocked']) {
            return $this->showForbidden('Аккаунт пользователя заблокирован.'.(!empty($aData['user_blocked_reason'])?' <br/><b>Причина:</b><i>'.nl2br($aData['user_blocked_reason']).'</i>':''), 'Аккаунт пользователя заблокирован');
        }


        $aDynprops = $dp->form($aData['cat_id'], $aData, true, array(), 'dp', 'dynprops.form.view.php', $this->module_dir_tpl);
        $aData['dp'] = $aDynprops['form']; unset($aDynprops);  
        
        if(!empty($_GET['print'])) 
        {
            $aData['cat'] = $this->db->one_array('SELECT id, pid, title, items, numlevel, numleft, numright, regions, prices, prices_sett 
                                               FROM '.TABLE_BBS_CATEGORIES.' WHERE id='.$aData['cat_id'].' LIMIT 1');
            
            $aData['cats'] = $this->db->select('SELECT id, title FROM '.TABLE_BBS_CATEGORIES.'
                        WHERE ((numleft < '.$aData['cat']['numleft'].' AND numright > '.$aData['cat']['numright'].') OR id = '.$aData['cat']['id'].') AND numlevel>0
                        ORDER BY numleft'); 
            echo $this->tplFetchPHP($aData, 'item.view.print.php'); 
            exit;
        }
        
        $aData['cat'] = $this->db->one_array('SELECT id, pid, title, items, numlevel, numleft, numright, regions, prices, prices_sett 
                                           FROM '.TABLE_BBS_CATEGORIES.' WHERE id='.$aData['cat_id'].' LIMIT 1');
        
        $aParentCatsID = $this->db->select_one_column('SELECT id FROM '.TABLE_BBS_CATEGORIES.'
                    WHERE ((numleft < '.$aData['cat']['numleft'].' AND numright > '.$aData['cat']['numright'].') OR id = '.$aData['cat']['id'].') AND numlevel>0
                    ORDER BY numleft'); 
        $aData['cats'] = $this->db->select('SELECT id, pid, title 
            FROM '.TABLE_BBS_CATEGORIES.' 
            WHERE enabled = 1 AND (numlevel = 1 '.(!empty($aParentCatsID) ? ' 
                    OR pid IN ('.join(',', $aParentCatsID).') 
                    OR id IN ('.join(',', $aParentCatsID).')' : '').') 
            ORDER BY numleft');
        $aData['cats'] = $this->db->transformRowsToTree($aData['cats'], 'id', 'pid', 'sub');
        $aData['cats_active'] = $aParentCatsID;

        $aData['comments'] = $this->getItemComments($nItemID);
        
        if( ! ( 
                ($aData['my'] = ($aData['user_id']!=0 && $aData['user_id'] == $nUserID ) ) ||
                $this->isEditPassGranted( $nItemID )
         ) ) {
            //update item views
            $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET views_total = views_total + 1 WHERE id = '.$nItemID);
            $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS_VIEWS.' (item_id, views, views_date) VALUES('.$nItemID.', 1, '.$sqlDate.')
                                ON DUPLICATE KEY UPDATE views = views + 1');
        }
        
        config::set(array(
                'mkeywords' => $aData['mkeywords'],
                'mdescription' => $aData['mdescription'],
                'bbsCurrentCategory' => $aData['cat_id'],
            ));
        
        $aData['from_search'] = (isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], '/search')!==FALSE);
        return $this->tplFetchPHP($aData, 'item.view.php');
    }
    
    function printlist()
    {
        $aData = $this->input->postm(array(
            'items' => TYPE_ARRAY_UINT,
            'from'  => TYPE_STR,
        ));
        
        if(empty($aData['items'])) {
            return $this->showForbidden('Невозвожно выполнить операцию');
        }
                             
        $aData['items'] = $this->db->select('SELECT
              I.id, I.status, I.publicated,
              I.cat1_id,   CAT1.title as cat1_title,
              I.cat2_id,   CAT2.title as cat2_title,
              I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
              I.cat_type,  CT.title as cat_type_title,
              I.imgfav, I.imgcnt, I.price, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart,
              I.contacts_name, I.contacts_email, I.contacts_phone, I.contacts_skype, I.contacts_site
          FROM '.TABLE_BBS_ITEMS.' I
            LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
            LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id,
            '.TABLE_BBS_CATEGORIES.' CAT1,
            '.TABLE_BBS_CATEGORIES.' C
          WHERE '.$this->db->prepareIN('I.id', $aData['items']).'
            AND CAT1.id = I.cat1_id
            AND C.id = I.cat_id
          ORDER BY I.publicated ASC');
        
        config::set('title', config::get('title').($aData['from'] == 'fav' ? ' / Избранное' : ''));
          
        echo $this->tplFetchPHP($aData, 'items.printlist.php'); exit;
    }
    
    function del()
    {
        $nUserID = $this->security->getUserID();
        
        if(bff::$isAjax) 
        {
            $aResponse = array();
            switch(func::GET('act'))
            {
                case 'del': {
                    $nItemID = $this->input->id('id', 'p');
                    if(!$nItemID) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    if(!$nUserID) { $this->errors->set(Errors::ACCESSDENIED); break; }
                    
                    $aResponse['deleted'] = $this->itemDelete( $nItemID, $nUserID );
                } break;
                case 'bulk': {
                    $aItemsID = $this->input->post('items', TYPE_ARRAY_UINT);
                    if(empty($aItemsID)) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    if(!$nUserID) { $this->errors->set(Errors::ACCESSDENIED); break; }
                    
                    $aResponse['deleted'] = $this->itemDelete( $aItemsID, $nUserID );
                    
                } break;                
                default: $this->errors->set(Errors::IMPOSSIBLE);
            }
            $aResponse['res'] = $this->errors->no();
            $this->ajaxResponse( $aResponse );
        }
        
        $nItemID = $this->input->id('id');
        if(!$nItemID) func::JSRedirect('/');
        
        if(!$nUserID) { return $this->showForbidden('В доступе отказано'); }
        
        $res = $this->itemDelete( $nItemID, $nUserID );
        
        func::JSRedirect('/items/my');
    }
    
    function fav()
    {
        $fav = $this->getFavorites();

        if(bff::$isAjax) 
        {
            $aResponse = array();
            $nUserID = $this->security->getUserID(); 
            switch(func::GET('act'))
            {
                case 'fav': 
                {
                    $nItemID = $this->input->id('id', 'p'); 
                    if(!$nItemID || !$nUserID) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    
                    $exists = $this->db->one_data('SELECT item_id FROM '.TABLE_BBS_ITEMS_FAV.' WHERE item_id = '.$nItemID.' AND user_id = '.$nUserID);
                    if(!empty($exists)) {
                        $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_FAV.' WHERE item_id = '.$nItemID.' AND user_id = '.$nUserID);
                        $aResponse['added'] = false;
                    } else {
                        $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS_FAV.' (item_id, user_id) 
                            VALUES('.$nItemID.','.$nUserID.')');
                        $aResponse['added'] = true;
                    }  
                } break;
                case 'email_bulk':
                {
                    if(!$nUserID) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    $aData = $this->input->postm(array(
                        'items' => TYPE_ARRAY_UINT,
                    ));
                    
                    if(empty($aData['items'])) {
                        $this->errors->set('Необходимо отметить объявления в списке'); break;
                    }
                    
                    $aData['items'] = $this->db->select('SELECT
                          I.id, I.status, I.publicated,
                          I.cat1_id,   CAT1.title as cat1_title,
                          I.cat2_id,   CAT2.title as cat2_title,
                          I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                          I.cat_type,  CT.title as cat_type_title,
                          I.imgfav, I.imgcnt, I.price, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart,
                          I.contacts_name, I.contacts_email, I.contacts_phone, I.contacts_skype, I.contacts_site
                      FROM '.TABLE_BBS_ITEMS.' I
                        LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
                        LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                        LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id,
                        '.TABLE_BBS_CATEGORIES.' CAT1,
                        '.TABLE_BBS_CATEGORIES.' C
                      WHERE '.$this->db->prepareIN('I.id', $aData['items']).'
                        AND CAT1.id = I.cat1_id
                        AND C.id = I.cat_id
                        AND (I.user_id = 0 OR U.blocked!=1)
                      ORDER BY I.publicated ASC');

                    if(!empty($aData['items'])) 
                    {
                        $res = bff::sendMail( $this->security->getUserEmail(), BFF_EMAIL_FROM_NAME, $this->tplFetchPHP($aData, 'items.list.fav2email.php') );
                        //$this->errors->set( ($res ? Errors::SUCCESS : Errors::IMPOSSIBLE) );
                    }
                      
                } break;
                case 'unfav_bulk': 
                {
                    $aItemsID = $this->input->post('items', TYPE_ARRAY_UINT);
                    
                    $nResultTotal = 0;
                    if(empty($aItemsID)) {
                        $this->errors->set(Errors::IMPOSSIBLE);
                        break;
                    }
                    
                    if($fav['total']==0) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    if($nUserID) {
                        $res = $this->db->execute('DELETE FROM '.TABLE_BBS_ITEMS_FAV.' WHERE user_id = '.$nUserID.' AND '.$this->db->prepareIN('item_id', $aItemsID));
                        $nResultTotal = $fav['total'] - intval($res);
                    } else {
                        $aRemoveID = array_intersect($fav['id'], $aItemsID);
                        $aResultID = array_diff($fav['id'], $aRemoveID);
                        func::setCOOKIE(BFF_COOKIE_PREFIX.'bbs_fav', (!empty($aResultID) ? join(',', $aResultID) : ''), BBS_FAV_COOKIE_EXPIRE, '/', '.'.SITEHOST);
                        $nResultTotal = (!empty($aResultID) ? sizeof($aResultID) : 0);
                    }
                    
                    $aResponse['total'] = $nResultTotal;
                } break;
                default: $this->errors->set(Errors::IMPOSSIBLE);
            }
            $aResponse['res'] = $this->errors->no();
            $this->ajaxResponse( $aResponse );
        }
        
        $aData = array('items'=>array());
        if($fav['total']>0) 
        {
            $sqlItemsIN = $this->db->prepareIN('I.id', $fav['id']);
            
            $aData['items'] = $this->db->select('SELECT
                  I.id, I.status, I.svc,
                  I.cat1_id,   CAT1.title as cat1_title,
                  I.cat2_id,   CAT2.title as cat2_title,
                  I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
                  I.cat_type,  CT.title as cat_type_title,
                  I.imgfav, I.imgcnt, I.price, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart,
                  I.views_total, IV.views,
                  U.blocked as user_blocked
                  FROM '.TABLE_BBS_ITEMS.' I
                    LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
                    LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                    LEFT JOIN '.TABLE_BBS_ITEMS_VIEWS.' IV ON I.id = IV.item_id AND IV.views_date = '.$this->db->str2sql(date('Y-m-d')).'
                    LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id,
                    '.TABLE_BBS_CATEGORIES.' CAT1,
                    '.TABLE_BBS_CATEGORIES.' C
                  WHERE '.$sqlItemsIN.'
                    AND CAT1.id = I.cat1_id
                    AND C.id = I.cat_id
                  ORDER BY I.publicated ASC');
        
            $aData['cats'] = $this->db->select('SELECT C.id, C.title 
                    FROM '.TABLE_BBS_ITEMS.' I, '.TABLE_BBS_CATEGORIES.' C
                    WHERE '.$sqlItemsIN.' AND I.cat1_id = C.id AND C.numlevel = 1 AND C.enabled = 1
                    GROUP BY C.id');
        }
        
        config::set('userMenuCurrent', 1);
        return $this->tplFetchPHP($aData, 'items.list.fav.php');    
    }
    
    function makefav() //noscript
    {
        $nItemID = $this->input->id('id');
        if(!$nItemID) func::JSRedirect('/');
    }                
    
    function my()
    {
        $nUserID = $this->security->getUserID();
        
        if(bff::$isAjax && !empty($_GET['act']) )
        {
            if(!$nUserID) {
                $this->ajaxResponse(Errors::ACCESSDENIED);
            }
            
            $aResponse = array();
            switch(func::GET('act'))
            {
                case 'unpubl_bulk': 
                {
                    $aItemsID = $this->input->post('items', TYPE_ARRAY_UINT);
                    $nResultTotal = 0;
                    if(empty($aItemsID)) {
                        $this->errors->set(Errors::IMPOSSIBLE);
                        break;
                    }

                    $aPublicatedItems = $this->db->select('SELECT I.id, I.cat_id, I.cat1_id, I.cat2_id, I.cat_type 
                                                    FROM '.TABLE_BBS_ITEMS.' I
                                                    WHERE I.user_id = '.$nUserID.' AND I.status = '.BBS_STATUS_PUBLICATED.' 
                                                        AND '.$this->db->prepareIN('I.id', $aItemsID));
                    if(!empty($aPublicatedItems)) 
                    {
                        $aUnpublID = array();
//                        $aCatsItemsDecrement = array();
//                        $aCatsTypesItemsDecrement = array();
                        foreach($aPublicatedItems as $item) 
                        {
//                            $catsUpdate = array_unique(array($item['cat_id'], $item['cat1_id'], $item['cat2_id']));
//                            foreach($catsUpdate as $c) {
//                                if($c == 0) continue;
//                                if(!isset($aCatsItemsDecrement[$c])) {
//                                    $aCatsItemsDecrement[$c] = 1;
//                                } else {
//                                    $aCatsItemsDecrement[$c] += 1;  
//                                }
//                            }
//                            if(!empty($item['cat_type'])) {
//                                if(!isset($aCatsTypesItemsDecrement[$item['cat_type']])) {
//                                    $aCatsTypesItemsDecrement[$item['cat_type']] = 1;
//                                } else {
//                                    $aCatsTypesItemsDecrement[$item['cat_type']] += 1;  
//                                }
//                            }
                            $aUnpublID[] = $item['id'];
                        }
                        
//                        $this->itemsCounterUpdate( (!empty($aCatsItemsDecrement) ? $aCatsItemsDecrement : array()),
//                           (!empty($aCatsTypesItemsDecrement) ? $aCatsTypesItemsDecrement : array()),
//                           false );
                        
                        $sqlDateEmpty = $this->db->str2sql('0000-00-00 00:00:00');
                        $nResultTotal = (int)$this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
                                    SET status_prev = status, status = '.BBS_STATUS_PUBLICATED_OUT.', moderated = 1, 
                                        svc = 0, premium_to = '.$sqlDateEmpty.', marked_to = '.$sqlDateEmpty.',
                                        publicated_to = '.$this->db->getNOW().' 
                                WHERE '.$this->db->prepareIN('id', $aUnpublID));
                    }
                    
                    $aResponse['total'] = $nResultTotal;
                } break;
                default: $this->errors->set(Errors::IMPOSSIBLE);
            }
            $aResponse['res'] = $this->errors->no();
            $this->ajaxResponse( $aResponse );
        }

        if(!$this->security->isMember()) {
            func::JSRedirect('/');
        }
        
        $inputSource = bff::$isAjax ? 'postm' : 'getm';
        
        $f = $this->input->$inputSource(array(
            'status'   => TYPE_UINT,
            'order'  => TYPE_UINT,
            'cat'    => TYPE_UINT,
            'page'   => TYPE_UINT,
        ));
        
        if(!bff::$isAjax) $f['cat'] = 0; //пока забьем на этот момент
        
        $sql = array();
        if(empty($f['status']) || !in_array($f['status'], array(BBS_STATUS_PUBLICATED, BBS_STATUS_PUBLICATED_OUT, BBS_STATUS_BLOCKED))) 
            $f['status'] = BBS_STATUS_PUBLICATED;
        $sql[] = 'I.status = '.$f['status'];

        if($f['cat']) {
            $sql[] = 'I.cat1_id = '.$f['cat'];
        }
        $sql = join(' AND ', $sql);
        
        $sqlOrder = '';
        if(empty($f['order']) || $f['order'] == 1) {
            $f['order'] = 1; 
            $sqlOrder = ' I.publicated_to ASC';
        } else {
            $sqlOrder = ' I.created DESC';
        }
        
        $aData = array('f'=>$f);  
        
        $aData['items'] = $this->db->select('SELECT
              I.id, I.status, I.press, I.svc, (I.svc = '.Services::typePremium.') as premium, I.publicated, I.publicated_to, I.blocked_reason, I.moderated,
              I.cat1_id,   CAT1.title as cat1_title,
              I.cat2_id,   CAT2.title as cat2_title,
              I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
              I.cat_type,  CT.title as cat_type_title,
              I.imgfav, I.imgcnt, I.price, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart,
              I.views_total, IV.views
              FROM '.TABLE_BBS_ITEMS.' I
                LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
                LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
                LEFT JOIN '.TABLE_BBS_ITEMS_VIEWS.' IV ON I.id = IV.item_id AND IV.views_date = '.$this->db->str2sql(date('Y-m-d')).',
                '.TABLE_BBS_CATEGORIES.' CAT1,
                '.TABLE_BBS_CATEGORIES.' C
              WHERE I.user_id = '.$nUserID.'
                AND CAT1.id = I.cat1_id
                AND C.id = I.cat_id
                '.(!empty($sql) ? ' AND '.$sql:'').'
              ORDER BY '.$sqlOrder);  
        
        if(bff::$isAjax) {
            $aData['show_no_items_block'] = true;
            $this->ajaxResponse( array('res'=>$this->errors->no(), 'list'=>$this->tplFetchPHP($aData, 'items.list.my.items.php')) );
        }
        
        $aData['cats'] = $this->db->select('SELECT C.id, C.title, 
                    SUM(I.status = '.BBS_STATUS_PUBLICATED.') as active, 
                    SUM(I.status = '.BBS_STATUS_PUBLICATED_OUT.') as notactive,
                    SUM(I.status = '.BBS_STATUS_BLOCKED.') as blocked
                FROM '.TABLE_BBS_ITEMS.' I, '.TABLE_BBS_CATEGORIES.' C
                WHERE I.user_id = '.$nUserID.' AND I.cat1_id = C.id AND C.numlevel = 1 AND C.enabled = 1
                GROUP BY C.id');        
         
        $aData['items_status'] = $this->db->one_array('SELECT 
                SUM( I.status = '.BBS_STATUS_PUBLICATED.' ) as active,
                SUM( I.status = '.BBS_STATUS_PUBLICATED_OUT.' ) as notactive,
                SUM( I.status = '.BBS_STATUS_BLOCKED.' ) as blocked 
              FROM '.TABLE_BBS_ITEMS.' I 
              WHERE I.user_id = '.$nUserID.' AND I.status !='.BBS_STATUS_NEW.'
              ');

        config::set('userMenuCurrent', 2);
        $this->includeJS(array('history.packed', 'jquery.paginator'));
        return $this->tplFetchPHP($aData, 'items.list.my.php');
    }
    
    function user()
    {
        $inputSource = bff::$isAjax ? 'postm' : 'getm';
        $filter = $this->input->$inputSource(array(  
            'id'    => TYPE_UINT, //пользователь
            'c'     => TYPE_UINT, //категория  
            'pp'    => TYPE_UINT, //кол-во на страницу
            'page'  => TYPE_UINT, //номер страницы
        ));
        
        if(!$filter['pp'] || !in_array($filter['pp'], array(10,20,30))) { $filter['pp'] = 10; }
        if(!$filter['page']) $filter['page'] = 1;
        
        if(!$filter['id']) func::JSRedirect('/');
        
        $aUserData = $this->db->one_array('SELECT blocked, blocked_reason FROM '.TABLE_USERS.' WHERE user_id = '.$filter['id']);
        if(empty($aUserData)) func::JSRedirect('/');
        if($aUserData['blocked']) {
            return $this->showForbidden('Аккаунт пользователя заблокирован.'.
                (!empty($aUserData['blocked_reason'])?' <br/><b>Причина:</b><i>'.nl2br($aUserData['blocked_reason']).'</i>':''), 
                'Аккаунт пользователя заблокирован');
        }
        
        $sql = array();
        $sql[] = 'I.user_id = '.$filter['id'];
        $sql[] = 'I.status = '.BBS_STATUS_PUBLICATED;

        $aData = array();
        $aData['f'] = &$filter;

        extract($filter);
        
        if($c>0) {
            $sql[] = 'I.cat1_id = '.$c;    
        }
        
        $aData['total_items'] = $this->db->one_data('SELECT COUNT(I.id) FROM '.TABLE_BBS_ITEMS.' I 
                '.(!empty($sql) ? 'WHERE '.join(' AND ', $sql):'')); 
                                                        
        $aData['pagenation'] = $this->generatePagenation_Paginator3000($page, $aData['total_items'], 15, '?'.http_build_query($filter).'&page=%number%', 'bbsUserList.onPage', $sqlLimit);
        
        $aData['items'] = $this->db->select('SELECT
          I.id, I.status, I.svc, (I.svc = '.Services::typePremium.') as premium, I.press, I.user_id,
          I.cat1_id,   CAT1.title as cat1_title,
          I.cat2_id,   CAT2.title as cat2_title,
          I.cat_id,    C.regions as cat_regions, C.prices as cat_prices, C.prices_sett as cat_prices_sett,
          I.cat_type,  CT.title as cat_type_title,
          I.imgfav, I.imgcnt, I.price, I.descr, I.descr_regions, I.price, I.price_torg, I.price_bart,
          I.publicated
          FROM '.TABLE_BBS_ITEMS.' I
            LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
            LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id
            LEFT JOIN '.TABLE_BBS_ITEMS_VIEWS.' IV ON I.id = IV.item_id AND IV.views_date,
            '.TABLE_BBS_CATEGORIES.' CAT1,
            '.TABLE_BBS_CATEGORIES.' C
          WHERE '.(!empty($sql) ? join(' AND ', $sql).' AND ':'').'
                C.id = I.cat_id
            AND CAT1.id = I.cat1_id
          GROUP BY I.id
          ORDER BY premium DESC, I.premium_order DESC, I.publicated_order DESC
        '.$sqlLimit);
       
        if(bff::$isAjax) {
            $list = $this->tplFetchPHP($aData, 'search.results.list.php');
            $this->ajaxResponse(array('list'=> $list, 'res'=>$this->errors->no()));
        }

        $aData['cats'] = $this->db->select('SELECT
          CAT1.id, CAT1.title, COUNT(I.cat1_id) as items
          FROM '.TABLE_BBS_ITEMS.' I
            LEFT JOIN '.TABLE_BBS_CATEGORIES.' CAT2 ON I.cat2_id = CAT2.id
            LEFT JOIN '.TABLE_BBS_CATEGORIES_TYPES.' CT ON I.cat_type = CT.id,
            '.TABLE_BBS_CATEGORIES.' CAT1,
            '.TABLE_BBS_CATEGORIES.' C
          WHERE '.(!empty($sql) ? join(' AND ', $sql).' AND ':'').'
                C.id = I.cat_id
            AND CAT1.id = I.cat1_id
          GROUP BY CAT1.id
          ORDER BY CAT1.title    
        ');

        $this->includeJS(array('jquery.paginator'));           
        return $this->tplFetchPHP($aData, 'items.user.php');
        
    }
    
    function getIndexCategories()
    {
        if(bff::$isAjax) 
        {
            $aResponse = array();
            switch(func::GET('act')) {
                case 'expand': 
                {
                    $nCatID = $this->input->post('id', TYPE_UINT);
                    if(!$nCatID) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    $aData = $this->db->one_array('SELECT id, title FROM '.TABLE_BBS_CATEGORIES.' 
                                                WHERE id = '.$nCatID.' AND numlevel = 1 AND enabled = 1');
                    if(empty($aData)) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    
                    $aData['sub'] = $this->db->select('SELECT id, title, items FROM '.TABLE_BBS_CATEGORIES.' WHERE pid='.$nCatID.' AND enabled = 1 ORDER by numleft');
                    $aResponse['block'] = $this->tplFetchPHP($aData, 'index.categories.block.php');
                } break;
            }
            
            $aResponse['res'] = $this->errors->no();
            $this->ajaxResponse($aResponse);
        }
        $aCats = $this->db->select('SELECT id, pid, title, numlevel, items FROM '.TABLE_BBS_CATEGORIES.' WHERE numlevel < 3 AND pid!=0 AND enabled = 1 ORDER by numleft ');
        return $this->db->transformRowsToTree($aCats, 'id', 'pid', 'sub');        
    }
                         
    function generatePagenation_Paginator3000($nPage, $nTotal, $nLimit, $href, $sJSHandler, &$sqlLimit, &$result = array(), $tpl_name = 'items.pagenation.php')
    {
        $perPage = $nLimit;
        $pagesPerStep = 15;
        $nCurrentPage = $nPage; if(!$nCurrentPage) $nCurrentPage = 1; 
        $numPages = ( $nTotal > 0 ? ceil($nTotal/$perPage) : 0);
                
        $sqlLimit = '';
        $pagenationData = array();
        $result['offset'] = 0; 
        
        if($nTotal > 0)
        {
            if($nCurrentPage>$pagesPerStep)
            {
                $stepLastPage = $pagesPerStep;
                while($stepLastPage<$nCurrentPage)
                    $stepLastPage+=$pagesPerStep;

                $stepFirstPage = ($stepLastPage-$pagesPerStep) + 1;
            }
            else
            {
                $stepFirstPage = 1;
                $stepLastPage  = $pagesPerStep;                    
            }
            
            //pages prev, next
            $sNextPages = $sPrevPages = '';
            if($nCurrentPage>$pagesPerStep) {
               $sPrevPages = ($stepFirstPage-1);
            }
            if($stepLastPage<$numPages) {
                $sNextPages = ($stepLastPage+1);
            }
            
            //page prev, next
            $sNextPage = $sPrevPage = '';
            if($nCurrentPage>1) {
               $sPrevPage = ($nCurrentPage-1);
            }
            if($nCurrentPage<$numPages) {
                $sNextPage = ($nCurrentPage+1);  
            }
            
            if($perPage<$nTotal)
            {
                $result['offset'] = (($nCurrentPage-1)*$perPage);
                $result['last']   = $nTotal - (($numPages-1)*$perPage);
                $sqlLimit = $this->db->prepareLimit($result['offset'], $perPage);
            }

            $pagenationData = array('pgNext'=>$sNextPage,
                                    'pgPrev'=>$sPrevPage,
                                    'pgsNext'=>$sNextPages,
                                    'pgsPrev'=>$sPrevPages);
        }
        else
        {
            $pagenationData = array('pgNext'=>0,
                                    'pgPrev'=>0,);
        }
        
        $pagenationData['total'] = $nTotal;
        $pagenationData['total_pages'] = $numPages;
        $pagenationData['page_current'] = $nPage;
        $pagenationData['perpage'] = $nLimit;

        //smart limit-offset
        if(!empty($result['order_dir'])) 
        {                      
            $nOffset = &$result['offset'];
            if($nTotal && $nOffset >= $nTotal/2)
            {
                /*
                * Например общее кол-во записей 1000 (total)
                * Если offset 1-499, тогда $sqlLimit = LIMIT offset,perpage
                * Если offset 500-1000, тогда  $sqlLimit = LIMIT (total-offset-perpage),perpage
                */
                $nOffset = $nTotal - $nOffset;
                if($nOffset >= $nLimit) $nOffset -= $nLimit;
                else{ //last page
                    $nOffset = 0;
                    $nLimit = $result['last']; 
                }
                            
                $sqlLimit = ($result['order_dir'] == 'asc' ? 'desc' : 'asc').' '.$this->db->prepareLimit($nOffset, $nLimit);
            } else {
                $sqlLimit = $result['order_dir'].' '.$this->db->prepareLimit($nOffset, $nLimit);
            }
        }
        
        $pagenationData['href'] = $href;
        $pagenationData['jshandler'] = $sJSHandler;

        return $this->tplFetchPHP($pagenationData, $tpl_name);
    }
    
    function ajax()
    {
        switch(func::GET('act'))
        {
            case 'item-u-update': // закрепляем объявление за пользователем
            {
                $this->input->postm(array(
                    'id'  => TYPE_UINT, 
                    'uid' => TYPE_UINT, // UID
                    'p'   => TYPE_STR,  // пароль 
                ), $p);
                
                $nUserID = $this->security->getUserID();
                $nItemID = $p['id'];
                
                if(!$nItemID || empty($p['p']) || !$nUserID) { 
                    $this->ajaxResponse(Errors::ACCESSDENIED);
                }
                
                $aItem = $this->db->one_array('SELECT id, cat1_id FROM '.TABLE_BBS_ITEMS.' 
                    WHERE id = '.$nItemID.' AND status = '.BBS_STATUS_NEW.' 
                        AND pass = '.$this->security->encodeBBSEditPass($p['p']));
                
                if(!empty($aItem)) {
                    $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET user_id = '.$nUserID.' WHERE id = '.$nItemID); // закрепляем за пользователем
                    $this->db->execute('UPDATE '.TABLE_USERS.' SET items = items+1 WHERE user_id = '.$nUserID); // обновляем счетчик объявлений пользователя
                }
                
                $sUID = $this->security->getUID(false, 'post');
                $bPayPublication = !$this->checkFreePublicationsLimit($aItem['cat1_id'], $nUserID, $sUID);
                $this->ajaxResponse(array('res'=>!empty($aItem), 'pp'=>$bPayPublication));  
            } break;
            case 'item-edit-pass':
            {
                $p = $this->input->postm(array(
                    'id'   => TYPE_UINT,    
                    'pass' => TYPE_STR,  
                ));
                
                $aResponse = array();
                do{
                    if(!$p['id']) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    if(empty($p['pass'])) { $this->errors->set('editpass_empty'); break; }
                    if($this->isEditPassGranted($p['id'])) {
                        $aResponse['result'] = true;
                        break;
                    }
                    $aData = $this->db->one_array('SELECT id, user_id FROM '.TABLE_BBS_ITEMS.' 
                              WHERE id = '.$p['id'].' AND pass = '.$this->security->encodeBBSEditPass($p['pass']));
                    if(empty($aData)) { $this->errors->set(Errors::ACCESSDENIED); break; }
                    else {
                        if($aData['user_id']>0) {
                            $userID = $this->security->getUserID();
                            if($userID>0) {
                                if($aData['user_id']!=$userID) {
                                    $this->errors->set('editpass_not_owner');
                                } else {
                                    $aResponse['result'] = true; break;
                                }
                            } else {
                                $this->errors->set('editpass_auth');
                            }
                        } else {
                            $this->grantEditPass($p['id']);
                            $aResponse['result'] = true;
                        }
                    }                    
                    
                } while(false);

                $aResponse['errno'] = $this->errors->no();
                $this->ajaxResponse($aResponse);
            } break;
            case 'item-claim': // оставляем жалобу на объявление
            {
                $p = $this->input->postm(array(
                    'id'      => TYPE_UINT,    
                    'reasons' => TYPE_ARRAY_UINT,  
                    'comment' => TYPE_STR,
                    'captcha' => TYPE_STR, 
                ));
                
                $p['comment'] = func::cleanComment($p['comment']);
                
                $aResponse = array();
                do{
                    if(!$p['id']) { $this->errors->set(Errors::IMPOSSIBLE); break; }
                    if(empty($p['reasons']) && $p['comment'] == '') { $this->errors->set('enter_claim_reason'); break; }

                    $nUserID = $this->security->getUserID();
                    
                    if(!$nUserID) {
                        $oProtection = new CCaptchaProtection();
                        if( !$oProtection->valid((isset($_SESSION['c2'])?$_SESSION['c2']:''), $p['captcha']) ) {
                            $aResponse['captcha_wrong'] = 1;
                            $this->errors->set('claim_wrong_captcha'); 
                            break;               
                        }   
                    }                    
                    
                    unset($_SESSION['c2']);
                    
                    $nReasons = array_sum($p['reasons']);
                    $res = $this->db->execute('INSERT INTO '.TABLE_BBS_ITEMS_CLAIMS.' (item_id, user_id, comment, reasons, ip, created)
                        VALUES('.$p['id'].', '.$nUserID.', '.$this->db->str2sql($p['comment']).', '.$nReasons.', :ip, '.$this->db->getNOW().')
                    ', array(':ip'=>func::getRemoteAddress()));
                    
                    if($res) {
                        config::saveCount('bbs_items_claims', 1);
                        bff::sendMailTemplate(array(
                                'user'=> (!$nUserID ? 'Аноним' : $this->security->getUserEmail()),
                                'claim'=>$this->getItemClaimText($nReasons, nl2br($p['comment'])),
                                'item_url'=>SITEURL.'/item/'.$p['id']), 
                            'admin_bbs_claim', config::get('mail_admin', BFF_EMAIL_SUPPORT));
                    }
                    
                                        
                } while(false);

                $aResponse['result'] = $this->errors->no();
                $this->ajaxResponse($aResponse);
            } break;            
            case 'img-upload':
            {
                $aFailResponse = array('success'=>false);
                
                $nUserID = $this->security->getUserID();
                $nItemID = $this->input->post('id', TYPE_UINT);
                
                if($nItemID > 0) 
                {
                    $aData = $this->db->one_array('SELECT user_id, uid, img, imgcnt, status, moderated FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID);
                    if(empty($aData)) {
                        $aFailResponse['error'] = 'Редактируемое объявление не найдено';
                        $this->ajaxResponse( $aFailResponse );
                    }
                    
                    if($aData['status'] == BBS_STATUS_BLOCKED && $aData['moderated']==0) {
                        $aFailResponse['error'] = 'Объявление ожидает проверки модератора';
                        $this->ajaxResponse( $aFailResponse );
                    }                    
                    
                    // доступ к редактированию объявления возможен только по паролю
                    if($aData['user_id'] == 0)
                    {
                        if( ! $this->isEditPassGranted($nItemID) ) {
                            $aFailResponse['error'] = 'В доступе отказано';
                            $this->ajaxResponse( $aFailResponse );
                        }
                    } else { // автор объявления = загеристрированный пользователь
                        if(!$nUserID // незарегистрированный пытается добавить фото к чужому объявлению
                        || ($nUserID > 0 && $aData['user_id'] != $nUserID) // текущий зарегистророванный пользователь не является владельцем объявления 
                          ) {
                            $aFailResponse['error'] = 'Вы не является владельцем данного объявления.';
                            $this->ajaxResponse( $aFailResponse );
                        }
                    }
                } else {
                    // грузить новые фотографии(без привязки к объявлению) можно пока без ограничений
                    // вернее с ограничением swfuploader'a, до перезагрузки :)
                }
                                
                $uploadResult = Upload::swfuploadStart(true);
                if(!is_array($uploadResult)) {
                    $sErrorMessage = $uploadResult;
                    $this->ajaxResponse(array('success' => false, 'error' => $uploadResult), 1);
                }
                
                $sFilename = $this->initImages()->saveImageFileCustom($this->items_images_path, $nItemID, $uploadResult);
                if(!empty($sFilename) && $nItemID>0) 
                {
                    $aData['img'] .= (!empty($aData['img']) ? ',' : '').$sFilename;
                    $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET imgcnt = imgcnt+1, img = '.$this->db->str2sql($aData['img']).'
                                        WHERE id = '.$nItemID);
                }

                $this->ajaxResponse(array('success' => true, 'filename' => $sFilename, 'id'=>$nItemID), 1);  
            } break;
            case 'img-delete':
            {
                $nUserID = $this->security->getUserID(); 
                $nItemID = $this->input->id('id', 'p'); 
                
                if($nItemID > 0) 
                {
                    $aData = $this->db->one_array('SELECT user_id, uid, img, imgcnt, status, moderated FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID);
                    if(empty($aData)) {
                        $aFailResponse['error'] = 'Редактируемое объявление не найдено';
                        $this->ajaxResponse( $aFailResponse );
                    }
                    
                    if($aData['status'] == BBS_STATUS_BLOCKED && $aData['moderated']==0) {
                        $aFailResponse['error'] = 'Объявление ожидает проверки модератора';
                        $this->ajaxResponse( $aFailResponse );
                    }                    
                    
                    // доступ к редактированию объявления возможен только по паролю
                    if($aData['user_id'] == 0)
                    {
                        if( ! $this->isEditPassGranted($nItemID) ) {
                            $aFailResponse['error'] = 'В доступе отказано';
                            $this->ajaxResponse( $aFailResponse );
                        }
                    } else { // автор объявления = загеристрированный пользователь
                        if(!$nUserID // незарегистрированный пытается удалить фото чужого объявлению
                        || ($nUserID > 0 && $aData['user_id'] != $nUserID) // текущий зарегистророванный пользователь не является владельцем объявления 
                          ) {
                            $aFailResponse['error'] = 'Вы не является владельцем данного объявления.';
                            $this->ajaxResponse( $aFailResponse );
                        }
                    }
                } else {
                    // удалять фотографии(без привязки к объявлению) можно без ограничений
                }
                
                if(!$sFilename = func::POST('filename')) {
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                }
                
                $this->initImages()->deleteImageFileCustom($this->items_images_path, $nItemID, $sFilename);
                
                $this->ajaxResponse(Errors::SUCCESS);
            } break;
            case 'regions':
            {
                $p = $this->input->postm(array(
                    'pid'   => TYPE_UINT,
                    'form'  => TYPE_STR,
                    'empty' => TYPE_STR,
                ));
                
                if(!$p['pid']) break;
                
                $result = false;
                if($p['form'] == 'options') {
                    $result = $this->regionsOptions(0, $p['pid'], true, (!empty($p['empty'])?$p['empty']:'Выбрать...') );
                }
                $this->ajaxResponse($result);
                
            } break;
            case 'sub-cats':
            {
                $p = $this->input->postm(array(
                    'pid'    => TYPE_UINT,
                    'dp'     => TYPE_BOOL,
                    'dp_form'=> TYPE_STR,
                    'format' => TYPE_STR,
                    'type' => TYPE_STR,
                ));

                 if(!$p['pid']) break;
                $returnTypes = 0;
                $returnSubTypes = 0;

                $aParentInfo = $this->db->one_array('SELECT id, numlevel, numleft, numright, prices, prices_sett, regions FROM '.TABLE_BBS_CATEGORIES.' WHERE id = '.$p['pid']);
                
                $aDynprops = array();
                $aCats = $this->db->select('SELECT id, title, numlevel FROM '.TABLE_BBS_CATEGORIES.' WHERE pid = '.$p['pid'].' AND enabled = 1 ORDER BY numleft');
                if(empty($aCats)) {
                    $returnTypes = 1;
                    $tableName = TABLE_BBS_CATEGORIES_TYPES;
                    if($p['type'] == 'type') {
                        $tableName = TABLE_BBS_CATEGORIES_SUBTYPES;
                    }
                    //если категории не найдены, пытаемся получить "типы"
                    $aCats = $this->db->select('SELECT T.id, T.title 
                                                    FROM '.$tableName.' T,
                                                         '.TABLE_BBS_CATEGORIES.' C 
                                                    WHERE ((C.numleft <= '.$aParentInfo['numleft'].' AND C.numright > '.$aParentInfo['numright'].') OR (C.id = '.$p['pid'].'))
                                                        AND C.id = T.cat_id AND T.enabled = 1 
                                                    GROUP BY T.id
                                                    ORDER BY C.numleft, T.num');
                    if($p['dp']) {
                        $sDynpropsForm = '';
                        switch($p['dp_form']) 
                        {
                            case 'add': $sDynpropsForm = 'dynprops.form.add.php'; break;
                        }
                        $aDynprops = $this->initDynprops()->form($p['pid'], false, true, array(), 'dp', $sDynpropsForm, $this->module_dir_tpl);
                    }
                }
                
                if($aParentInfo['prices']) {
                    $aParentInfo['prices_sett'] = unserialize( $aParentInfo['prices_sett'] );
                    if(is_array($aParentInfo['prices_sett'])) {
                        unset($aParentInfo['prices_sett']['ranges']);
                    }
                }
                
                $this->ajaxResponse( array('cats'=>$aCats, 'is_types'=>$returnTypes, 'is_subtypes'=>$returnSubTypes, 'dp'=>$aDynprops,
                        'regions'=>$aParentInfo['regions'], //определяем наличие блока "Местоположение" 
                        'prices' =>$aParentInfo['prices'], 'prices_sett' =>$aParentInfo['prices_sett'], //определяем наличие блока "Цена"
                        ));
            } break;
            case 'dp-child':
            {
                $p = $this->input->postm(array(
                    'dp_id'    => TYPE_UINT,
                    'dp_value' => TYPE_UINT,
                ));
                
                if(empty($p['dp_id']) && empty($p['dp_value']))
                    $this->ajaxResponse('');
                
                $aChildDynpropForm = $this->initDynprops()->formChildAdd($p['dp_id'], $p['dp_value'], 'dynprops.form.child.php', $this->module_dir_tpl);
                $this->ajaxResponse( $aChildDynpropForm );
                
            } break;
            case 'dp-child-filter':
            {
                $p = $this->input->postm(array(
                    'dp_id'    => TYPE_UINT,
                    'dp_value' => TYPE_UINT,
                ));
                
                do
                {
                    if(!$p['dp_id'] || !$p['dp_value'])
                        break;
                    
                    $aPairs = array(array('parent_id'=>$p['dp_id'], 'parent_value'=>$p['dp_value']));
                    
                    $dp = $this->initDynprops();
                    
                    $aResult = array();
                    $aDynprops = $dp->getByParentIDValuePairs($aPairs, true);
                    if(!empty($aDynprops[$p['dp_id']])) 
                    {
                        $aDynprop = current( $aDynprops[$p['dp_id']] );
                        $aResult = $dp->formChildEdit($aDynprop, 'search.dp.child.php', $this->module_dir_tpl);
                    } else {
                        $aResult['form'] = '';
                    }
                    
                    $aResult['pid'] = $p['dp_id'];
                    $aResult['vid'] = $p['dp_value'];                    
                    $this->ajaxResponse( array('form'=>$aResult, 'res'=>true) );
                } while(false);
                
                $this->ajaxResponse(array('form'=>array(),'res'=>false));
                
            } break;
            case 'item-publicate2': // продлеваем публикацию объявления
            {
                $bSave = $this->input->post('save', TYPE_BOOL); 
                $nItemID = $this->input->post('item', TYPE_UINT);
                $nUserID = $this->security->getUserID();
                if(!$nItemID) { $this->ajaxResponse(Errors::IMPOSSIBLE); }
                if(!$nUserID) { $this->ajaxResponse(Errors::ACCESSDENIED); }
                
                $aItem = $this->db->one_array('SELECT id, user_id, status, moderated, publicated, publicated_to,
                             cat_id, cat1_id, cat2_id, cat_type 
                        FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID.' AND status != '.BBS_STATUS_NEW.' AND user_id = '.$nUserID);
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
                
                if( !empty($bSave) ) 
                {
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
                                moderated = 0
                            WHERE id = '.$nItemID.'
                        '); 
                        
                        if(!empty($res)){
                            # накручиваем счетчики кол-ва опубликованных объявлений:
                            # в категориях и типах:   
                            $this->itemsCounterUpdate(array($aItem['cat1_id'], $aItem['cat2_id'], $aItem['cat_id']),
                                                      (!empty($aItem['cat_type']) ? array($aItem['cat_type']) : array()), 
                                                      true, true);
                        }
                                                
                    } else {
                        // продление опубликованных пока НЕ делаем
//                        $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' 
//                            SET publicated_to = '.$this->db->str2sql( $publicateTo ).'
//                            WHERE id = '.$nItemID.'
//                        '); 
                    }
                    
                    $this->ajaxResponse(array('res'=>$this->errors->no()));                    
                }
                
                $aResponse['res'] = $this->errors->no();
                $aResponse['popup'] = $this->tplFetchPHP($aItem, 'items.publicate2.popup.php');
                $this->ajaxResponse($aResponse);
            } break;
        }
        
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
    
}
