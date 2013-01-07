<?php

function smarty_modifier_publicator($aData, $ID, $sFieldName, $sAction = 'edit', $sJSObjectName = '')
{
    return tpl::publicator($aData, $ID, $sFieldName, $sAction = 'edit', $sJSObjectName);
}