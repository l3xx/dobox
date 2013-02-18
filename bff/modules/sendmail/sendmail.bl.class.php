<?php

define('SENDMAIL_TEMPLATES_PATH',     PATH_BASE.'files'.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR );
define('SENDMAIL_TEMPLATES_PATH_SRC', SENDMAIL_TEMPLATES_PATH.'src'.DIRECTORY_SEPARATOR);
define('SENDMAIL_TEMPLATES_EXT',      '.txt' );

abstract class SendmailBase extends Module
{
	var $securityKey = 'b63f76553d62bf6e4bbce777b3343d7d';
    
    var $aTemplates = array();
    const TAG_START = '{';
    const TAG_END = '}';        
    
    /**
     * Получение шаблона письма  
     * @param string ключ шаблона
     * @param array набор (макрос=>значение, ...), для автозамены в шаблоне
     * @return array (body=>string, subject=>string)
     */
    function getMailTemplate($sTemplateKey, $aTplVars=false)
    {
        $aTemplateData = $this->getMailTemplateFromFile($sTemplateKey);
        
        if($aTplVars!==false) {
            $aTplVars = array_merge($aTplVars, array('siteurl' => SITEURL));
            $aReplace = array(); 
            foreach ($aTplVars as $key => $value)
                $aReplace[self::TAG_START.$key.self::TAG_END] = $value;

            $aTemplateData['body'] = strtr(nl2br($aTemplateData['body']), $aReplace);
            $aTemplateData['subject'] = strtr($aTemplateData['subject'], $aReplace);
        }
        return $aTemplateData;
    }

    /**
     * Получение шаблона письма из файла                                                    
     * @param string ключ шаблона
     * @return array (body=>string, subject=>string)
     */
    function getMailTemplateFromFile($sTemplateKey)
    {
        static $cache = array();                 
        
        if(!empty($cache[$sTemplateKey])) {
            return $cache[$sTemplateKey];
        }
        
        # получаем шаблон письма
        $sContent = CDir::getFileContent(SENDMAIL_TEMPLATES_PATH.$sTemplateKey.SENDMAIL_TEMPLATES_EXT);
        if(!$sContent) {
            # нигде не нашли, восстанавливаем
            if($this->restoreMailTemplateFile($sTemplateKey)!==false)
                $sContent = CDir::getFileContent(SENDMAIL_TEMPLATES_PATH.$sTemplateKey.SENDMAIL_TEMPLATES_EXT);
        }                              
                
        $sContentUn = (!empty($sContent) ? @unserialize( $sContent ) : false); 
        if(is_array($sContentUn)) //un-сериализация прошла успешно
            $sContent = $sContentUn;
                                            
        //сохраняем в кеш и возвращаем шаблон
        return ($cache[$sTemplateKey] = (is_array($sContent) ? $sContent : array('body'=>$sContent,'subject'=>'')));
    }
    
    /**
     * Восстановление шаблона письма из файла (доступно главному админу)
     * @param string ключ шаблона    
     * @param integer chmod
     */
    function restoreMailTemplateFile($sTemplateKey, $chmod = 0666)
    {
        $sFilename = $sTemplateKey.SENDMAIL_TEMPLATES_EXT;
        if(file_exists(SENDMAIL_TEMPLATES_PATH_SRC.$sFilename) && is_dir(SENDMAIL_TEMPLATES_PATH))
        {
            $bResult = copy(SENDMAIL_TEMPLATES_PATH_SRC.$sFilename, SENDMAIL_TEMPLATES_PATH.$sFilename);
            @chmod( SENDMAIL_TEMPLATES_PATH.$sFilename, $chmod);
            return $bResult;
        }
        return false;
    }

    /**
     * Сохранение шаблона письма в файл
     * @param string ключ шаблона                                           
     * @param array (body=>string, subject=>string) данные шаблона
     */
    function saveMailTemplateToFile($sTemplateKey, $aTemplateData)
    {                              
        return CDir::putFileContent(SENDMAIL_TEMPLATES_PATH.$sTemplateKey.SENDMAIL_TEMPLATES_EXT, serialize($aTemplateData));
    }
    
    function sendMail($to, $subject, $body, $from = '', $formName = '')
    {
        $mailer = new CMail();
        
        set_time_limit(30);
        
        if(!empty($from)) 
            $mailer->From = $from;
            
        if(!empty($formName))    
            $mailer->FromName = $formName; 
                
        $mailer->Subject = $subject;          
        $mailer->MsgHTML($body);         
        $mailer->AddAddress($to, '');
        
        $result = $mailer->Send();
        usleep(1000000); // sleep for 1 second
        return $result;        
    }
    
    
}
