
{assign var="user_agent" value=$smarty.server.HTTP_USER_AGENT|lower}
{if $user_agent|default:''}
    {if $user_agent|stristr:'msie'}
        {if $user_agent|stristr:'msie 6'}
        <link rel="stylesheet" type="text/css" href="/css/ie6.css"  media="screen" />
        {else}
        <link rel="stylesheet" type="text/css" href="/css/ie.css" media="screen" />
        {/if}
    {elseif $user_agent|stristr:'opera'}
    <link rel="stylesheet" type="text/css" href="/css/opera.css" media="screen" /> 
    {elseif $user_agent|stristr:'safari'}
    <link rel="stylesheet" type="text/css" href="/css/safari.css" media="screen" /> 
        {if $user_agent|stristr:'chrome'}
        <link rel="stylesheet" type="text/css" href="/css/chrome.css" media="screen" />
        {/if} 
    {elseif $user_agent|stristr:'ns'}
    <link rel="stylesheet" type="text/css" href="/css/netscape.css" media="screen" /> 
    {elseif $user_agent|stristr:'moz'}
    <link rel="stylesheet" type="text/css" href="/css/moz.css" media="screen" />
    {/if}
{/if} 
                        
{foreach from=$tplCSSIncludes item=v}
    <link rel="stylesheet" href="{$v}" type="text/css" />
{/foreach} 
