<?php   

class Banners extends BannersBase 
{
    var $positions = array();

    function init()
    {
        parent::init();
        config::set('banners_show_to_reguser', 0);
        $this->positions = include $this->module_dir.'banners.positions.php';        
    }
    
    function listing()
    {
        if( !$this->haveAccessTo('listing') )
            return $this->showAccessDenied();

        $f = $this->input->getm(array(
            'pos'         => TYPE_STR,
            'cur_day'     => TYPE_UINT,
            'show_start'  => TYPE_STR,
            'show_finish' => TYPE_STR,
            'en'          => TYPE_STR,
            'cat'         => TYPE_INT,
        )); $sql = array();

        if(!empty($f['pos']))    
        {
            $sql[] = 'BN.position='.$this->db->str2sql($f['pos']);    
        }
        if(!empty($f['cat']))
        {                   
            if($f['cat']==1) {
                $sql[] = 'BN.cat='.$this->db->str2sql($f['cat']);
            } else {
                $sql[] = 'BN.cat!='.$this->db->str2sql('').' AND 
                         (BN.cat = '.$this->db->str2sql($f['cat']).' 
                       OR BN.cat LIKE '.$this->db->str2sql($f['cat'].',%').'
                       OR BN.cat LIKE '.$this->db->str2sql('%,'.$f['cat'].',%').'
                       OR BN.cat LIKE '.$this->db->str2sql('%,'.$f['cat']).')';
            }
        }
        
        if(!empty($f['show_start']) && !empty($f['show_finish']))    
        {
            if($f['show_start'] > $f['show_finish']){  
                $sql[] = 'BN.show_start >= '.$this->db->str2sql(date('Y-m-d',strtotime($f['show_start'])));
                $f['show_finish'] = 0;    
            }
            else{              
                $sql[] = 'DATE_FORMAT(BN.show_start,"%Y-%m-%d")>='.$this->db->str2sql(date('Y-m-d',strtotime($f['show_start']))).'
                 AND DATE_FORMAT(BN.show_finish,"%Y-%m-%d")<='.$this->db->str2sql(date('Y-m-d',strtotime($f['show_finish'])));
            }

        } elseif(!empty($f['show_start']) && empty($f['show_finish'])){
            $sql[] = 'BN.show_start>='.$this->db->str2sql(date("Y-m-d",strtotime($f['show_start'])));    
        } elseif(empty($f['show_start'])&&!empty($f['show_finish'])){
            $sql[] = 'BN.show_finish<='.$this->db->str2sql(date("Y-m-d",strtotime($f['show_finish'])));    
        }
        
        if($f['cur_day']) {
            $sql[] = 'BNST.period='.$this->db->str2sql(date('Y-m-d'));    
        }
        if(!empty($f['en']))    
        {
            if($f['en']=='Y'){
                $sql[] = 'BN.enabled=1';    
            }
            else{
                $sql[] = 'BN.enabled=0';    
            }
        }
        
        //echo '<pre>', print_r($sql, true), '</pre>'; exit;
        
        $this->prepareOrder($orderBy, $orderDir, 'title,asc', array('title'=>'asc','show_start'=>'desc','show_finish'=>'desc','numshow'=>'desc','numclick'=>'desc','ctr'=>'desc'));
        
        $aData = $this->db->select('SELECT BN.*, SUM(BNST.shows) as numshow, SUM(BNST.clicks) as numclick 
                               FROM '.TABLE_BANNERS.' BN
                                LEFT JOIN '.TABLE_BANNERS_STAT.' BNST ON BNST.id= BN.id
                               '.(!empty($sql) ? ' WHERE '.join(' AND ', $sql) : '' ).'
                               GROUP BY BN.id 
                               ORDER BY position');

        $aCategories = $this->getBBSCategories($f['cat'], true);
        if(!empty($aData)) 
        {
            for ($i=0; $i<count($aData); $i++)
            {
                $aData[$i]['positioninfo'] = $this->positions[ $aData[$i]['position'] ];
                $aData[$i]['title'] = $aData[$i]['positioninfo']['title'];
                $aData[$i]['img_thumb'] = BANNERS_URL.'/'.$aData[$i]['id'].'_th_'.$aData[$i]['banner'];
                $aData[$i]['ctr'] = round(($aData[$i]['numclick']/($aData[$i]['numshow']?$aData[$i]['numshow']:1))*100,2);
                if(!empty($aData[$i]['cat'])){
                    $cat = explode(',', $aData[$i]['cat']);
                    $aData[$i]['cat'] = array();
                    foreach($cat as $k=>$v) {
                        $aData[$i]['cat'][] = $aCategories['cats'][$v]['title'];
                    }
                    $aData[$i]['cat'] = join(', ', $aData[$i]['cat']);
                }
            }
        }
        
        if( $orderBy ) {
            $aData = Func::array_key_multi_sort($aData, $orderBy, ($orderDir=='asc'?true:false));
        }

        $aInfo['cur_pos'] = ($f['pos']?$f['pos']:'');
        $aInfo['cur_day'] = $f['cur_day'];
        $aInfo['cur_stat'] = ($f['en']?$f['en']:'');
        $aInfo['show_start'] = $f['show_start'];
        $aInfo['show_finish'] = $f['show_finish'];
        
        $this->tplAssign('aInfo', $aInfo);  
        $this->tplAssignByRef('aData', $aData);  
        $this->tplAssignByRef('aCategories', $aCategories);
        $this->tplAssignByRef('aPositions', $this->positions); 
        $this->includeJS(array('datepicker'));
        return $this->tplFetch('admin.listing.tpl');
    }
    
    function add()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();

        $aData = $this->input->postm(array(
            'position' => TYPE_STR,
            'cat'      => TYPE_ARRAY_UINT,
            'enabled'  => TYPE_BOOL,
            'banner_type' => TYPE_UINT,
            'show_limit'  => TYPE_UINT,
            'resize_img'  => TYPE_BOOL,
            'code'        => TYPE_STR, 
            'show_start'  => TYPE_STR, 
            'show_finish' => TYPE_STR,
            'clickurl'    => TYPE_STR,
            'showurl'     => TYPE_STR,
            'showurl_recursive' => TYPE_BOOL, 
            //flash
            'flash_width' => TYPE_UINT,
            'flash_height'=> TYPE_UINT,
            'flash_key'   => TYPE_STR,
            //meta
            'title'       => TYPE_STR,
            'alt'         => TYPE_STR,
            'description' => TYPE_STR,
        ));
        
        if(bff::$isPost)
        { 
            if(!$aData['position']) $this->errors->set('position');
            if(!$this->checkRotation($aData['position'])) $this->errors->set('no_rotation');   

            $aData['clickurl'] = preg_replace("[^http://|www\.|https://|ftp://]", '', $aData['clickurl']);
            $aData['showurl'] = preg_replace("[^http://|www\.|https://|ftp://]", '', $aData['showurl']);

            $sFlashAddFields = '';
            $sFlashAddValues = '';
            if($aData['banner_type'] == BANNERS_TYPE_FLASH)
            {
                if(!$aData['flash_width'] || !$aData['flash_height'])
                    $this->errors->set('no_flash_size');

                $sFlashAddValues =  $this->db->str2sql(serialize( array('width'=>$aData['flash_width'], 'height'=>$aData['flash_height'], 'key'=>$aData['flash_key']) ) ).', ';
                $sFlashAddFields = 'flash,';
            }
            
            Func::setSESSION('banner_position', $aData['position']);
            
            if($this->errors->no())
            {                     
                $this->db->execute('INSERT INTO '.TABLE_BANNERS.' 
                           ( banner_type, position, cat, clickurl, showurl, showurl_recursive, show_limit, show_start, show_finish, '.$sFlashAddFields.' enabled, title, alt, description)
                            VALUES('.$aData['banner_type'].', :position, :cat, :clickurl, :showurl, '.$aData['showurl_recursive'].',  
                                    '.$aData['show_limit'].', :show_start, :show_finish,
                                    '.$sFlashAddValues.'
                                    '.$aData['enabled'].', :title, :alt, :description)', array(
                                    ':position'=>$aData['position'],
                                    ':cat' => join(',', $aData['cat']),
                                    ':clickurl'=>$aData['clickurl'],
                                    ':showurl'=>$aData['showurl'],
                                    ':show_start'=>date("Y-m-d H:i",strtotime($aData['show_start'])),
                                    ':show_finish'=>date("Y-m-d H:i",strtotime($aData['show_finish'])),
                                    ':title'       => $aData['title'], ':alt' => $aData['alt'],
                                    ':description' => $aData['description'],                                    
                                    ));
                                    
                $nRecordID = $this->db->insert_id(TABLE_BANNERS, 'id');
                
                if($aData['banner_type'] == BANNERS_TYPE_IMG)
                {
                    $oUpload = new Upload('bnrimg', false);
                    $oUpload->checkIsIMG();   
                    
                    if($oUpload->isSuccessfull())
                    {
                        $aPositionInfo = $this->positions[$aData['position']];;
                        $aImgInfo = getimagesize($_FILES['bnrimg']['tmp_name']);
                        $sExtension = image_type_to_extension($aImgInfo[2], false);
                        $sFilename  = Func::generateRandomName(5,true,true).'.'.$sExtension;
                        
                        if(!isset($aPositionInfo['height']) || !$aPositionInfo['height'])
                        {
                            $aPositionInfo['height'] = false;
                        }
                        if(!$aData['resize_img'])
                        {
                            $aWorkingImg = array('filename'=>BANNERS_PATH.$nRecordID.'_work_'.$sFilename,
                                        'width' => $aImgInfo[0], 
                                        'height' => $aImgInfo[1],
                                        'autofit'=>true, 
                                        'crop_v'=>'center',
                                        'crop_h'=>'center'
                                        );
                        }
                        else{
                            $aWorkingImg = array('filename'=>BANNERS_PATH.$nRecordID.'_work_'.$sFilename,
                                        'width' => $aPositionInfo['width'], 
                                        'height' => $aPositionInfo['height'],
                                        'autofit'=>true, 
                                        'crop_v'=>'center',
                                        'crop_h'=>'center'
                                        );
                        }
                        $aParams = array(array('filename'=>BANNERS_PATH.$nRecordID.'_th_'.$sFilename,
                                        'width' => 100, 
                                        'height' => false,
                                        'autofit'=>true, 
                                        'crop_v'=>'center',
                                        'crop_h'=>'center'
                                        ),($aWorkingImg?$aWorkingImg:'')
                        );
                        $oThumb = new CThumbnail($_FILES['bnrimg']['tmp_name'],false);
                        $oThumb->setSaveMethod('gd');
                        $oThumb->save($aParams);
                        
                        $this->db->execute('UPDATE '.TABLE_BANNERS.'
                                   SET banner='.$this->db->str2sql($sFilename).'
                                   WHERE id='.$nRecordID);
                        }
                }
                elseif($aData['banner_type']==BANNERS_TYPE_FLASH)
                {
                    $fUpload = new Upload('flash', false);
                    $fUpload->save(BANNERS_PATH, $nRecordID.'_src_');

                    $this->db->execute('UPDATE '.TABLE_BANNERS.'
                               SET banner='.$this->db->str2sql($fUpload->getFileName()).'
                               WHERE id='.$nRecordID);
                }
                else
                {
                    $this->db->execute('UPDATE '.TABLE_BANNERS.'
                               SET banner='.$this->db->str2sql($aData['code']).'
                               WHERE id='.$nRecordID);    
                }
                
                $this->adminRedirect(Errors::SUCCESSFULL); 
            }
        }
        if(empty($aData['position']))
            $aData['position'] = Func::SESSION('banner_position');

        // подготавливаем линк
        $aData['id_from'] = (int)$this->db->one_data('SELECT MAX(id)+1 FROM '.TABLE_BANNERS);
        if ($aData['id_from'] == 0) $aData['id_from']++;
        if($aData['id_from']>0)
            $aData['link'] = $this->prepareClickURL( $aData['id_from'] ); 

        $aData['id'] = 0; $aData['img_big'] = ''; $aData['banner'] = ''; //defaults
        $aData['resize_img'] = 1;
        $aData['flash'] = array('key'=>'','width'=>'','height'=>'');
        $aData['date_min'] = date( 'Y,n,d', mktime(0,0,0,date('n')-1, date('d'), date('y')) );
        if(empty($aData['cat'])) {
            $aData['cat'][] = 1;
        }
        
        $this->includeJS(array('datepicker'));                  
        $this->tplAssign('aCategories', $this->getBBSCategories($aData['cat'], false));
        $this->tplAssign('aPosOptions', $this->positions);
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.form.tpl');
    }
    
    function edit()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
        
        $nRecordID = Func::POSTGET('rec', false, true);
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE);
        
        $aData = $this->db->one_array('SELECT * FROM '.TABLE_BANNERS.' WHERE id='.$nRecordID);
        if(empty($aData)) $this->adminRedirect(Errors::IMPOSSIBLE);
        
        $sPrevPosition = $aData['position']; 
            
        if(bff::$isPost)
        {
            $this->input->postm(array(
                'position' => TYPE_STR,
                'cat'      => TYPE_ARRAY_UINT,
                'enabled'  => TYPE_BOOL,
                'banner_type' => TYPE_UINT,
                'show_limit'  => TYPE_UINT,
                'resize_img'  => TYPE_BOOL,
                'code'        => TYPE_STR, 
                'show_start'  => TYPE_STR, 
                'show_finish' => TYPE_STR,
                'clickurl'    => TYPE_STR,
                'showurl'     => TYPE_STR,
                'showurl_recursive' => TYPE_BOOL, 
                //flash
                'flash_width' => TYPE_UINT,
                'flash_height'=> TYPE_UINT,
                'flash_key'   => TYPE_STR,
                //meta
                'title'       => TYPE_STR,
                'alt'         => TYPE_STR,
                'description' => TYPE_STR,
            ), $aData);

            if(!$aData['position']) $this->errors->set('position');
            /* если при редактировании позиция меняется, проверить новую позицию на возможность ротации */    
            if($aData['position']!=$sPrevPosition)
            {
                if(!$this->checkRotation($aData['position'])) {
                    $this->errors->set('no_rotation');
                }
            }      

            $aData['clickurl'] = preg_replace("[^http://|www\.|https://|ftp://]", '', $aData['clickurl']);
            $aData['showurl'] = preg_replace("[^http://|www\.|https://|ftp://]", '', $aData['showurl']);
            
            $sFlashAddFields = '';
            if($aData['banner_type'] == BANNERS_TYPE_FLASH)
            {
                if(!$aData['flash_width'] || !$aData['flash_height'])
                    $this->errors->set('no_flash_size');
                                             
                $sFlashAddFields = 'flash = '.$this->db->str2sql(serialize( array('width'=>$aData['flash_width'], 'height'=>$aData['flash_height'], 'key'=>$aData['flash_key']) ) ).',';
            }

            Func::setSESSION('banner_position', $aData['position']);
            
            if($this->errors->no())
            {
                $this->db->execute('UPDATE '.TABLE_BANNERS.'
                            SET clickurl = :clickurl,
                                position = :position,
                                cat = :cat,
                                show_start = :show_start,
                                show_finish = :show_finish,
                                showurl = :showurl,
                                showurl_recursive ='.$aData['showurl_recursive'].',
                                enabled ='.$aData['enabled'].',
                                banner_type ='.$aData['banner_type'].','.$sFlashAddFields.'
                                show_limit = '.$aData['show_limit'].',
                                title = :title,  alt = :alt, description = :description
                            WHERE id='.$nRecordID, array(
                                ':clickurl'   => $aData['clickurl'],
                                ':position'   => $aData['position'],
                                ':cat' => join(',', $aData['cat']),
                                ':show_start' => date('Y-m-d H:i',strtotime($aData['show_start'])),
                                ':show_finish'=> date('Y-m-d H:i',strtotime($aData['show_finish'])),
                                ':showurl'     => $aData['showurl'],
                                ':title'       => $aData['title'], ':alt' => $aData['alt'],
                                ':description' => $aData['description'],
                            ));
                
                do {
                
                    if($aData['banner_type']== BANNERS_TYPE_IMG)
                    {
                        $oUpload = new Upload('bnrimg', false);
                        if(isset($_FILES['bnrimg']) && $_FILES['bnrimg']['error']==4) break;
                        $oUpload->checkIsIMG();
                        if($oUpload->isSuccessfull())
                        {   
                            $this->delImages($nRecordID,array('banner'=>$aData['banner']));
                            
                            $aImgInfo = getimagesize($_FILES['bnrimg']['tmp_name']);
                            $sExtension = image_type_to_extension($aImgInfo[2], false);
                            $sFilename  = func::generateRandomName(5,true,true).'.'.$sExtension;
                            
                            $aPositionInfo = $this->positions[$aData['position']];;
                            if(!isset($aPositionInfo['height']) || !$aPositionInfo['height'])
                            {
                                $aPositionInfo['height'] = false;
                            }
                            if(!$aData['resize_img'])
                            {
                                $aWorkingImg = array('filename'=>BANNERS_PATH.$nRecordID.'_work_'.$sFilename,
                                            'width' => $aImgInfo[0], 
                                            'height' => $aImgInfo[1],
                                            'autofit'=>true, 
                                            'crop_v'=>'center',
                                            'crop_h'=>'center'
                                            );
                            }
                            else{
                                $aWorkingImg = array('filename'=>BANNERS_PATH.$nRecordID.'_work_'.$sFilename,
                                            'width' => $aPositionInfo['width'], 
                                            'height' => $aPositionInfo['height'],
                                            'autofit'=>true, 
                                            'crop_v'=>'center',
                                            'crop_h'=>'center'
                                            );
                            }
                            $aParams = array(array('filename'=>BANNERS_PATH.$nRecordID.'_th_'.$sFilename,
                                            'width' => 100, 
                                            'height' => false,
                                            'autofit'=>true, 
                                            'crop_v'=>'center',
                                            'crop_h'=>'center'
                                            ),($aWorkingImg?$aWorkingImg:'')
                            );

                            $oThumb = new CThumbnail($_FILES['bnrimg']['tmp_name'],false);
                            $oThumb->save($aParams);
                            
                            $this->db->execute('UPDATE '.TABLE_BANNERS.'
                                       SET banner='.$this->db->str2sql($sFilename).'
                                       WHERE id='.$nRecordID);
                        }
                    }
                    elseif($aData['banner_type'] == BANNERS_TYPE_FLASH)
                    {
                        $fUpload = new Upload('flash', false);
                        if(!empty($fUpload->filename))
                        {
                            $this->delImages($nRecordID, array('banner'=>$aData['banner']));
                            $fUpload->save(BANNERS_PATH, $nRecordID.'_src_');
                            $this->db->execute('UPDATE '.TABLE_BANNERS.'
                                       SET banner='.$this->db->str2sql($fUpload->getFilename()).'
                                       WHERE id='.$nRecordID);
                        }
                    }
                    else
                    {
                        $this->db->execute('UPDATE '.TABLE_BANNERS.'
                                   SET banner='.$this->db->str2sql($aData['code']).'
                                   WHERE id='.$nRecordID);
                    }
                
                } while(false);
                
                $this->adminRedirect(Errors::SUCCESSFULL);
            }

            $aData['banner'] = $this->db->one_data('SELECT banner FROM '.TABLE_BANNERS.' WHERE id='.$nRecordID);
        }
        
        $aData['cat'] = explode(',', $aData['cat']);
        if(empty($aData['position']))
            $aData['position'] = Func::SESSION('banner_position');

        $aData['width']  = $this->positions[ $aData['position'] ]['width'];
        $aData['height'] = $this->positions[ $aData['position'] ]['height'];
        
        //prepare link
        $aData['link'] = $this->prepareClickURL( $aData['id'] );
        
        //prepare thumbnail path    
        $aData['img_small'] = '';
        $aData['img_big'] = '';
        $sFilename = $aData['id'].'_th_'.$aData['banner'];

        if(file_exists(BANNERS_PATH.$sFilename)) {
            $aData['img_small'] = BANNERS_URL.'/'.$sFilename;
        }         
        $sFilename = $aData['id'].'_work_'.$aData['banner'];
        if(file_exists(BANNERS_PATH.$sFilename)) {
            $aData['img_big'] = BANNERS_URL.'/'.$sFilename;
        }
        
        $aData['flash'] = unserialize( $aData['flash'] );
        $aData['resize_img'] = 1;
        $aData['date_min'] = date( 'Y,n,d', mktime(0,0,0,date('n')-1, date('d'), date('y')) );
        
        $this->includeJS(array('datepicker'));         
        $this->tplAssign('aCategories', $this->getBBSCategories($aData['cat'], false));
        $this->tplAssign('aPosOptions', $this->positions); 
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.form.tpl');
    }
                    
    function delete()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();

        $nRecordID = Func::POSTGET('rec', false, true);
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE);

        //delete images
        $aFiles = $this->db->one_array('SELECT banner, banner_type FROM '.TABLE_BANNERS.' WHERE id='.$nRecordID);
        if($aFiles['banner_type'] == BANNERS_TYPE_IMG)
        {
            $this->delImages( $nRecordID, $aFiles['banner'] );
        }
        else if($aFiles['banner_type'] == BANNERS_TYPE_FLASH)
        {
            @unlink(BANNERS_PATH.$nRecordID.'_src_'.$aFiles['banner']);
        }
        //delete record
       $this->db->execute('DELETE FROM '.TABLE_BANNERS.' WHERE id='.$nRecordID);
       $this->db->execute('DELETE FROM '.TABLE_BANNERS_STAT.' WHERE id='.$nRecordID);

       $this->adminRedirect(Errors::SUCCESSFULL);
    }
                              
