<?php 
    extract($aData, EXTR_REFS);

    foreach($svc as $k=>$v) {
        $svc[$k]['price_prefix'] = '&nbsp;<span class="desc">руб.</span>';
    }
?>
<div class="blueblock whiteblock">
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Услуги / Управление</span>
        <span class="rightC"></span>
    </div>
    <div class="content clear">
        <div class="text">

            <div class="tabsBar" id="j-services-tabs">
                <span class="tab tab-active" onclick="return jServices.onTab('publicate', this);">Платное размещение</span>
                <span class="tab" onclick="return jServices.onTab('up', this);">Поднять объявление</span> 
                <span class="tab" onclick="return jServices.onTab('mark', this);">Выделить объявление</span>
                <span class="tab" onclick="return jServices.onTab('premium', this);">Премиум</span> 
                <span class="tab" onclick="return jServices.onTab('press', this);">Публикация в прессе</span>                
                <div class="progress" style="display:none;" id="j-services-progress"></div>
            </div>
            
            <div id="j-services-tabs-content">
                <div id="j-services-publicate">
                    <?= $this->tplFetchPHP($svc['publicate'], 'admin.settings.publicate.php'); ?>
                </div>
                <div id="j-services-up" class="hidden">
                    <?= $this->tplFetchPHP($svc['up'], 'admin.settings.up.php'); ?>
                </div>
                <div id="j-services-mark" class="hidden">
                    <?= $this->tplFetchPHP($svc['mark'], 'admin.settings.mark.php'); ?>
                </div>
                <div id="j-services-premium" class="hidden">
                    <?= $this->tplFetchPHP($svc['premium'], 'admin.settings.premium.php'); ?>
                </div>
                <div id="j-services-press" class="hidden">
                    <?= $this->tplFetchPHP($svc['press'], 'admin.settings.press.php'); ?>
                </div>
            </div>
            
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

<script type="text/javascript">
var jServices = (function(){
    var url_ajax = '<?= $this->adminCreateLink('settings&act='); ?>';
    var $tabs, $progress;
    
    $(function(){
        $tabs = $('#j-services-tabs-content > div'); 
        $progress = $('#j-services-progress');
        $('textarea.wy').bffWysiwyg({autogrow: false});
    });
    
    return {
        onTab: function(key,link){
            $tabs.addClass('hidden');
            $tabs.filter('#j-services-'+key).removeClass('hidden');
            $(link).addClass('tab-active').siblings().removeClass('tab-active');
            return false;
        },
        update: function(form) {
            form = $(form);
            form.addClass('disabled');
            form.trigger('submit'); //sync wysiwyg's            
            bff.ajax(url_ajax+'update', form.serializeArray(), function(data){
                if(data){
                    bff.error('Настройки успешно сохранены', {success: true});
                }
                form.removeClass('disabled');
                location.reload();
            }, $progress);
        }
    };
}());
</script>
