<?php

require('general.config.php');
bff::sessionStart('u');
require(PATH_CORE.'init.php');
$oBff = bff::i()->init(true);
 
//check offline status        
if(!config::get('enabled', 0) && !bff::$isAjax && !FORDEV) {
    $oSm->display('offline.tpl', __FILE__, __FILE__);
    exit( );    
} 
                                                 
//-----------------------------------------------------------

//authorize if user was remembered    
$oSm->assign('userLogined', $oSecurity->isLogined());

//run module method
if(bff::$class == 1) 
{              
    $sCenterArea = tpl::fetchPHP($aData, TPL_PATH.'index.php'); 
}
else
{
    $sCenterArea = $oBff->callModule(bff::$class.'_'.bff::$event);
    Errors::i()->assign();
} 

$oBff->Banners_assignFrontendBanners();  
$oBff->Bbs_getFavorites();
$oBff->Users_getUserBalance();

$oSm->assign('menu_useful', $oBff->Sitemap_getmenu('useful'));
$oSm->assign('counters', $oBff->Sites_getSiteCounters(true) ); 

if(!$oSecurity->isLogined() && config::get('tpl_bbs_item_edit')!=1) {
    //faq - add questions        
    $oSm->assign('footer_faq', $oBff->Faq_getFooterQuestions(true) ); 
} else {
    //faq - up questions
    $oSm->assign('footer_faq', $oBff->Faq_getFooterQuestions(false) );
}

$oSm->assign_by_ref('center_area', $sCenterArea );
$oSm->display('template.tpl', __FILE__, __FILE__);

exit(0);
