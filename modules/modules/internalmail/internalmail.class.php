<?php

class InternalMail extends InternalMailBase 
{
    function cron()
    {
        // удаление сообщений удаленных отправителем и получателем
        $this->db->execute('DELETE FROM '.TABLE_INTERNALMAIL.' 
                WHERE (status ^ '.(INTERNALMAIL_STATUS_DELAUTHOR | INTERNALMAIL_STATUS_DELRECIPIENT).') IN (0,1) ');
    }
    
}