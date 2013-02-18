<?php

abstract class SendmailBaseApp extends SendmailBase
{
    function init()
    {
        parent::init();
        
//        $this->aTemplates['member_subscribe'] = array(
//            'title'=>'Уведомление о подписке',
//            'description'=>'Шаблон письма, отправляемого <u>пользователю</u> в случае успешной подписки(пререгистрации)',
//            'vars'=>array('{name}'=>'Имя пользователя', '{email}'=>'E-mail пользователя', '{password}'=>'Пароль пользователя')
//            ,'impl'=>true
//            );                  
//        $this->aTemplates['member_subscribe_open'] = array(
//            'title'=>'Уведомление об открытии проекта',
//            'description'=>'Шаблон письма, отправляемого <u>подписавшемуся пользователю</u>',
//            'vars'=>array('{email}'=>'E-mail', '{password}'=>'Пароль')
//            ,'impl'=>true
//            );
        $this->aTemplates['member_passforgot'] = array(
            'title'=>'Восстановление пароля',
            'description'=>'Шаблон письма, отправляемого <u>пользователю</u> в случае восстановления пароля',
            'vars'=>array('{name}'=>'Имя пользователя', '{email}'=>'Email пользователя', '{password_link}'=>'Ссылка восстановления')
            ,'impl'=>true
            );               
        $this->aTemplates['member_registration'] = array(
            'title'=>'Уведомление о регистрации',
            'description'=>'Шаблон письма, отправляемого <u>пользователю</u> в случае регистрации',
            'vars'=>array('{email}'=>'Email пользователя', '{password}'=>'Пароль', '{activate_link}'=>'Ссылка активации')
            ,'impl'=>true
            );
        $this->aTemplates['member_bbs_press_payed'] = array(
            'title'=>'Уведомление о публикации в прессе 1',
            'description'=>'Шаблон письма, отправляемого <u>пользователю</u> в случае успешной оплаты услуги "Публикация в прессе"',
            'vars'=>array('{email}'=>'Email пользователя', '{item_url}'=>'Ссылка на страницу объявления')
            ,'impl'=>true
            );  
        $this->aTemplates['member_bbs_press_publicated'] = array(
            'title'=>'Уведомление о публикации в прессе 2',
            'description'=>'Шаблон письма, отправляемого <u>пользователю</u> в случае начала публикации объявления в прессе',
            'vars'=>array('{email}'=>'Email пользователя', '{item_url}'=>'Ссылка на страницу объявления')
            ,'impl'=>true
            );                       
        $this->aTemplates['bbs_comments_notify'] = array(
            'title'=>'Уведомление о новом комментарии',
            'description'=>'Шаблон письма, отправляемого <u>пользователю</u> в случае добавления нового комментария',
            'vars'=>array('{item_id}'=>'№ объявления', 
                          '{item_url}'=>'Ссылка на страницу объявления', 
                          '{item_comment_url}'=>'Ссылка на страницу комментариев объявления', 
                          '{comment}'=>'Текст комментария (первые 100 символов)')
            ,'impl'=>true
            ); 
        $this->aTemplates['admin_bbs_claim'] = array(
            'title'=>'Уведомление о жалобе',
            'description'=>'Шаблон письма, отправляемого <u>администратору</u> в случае поступления жалобы',
            'vars'=>array('{user}'=>'Пользователь', '{claim}'=>'Текст жалобы', '{item_url}'=>'Ссылка на страницу объявления')
            ,'impl'=>true
            ); 
        $this->aTemplates['admin_contacts'] = array(
            'title'=>'Уведомление о сообщении редактору',
            'description'=>'Шаблон письма, отправляемого <u>администратору</u> в случае отправления сообщения редактору',
            'vars'=>array('{email}'=>'Email', '{phone}'=>'Телефон', '{user}'=>'Пользователь', '{message}'=>'Текст сообщения')
            ,'impl'=>true
            );               
//        $this->aTemplates['autoreply_contactus'] = array(
//            'title'=>'Спасибо что нам написали',
//            'description'=>'Шаблон письма, отправляемого в ответ на запрос формы "Контакты"',
//            'vars'=>array('{fullname}'=>'Имя', '{email}'=>'E-mail', '{comments}'=>'Сообщение пользователя', '{site_url}'=>'URL этого сайта')
//            );

    }

    function getReceiversOptions($clients=false, $employees=false, $users=0, $exludeBlocked=false)
    {
        //строим запрос
        $sQuery = '';
        
        if($clients || $employees)
        {
            $sQuery = 'SELECT id, email, login FROM '.TABLE_CLIENTS.' 
                        WHERE ( '.($clients?' cid = 0':'').($employees?($clients?' OR ':'').' cid = 1':'').' )
                              '.($exludeBlocked?' AND status!='.CLIENTS_STATUS_BLOCKED:'')."
                              AND email!=''
                        ORDER BY login ASC ";
        }
        else if($users)
        {   
            //users = 1 - пользователей подписавшихся на рассылку от сервиса и разрешивших корреспонденцию
            //users = 2 - пользователей не зависимо от того, подписались они на рассылку от сервиса или запретили корреспонденцию
            $sQuery = 'SELECT id, email, name as login FROM '.TABLE_USERS.' 
                        WHERE member=1 '.($exludeBlocked?' AND blocked=0 AND deleted=0':'').'
                              '.($users == 1?' AND subscribed=1 AND mail_allow = 1 ':'').'
                        ORDER BY login asc ';
        }
        
        if($sQuery != '')
        {
            $aExists = $this->db->select($sQuery);
            
            $aData = array('exists' => '');
            foreach($aExists as $v)
                $aData['exists'] .= '<option value="'.$v['id'].'">'.$v['login'].' &lt;'.$v['email'].'&gt; </option>';
            return $aData;
            
        } else {
            return array('exists' => '');
        }
    }

    function getReceiversInfo($clientsOrEmployess = false, $users = false, $receivers = array())
    {
        if(!count($receivers) || (!$clientsOrEmployess && !$users))
            return array();
        
        //строим запрос
        $sQuery = '';
        $sQueryReceivers = implode(',', $receivers);
        
        if($clientsOrEmployess)
        {
            $sQuery .= 'SELECT id, email, name, login FROM '.TABLE_CLIENTS.' 
                        WHERE id IN ('.$sQueryReceivers.') 
                        ORDER BY id';
        }
        else if($users)
        {   
            $sQuery .= 'SELECT id, email, name, name as login FROM '.TABLE_USERS.' 
                        WHERE id IN ('.$sQueryReceivers.') AND member=1 
                        ORDER BY id';
        }
        
        return $this->db->select($sQuery);
    }
    
    function isSubscribed($sEmail)
    {
        if(isset($sEmail) && $sEmail)
        {
            return ((integer)$this->db->one_data('SELECT id FROM '.DB_PREFIX.'subscribers WHERE email='.$this->db->str2sql($sEmail).' LIMIT 1') > 0);
        }
        return false;
    }
    
    
}
