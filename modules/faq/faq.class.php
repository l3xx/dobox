<?php

class Faq extends FaqBase
{
    function view()
    {
        $nQuestionID = $this->input->id();
        
        $aData = array();   
        
        $aCats = $this->db->select('SELECT C.* FROM '.TABLE_FAQ_CATEGORIES.' C ORDER BY C.num');
        for($l=0;$l<count($aCats);$l++) {
            $aCats[$l]['q'] = array();
            $aData[$aCats[$l]['id']] = $aCats[$l];
        }                                         
        
        $aQuestions = $this->db->select('SELECT id, category_id as pid, question FROM '.TABLE_FAQ.' ORDER BY category_id');
        foreach($aQuestions as $v) {
            $v['a'] =  ($v['id'] == $nQuestionID);
            if(isset($aData[$v['pid']])) {
                $aData[$v['pid']]['q'][] = $v;
            }
        }
        
        if($nQuestionID) {
            $aQuestion = $this->db->one_array('SELECT id, question, answer FROM '.TABLE_FAQ.' WHERE id = '.$nQuestionID);
        }
        
        $this->tplAssign('aQuestion', (!empty($aQuestion)? $aQuestion : false) );
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('view.tpl');
    }
    
    function getFooterQuestions($isAddPos = true)
    {
        return $this->db->select('SELECT id, question FROM '.TABLE_FAQ.' WHERE '.($isAddPos?'pos_add':'pos_up').' != 0 ORDER BY num');
    }
}