{foreach from=$tplJSIncludes item=v}
<script src="{$v}" type="text/javascript" charset="utf-8"></script>
{/foreach}                                                                                    

<script type="text/javascript" charset="utf-8">
app.themeUrl = '{$theme_url}';
app.adm = true; 
app.yCountry = {ldelim}title: '{$smarty.const.GEO_YMAPS_COUNTRY_TITLE}', coords: '{$smarty.const.GEO_YMAPS_COUNTRY_COORDS}', bounds: '{$smarty.const.GEO_YMAPS_COUNTRY_BOUNDS}'.split(';'){rdelim};
{literal}
$(document).ready(function(){          
    $('a.userlink').fancybox({type: 'ajax', scrolling: 'no'});
    $('a.itemlink').fancybox({type: 'ajax', scrolling: 'no'});
});
{/literal}
</script>

