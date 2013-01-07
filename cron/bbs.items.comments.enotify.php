<?php
                                                         
require(dirname(dirname(__FILE__)).'/general.config.php');
bff::sessionStart('u');
require(PATH_CORE.'init.php');
$oBff = bff::i()->init(true);

error_reporting(E_ALL & ~E_DEPRICATED);
ini_set('display_errors', 1);

set_time_limit(0); 
ignore_user_abort(true);

/*
* Период выполнения: каждые 15 минут
*/

$log = new CFileLogger(PATH_BASE.'files/logs/', 'cron.log');
$log->log('bbs.items.comments.enotify: started...');

$oBff->GetModule('bbs');

//echo "start\r\n";

$oDb->execute('START TRANSACTION');
$aNotify = $oDb->select('SELECT EN.*
    FROM '.TABLE_BBS_ITEMS_COMMENTS_ENOTIFY.' EN
    WHERE EN.sended = 0
    ORDER BY EN.created
    LIMIT 100 
    FOR UPDATE'); // <- lock пока не разошлём письма (100)
    
if(!empty($aNotify))
{
    $mail = new CMail();
    $tpl = $oBff->Sendmail_getMailTemplate('bbs_comments_notify');

    foreach($aNotify as $v)
    {                   
        try {
            $mail->From = BFF_EMAIL_NOREPLY;
            $mail->FromName = BFF_EMAIL_FROM_NAME;
            $mail->Subject = $tpl['subject'];
            $mail->AltBody = '';
            $mail->MsgHTML( nl2br( strtr( $tpl['body'], array(
                '{item_id}' => $v['item_id'],
                '{item_url}'=> SITEURL.'/item/'.$v['item_id'], 
                '{item_comment_url}'=>SITEURL.'/item/'.$v['item_id'].'?comments=1', 
                '{comment}'=> $v['comment'],
            ) ) ) );
            
            $mail->AddAddress($v['email']);
            
            $res = $mail->Send() ? 1 : 0;
            if($res) {
                $aNotifyID[] = $v['id'];
            }
            $mail->ClearAddresses();
        }
        catch(Exception $e) {  
            $log->log( 'enotify error: '.$e->getMessage() );
            $mail->SmtpClose();         
        }
           
        usleep(300000); // sleep for 0.3 second
    }    
    
    if(!empty($aNotifyID)) {              
        $oDb->execute('UPDATE '.TABLE_BBS_ITEMS_COMMENTS_ENOTIFY.' SET sended = 1 WHERE id IN ('.join(',', $aNotifyID).')');
        $oDb->execute('DELETE FROM '.TABLE_BBS_ITEMS_COMMENTS_ENOTIFY.' WHERE id IN ('.join(',', $aNotifyID).')');
        $log->log('cron - bbs.items.comments.enotify: '.sizeof($aNotifyID).' sends');
    }
}

$oDb->execute('COMMIT');

$log->log('cron - bbs.items.comments.enotify: finished');

echo 'OK'; exit;