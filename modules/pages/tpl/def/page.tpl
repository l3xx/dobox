<div class="greyLine">
<span class="left"><a href="/" title="">Главная</a></span>
<span class="arrow"><img src="/img/arrowRight.png" /></span>
<span class="left">Информация</span>
<div class="clear"></div>
</div>
<div class="leftMenu">
    <div class="blockCat">
        <div class="top"></div>
        <div class="center">
            {foreach from=$aData.menu item=v}
                <span class="caption">{$v.menu_title}:</span>
                {foreach from=$v.subs item=v2}
                    {if $v2.active}{$v2.menu_title}<br/>{else}
                    <a href="{$v2.menu_link}">{$v2.menu_title}</a><br/>
                    {/if}
                {/foreach}
            {/foreach}
        </div>
        <div class="bottom"></div>
    </div>
</div>
<div class="centerBlock">
<h1>{$aData.title}</h1>
{$aData.content}
</div>
<div class="clear"></div>