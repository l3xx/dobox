<?php

class Contacts extends ContactsBase
{
	function main()
	{
		return $this->listing();
	}
    
    function listing()
    {
        if( !$this->haveAccessTo('view') )
            return $this->showAccessDenied();
        
        $nType = func::GET('type',false,true);
        
        $sQuery = '';
        switch($nType)
        {
            case CONTACTS_TYPE_CONTACT:
            {
                $sQuery = 'SELECT C.*, U.login as ulogin, U.name as uname, U.blocked as ublocked, U.admin as uadmin
                        FROM '.TABLE_CONTACTS.' C
                            LEFT JOIN '.TABLE_USERS.' U ON C.user_id=U.user_id
                        WHERE C.ctype='.$nType;
            } break;
        }
        
        $this->generatePagenationPrevNext($sQuery.' ORDER BY C.created DESC', $aData, 'contacts', 15);
        
        $this->db->execute('UPDATE '.TABLE_CONTACTS.' SET viewed = 1 WHERE viewed = 0');
        config::save('contacts_new', 0);

        $aData['type']  = $nType;
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.listing.tpl'); 
    } 
    
    function ajax()
    {
        if(!$this->haveAccessTo('edit') || !bff::$isAjax)
            $this->ajaxResponse(Errors::ACCESSDENIED);       
        
        switch(func::GET('act'))
        {
            case 'del':
            {
                $nContactID = func::POST('rec', false, true);
                if($nContactID<=0) $this->ajaxResponse(Errors::IMPOSSIBLE);
                
                $this->db->execute('DELETE FROM '.TABLE_CONTACTS.' WHERE id = '.$nContactID);
                $this->ajaxResponse(Errors::SUCCESSFULL);
            } break; 
            case 'send':
            {
                $nType = func::POST('type', false, true);
                switch($nType)
                {
                    case CONTACTS_TYPE_CONTACT:
                    {
                        //
                    } break;
                }
                $this->ajaxResponse(Errors::IMPOSSIBLE); 
            } break; 
        }            

        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
                 
}
