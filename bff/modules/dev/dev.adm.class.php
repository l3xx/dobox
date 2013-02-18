<?php

class Dev extends DevBase
{
    function sys()
    {
        if(!FORDEV)
            return $this->showAccessDenied();
        
        $aData = array();
            
        $aData['maxmemory'] = (@ini_get('memory_limit')!='') ? @ini_get('memory_limit') : 'Неопределено';
        $aData['disabledfunctions'] = (strlen(ini_get('disable_functions')) > 1) ? @ini_get('disable_functions') : 'Неопределено';
        $aData['disabledfunctions'] = str_replace (",", ", ", $aData['disabledfunctions']);
        $aData['safemode'] = (@ini_get('safe_mode') == 1) ? 'Включен' : 'Выключен';

        //mod_rewrite
        if(function_exists('apache_get_modules')) {
          if (array_search('mod_rewrite', apache_get_modules()))
          {
            $aData['mod_rewrite'] = 'Включен';
          } else {
            $aData['mod_rewrite'] = '<font color="red">Выключен</font>';
          }
        } else {
            $aData['mod_rewrite'] = 'Неопределено';
        }
        
        //mcrypt
        $aData['extension_mcrypt'] = (extension_loaded("mcrypt")?'Включен':'<font color="red">Выключен</font>');
                                            
        //imap
        $aData['extension_imap'] = (extension_loaded("imap")?'Включен':'<font color="red">Выключен</font>');
                   
        //pdo
        $aData['extension_pdo'] = (extension_loaded("pdo") ? 'Включен ('.implode(', ', PDO::getAvailableDrivers()).')' : '<font color="red">Выключен</font>');

        //spl
        $aData['extension_spl'] = (extension_loaded("spl")?'Включен (autoload, iterators...)':'<font color="red">Выключен</font>');
        
        $aData['os_version'] = php_uname ("s")." ".php_uname ("r");
        $aData['php_version'] = phpversion(); 
        
        //mysql version
        if(extension_loaded('mysql'))
        {
            if(function_exists('mysql_get_client_info'))
            {
                $current_version = mysql_get_client_info();
                // Process version
                preg_match('/[0-9.]+/', $current_version, $version_matches);
                $aData['mysql'] = $version_matches[0];
            } else {
                $aData['mysql'] = 'Включено';
            }
        } else {
            $aData['mysql'] = '<font color="red">Выключено</font>';
        }
        
        //gd version
        static $gd_version_number = null;
        if ($gd_version_number === null) {
            ob_start();
            phpinfo(INFO_MODULES);
            $module_info = ob_get_contents();
            ob_end_clean();
            if (preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i",
                    $module_info, $matches)) {
                $gdversion_h = $matches[1];
            } else {
                $gdversion_h = 0;
            } 
        } 
        $aData['gd_version'] = $gdversion_h;
        $aData['mbstring'] = (extension_loaded('mbstring')?'Включено':'<font color="red">Выключено</font>');
        
        $aData['maxexecution'] = @ini_get('max_execution_time');
        
        //upload_max_filesize
        $aData['maxupload'] = str_replace(array('M','m'), '', @ini_get('upload_max_filesize'));
        $aData['maxupload'] = func::getfilesize($aData['maxupload']*1024*1024);
        
        //post_max_size
        $aData['maxpost'] = str_replace(array('M','m'), '', @ini_get('post_max_size'));
        $aData['maxpost'] = func::getfilesize($aData['maxpost']*1024*1024);

