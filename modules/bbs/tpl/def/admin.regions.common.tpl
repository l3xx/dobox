
{if !$rotate}{$pagenation_template}{/if}

<div style="margin-top:7px;">
    <div class="left">
        <a class="but add" id="bbs-regions-add-link" href="#bbs-regions-add-popup"></a>
    </div>    
    <div class="right desc" style="width:80px; text-align:right;">
        {if !$pid}<span id="bbs-regions-progress" style="margin-left:5px; display:none;" class="progress"></span>{/if}
        {if $rotate}&nbsp;&nbsp; &darr; &uarr;{/if}
    </div>
    <div class="clear"></div>
</div> 

<div style="display:none;">
    <div class="ipopup" id="bbs-regions-add-popup" style="width:300px;">
        <div class="ipopup-wrapper">
            <div class="ipopup-title">Добавить регион</div>
            <div class="ipopup-content">   
                <form action="" method="post" id="bbs-regions-add-form">
                    <input type="hidden" name="pid" value="{$pid}" />
                    <input type="hidden" name="numlevel" value="{$numlevel}" />
                    <table class="admtbl tbledit">
                    <tr>
                        <td style="width:60px;"><span class="field-title">Название</span>:</td>
                        <td><input type="text" name="title" class="stretch" id="bbs-regions-add-title" /></td>
                    </tr>
                    <tr class="footer">
                        <td colspan="2" align="center">
                            <input type="submit" class="button submit" value="Добавить" />
                            <input type="button" class="button cancel" value="Отмена" onclick="$.fancybox.close();" />
                        </td>
                    </tr>
                    </table>
                </form>
            </div> 
        </div>
    </div>     
</div>

<script type="text/javascript">
var bbsRegionsPID = {$pid};      
{literal}
var bbsreg = (function(){
    var isEdit = false, id = 0, title = '', titleOld = '', $progress; 

    $(function(){  

        $progress = $('#bbs-regions-progress');
        
        if($.tableDnD)
            bff.initTableDnD('#bbs-regions-listing', 'index.php?s=bbs&ev=regions_ajax&act='+(bbsRegionsPID==0?'country':'region')+'-rotate', $progress);
        
        var $formAdd = $('#bbs-regions-add-form');
        $formAdd.submit(function(){
            bff.ajax('index.php?s=bbs&ev=regions_ajax&act=region-add', $formAdd.serialize(), function(data, errors){
                if(data && !errors.length) {
                    $.fancybox.close();
                    location.reload();
                }
            }, $progress);
            return false;
        });
        $('#bbs-regions-add-link').fancybox({onComplete: function(){
            $('#bbs-adds-add-title', $formAdd).focus();
        }});
        
        $('#bbs-regions-edit-finish').live('click', function(){
            var $td = $(this).parent();
            var title = $('input[name="title"]', $td).val();
            bff.ajax('index.php?s=bbs&ev=regions_ajax&act=region-save', {rec: id, title: title}, function (data) {
                if(data) {
                    $td.html(data.title);
                    isEdit = false;
                }
            });
        });
        $('#bbs-regions-edit-cancel').live('click', function(){
            editCancel();
        });
    });
   
    function edit(nID)
    {
        id = nID;
        var $td = $('#dnd-'+nID+' [rel="title"]');
        titleOld = $td.html();

        $td.html('<input type="text" name="title" value="'+titleOld+'" size="30" />\
               <input type="button" class="button submit" id="bbs-regions-edit-finish" value="Сохранить" />\
               <input type="button" class="button cancel" id="bbs-regions-edit-cancel" value="Отмена" />').find('input:first').focus();
               
        isEdit = true;

        return false;
    }   

    function editCancel()
    {
        $('#dnd-'+id+' [rel=title]').html(titleOld);
        isEdit = false;
    }
    
    function toggle(id, $link, main)
    {
        if(main) {
            bff.ajaxToggle(id, 'index.php?s=bbs&ev=regions_ajax&act=region-toggle-main&rec='+id, 
                           {link: $link, progress: $progress, block: 'fav', unblock: 'unfav'});            
        } else {
            bff.ajaxToggle(id, 'index.php?s=bbs&ev=regions_ajax&act=region-toggle&rec='+id, 
                           {link: $link, progress: $progress});
        }
        return false;
    }
    
    function del(id, $link) {
        bff.ajaxDelete('sure', id, 'index.php?s=bbs&ev=regions_ajax&act=region-delete', $link, 
            {progress: $progress});
        return false;
    }
    
    return {edit: edit, toggle: toggle, del: del}; 
})();

{/literal}
</script>
