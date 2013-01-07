
<div class="ipopup">
<div class="ipopup-wrapper">
    <div class="ipopup-title">Просмотр баннера</div>
    <div class="ipopup-content" style="width:{if $aData.banner_type == 1}{$aData.img_size}{/if}px;">   
        {if $aData.banner_type==3}
            {$aData.banner}
        {elseif $aData.banner_type == 2}
            <div id="popup_bn_fl"></div>
        {else}
            <img id="popup_bn_img" src="{$aData.img_thumb}" />
        {/if}
        <script type="text/javascript">
        var banner_type   = '{$aData.banner_type}';
        var banner_width  = '{$aData.flash.width}';
        var banner_height = '{$aData.flash.height}';
        var banner_url    = '{$smarty.const.BANNERS_URL}/{$aData.id}_src_{$aData.banner}';
        {literal}
        //<![CDATA[
         $(function (){
                if(banner_type==2) {
                   $('#popup_bn_fl').html('<object type="application/x-shockwave-flash" data="'+banner_url+'" width="'+banner_width+'" height="'+banner_height+'"><param name="movie" value="'+banner_url+'" /></object>');
                }
            });
        //]]> 
        {/literal}
        </script>
    </div> 
</div>
</div>
