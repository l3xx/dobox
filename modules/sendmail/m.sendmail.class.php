<?php

class M_Sendmail
{
    function declareAdminMenu( )
    {
        global $oMenu, $oSecurity;

//        $oMenu->assign('Работа с почтой', 'Список подписавшихся', 'sendmail', 'subscriber_listing', true);
//        $oMenu->assign('Работа с почтой', 'Подписать на рассылку', 'sendmail', 'subscriber_add', true);
//        $oMenu->assign('Работа с почтой', 'Редактировать подписку', 'sendmail', 'subscriber_edit', false);   
//        $oMenu->assign('Работа с почтой', 'Рассылка подписавшимся','sendmail', 'subscriber_sendspam', true);

        //$oMenu->assign('Работа с почтой', 'Рассылка пользователям','sendmail', 'users_sendspam', true); 
        
        //$oMenu->assign('Работа с почтой', 'Рассылка', 'sendmail', 'massend', true);    
        $oMenu->assign('Работа с почтой', 'Шаблоны писем', 'sendmail', 'template_listing', true);
        $oMenu->assign('Работа с почтой', 'Редактирование шаблона', 'sendmail', 'template_edit', false);
                                                                                    
        //$oMenu->assign('Работа с почтой', 'Gmail', 'sendmail', 'gmail', true);    
        
      //  $oMenu->assign('Работа с почтой', 'У нас новый пользователь', 'sendmail', 'admnotify_user_registration', true);
    }    

}
