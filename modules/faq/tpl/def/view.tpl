<script type="text/javascript">{literal}
$(function(){
    $(".greyLine").corner("8px");
});
{/literal}
</script>  

<div class="greyLine">
    <span class="left"><a href="/" title="Главная">Главная</a></span>
    <span class="arrow"><img src="/img/arrowRight.png" alt=""/></span>
    <span class="left">Помощь</span>
    <div class="clear"></div>
</div>

<div class="leftMenu">
    <div class="blockCat">
        <div class="top"></div>
            <div class="center">
                {foreach from=$aData item=v}
                    <span class="caption">{$v.title}:</span>
                   {foreach from=$v.q item=q}
                        {if $q.a}{$q.question}{else}<a href="/help/{$q.id}" title="{$q.question}">{$q.question}</a>{/if}<br/>
                    {/foreach}
                {/foreach}
            </div>
        <div class="bottom"></div>
    </div>
</div>

<div class="centerBlock">
    {if $aQuestion}<h1>{$aQuestion.question}</h1>{$aQuestion.answer}{/if}
    
</div>

<div class="clear"></div>