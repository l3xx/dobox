<?php

class Contacts extends ContactsBase
{
    function write()
    {
        if(bff::$isAjax)
        {
            $nUserID = $this->security->getUserID();
            
            $p = $this->input->postm(array(
                'email'   => TYPE_STR,
                'phone'   => TYPE_NOHTML,
                'message' => TYPE_NOHTML,
                'captcha' => TYPE_STR,
            ));
            
            if(!$nUserID) {
                if(empty($p['email']) || !Func::IsEmailAddress($p['email'])) 
                    $this->errors->set('wrong_email');
            }
                
            $p['phone'] = func::cleanComment($p['phone']);
            if(empty($p['phone']))
                $this->errors->set('no_phone');
            
            $p['message'] = func::cleanComment($p['message']);
            if(empty($p['message'])) 
                $this->errors->set('no_message');                
                
            if(!$nUserID) {
                $oProtection = new CCaptchaProtection();
                if( !$oProtection->valid((isset($_SESSION['c2'])?$_SESSION['c2']:''), $p['captcha']) ) {
                    $this->errors->set('wrong_captcha');                
                }   
            }
                    
            if($this->errors->no())
            {
                unset($_SESSION['c2']);
                
                $this->db->execute('INSERT INTO '.TABLE_CONTACTS.' (user_id, email, phone, message, created) 
                               VALUES ('.$nUserID.', '.$this->db->str2sql($p['email']).', 
                                       '.$this->db->str2sql($p['phone']).', '.$this->db->str2sql(nl2br($p['message'])).', 
                                       '.$this->db->getNOW().')');
                $nRecordID = $this->db->insert_id(TABLE_CONTACTS, 'id');
                if($nRecordID)
                {    
                    config::saveCount('contacts_new', 1);
                    
                    bff::sendMailTemplate(array(
                            'user'=>  (!$nUserID ? 'Аноним' : $this->security->getUserEmail()),
                            'email'=> (!$nUserID ? $p['email'] : $this->security->getUserEmail()),
                            'phone'=> $p['phone'],
                            'message'=> nl2br($p['message']),), 
                        'admin_contacts', config::get('mail_admin', BFF_EMAIL_SUPPORT));
                }
            }
            $this->ajaxResponse(Errors::SUCCESS);
        }
        
        config::set('title', 'Связь с редактором - '.config::get('title', ''));
                       
        return $this->tplFetch('write.tpl');
    }

}