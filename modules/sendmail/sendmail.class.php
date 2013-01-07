<?php

class Sendmail extends SendmailBaseApp
{
    function fill()
    {                                          
        $aQueryInsert = array();
        $i=200; while($i--){ $aQueryInsert[] = '('.mt_rand(1,100).')';  }
        $this->db->execute('INSERT INTO '.TABLE_ENOTIFY_SUBSCRIBE.' (user_id) VALUES '.implode(',', $aQueryInsert));
        echo 'OK'; exit;
    }
    
    /**
     * CRON задачи по рассылке писем
     */
    function cron()
    {
        if(empty($_GET['c']) || $_GET['c'] != 17)
            return;
       
        set_time_limit(0);
        
        switch(func::GET('action'))
        {
            //уведомления посетителя об успешной подписке
            case 'enotify-subscribe':
            {
                $nLimit = 10;      
                $aNotify = $this->db->select(' SELECT EN.id, EN.user_id, U.name, U.email, U.password
                            FROM '.TABLE_ENOTIFY_SUBSCRIBE.' as EN, '.TABLE_USERS.' as U
                            WHERE EN.user_id = U.user_id
                            ORDER BY EN.created
                            LIMIT '.$nLimit.'
                            FOR UPDATE 
                           ');

                if(!empty($aNotify)) 
                {
                    try{
                        
                        $aNotifyID = array();

                        # инициализируем класс рассылки
                        $mailer = new CMail();                                      
                        $mailer->From = config::get('mail_noreply', BFF_EMAIL_NOREPLY); //"E-Mail адрес уведомлений" из настроек сайта
                        # подготавливаем заголовок письма
                        $mailer->FromName = 'ILove.zp.ua';
                        $mailer->Subject = 'Заявка на участие в акции "Согрей любовью родной город"';
                        
                        $sTpl = nl2br($this->getMailTemplateContent('member_subscribe'));
                                               
                        foreach($aNotify as $v) 
                        {
                            # подготавливаем тело письма
                            $mailer->AltBody = '';
                            $mailer->MsgHTML( strtr($sTpl, array('{name}'=>$v['name'], '{email}'=>$v['email'], '{password}'=>$v['password'])) ); 
                            
                            # отправляем письмо
                            $mailer->AddAddress($v['email']);
                            if($mailer->Send()) {
                                $aNotifyID[] = $v['id'];
                            }
                            $mailer->ClearAddresses();
                            usleep(150000); // sleep for 0.15 second                        
                        }
                        
                        $this->db->execute('DELETE FROM '.TABLE_ENOTIFY_SUBSCRIBE.' WHERE id IN('.join(',', $aNotifyID).')');
                    } catch(phpmailerException $e) { 
                        $this->errors->set( $e->getMessage() );
                    }                                 
                } 
                 
            } break;    
        }
        
        if(!$this->errors->no()) {
           echo print_r($this->errors->get(), true), '\n';
        }          
        exit;
                
    }
    
    function subscribe()
    {
        $aData = array();

        if(func::isPostMethod())
        {
            $this->input->postm(array(
                    'name'  => TYPE_NOHTML,
                    'email' => TYPE_NOHTML,
                ), $aData);                              
            $sEmail =& $aData['email'];
            
            if(!$aData['name']) 
                $this->errors->set('no_subscriber_name');
                
            if(!$sEmail) 
                $this->errors->set('no_subscriber_email');        
            elseif (!func::IsEmailAddress($sEmail))
                $$this->errors->set('subscriber_wrong_email');
            elseif($this->isSubscribed($sEmail))
                $this->errors->set('subscriber_email_exists');

            if($this->errors->no())
            {
                $this->db->execute('INSERT INTO '.DB_PREFIX.'subscribers (name, email, create_datetime)
                            VALUES ('.$this->db->str2sql($aData['name']).', '.$this->db->str2sql($sEmail).', '.$this->db->getNOW().')'); 
            }
        }

        Func::JSRedirect('/');
    }
    
    function subscribe_finish()
    {                            
        set_time_limit(0); 
        
        $aSendedID = array();
        $aReceivers = $this->db->select('SELECT I.user_id, U.user_id as id, U.login, U.password
                            FROM '.TABLE_ITEMS.' I, '.TABLE_USERS.' U
                            WHERE I.status = '.ITEMS_STATUS_COMPLETED.' AND I.user_id!=1
                                AND I.user_id = U.user_id
                                AND I.id NOT IN(
                                    39, 41, 70
                                    )
                            GROUP BY I.user_id
                            ORDER BY U.login ASC');
        
        //echo '<pre>', print_r($aReceivers, true), '</pre>'; exit;
        
//        $aReceivers = array(
//            array('id'=>1, 'login'=>'battazo@gmail.com', 'password'=>'x123')
//        );
        
        # инициализируем класс рассылки
        $mailer = new CMail();                                      
        $mailer->From = config::get('mail_noreply', BFF_EMAIL_NOREPLY); //"E-Mail адрес уведомлений" из настроек сайта
        # подготавливаем заголовок письма
        $mailer->FromName = 'ILove.zp.ua';
        $mailer->Subject = 'ILove.zp.ua - итоги акции!';
        
        $sTpl = nl2br($this->getMailTemplateContent('member_subscribe_open'));
                               
        foreach($aReceivers as $v) 
        {
            if(func::IsEmailAddress($v['login'])) 
            {
                # подготавливаем тело письма
                $mailer->AltBody = '';
                $mailer->MsgHTML( strtr($sTpl, array('{login}'=>$v['login'], '{password}'=>$v['password'])) ); 
                
                # отправляем письмо
                $mailer->AddAddress($v['login']);
                if($mailer->Send()) {
                    $aSendedID[] = $v['id'];
                }
                $mailer->ClearAddresses();
                usleep(150000); // sleep for 0.15 second                        
            }
        }
        
        echo sizeof($aReceivers), ' / ', sizeof($aSendedID), '<br/>';
        echo '<pre>', print_r($aSendedID, true), '</pre>'; exit;        
    }
    
    function thanks()
    {
	    return CDir::getFileContent(PATH_BASE.'modules/forms/tpl/thanks.htm');
    }
  
}
