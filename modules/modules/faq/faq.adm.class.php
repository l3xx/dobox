<?php

class Faq extends FaqBase
{
    # ------------------------------------------------------------------------------------------
    # вопросы
    
    function questions_listing()
    {
        if(!$this->haveAccessTo('questions-listing'))
            return $this->showAccessDenied();

        $nCategoryID = func::GETPOST('category', false, true);  
        
        //prepare order 
        $_GET['order'] = ( $nCategoryID>0 ? 'F.num,asc' : (!isset($_GET['order']) || $_GET['order'] == 'F.num,asc' ? 'created,desc' : $_GET['order']) );
        $this->prepareOrder($orderBy, $orderDirection, 'created,desc');
                                                    
        //generate pagenation
        $nCount = (integer)$this->db->one_data( 'SELECT COUNT(*) FROM '.TABLE_FAQ.' F '.($nCategoryID?'WHERE F.category_id = '.$nCategoryID:'') );    
        $this->generatePagenation( $nCount, 20, "index.php?s=faq&ev=questions_listing&order=$orderBy,$orderDirection".($nCategoryID?"&category=$nCategoryID":'')."&{pageId}", $sqlLimit);
        
        $aData = array();
        
        //получаем вопросы
        $sQuery = 'SELECT F.id, F.category_id, F.question, F.created, FC.title as ctitle, U.login as creator
                   FROM '.TABLE_FAQ.' F
                            INNER JOIN '.TABLE_FAQ_CATEGORIES.' FC ON F.category_id = FC.id
                            INNER JOIN '.TABLE_USERS.' U ON F.user_id = U.user_id
                   '.($nCategoryID?' WHERE F.category_id = '.$nCategoryID:'').' 
                   ORDER BY '." $orderBy $orderDirection $sqlLimit";
        $aData['questions'] = $this->db->select($sQuery);

        $aData['categories_options'] = $this->getCategoriesOptions($nCategoryID, false); 
        $aData['cat'] = $nCategoryID;
        if($nCategoryID>0) {
            $this->includeJS('tablednd');
        }
        
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.questions.listing.tpl');
    }

