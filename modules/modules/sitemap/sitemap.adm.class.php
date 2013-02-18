<?php

class Sitemap extends SitemapBase
{
	var $aTargets = array(
            array('value'=>'_self',  'text'=>'в текущем окне'),
            array('value'=>'_blank', 'text'=>'в новом окне'),
        );
    
	function main()
	{
        return $this->listing();
	}

	function listing()
	{
        if( !$this->haveAccessTo('listing') )
            return $this->showAccessDenied();

		$aData = $this->db->select('SELECT *, IF(T.numright-T.numleft>1,1,0) as node
                               FROM '.TABLE_SITEMAP_TREE.' T, '.TABLE_SITEMAP.' I
                               WHERE I.node_id = id AND T.pid!=0
                               ORDER BY T.numleft');
		
		for($i=0;$i<count($aData); $i++) {
			if($aData[$i]['menu_link'] && mb_strpos($aData[$i]['menu_link'], 'http://')===FALSE)
			    $aData[$i]['menu_link'] = SITEURL.'/'. $aData[$i]['menu_link'];
		}  

		$this->tplAssignByRef('aData', $aData); 
		return $this->tplFetch('admin.listing.tpl');
	}
      
	function add()
	{
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
        
        $nParentID = Func::POSTGET('pid', false, true);    
		$aData = array('title'=>'', 'pid'=>$nParentID, 'keyword'=>'', 'keyword'=>'', 'menu_link'=>'', 'menu_target'=>'', 'mkeywords'=>'', 'mdescription'=>'');

		if(Func::isPostMethod())
		{
			Func::setSESSION('pid', $nParentID);
            $sKeyword = Func::POST('keyword', true);
		    $sMenuTarget = Func::POST('target');
            if(!in_array($sMenuTarget, array('_self','_blank')))
                $sMenuTarget = '_self';
                
            $sType = Func::POST('type');
            
			switch ($sType)
			{
				case 'menu_type':
                    {
					    $sMenuTitle       = Func::POST('menu_title4', true);
					    $sMetaKeywords    = Func::POST('mkeywords4', true);
					    $sMetaDescription = Func::POST('mdescription4', true);
					    $sMenuLink        = '#';
                    }
					break;
				case 'page':
                    {
    					$nPageID = Func::POST('page_id', false, true);
    					$aPageInfo = $this->db->one_array('SELECT * FROM '.TABLE_PAGES.' WHERE id='.$nPageID.' LIMIT 1');
    					if(!$aPageInfo) $this->adminRedirect(Errors::IMPOSSIBLE);
    				
    					$sMenuTitle = Func::POST('menu_title3', true);
    					if(!$sMenuTitle) $sMenuTitle = $aPageInfo['title'];
    					
    					$sMetaKeywords    = $aPageInfo['mkeywords'];
    					$sMetaDescription = $aPageInfo['mdescription'];
    					$sMenuLink        = $aPageInfo['filename'].PAGES_EXTENSION;
                    }
					break;
                case 'link':
				default:
                    {
					    $sMenuTitle       = Func::POST('menu_title2', true);
					    $sMenuLink        = Func::POST('menu_link2', true);
					    $sMetaDescription = Func::POST('mdescription2', true);
					    $sMetaKeywords    = Func::POST('mkeywords2', true);
                    }
			}

            $_POST['menu_title']   = $sMenuTitle;
            $_POST['menu_link']    = $sMenuLink;
            $_POST['mdescription'] = $sMetaDescription;
            $_POST['mkeywords']    = $sMetaKeywords;
            
            if(!$sMenuTitle || !trim($sMenuTitle)) 
                $this->errors->set('empty:title');
            
			if($this->errors->no())
			{
				$nNodeID = $this->tree_insertNode($nParentID);
				$this->addItem($nNodeID, $sMenuTitle, $sKeyword, $sMenuLink, $sMetaKeywords, $sMetaDescription, $sMenuTarget);
				
                $this->adminRedirect(Errors::SUCCESS);
            }             
            
            $aData = $_POST;
		}                   

        if(!$nParentID) $nParentID = Func::SESSION('pid');
        
        //parent options
        $sParentOptions ='';
        $aItems = $this->db->select('SELECT I.menu_title, T.id, T.numlevel, I.keyword FROM '.TABLE_SITEMAP_TREE.' T, '.TABLE_SITEMAP.' I
                                WHERE T.id=I.node_id AND T.numlevel<=2 
                                ORDER BY T.numleft');
		foreach($aItems as $v)
		{
            if(!empty($v['keyword']))
			$sParentOptions .= '<option value="'.$v['id'].'" style="padding-left:'.($v['numlevel']*18).'px;" 
                                    '.($nParentID == $v['id']?' selected':'').'>'.$v['menu_title'].'</option>';
		}
        
		$this->tplAssign('aData',      $aData);
		$this->tplAssign('pid_options',    $sParentOptions);
		$this->tplAssign('target_options', $this->getTargetsOptions($this->aTargets, $aData['menu_target']) );  
		$this->tplAssign('pages_options',  func::MakeOptionsListEx($this->db->select('SELECT * FROM '.TABLE_PAGES), Func::POSTGET('page_id', false, true), 'title', 'id') );   
		return $this->tplFetch('admin.add.tpl');
	}
    
    function edit()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
       
        if(($nRecordID = Func::POSTGET('rec', false, true))<=0) 
            $this->adminRedirect(Errors::IMPOSSIBLE);
            
        $aData = array('pid_options'=>'');

        if(Func::isPostMethod())
        {
            $sMenuTitle       = Func::POST('menu_title', true);
            $sKeyword         = Func::POST('keyword', true);
            $sMetaKeywords    = Func::POST('mkeywords', true);
            $sMetaDescription = Func::POST('mdescription', true);
            $sMenuTarget      = Func::POST('menu_target');
            if(!in_array($sMenuTarget, array('_self','_blank')))
                $sMenuTarget = '_self';
            
            $sMenuLink        = Func::POST('menu_link', true);
            if(!$sMenuTitle || !trim($sMenuTitle)) 
                $this->errors->set('empty:title');

            if($this->errors->no())
            {
                $sQuery = 'UPDATE '.TABLE_SITEMAP.'
                            SET menu_title = '.$this->db->str2sql($sMenuTitle).',
                                '.(FORDEV?'keyword = '.$this->db->str2sql($sKeyword).', ':'').'
                                menu_link = '.$this->db->str2sql($sMenuLink).',   
                                menu_target='.$this->db->str2sql($sMenuTarget).',
                                mkeywords = '.$this->db->str2sql($sMetaKeywords).',
                                mdescription = '.$this->db->str2sql($sMetaDescription).'
                            WHERE node_id='.$nRecordID;
                $this->db->execute($sQuery);    
                
                $this->adminRedirect(Errors::SUCCESSFULL);
            }
            $aData = $_POST;
            $aData['id'] = $nRecordID;
        }
        else
        {
            $sQuery = 'SELECT *
                        FROM '.TABLE_SITEMAP_TREE.' T,
                             '.TABLE_SITEMAP.' I
                        WHERE T.id='.$nRecordID.' AND I.node_id = T.id';
            $aData = $this->db->one_array($sQuery);
            $aData = func::array_2_htmlspecialchars($aData);
        }


        $aParentsID = $this->tree_getNodeParentsID($aData['id']); 
        if(!empty($aParentsID)) {
            $sQuery = 'SELECT menu_title
                       FROM '.TABLE_SITEMAP_TREE.' T,
                            '.TABLE_SITEMAP.' I
                       WHERE id IN ('.implode( ',', $aParentsID).') AND I.node_id = T.id
                       ORDER BY T.id';
            $aData['pid_options'] = ''.ucwords(implode(' > ', $this->db->select_one_column($sQuery) )).'';
        }

        $this->tplAssign('aData', $aData);  
        $this->tplAssign('target_options', $this->getTargetsOptions($this->aTargets, $aData['menu_target']) );
        $this->tplAssign('rec', $nRecordID);   
        return $this->tplFetch('admin.edit.tpl');
    }
    
    function actions()
    {
        if( !$this->haveAccessTo('edit') )
            return $this->showAccessDenied();
            
        $sAction = Func::POSTGET('action');
            
        $nRecordID = Func::POSTGET('rec', false, true);
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE);
        
        switch($sAction)
        {
            case 'delete':
            {
                $aDeleteItemsID = $this->tree_deleteNode($nRecordID);
                if(!$aDeleteItemsID)
                    $this->adminRedirect(Errors::IMPOSSIBLE);
                
                $this->db->execute('DELETE FROM '.TABLE_SITEMAP.' WHERE node_id IN ('.implode(',', $aDeleteItemsID).')');            
            }break;
            case 'enable':
            {
                $this->tree_toggleNodeEnabled($nRecordID, true);
            }break;
            case 'move':
            {
                $bResult = false;
                $sDirection = Func::POSTGET('dir');
                if($sDirection == 'up')
                    $bResult = $this->tree_moveNodeUp($nRecordID);
                elseif($sDirection == 'down')
                    $bResult = $this->tree_moveNodeDown($nRecordID);
                
                if(!$bResult)
                     $this->adminRedirect(Errors::IMPOSSIBLE);
                    
            }break;
        }
        
        $this->adminRedirect(Errors::SUCCESSFULL);                
    } 

    function treevalidate()
    {
        $this->adminIsListing();
        return $this->tree_validate(true);
    }
}
