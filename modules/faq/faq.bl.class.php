<?php
                  
abstract class FaqBase extends Module 
{
    function getCategoriesOptions($nSelectedID = null, $mAddRootCategory=false, $aExludeID = array())
    {                              
        $sQueryExlude = $this->db->prepareIN('id', $aExludeID, true);
        
        $sOptions ='';

        $aCategories = $this->db->select('SELECT * FROM '.TABLE_FAQ_CATEGORIES.' 
                                     WHERE '.$sQueryExlude.' ORDER BY title');
        foreach($aCategories as $cat)
            $sOptions .= '<option value="'.$cat['id'].'"'.(isset($nSelectedID) && $nSelectedID == $cat['id']?' selected':'').'>'.$cat['title'].'</option>';
        
        if($mAddRootCategory)
             $sOptions = '<option style="font-weight:bold;" value="0"'.(!isset($nSelectedID)?' selected':'').'>'.($mAddRootCategory===true?'корневая категория':$mAddRootCategory).'</option>'.$sOptions;
        
        return $sOptions;
    }
}