    function showBanner()
    {
        if(!$this->haveAccessTo('listing'))
            return $this->showAccessDenied();
                
        $nRecordID = Func::GETPOST('rec', false, true);
        if($nRecordID<=0) $this->ajaxResponse('');

        $aData = $this->db->one_array('SELECT * FROM '.TABLE_BANNERS.' WHERE id = '.$nRecordID);
        $aData['img_thumb'] = BANNERS_URL.'/'.$aData['id'].'_work_'.$aData['banner'];     
        if(file_exists(BANNERS_PATH.$aData['id'].'_work_'.$aData['banner'])) {
            $aData['img_size'] = getimagesize(BANNERS_PATH.$aData['id'].'_work_'.$aData['banner']);
            $aData['img_size'] = $aData['img_size'][0];
        } else {
            $aData['img_size'] = 240;
        }
        
        $aData['flash'] = unserialize( $aData['flash'] );
                             
        $this->tplAssign('aData', $aData);               
        $this->ajaxResponse( $this->tplFetch('admin.banner.show.tpl') );
    }
    
    /*Показать статистику по баннеру*/
    function statistic()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
            
        $nRecordID = Func::POSTGET('rec', false, true);
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE);

        $f = $this->input->getm(array(
            'page'        => TYPE_UINT,
            'order'       => TYPE_STR,
            'cur_day'     => TYPE_BOOL,
            'date_start'  => TYPE_STR,
            'date_finish' => TYPE_STR, 
        )); $sql = array();

        $this->prepareOrder($orderBy, $orderDir, 'period,desc', array('period'=>'asc','shows'=>'asc','clicks'=>'asc','ctr'=>'asc'));
                
        if(!empty($f['date_start']) && !empty($f['date_finish']))    
        {
            if( $f['date_start'] > $f['date_finish'] ){  
                $sql[] = 'BNST.period >= '.$this->db->str2sql(date("Y-m-d",strtotime($f['date_start'])));
                $f['date_finish'] = 0;    
            }
            else{
                $sql[] = 'BNST.period >= '.$this->db->str2sql(date("Y-m-d",strtotime($f['date_start']))).' AND BNST.period<='.$this->db->str2sql(date("Y-m-d",strtotime($f['date_finish'])));        
            }
        } elseif (!empty($f['date_start']) && empty($f['date_finish'])){
            $sql[] = 'BNST.period>='.$this->db->str2sql(date("Y-m-d",strtotime($f['date_start'])));    
        } elseif (empty($f['date_start']) && !empty($f['date_finish'])){
            $sql[] = 'BNST.period<='.$this->db->str2sql(date("Y-m-d",strtotime($f['date_finish'])));    
        }
        
        if(!empty($f['cur_day']))    
        {
            if(!empty($f['date_start']) || empty($f['date_finish'])) {
                $f['date_finish'] = 0;
                $f['date_start'] = 0;
                $sql = array();
            }
            $sql[] = 'BNST.period='.$this->db->str2sql(date('Y-m-d'));    
        }
        
        $sql = (!empty($sql) ? ' AND '.join(' AND ', $sql).' ' : '' );
        
        $nCount = $this->db->one_data('SELECT count(BNST.id) FROM '.TABLE_BANNERS_STAT.' BNST
                                  LEFT JOIN '.TABLE_BANNERS.' BN ON BN.id= BNST.id
                                  WHERE BNST.id='.$nRecordID.$sql);
        //generate pagenation
        $this->generatePagenation($nCount, 30, 'index.php?s='.$this->module_name.'&ev=statistic&rec='.$nRecordID.'&{pageId}', $sSqlLimit);    
        
        $aData = $this->db->select('SELECT BNST.*,BN.* FROM '.TABLE_BANNERS_STAT.' BNST
                               LEFT JOIN '.TABLE_BANNERS.' BN ON BN.id= BNST.id
                               WHERE BNST.id='.$nRecordID.$sql.'  
                               ORDER BY BNST.period DESC '.$sSqlLimit);
        if($aData)
        {
            foreach($aData as $key=>$v)
            {
                $aData[$key]['positioninfo'] = $this->positions[ $v['position'] ];
                $aData[$key]['img_thumb'] = BANNERS_URL.'/'.$v['id'].'_th_'.$v['banner'];
                $aData[$key]['ctr'] = round(($v['clicks']/($v['shows']?$v['shows']:1))*100,2);
                $aData[$key]['cur_day'] = $f['cur_day'];
            } 
            
            if($orderBy)  {
                $aData = func::array_subsort($aData, $orderBy,($orderDir=='asc'?SORT_ASC:SORT_DESC) , ($orderBy=='period'?SORTDATE:SORT_NUMERIC));    
            } 
            
            $aData[0]['date_start'] = $f['date_start'];
            $aData[0]['date_finish']= $f['date_finish'];
            $bNorecords = false;
        }    
        else{
            $aData = $this->db->select('SELECT * FROM '.TABLE_BANNERS.' WHERE id='.$nRecordID);
            $aData[0]['positioninfo'] = $this->positions[ $aData[0]['position'] ];
            $aData[0]['img_thumb'] = BANNERS_URL.'/'.$aData[0]['id'].'_th_'.$aData[0]['banner'];
            $aData[0]['cur_day'] = $f['cur_day'];
            $bNorecords = true;
        }   
                                             
        $this->includeJS(array('datepicker'));
        $this->adminIsListing();
         
        $this->tplAssign('aData', $aData);
        $this->tplAssign('f',     $f);
        $this->tplAssign('bNorecords', $bNorecords);
        $this->tplAssign('cur_page',   $f['page']);
        
        return $this->tplFetch('admin.statistic.tpl'); 
    }
    
    function checkRotation($sKeyword = '')
    {
       if($sKeyword)
       {
           $nBnCnt = $this->db->one_data('SELECT COUNT(id) FROM '.TABLE_BANNERS.' WHERE position ='.$this->db->str2sql($sKeyword).' AND enabled=1');
           if($this->positions[$sKeyword]['rotation']==0 && $nBnCnt!=0)
           {
               return false;
           }
           else{
               return true;
           }
       }
       return false; 
    }
    
    # Позиции баннеров
    
    function position_listing()
    {
        if( !$this->haveAccessTo('listing') )
            return $this->showAccessDenied();
                                                                       
        foreach($this->positions as &$b)  {
            $b['banner_cnt'] = 0;
        }    
 
        $aBanners = $this->db->select('SELECT position, COUNT(id) as cnt FROM '.TABLE_BANNERS.' WHERE '.$this->db->prepareIN('position', array_keys($this->positions)).' GROUP BY position');
        foreach($aBanners as $v) {
            if(isset($this->positions[$v['position']]))
                $this->positions[$v['position']]['banner_cnt'] = $v['cnt'];
        }

        $this->tplAssignByRef('aData', $this->positions);
        return $this->tplFetch('admin.positions.listing.tpl');
    }
    
    function position_add()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
        
        $aData = $this->input->postm(array(
            'width'   => TYPE_UINT,
            'height'  => TYPE_UINT,
            'keyword' => TYPE_STR,
            'title'   => TYPE_STR,
            'rotation' => TYPE_BOOL,
            'show_to_reguser' => TYPE_BOOL,
            'enabled' => TYPE_BOOL,
        ));
           
        if(bff::$isPost)
        {                                                                    
            //if(!$aData['width']) $this->errors->set('no_position_width');
            //if(!$aData['height']) $this->errors->set('no_position_height');
            if(!$aData['keyword']) $this->errors->set('no_position_keyword');
            if(empty($aData['title'])) $this->errors->set('no_position_title');

            if(!isset($this->positions[$aData['keyword']])) {
                $this->positions[$aData['keyword']] = $aData;     
            }
            else {
                $this->errors->set('keyword_exist');
            }
            
            if($this->positions) {
                $this->positions = func::array_subsort($this->positions, 'title', SORT_ASC, SORT_STRING);    
            }
            
            if($this->errors->no())
            {
                $this->savePositions($this->positions);
                $this->adminRedirect(Errors::SUCCESSFULL, 'position_listing');
            }   
        }
        
        $aData['edit'] = 0;
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.positions.form.tpl');
    }
    
    function position_edit()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
        
        $sPosKey = Func::POSTGET('key', true);
        if(!isset($this->positions[$sPosKey])) {
            $this->adminRedirect('undefined_key', 'position_listing');
        }
        
        $aData = $this->positions[$sPosKey];    
            
        if(bff::$isPost)
        {
            $this->input->postm(array(
                'title'   => TYPE_STR,
                'rotation' => TYPE_BOOL,
                'show_to_reguser' => TYPE_BOOL,
                'enabled' => TYPE_BOOL,
            ), $aData);
            
            if(FORDEV)
            {                                                             
               $this->input->postm(array(
                'width'   => TYPE_UINT,
                'height'  => TYPE_UINT,
                'keyword' => TYPE_STR,
                ), $aData);   
                          
                if(!$aData['width']) $this->errors->set('no_position_width');
                if(!$aData['height']) $this->errors->set('no_position_height');
                if(!$aData['keyword']) $this->errors->set('no_position_keyword');    
            }
            
            if(empty($aData['title'])) $this->errors->set('no_position_title');
            
            if($this->errors->no())
            {
                unset($this->positions[$sPosKey]);
                if(isset($this->positions[$aData['keyword']])) {
                    $this->errors->set('keyword_exist');
                }
                else {
                    $this->positions[$aData['keyword']] = $aData;                         
                     $this->positions = func::array_subsort($this->positions, 'title', SORT_ASC, SORT_STRING);
                }
            }
            if($this->errors->no())
            {
                $this->savePositions($this->positions);
                $this->adminRedirect(Errors::SUCCESSFULL, 'position_listing');
            }   
        }
        
        $aData['edit'] = 1;         
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.positions.form.tpl');
    }
   
    function position_confirm_delete()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
        
        $sPosKey = Func::POSTGET('key', true);
        if(!isset($this->positions[$sPosKey])) $this->errors->set('undefined_key');
        
        $aData = $this->positions;
        unset($aData[$sPosKey]);
        $aCurInfo['banners_count'] = $this->db->one_data('SELECT COUNT(id) as cnt FROM '.TABLE_BANNERS.' WHERE position = '.$this->db->str2sql($sPosKey));    
        if($aCurInfo['banners_count']==0) {
            $this->position_delete($sPosKey);
        }
        
        $aCurInfo['pos_title'] = $this->positions[$sPosKey]['title'];
        $aCurInfo['pos_keyword'] = $sPosKey;
        if(bff::$isPost)
        {
            $sNewPosition = Func::POST('position',true);
            if($sNewPosition == '0')
            {
                $this->errors->set('no_position');
            }
            else{
                $this->db->execute('UPDATE '.TABLE_BANNERS.' SET position ='.$this->db->str2sql($sNewPosition).' WHERE position = '.$this->db->str2sql($sPosKey));
                $this->position_delete($sPosKey);
            }
        }
        
        $this->tplAssign('aCurInfo', $aCurInfo);
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.positions.delconfirm.tpl');
    }
    
    function position_del_all()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();

        $sPosKey = Func::POSTGET('key', true);
        if(!isset($this->positions[$sPosKey])) $this->errors->set('undefined_key');

        $this->db->execute('DELETE FROM '.TABLE_BANNERS.' WHERE position = '.$this->db->str2sql($sPosKey));

        $this->position_delete($sPosKey);
    }
    
    private function position_delete($sPosKey)
    {
        if($this->errors->no())
        {
            unset($this->positions[$sPosKey]);

            $this->savePositions($this->positions);

            $this->adminRedirect(Errors::SUCCESSFULL, 'position_listing');
        }
        $this->adminRedirect(Errors::IMPOSSIBLE,'position_listing');
    }
    
    function ajax()
    {
        switch(Func::POSTGET('act'))
        {
            case 'position_toggle':
            {
               if( !$this->haveAccessTo('edit') )
                    $this->ajaxResponse(Errors::ACCESSDENIED);
                    
               $sKey = Func::POST('keyword',true);
               if(empty($sKey) || !isset($this->positions[$sKey])){
                   $this->errors->set(Errors::IMPOSSIBLE);
                   $this->ajaxResponse(null);
               } 
               else
               {
                    $this->positions[$sKey]['enabled'] = ($this->positions[$sKey]['enabled']==1?0:1);
                    $this->savePositions($this->positions);
                    $this->ajaxResponse(($this->positions[$sKey]['enabled']==1?'Y':'N'));
               }
            } break;
            case 'banner_toggle':
            {
                if( !$this->haveAccessTo('edit') )
                    $this->ajaxResponse(Errors::ACCESSDENIED);

                $nRecordID = Func::POSTGET('rec', false, true);
                if(!$nRecordID) $this->ajaxResponse(Errors::IMPOSSIBLE);

                $aBnInfo = $this->db->one_array('SELECT position, enabled FROM '.TABLE_BANNERS.' WHERE id ='.$nRecordID);
                /* Проверка возможно ли включить баннер( не используется ли на неротируемой позиции другой баннер) */
                if($aBnInfo['enabled'] == 0 && $this->checkRotation($aBnInfo['position']))
                {
                    $this->db->execute('UPDATE '.TABLE_BANNERS.' SET enabled= 1 WHERE id='.$nRecordID);
                }
                elseif($aBnInfo['enabled']==1)
                {
                    $this->db->execute('UPDATE '.TABLE_BANNERS.' SET enabled= 0 WHERE id='.$nRecordID);
                }
                else{
                    $this->errors->set('no_rotation');
                    $this->ajaxResponse(0);        
                } 
                $this->ajaxResponse(($aBnInfo['enabled']==0?'Y':'N'));    
            }break;
        }
        
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
    
}
