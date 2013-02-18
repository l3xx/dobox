<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Категории / Подтипы</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
        
            <div class="actionBar">                                             
                <div class="left">
                    <?  foreach($aData['cats'] as $c) { ?> 
                        <a href="<?= $aData['url_listing']; ?>&cat_id=<?= $c['id']; ?>"><?= $c['title']; ?></a>
                         &rarr;&nbsp;&nbsp;
                    <? } ?>  <b>типы</b>

                </div>
                <div class="right">   
                </div>
                <div class="clear-all"></div>
            </div>
                                                              
            <table class="admtbl tblhover" id="subtypes_listing">
                <tr class="header nodrag nodrop">
                <? if(FORDEV): ?><th width="30">ID</th><? endif; ?>
                    <th align="left">Название подтипа</th>
                    <th width="250">Категория</th>
                    <th width="80">Действие</th>
                </tr>
                <? 
                $cols = 3; if(FORDEV) $cols++;              
                if(!empty($aData['subtypes']))
                {   $i=1; $in=0; 
                    foreach($aData['subtypes'] as $v) { $inherited = $v['cat_id']!=$aData['cat_id']; if($inherited) $in++; ?>
                    <?php if(!$inherited && $in){ $in = 0; ?><tr><td style="padding:0;" colspan="<?= $cols; ?>"><hr/></td></tr><?php } ?>
                    <tr align="center" class="row<?= ($i++%2); ?><? if($inherited): ?> nodrag nodrop<? endif; ?>" id="dnd-<?= $v['id']; ?>">
                    <?  if(FORDEV): ?><td><?= $v['id']; ?></td><? endif; ?>
                        <td align="left"><?= $v['title']; ?></td>
                        <td class="desc"><?= $v['cat_title']; ?></td> 
                        <td> 
                            <a class="but <? if($v['enabled']): ?>un<? endif; ?>block<? if($inherited): ?> disabled<? endif; ?>" href="#" onclick="return isubTypes.toggle(<?= $v['id']; ?>, this);"></a>
                            <a class="but edit<? if($inherited): ?> disabled<? endif; ?>" title="редактировать" href="#" onclick="return isubTypes.edit(<?= $v['id']; ?>);"></a>
                            <? if( !$inherited): ?>
                            <a class="but del" title="удалить" href="#" onclick="return isubTypes.del(<?= $v['id']; ?>, this);"></a>
                            <? else: ?>
                            <a class="but"></a>
                            <? endif; ?>                        
                        </td>
                    </tr>
                    <?
                    } 
                } else {
                ?>
                <tr class="norecords">
                    <td colspan="<?= $cols; ?>">нет подтипов в указанной категории</td>
                </tr>
                <? } ?>
            </table>

            <div style="margin-top: 8px;">
                <div class="left">
                    <a title="добавить тип" href="#" onclick="return isubTypes.add();" class="but add"></a>
                </div>                                                                                   
                <div class="right desc" style="width:80px; text-align:right;">
                    <span id="progress" style="margin-left:5px; display:none;" class="progress"></span>
                    &nbsp;&nbsp; &darr; &uarr;
                </div>
                <div class="clear-all"></div>
            </div>
        
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>              

<script type="text/html" id="subtype_form_tmpl">
<div class="ipopup" style="width:300px;">
<div class="ipopup-wrapper">
<div class="ipopup-title"><% if(edit){ %>Редактирование<% }else{ %>Добавление<% } %> подтипа</div>
<div class="ipopup-content">   
    <form action="" method="post">
        <input type="hidden" name="action" value="<%=action%>" />
        <input type="hidden" name="cat_id" value="<%=cat_id%>" />
        <input type="hidden" name="subtype_id" value="<%=id%>" />
        <table class="admtbl tbledit">
        <%=form%>
        <tr class="footer">
            <td colspan="2" style="text-align: center;">
                <input type="submit" class="button submit" value="Сохранить" />
                <input type="button" class="button cancel" value="Отмена" onclick="$.fancybox.close();" />
            </td>
        </tr>
        </table>
    </form>
</div> 
</div>
</div>
</script>

<script type="text/javascript">
var catID = <?= $aData['cat_id'] ?>; 
var isubTypes = (function(){
    var $progress, formTpl;
    function init()
    {
        $progress = $('#progress');
        formTpl = bff.tmpl('subtype_form_tmpl');
        <? if(!empty($aData['subtypes'])): ?>
        bff.initTableDnD('#subtypes_listing', '<?= $aData['url_action']; ?>rotate', '#progress');
        <? endif; ?>
    }
               
    function add()
    {
        bff.ajax('<?= $aData['url_action']; ?>form&subtype_id=0',{edit:0},function(data){
            if(data) {
                data.edit = 0; data.action = 'add'; data.cat_id = catID;
                $.fancybox( formTpl(data) );
            }                  
        }, $progress);
        return false;
    }
    
    function del(id, link)
    {
        bff.ajaxDelete('Удалить тип?', id, 
            '<?= $aData['url_action']; ?>delete&subtype_id='+id,
            link, {progress: $progress, repaint: false});
        return false;
    }
    
    function edit(id)
    {                          
        bff.ajax('<?= $aData['url_action']; ?>form&subtype_id='+id,{edit:1},function(data){
            if(data) {                
                data.edit = 1; data.action = 'edit'; data.cat_id = catID;
                $.fancybox( formTpl(data) );
            }                  
        }, $progress);
        return false;
    }
    
    function toggle(id, $link)
    {
        bff.ajaxToggle(id, '<?= $aData['url_action']; ?>toggle&subtype_id='+id,
               {link: $link, progress: $progress});
               return false;
    }
    
    return {init: init, add: add, edit: edit, del: del, toggle:toggle};
}());

$(function(){
    isubTypes.init();
});     
</script>