    function questions_add()
    {
        if(!$this->haveAccessTo('questions-edit'))
            return $this->showAccessDenied();
        
        $aData = array('parent'=>func::GET('parent', false, true), 'category_id'=>'');
        $this->input->postm(array(
            'question' => TYPE_STR,
            'answer'   => TYPE_STR,
            'category' => TYPE_UINT,
            'pos_add'  => TYPE_BOOL,
            'pos_up'   => TYPE_BOOL,
        ), $aData);
        
        if(func::isPostMethod())
        {
            if(!$aData['category']) $this->errors->set('no_category'); 
            if(!$aData['question']) $this->errors->set('no_question');
            if(!$aData['answer'])   $this->errors->set('no_answer');
                
            if($this->errors->no() && $aData['category'])
            {                  
                $nNum = intval($this->db->one_data('SELECT MAX(num) FROM '.TABLE_FAQ.' WHERE category_id = '.$aData['category'])) + 1;  
                $this->db->execute('INSERT INTO '.TABLE_FAQ.' (user_id, category_id, question, answer, pos_add, pos_up, created, num) 
                               VALUES ('.$this->security->getUserID().', '.$aData['category'].', 
                                       '.$this->db->str2sql($aData['question']).', '.$this->db->str2sql($aData['answer']).', 
                                       '.$aData['pos_add'].', '.$aData['pos_up'].',
                                       '.$this->db->getNOW().', '.$nNum.')');
                
                $this->adminRedirect(Errors::SUCCESSFULL, 'questions_listing');
            }
            $aData = func::array_2_htmlspecialchars($aData, array('question')); 
        }

        $aData['category_options'] = $this->getCategoriesOptions($aData['parent'], ($aData['parent']?false:'не указана'));
        $aData['edit'] = 0;
        
        $this->tplAssignByRef('aData', $aData);   
        return $this->tplFetch('admin.questions.form.tpl');
    }
    
    function questions_edit()
    {
        if(!$this->haveAccessTo('questions-edit'))
            return $this->showAccessDenied();
            
        if(($nRecordID = func::GETPOST('rec', false, true))<=0) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'questions_listing');

        $aData = $this->db->one_array('SELECT F.* 
                                  FROM '.TABLE_FAQ.' F   
                                  WHERE F.id='.$nRecordID.'                                 
                                  LIMIT 1');
        if(empty($aData)) $this->adminRedirect(Errors::IMPOSSIBLE, 'questions_listing'); 
                
        if(func::isPostMethod())
        {    
            $this->input->postm(array(
                'question' => TYPE_STR,
                'answer'   => TYPE_STR,
                'category' => TYPE_UINT,
                'pos_add'  => TYPE_BOOL,
                'pos_up'   => TYPE_BOOL,
            ), $aData);
            
            if($this->errors->no())
            {
                $this->db->execute('UPDATE '.TABLE_FAQ.'  
                               SET question  = '.$this->db->str2sql($aData['question']).', answer='.$this->db->str2sql($aData['answer']).',
                                   category_id = '.$aData['category'].', pos_add = '.$aData['pos_add'].', pos_up = '.$aData['pos_up'].', 
                                   modified = '.$this->db->getNOW().'
                               WHERE id='.$nRecordID);
                
                $this->adminRedirect(Errors::SUCCESSFULL, 'questions_listing');
            }
        }
        
        $aData['category_options'] = $this->getCategoriesOptions($aData['category_id'], ($aData['category_id']?false:'не указана') );
        $aData['edit'] = 1;

        $this->tplAssignByRef('aData', func::array_2_htmlspecialchars($aData, array('question')) );
        return $this->tplFetch('admin.questions.form.tpl');
    }    
    
    function questions_ajax()
    {
        if(!$this->haveAccessTo('questions-edit'))
            return $this->showAccessDenied();

        if(bff::$isAjax)
        {                   
            switch(func::GET('act'))
            {
                case 'delete': 
                {                         
                    if(($nQuestionID = func::GETPOST('rec', false, true))<=0)
                        $this->ajaxResponse(Errors::IMPOSSIBLE);
                                        
                    # удаляем вопрос 
                    $this->db->execute('DELETE FROM '.TABLE_FAQ.' WHERE id = '.$nQuestionID);                     
                    $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;
                case 'rotate': 
                {                         
                    $res = $this->db->rotateTablednd(TABLE_FAQ, '', 'id', 'num', true, 'category_id');
                    $this->ajaxResponse( $res ? Errors::SUCCESSFULL : Errors::IMPOSSIBLE );
                } break;                
            }
        }
            
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
    
    # ------------------------------------------------------------------------------------------
    # категории

    function categories_listing()
    {
        if(!$this->haveAccessTo('categories-listing'))
            return $this->showAccessDenied();

        $aData = array();
        
        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'rotate': {
                    $res = $this->db->rotateTablednd(TABLE_FAQ_CATEGORIES);
                    if($res) $this->ajaxResponse( Errors::SUCCESSFULL );
                } break;
            }
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        }
        
        //получаем категории
        $sQuery = 'SELECT FC.id, FC.title, COUNT(F.id) as questions
                   FROM '.TABLE_FAQ_CATEGORIES.' FC
                      LEFT JOIN '.TABLE_FAQ.' F ON F.category_id=FC.id
                   GROUP BY FC.id
                   ORDER BY FC.num'; 
        $aData['categories'] = $this->db->select($sQuery);
        
        $this->includeJS('tablednd');
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.categories.listing.tpl');
    }
    
    function categories_add()
    {                    
        if(!$this->haveAccessTo('categories-edit'))
            return $this->showAccessDenied();
        
        $aData = array('title'=>'');
        if(func::isPostMethod())
        {
            $aData['title']  = func::POST('title', true);        
            
            if(empty($aData['title']))
                $this->errors->set('empty:title');    
             
            if($this->errors->no())
            {   
                $this->db->execute('INSERT INTO '.TABLE_FAQ_CATEGORIES.' (title) 
                               VALUES ('.$this->db->str2sql($aData['title']).') ');
                
                $this->adminRedirect(null, 'categories_listing');
            }  
            $aData = func::array_2_htmlspecialchars($aData, array('title'));
        }
        
        $this->tplAssign('aData', $aData);       
        return $this->tplFetch('admin.categories.form.tpl');
    }
    
    function categories_edit()
    {
        if(!$this->haveAccessTo('categories-edit'))
            return $this->showAccessDenied();

        if(($nRecordID = func::GETPOST('rec', false, true))<=0) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');
            
        $aData = $this->db->one_array('SELECT id, title
                   FROM '.TABLE_FAQ_CATEGORIES.'
                   WHERE id='.$nRecordID.' LIMIT 1');
        if(!$aData) $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');
                   
        if(func::isPostMethod())
        {
            $aData['title'] = func::POST('title', true);
            if(empty($aData['title']))
                $this->errors->set('empty:title');
             
            if($this->errors->no())
            {   
                $this->db->execute('UPDATE '.TABLE_FAQ_CATEGORIES.' 
                               SET title='.$this->db->str2sql($aData['title']).' 
                               WHERE id='.$nRecordID);
                
                $this->adminRedirect(Errors::SUCCESSFULL, 'categories_listing');
            }                                       
        }

        $this->tplAssign('aData', func::array_2_htmlspecialchars($aData, array('title')) );
        return $this->tplFetch('admin.categories.form.tpl');
    }

    function categories_delete()
    {
        if(!$this->haveAccessTo('categories-edit'))
            return $this->showAccessDenied();
            
        if(($nRecordID = func::GETPOST('rec', false, true))<=0) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');

        $aData = $this->db->one_array('SELECT FC.id, FC.title, COUNT(F.id) as cnt_items 
                   FROM '.TABLE_FAQ_CATEGORIES.' FC
                        LEFT JOIN '.TABLE_FAQ.' F on FC.id=F.category_id
                   WHERE FC.id='.$nRecordID.'
                   GROUP BY FC.id 
                   LIMIT 1');
        if(!$aData) $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');  
        
        if(func::isPostMethod())
        {
            $nNextCategoryID = func::POST('next', false, true);
            if($nNextCategoryID > 0 )
            {
                //проверяем: ее ID не равен ID удаляемой, категория не является подкатегорией
                $nResultID = $this->db->one_data('SELECT id FROM '.TABLE_FAQ_CATEGORIES.' WHERE id='.$nNextCategoryID.' LIMIT 1');
                if($nResultID != $nNextCategoryID || $nNextCategoryID==$nRecordID)
                     $this->adminRedirect(Errors::IMPOSSIBLE, 'categories_listing');

                //перемещаем вопросы
                $this->db->execute('UPDATE '.TABLE_FAQ.' SET category_id='.$nNextCategoryID.' WHERE category_id='.$nRecordID);
                
                //удаляем категорию
                $this->db->execute('DELETE FROM '.TABLE_FAQ_CATEGORIES.' WHERE id='.$nRecordID);   
            }
            else
            {
                if($aData['cnt_items']) {    
                    //удаляем вопросы 
                    $this->db->execute('DELETE FROM '.TABLE_FAQ.' WHERE category_id = '.$nRecordID);
                    
                    //удаляем категорию
                    $this->db->execute('DELETE FROM '.TABLE_FAQ_CATEGORIES.' WHERE id='.$nRecordID);   
                } else {
                    //удаляем категорию
                    $this->db->execute('DELETE FROM '.TABLE_FAQ_CATEGORIES.' WHERE id='.$nRecordID);
                }
            }
            
            $this->adminRedirect(Errors::SUCCESSFULL, 'categories_listing');
        }

        $aData['categories'] = $this->getCategoriesOptions(0, false, array($nRecordID));
        
        $this->tplAssign('aData', $aData);      
        return $this->tplFetch('admin.categories.delete.tpl'); 
    }

    function categories_move()
    {                      
        if(!$this->haveAccessTo('categories-edit'))
            return $this->showAccessDenied();

        $nQuestionID = func::GETPOST('rec', false, true);
        if(!$nQuestionID) $this->ajaxResponse(Errors::IMPOSSIBLE);
                                       
        if(bff::$isAjax)
        {                   
            switch(func::GET('act'))
            {
                case 'delete': 
                {                         
                    # удаляем вопрос 
                    $this->db->execute('DELETE FROM '.TABLE_FAQ.' WHERE id = '.$nQuestionID);                     
                    $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;
            }
        }
            
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
    
}
