<?php
                                   
class Pages extends PagesBase
{
    function show($nPageID = null)
    {
        $nRecordID = (!isset($nPageID)? func::POSTGET('page'):(integer)$nPageID);
       
        $aData = $this->db->one_array('SELECT title, mkeywords, mdescription, filename 
                                  FROM '.TABLE_PAGES.' 
                                  WHERE filename = '.$this->db->str2sql($nRecordID).' 
                                  LIMIT 1');  
        if(empty($aData))
            Errors::httpError(404);
                                  
        //get page content
        $aData['content'] = CDir::getFileContent(PAGES_PATH.$aData['filename'].PAGES_EXTENSION);
        
        config::set(array(
                'title' => $aData['title'].' | '.config::get('title', ''),
                'mkeywords' => $aData['mkeywords'],
                'mdescription' => $aData['mdescription'],
            ));
        
        if($aData['content'] === false)
            Errors::httpError(404);
        
        $aData['menu'] = bff::i()->Sitemap_getmenu('info', 'all-sub');
                 // echo '<pre>', print_r($aData['menu'], true), '</pre>'; exit;
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('page.tpl');
    }
}
