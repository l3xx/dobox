<?php

class Pages extends PagesBase
{
	function main()
	{
		return $this->listing();
	}
	                           
	function listing()
	{
        if(!$this->haveAccessTo('listing'))
            return $this->showAccessDenied();
        
		$this->tplAssign('aData', $this->db->select('SELECT id, title, filename, issystem FROM '.TABLE_PAGES.' ORDER BY title') );  
		return $this->tplFetch('admin.listing.tpl');
	}

    function add()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();

        $aData = array('content'=>'', 'title'=>'', 'filename'=>'', 'mkeywords'=>'', 'mdescription'=>'');
        
        if(func::isPostMethod())
        {
            $sTitle = func::POST('title', true);  
            
            $sFilename = func::POST('filename', true);
            $sFilename = str_replace('.html','',$sFilename);
            $sFilename = str_replace('.htm','',$sFilename);
            if(!$sFilename) $sFilename =  str_replace(' ','_', str_replace('"', '', mb_strtolower($sTitle)));
                        
            $sMetaDescription = func::POST('mdescription', true);
            $sMetaKeywords = func::POST('mkeywords', true);
                    
            $sContent = func::POST('content'); 
            $sContent = stripslashes($sContent);
            $sContent = eregi_replace('\\\"','"',$sContent);
            $sContent = eregi_replace('\\"','"',$sContent);
            $sContent = eregi_replace('\"','"',$sContent);

            $sResFilename = $this->db->one_data('SELECT filename FROM '.TABLE_PAGES." WHERE filename=".$this->db->str2sql($sFilename)." LIMIT 1");
            
            if($sResFilename===$sFilename && !empty($sFilename))
                $this->errors->set('change_filename');
            if(empty($sFilename)) 
                $this->errors->set('no_filename');

            if($this->errors->no())
            {
                CDir::putFileContent(PAGES_PATH.$sFilename.PAGES_EXTENSION, $sContent);
                            
                if(BFF_GENERATE_META_AUTOMATICALY)
                {
                    if( (empty($sMetaKeywords) || empty($sMetaDescription) ) && !empty($sContent) )
                    {
                       func::generateMeta($sContent, $aData);
                       if(empty($sMetaDescription))
                            $sMetaDescription = $aData['mdescription'];
                       if(empty($sMetaKeywords))
                            $sMetaKeywords = $aData['mkeywords'];
                    }
                }
                                    
                $this->db->execute('INSERT INTO '.TABLE_PAGES.'
                               (filename, title, mkeywords, mdescription, created, modified) 
                                VALUES ('.$this->db->str2sql($sFilename).', '.$this->db->str2sql($sTitle).',  
                                        '.$this->db->str2sql($sMetaKeywords).', '.$this->db->str2sql($sMetaDescription).', 
                                        '.$this->db->getNOW().', '.$this->db->getNOW().')');

                $this->adminRedirect(Errors::SUCCESSFULL);
            }
            $aData = $_POST;
        }

        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.form.tpl');
    }
        
    function edit()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();

        $aData = array('content'=>'', 'title'=>'', 'filename'=>'');
        
        $nRecordID = func::POSTGET('rec', false, true);
        if($nRecordID<=0) $this->adminRedirect(Errors::IMPOSSIBLE);
        
        if(func::isPostMethod())
        {
            $sFilename        = func::POST('filename', true); 
            $sTitle           = func::POST('title', true);
            $sMetaDescription = func::POST('mdescription', true);
            $sMetaKeywords    = func::POST('mkeywords', true);

            $sContent = stripslashes( func::POST('content') );
            $sContent = eregi_replace('\\\"','"',$sContent);
            $sContent = eregi_replace('\\"','"',$sContent);
            $sContent = eregi_replace('\"','"',$sContent);
            
            $sFilename = $this->db->one_data('SELECT filename FROM '.TABLE_PAGES.' WHERE id='.$nRecordID.' LIMIT 1');
            
            if($this->errors->no())
            {
                CDir::putFileContent(PAGES_PATH.$sFilename.PAGES_EXTENSION, $sContent);
                
                if(BFF_GENERATE_META_AUTOMATICALY)
                {
                    if( (empty($sMetaKeywords) || empty($sMetaDescription) ) && !empty($sContent) )
                    {
                       func::generateMeta($sContent, $aData);
                       if(empty($sMetaDescription))
                            $sMetaDescription = $aData['mdescription'];
                       if(empty($sMetaKeywords))
                            $sMetaKeywords = $aData['mkeywords'];
                    }
                }
                
                $this->db->execute('UPDATE '.TABLE_PAGES.'
                                 SET title = '.$this->db->str2sql($sTitle).', 
                                     mkeywords = '.$this->db->str2sql($sMetaKeywords).',
                                     mdescription = '.$this->db->str2sql($sMetaDescription).", 
                                     modified = {$this->db->getNOW()}
                                 WHERE id=$nRecordID");
                
                $this->adminRedirect(Errors::SUCCESSFULL);
            }
            $aData = $_POST;
        }
        else
        {
            $aData = $this->db->one_array('SELECT * FROM '.TABLE_PAGES.' WHERE id='.$nRecordID.' LIMIT 1');
            $aData['content'] = CDir::getFileContent(PAGES_PATH.$aData['filename'].PAGES_EXTENSION);
        }

        $this->tplAssign('aData', $aData);         
        return $this->tplFetch('admin.form.tpl');
    }    
    
    function action()
    {
        if(!$this->haveAccessTo('edit'))
            return $this->showAccessDenied();

        $nRecordID = func::POSTGET('rec', false, true);
        if($nRecordID<=0) $this->adminRedirect(Errors::IMPOSSIBLE, 'listing');
                                                                  
        $sAction = func::POSTGET('type');
        switch($sAction)
        {
            case 'delete': 
            {
                //delete page file
                $aData = $this->db->one_array('SELECT filename, issystem FROM '.TABLE_PAGES.' WHERE id='.$nRecordID.' LIMIT 1');
                if(!empty($aData) && !$aData['issystem']) {
                    @unlink(PAGES_PATH.$aData['filename'].PAGES_EXTENSION);
                    $this->db->execute('DELETE FROM '.TABLE_PAGES.' WHERE id='.$nRecordID);
                } else {
                    $this->adminRedirect(Errors::ACCESSDENIED, 'listing');
                }
            } break;
        }
            
        $this->adminRedirect(Errors::SUCCESSFULL, 'listing');
    }
}

