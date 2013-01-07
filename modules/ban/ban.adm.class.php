<?php

class Ban extends BanBase
{
    function main()
    {
        return $this->users();
    }

    function users()
    {
        if(!$this->haveAccessTo('ban'))
            return $this->showAccessDenied();
        
        $aData = array();
       
        if(Func::isPostMethod())
        {
            if(Func::POST('action') == 'massdel')
            {
                $mBanID = func::POST('banid',false);
                $this->removeBan($mBanID);
                
            } else 
            {
                $sMode = Func::POST('banmode');  
                if(empty($sMode)) $sMode = 'ip';
                
                $ban          = func::POST('ban_'.$sMode, true);
                $nBanPeriod   = func::POST('banlength', false, true);
                $nBanPeriodDate = func::POST('bandate', true);
                $nExclude     = (func::POST('exclude') ? 1 : 0);
                $sDescription = func::POST('description', true);
                $sReason      = func::POST('reason', true);

                if(!empty($ban))
                {
                    $this->createBan($sMode, $ban, $nBanPeriod, $nBanPeriodDate, $nExclude, $sDescription, $sReason);
                    
                    $this->adminRedirect(Errors::SUCCESSFULL, 'users');
                }
            }
        }

        $aBanEndText = array( 0 => 'бессрочно', 30 => '30 минут', 60 => '1 час', 360 => '6 часов', 
            1440 => '1 день', 10080 => '7 дней', 20160 => '2 недели', 40320 => '1 месяц');
       
       /*                                     
          `uid` int(11) unsigned NOT NULL default '0',
          `ip` varchar(40) NOT NULL default '',
          `email` varchar(100) NOT NULL default '',
          `started` int(11) unsigned NOT NULL default '0',
          `finished` int(11) unsigned NOT NULL default '0',
          `exclude` tinyint(1) unsigned NOT NULL default '0',
          `description` varchar(255) NOT NULL default '',
          `reason` varchar(255) NOT NULL default '',
          `status` tinyint(1) unsigned NOT NULL default '0',  
       */
       
        $aData['bans'] = $this->db->select('SELECT B.* 
                                       FROM '.TABLE_USERS_BANLIST.' B
                                       WHERE (B.finished >= '.time().' OR B.finished = 0) 
                                       ORDER BY B.ip, B.email');
        foreach($aData['bans'] as $key=>&$ban)
        {                 
            $timeLength = ($ban['finished']) ? ($ban['finished'] - $ban['started']) / 60 : 0;
            $ban['till'] = (isset($aBanEndText[$timeLength]) ? $aBanEndText[$timeLength] : '' );
            $ban['finished_formated'] = date('Y-m-d H:i:s', $ban['finished']);  //0000-00-00 00:00:00
        }         
        
        $this->tplAssign('aData', $aData);
        $this->adminCustomCenterArea();       
        return $this->tplFetch('admin.listing.tpl');  
    }
    
    function ajax()
    {
        if(!$this->haveAccessTo('ban'))
            $this->ajaxResponse(Errors::ACCESSDENIED);

        if(bff::$isAjax)
        {   
            switch( Func::POSTGET('action') )
            {
                case 'delete':
                {
                    if(!($nBanID = Func::POSTGET('rec', false, true))) 
                        $this->ajaxResponse(Errors::IMPOSSIBLE); 
                    
                    $this->removeBan($nBanID);
                    
                    $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;
            }
        }
            
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
    
}
