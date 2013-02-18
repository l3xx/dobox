<script type="text/javascript">window.locDomain = '{$smarty.const.SITEHOST}'; window.bff_table = {php}global $oSecurity; echo $oSecurity->getUID();{/php}</script>                 
{foreach from=$tplJSIncludes item=v}
    <script src="{$v}" type="text/javascript" charset="utf-8"></script>
{/foreach}
<script type="text/javascript">app.m={if $userLogined}1{else}0{/if};
{if $event=='search' && !$smarty.get.quick}{literal}if(window.History.emulated.pushState) {
    var hash = window.History.getHash();
    if(hash && hash.length>0) {
        if(/search\?/i.test(hash)) document.location = '/'+hash;
    }
}{/literal}{/if} 
</script>