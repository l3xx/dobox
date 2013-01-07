<?php
                                                                                                
class SendmailAdm extends SendmailBaseApp
{
    /**
     * Список доступных для редактирования шаблонов писем. 
     */
    function template_listing()
    {
        $this->tplAssign('aData', $this->aTemplates);
        return $this->tplFetch('admin.template.listing.tpl', PATH_CORE.'modules/sendmail/tpl/'.LANG_DEFAULT.'/');
    }
    
    /**
     * Редактирование шаблона письма. 
     * @param string $sTemplateKey(tpl) ключ шаблона
     */
    function template_edit()
    {
        if( !$this->haveAccessTo('templates-edit') )
            return $this->showAccessDenied();

        $sTemplateKey = Func::POSTGET('tpl', true);
        if(empty($sTemplateKey))
            $this->adminRedirect(Errors::IMPOSSIBLE, 'template_listing');
        
        $aTemplateData = $this->getMailTemplateFromFile($sTemplateKey); 

        if(bff::$isPost)
        {    
            $aTemplateData['body'] = Func::POST('tpl_body', true);
            $aTemplateData['subject'] = Func::POST('tpl_subject', true);
            if($aTemplateData['body'] == '') {
                $this->errors->set('no_tpl');
            }
            else {
                if (get_magic_quotes_gpc()) {
                    $aTemplateData['body'] = stripslashes($aTemplateData['body']);
                }
 
                $this->saveMailTemplateToFile($sTemplateKey, $aTemplateData);                                           
                    
                $this->adminRedirect(Errors::SUCCESSFULL, 'template_listing');
            }
        }

        $aTemplateData['body'] = htmlspecialchars( $aTemplateData['body'] );

        $this->tplAssign('aData', array('keyword'    => $sTemplateKey, 
                                    'description'=> $this->aTemplates[$sTemplateKey]['description'], 
                                    'vars'       => $this->aTemplates[$sTemplateKey]['vars'], 
                                    'title'      => $this->aTemplates[$sTemplateKey]['title'], 
                                    'tpl'        => $aTemplateData,
                                    'clientside' => 0));
                                    
        return $this->tplFetch('admin.template.form.tpl', PATH_CORE.'modules/sendmail/tpl/'.LANG_DEFAULT.'/');
    }
    
    /**
     * Восстановление шаблона письма к состоянию по-умолчанию (из файла). 
     * @param string $sTemplateKey(tpl) ключ шаблона
     */
    function template_restore()
    {
        if( !$this->haveAccessTo('templates-edit') )
            return $this->showAccessDenied();

        $sTemplateKey = Func::POSTGET('tpl', true);
        if(empty($sTemplateKey))
            $this->adminRedirect(Errors::IMPOSSIBLE, 'template_listing');
            
        $this->restoreMailTemplateFile($sTemplateKey);
        
        $this->adminRedirect(Errors::SUCCESSFULL, 'template_edit&tpl='.$sTemplateKey );
    }
}