        //Can we find Imagemagick anywhere on the system?
        $exe = ( (strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/') == '\\') ? '.exe' : '';
        $magic_home = getenv('MAGICK_HOME');
        $aData['img_imagick'] = '';
        if (empty($magic_home))
        {
            $locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');
            $path_locations = str_replace('\\', '/', (explode(($exe) ? ';' : ':', getenv('PATH'))));

            $locations = array_merge($path_locations, $locations);
            foreach ($locations as $location)
            {
                // The path might not end properly, fudge it
                if(substr($location, -1, 1) !== '/')
                    $location .= '/';

                if(@file_exists($location) && @is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
                {
                    $aData['img_imagick'] = str_replace('\\', '/', $location);
                    continue;
                }
            }
        }
        else
        {
            $aData['img_imagick'] = str_replace('\\', '/', $magic_home);
        }    
        
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.system.tpl'); 
    }

    function module_create()
    {
        if(!FORDEV)
            return $this->showAccessDenied();
        
        //получаем список существующих модулей       
        $aModules = CDir::getDirs(PATH_MODULES, false, false, false);
        foreach($aModules as $k=>$v) {
            if($v{0}!='.' && $v{0}!='_')   
                $aModules[$v] = $v;
            unset($aModules[$k]);   
        }
        $aData = array('modules' => $aModules, 'title'=>'', 'languages'=>'');
        
        if(func::isPostMethod())
        {
            $aData['title'] = mb_strtolower(func::POST('title', true));
            $aData['languages'] = func::POST('languages', true);
            $aData['aLanguages'] = (!empty($aData['languages'])? explode(',', $aData['languages']) : array(LANG_DEFAULT) );
            
            do{
                if(empty($aData['title'])) {
                    $this->errors->set('no_title');
                    break;
                }
                
                if(in_array($aData['title'], $aData['modules'])) {
                    $this->errors->set('title_exists');
                    break;
                }
                
                $sModuleName = ucfirst( $aData['title'] );
                $sModuleFileName = mb_strtolower($sModuleName);

                $sModulesPath = PATH_MODULES;      
                if(file_exists($sModulesPath.$sModuleFileName.DIRECTORY_SEPARATOR.$sModuleName.'.class.php')) {
                    $this->errors->set('title_exists');
                    break;
                }

                $sModuleDirectory = $sModulesPath.$sModuleFileName.DIRECTORY_SEPARATOR;
                if(!@mkdir( $sModuleDirectory, 0666)) {
                    $this->errors->set('create_dir_error', '', false, $sModulesPath.$sModuleFileName);
                    break;
                }
                
                //create Template Directories
                if(!@mkdir($sModuleDirectory.'tpl', 0666)){
                    $this->errors->set('create_dir_error', '', false, $sModuleDirectory.'tpl');
                    break;
                }
                foreach($aData['aLanguages'] as $lng)
                    @mkdir($sModuleDirectory.'tpl'.DIRECTORY_SEPARATOR.$lng.DIRECTORY_SEPARATOR, 0666);

                //create Language Files [+directory]
                if(!@mkdir($sModuleDirectory.'lang', 0666)){
                    $this->errors->set('create_dir_error', '', false, $sModuleDirectory.'lang');
                    break;
                }
                foreach($aData['aLanguages'] as $lng)
                    CDir::putFileContent($sModuleDirectory.'lang'.DIRECTORY_SEPARATOR."$lng.inc.php", "<?php\n".($lng!='def'?"include_once 'def.inc.php';":'')."\n\n");
                                                                                       
                //create BL file
                if(!CDir::putFileContent($sModuleDirectory.$sModuleFileName.'.bl.class.php', "<?php\n\nabstract class {$sModuleName}Base extends Module\n{\n    var \$securityKey = '".md5(uniqid($sModuleName))."';\n}\n")) {
                    $this->errors->set('create_file_error', '', false, $sModuleFileName.'.bl.class.php' );
                    break;
                }  

                //create Menu file
                if(!CDir::putFileContent($sModuleDirectory.'m.'.$sModuleFileName.'.class.php', "<?php\n\nclass M_$sModuleName\n{\n    function declareAdminMenu()\n    {\n        global \$oMenu;\n\n        \$oMenu->assign('$sModuleName', 'Список', '$sModuleFileName', 'listing', true, 1);\n\n    }\n\n}\n")) {
                    $this->errors->set('create_file_error', '', false, 'm.'.$sModuleFileName.'.class.php');
                    break;
                }  

                //create Install.SQL file
                if(!CDir::putFileContent($sModuleDirectory.'install.sql', "")) {
                    $this->errors->set('create_file_error', '', false, 'install.sql');
                    break;
                }  
                
                //[create Admin directory]
                $sModuleAdmDirectory = $sModuleDirectory;
                                             
                //create Admin file
                if(!CDir::putFileContent($sModuleAdmDirectory.$sModuleFileName.'.adm.class.php', "<?php\n\nclass $sModuleName extends {$sModuleName}Base\n{\n\n\n}\n")) {
                    $this->errors->set('create_file_error', '', false, $sModuleFileName.'.adm.class.php');
                    break;
                }  
                                        
                //create Frontend file
                if(!CDir::putFileContent($sModuleDirectory.$sModuleFileName.'.class.php', "<?php\n\nclass $sModuleName extends {$sModuleName}Base\n{\n\n\n}\n")) {
                    $this->errors->set('create_file_error', '', false, $sModuleFileName.'.class.php');
                    break;
                }
                
                $this->adminRedirect(Errors::SUCCESSFULL, 'module_create');
                
            }while(false);
        }
                                           
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.module.create.tpl');
    }

    function dirs_listing()
    {
        if(!FORDEV)
            return $this->showAccessDenied();
        
        $aData = array('dirs'=>array());
        
        //список папок для проверки                              
    
        $aData['dirs'][] = PATH_BASE.'files/bnnrs';
        $aData['dirs'][] = PATH_BASE.'files/mail';
        $aData['dirs'][] = PATH_BASE.'files/images/avatars'; 
        $aData['dirs'][] = PATH_BASE.'files/lock';    
        $aData['dirs'][] = PATH_BASE.'files/pages';

        clearstatcache();
        
        foreach($aData['dirs'] as &$v)
        {       
            $dir = $v;                    
            $v = array('path'=>str_replace(PATH_BASE, '', $dir), 'access'=>0);
            if(!file_exists($dir))
                $v['access'] = 1;
            else
            {
                if(!is_writable($dir))
                    $v['access'] = 2;
            }
                
        }        
        
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.dirs.tpl'); 
    }
    
    function phpinfo1()
    {
        echo phpinfo();
        exit();
    }

    function sysword()
    {
        if(!FORDEV)
            return $this->showAccessDenied();

        return $this->tplFetch('admin.sysword.tpl');
    }

    
    //-------------------------------------------------------------------
    // module methods
    
    function mm_listing()
    {
        if(!FORDEV || !$this->security->isSuperAdmin())
            return $this->showAccessDenied();

        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'rotate': 
                {
                    $res = $this->db->rotateTablednd(TABLE_MODULE_METHODS, '', 'id', 'number');
                    $this->ajaxResponse( $res ? Errors::SUCCESS : Errors::IMPOSSIBLE );
                } break;
                case 'delete':
                {
                    if(!($nRecordID = $this->input->id('rec', 'p'))) 
                        break;
                        
                    $aResult = $this->db->one_array('SELECT *
                               FROM '.TABLE_MODULE_METHODS.'
                               WHERE id = '.$nRecordID);
                    
                    if(empty($aResult))
                       $this->ajaxResponse(Errors::IMPOSSIBLE);

                    if($aResult['module'] == $aResult['method'])
                    {
                        //если модуль, получаем методы
                        $aMethodsID = $this->db->select_one_column('SELECT id FROM '.TABLE_MODULE_METHODS.'
                                   WHERE module='.$this->db->str2sql($aResult['module']).' AND module!=method
                                   ORDER BY number, id');                
                        //удалить методы
                        $this->db->execute('DELETE FROM '.TABLE_MODULE_METHODS.' WHERE id IN ('.implode(',', $aMethodsID).')'); 
                        $this->db->execute('DELETE FROM '.TABLE_USERS_GROUPS_PERMISSIONS.' WHERE item_type = '.$this->db->str2sql('module').' and item_id IN ('.implode(',', $aMethodsID).')');
                    }
                    //удалить модули и методы 
                    $this->db->execute('DELETE FROM '.TABLE_MODULE_METHODS.' WHERE id = '.$nRecordID);
                    $this->db->execute('DELETE FROM '.TABLE_USERS_GROUPS_PERMISSIONS.' WHERE unit_type='.$this->db->str2sql('group').' AND item_type='.$this->db->str2sql('module').' AND item_id='.$nRecordID);
                    
                    $this->ajaxResponse(Errors::SUCCESS);                    
                } break;
            }                                       
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        }
            
        $aData = $this->db->select(' SELECT M.*, 1 as numlevel
                    FROM '.TABLE_MODULE_METHODS.' M  
                    WHERE M.module=M.method
                    ORDER BY M.number, M.id');
                    
        $aSubData = $this->db->select('SELECT M.*, 2 as numlevel
                    FROM '.TABLE_MODULE_METHODS.' M
                    WHERE M.module!=M.method
                    ORDER BY M.number, M.id');
        $aSubData = Func::array_transparent($aSubData, 'module');
        
        for ($i=0; $i<count($aData); $i++) {
            $aData[$i]['subitems'] = array();
            if(isset($aSubData[$aData[$i]['module']])) {
                $aData[$i]['subitems'] = $aSubData[$aData[$i]['module']];
            }
        }

        $this->includeJS('tablednd');
        $this->tplAssign('aData', $aData); 
        return $this->tplFetch('admin.mm.tpl');
    }

    function mm_add()
    {   
        if(!FORDEV || !$this->security->isSuperAdmin())
            return $this->showAccessDenied();
                                         
        $aData = array('method'=>'', 'title'=>'', 'module'=>'');
        if(Func::isPostMethod())
        {            
            $sMethod = Func::POST('method', true);
            $sTitle  = Func::POST('title', true);
            $sModule = $this->db->str2sql(Func::POST('module'));

            Func::setSESSION('save_module', $sModule);
            
            if(!$sMethod) {
                 $sMethod = $sModule;
            }
            if(!$sTitle) {
                $sTitle = ucwords($sModule.' '.$sMethod);
            }

            //get max module number
            $nNumber = (int)$this->db->one_data('SELECT max(number) FROM '.TABLE_MODULE_METHODS." 
                                            WHERE module=$sModule AND method!=$sModule ");
            $nNumber++;
            
            //insert module-method
            $this->db->execute('INSERT INTO '.TABLE_MODULE_METHODS." (module, method, title, number)
                       VALUES ($sModule, ".$this->db->str2sql($sMethod).', '.$this->db->str2sql($sTitle).", $nNumber)");
            
            if($this->errors->no())
                $this->adminRedirect(Errors::SUCCESSFULL, 'mm_listing');
        }
        
        if(!$aData['module']) {
            $aData['module'] = Func::SESSION('save_module');
        }
                                                           
        $aModules = CDir::getDirs(PATH_MODULES, false, false, false);
        foreach($aModules as $k=>$v) {
            if($v{0}!='.' && $v{0}!='_')   
                $aModules[$v] = $v;
            unset($aModules[$k]);   
        }
        
        $this->tplAssign(array('aModules' => $aModules, 'aData' => $aData));
        return $this->tplFetch('admin.mm.create.tpl');
    }
    
}
