<?php

abstract class SitemapBase extends Module
{
	var $securityKey = 'a5bc36ff371a593b04c14a786c7eea79';

    public function init()
    {
        parent::init();
        
        //nested sets tree
        $tree = $this->attachComponent('tree', new dbNestedSetsTree(TABLE_SITEMAP_TREE) );  
        
//        //проверяем наличие корневого элемента
//        if( $tree->checkRootNode( $nRootID ) )
//        {
            //если только-что создан, создаем и в "недеревянной" таблице
//            $this->db->execute('INSERT INTO '.TABLE_SITEMAP.' (node_id, keyword, menu_title, created)
//                           VALUES('.$nRootID.', '.$this->db->str2sql('root').', '.$this->db->str2sql('Корневой раздел').', '.$this->db->getNOW().')');
//        }
    }
    
	function addItem($nNodeID, $sTitle, $sKeyword='', $sMenuLink = '', $sMetaKeywords='', $sMetaDescription='', $sTarget="_self")
	{
        $nNodeID = (integer)$nNodeID;
		$sQuery = 'INSERT INTO '.TABLE_SITEMAP.'
                    (node_id, menu_title, keyword, menu_link, menu_target, mdescription, mkeywords, created)
                    VALUES ('.$nNodeID.', '.$this->db->str2sql($sTitle).', '.$this->db->str2sql($sKeyword).', '.$this->db->str2sql($sMenuLink).', 
                    '.$this->db->str2sql($sTarget).', '.$this->db->str2sql($sMetaDescription).', '.$this->db->str2sql($sMetaKeywords).",
                    {$this->db->getNOW()})";
		return $this->db->execute($sQuery);
	}
    
    function getTargetsOptions($aTargets, $sCurTarget='_self')
    {
        return func::MakeOptionsListEx($aTargets, $sCurTarget, 'text', 'value');
    }

}