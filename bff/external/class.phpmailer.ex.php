<?php

include (PATH_CORE.'external/phpmailer/class.phpmailer.php');

class CMail extends PHPMailer 
{
     function __construct()
     {
         $this->From = config::get('mail_admin', BFF_EMAIL_NOREPLY);
         $this->FromName = BFF_EMAIL_FROM_NAME;
         $this->CharSet = 'UTF-8';
         $this->Host = '';
         
         $this->IsHTML(true);           
         
         switch(config::get('mail_type', 'mail'))
         {
            case 'mail':{
                $this->IsMail();
            } break;
            case 'sendmail':{
                $this->IsSendmail();
            } break;
            case 'smtp':{
                $this->IsSMTP();        
                $this->SMTPKeepAlive = true;
                
                $Hosts = array();
                for($index = 1; $index<=2; $index++)
                {
                    $index_mailer = $index - 1;
                    $key = 'mail_smtp'.$index.'_';
                    if(config::get($key.'on', false))
                    {              
                        $Hosts[] = config::get($key.'host').':'.((integer)config::get($key.'port'));
                        $user = config::get($key.'user');
                        if( ($this->SMTPAuth[$index_mailer] = !empty($user)) )
                        {
                            $this->Username[$index_mailer] = $user;
                            $this->Password[$index_mailer] = config::get($key.'pass', '');
                        }
                    }                    
                }
                
                if(count($Hosts))
                    $this->Host = implode(';', $Hosts);
                
            } break;
         }
     }
     
     static function SendQueue($sType, $aParams)
     {
         global $oDb;
         
         $time = time();
         switch($sType)
         {
             case 'subscribe': {
                    $res = $oDb->execute('INSERT INTO '.TABLE_ENOTIFY_SUBSCRIBE.' (user_id, created) 
                            VALUES('.$aParams['user_id'].', '.$time.') ');
                    if(empty($res)) {
                        func::log('Ошибка sql-запроса CMail::SendQueue('.$sType.', uid='.$aParams['user_id'].'); ');
                        return false;
                    }
                    return true;
             } break;
         }
         return false;
     }
}                   
