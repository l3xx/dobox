{if $aBannerData.banner_type==2}
    <script type="text/javascript">
        var fG = new Flash ();
        fG.setSWF ('{$smarty.const.BANNERS_URL}/{$aBannerData.id}_src_{$aBannerData.banner}', '{$aBannerData.flash.width}', '{$aBannerData.flash.height}');
        fG.setParam ('wmode', 'transparent');
        fG.setParam ('FlashVars', '{$aBannerData.flash.key}={$aBannerData.clickurl}');
        fG.display ();
    </script>
    <div style="display:none;" >
        <img src="{$aBannerData.img_work}" width="1" height="1" alt="" />
    </div>
{else}
    {if $aBannerData.clickurl|default:''}<a target="_blank" title="{$aBannerData.title}" alt="{$aBannerData.alt}" href="{$aBannerData.clickurl}">{/if} 
        {if $aBannerData.banner_type==3}{$aBannerData.banner}{/if}<img src="{$aBannerData.img_work}" class="border1" />
    {if $aBannerData.clickurl|default:''}</a>{/if}
{/if}
