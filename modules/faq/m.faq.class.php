<?php

class M_Faq
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;

        $oMenu->assign('FAQ', 'Вопросы', 'faq', 'questions_listing', true, 1, 
                    array( 'rlink' => array('event'=>'questions_add') ));
        $oMenu->assign('FAQ', 'Добавить вопрос', 'faq', 'questions_add', false, 2); 
        $oMenu->assign('FAQ', 'Редактирование вопроса', 'faq', 'questions_edit', false, 3);
        $oMenu->assign('FAQ', 'Комментарии вопроса', 'faq', 'questions_comments', false, 4);
                
        $oMenu->assign('FAQ', 'Категории', 'faq', 'categories_listing', true, 10, 
                    array( 'rlink' => array('event'=>'categories_add') )); 
        $oMenu->assign('FAQ', 'Добавить категорию', 'faq', 'categories_add', false, 11);
        $oMenu->assign('FAQ', 'Редактирование категории', 'faq', 'categories_edit', false, 12);
        $oMenu->assign('FAQ', 'Удаление категории', 'faq', 'categories_delete', false, 13); 
    }

}