<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Динамические свойства</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
        
        <div class="actionBar">                                             
            <div class="left">
                <? if($aData['owner_parent']!=0): ?> 
                <a href="<?= $aData['url_listing']; ?>&owner=<?= $aData['owner_parent']['id']; ?>"><?= $aData['owner_parent']['title']; ?></a>
                &rarr;&nbsp;&nbsp;
                <? endif; ?>        
                <a href="<?= $aData['url_listing']; ?>&owner=<?= $aData['owner_id']; ?>"><?= $aData['owner_title']; ?></a>
                &rarr;&nbsp;&nbsp;
                <span class="bold">динамические свойства</span>
            </div>
            <div class="right">
                <? if(false && $this->inherit === 1 ){ 
                    $vis = !func::getCOOKIE(BFF_COOKIE_PREFIX.'bbs_dynprop_inh');
                    ?>
                    <a href="#" class="ajax desc">скрыть наследуемые</a>
                <? } ?>
            </div>
            <div class="clear-all"></div>
        </div>
                                                          
        <table class="admtbl tblhover" id="dynprop_listing">
            <tr class="header nodrag nodrop">
            <? if(FORDEV): ?><th width="30">DF</th><? endif; ?>
                <th align="left">Название</th>
                <? if($this->inherit): ?><th width="100">Наследование</th><? endif; ?>
                <th width="210">Тип</th>
                <th width="80">Действие</th>
            </tr>
            <? 
            $cols = 3; if(FORDEV) $cols++; if($this->inherit) $cols++;  
            
            if(!empty($aData['dynprops'])) 
            {   $i=1; $in=0; 
                foreach($aData['dynprops'] as $v) { $inherited = $v['inherited']; if($inherited) $in++; ?>   
                <?php if(!$inherited && $in){ $in = 0; ?><tr><td style="padding:0;" colspan="<?= $cols; ?>"><hr/></td></tr><?php } ?>
                <tr align="center" class="row<?= ($i++%2); ?><? if($this->inherit===1 && $inherited): ?> nodrag nodrop<? endif; ?>" id="dnd-<?= $v['id']; ?>">
                <?  if(FORDEV): ?><td>f<?= $v['data_field']; ?></td><? endif; ?>
                    <td align="left"><?= $v['title']; ?><? if($v['is_search']): ?> <span class="desc">[поиск]</span><? endif; ?><? if($v['parent']): ?><a class="but chain" style="margin-left:5px;"></a><? endif; ?></td>
                    <? if($this->inherit): ?><td><?= (!$inherited?'нет':'да'); ?></td><? endif; ?>
                    <td><?= dbDynprops::getTypeTitle($v['type']); ?></td>
                    <td> 
                        <a class="but edit<? if($inherited): ?> disabled<? endif; ?>" title="редактировать" href="<?= $aData['url_action_owner']; ?>edit&dynprop=<?= $v['id']; ?><? if($inherited): ?>&owner=<?= $v[ $this->ownerColumn ]; ?>&owner_from=<?= $aData['owner_id']; ?><? endif; ?>"></a>
                        <? if( !$inherited || $this->isInheritParticular() ): ?>
                        <a class="but del<? if($inherited): ?> disabled<? endif; ?>" title="удалить" href="<?= $aData['url_action_owner']; ?>del&dynprop=<?= $v['id']; ?><? if($inherited): ?>&inherit=1<? endif; ?>" onclick="if(!confirm('Удалить поле безвозвратно?')) return false;"></a>
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
                <td colspan="<?= $cols; ?>">нет динамических свойств</td>
            </tr>
            <? } ?>
        </table>

        <div style="margin-top: 8px;">
            <div class="left">
                <a title="добавить новое" href="<?= $aData['url_action_owner']; ?>add" class="but add"></a>
                <? if($aData['owner_parent']!=0 && $this->isInheritParticular()): ?>
                <a title="наследовать" href="#" onclick="return dpShowInherits(this);" class="but add disabled"></a>
                <? endif; ?>
            </div>                                                                                   
            <div class="right desc" style="width:80px; text-align:right;">
                <span id="progress" style="margin-left:5px; display:none;" class="progress"></span>
                &nbsp;&nbsp; &darr; &uarr;
            </div>
            <div class="clear-all"></div>
        </div>

        <? if(!empty($aData['dynprops'])): ?>    
        <script type="text/javascript">
            $(function(){
               bff.initTableDnD('#dynprop_listing', '<?= $aData['url_action_owner']; ?>rotate', '#progress');                                                                                                                                         
            });     
        </script>
        <? endif; ?>
        
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

<? if( $this->isInheritParticular() ): ?>
<div class="blueblock whiteblock hidden" id="inherit_block">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Наследование свойств</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text" id="inherit_listing">
                
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

<script type="text/javascript">
    function dpAddInherit(link, dynpropID, ownerID)
    {
        bff.ajax('<?= $aData['url_action']; ?>&act=inherit_do&dynprop='+dynpropID+'&owner='+ownerID, {}, 
            function(data){
                location.reload();
            }, '#progress-inherit');
        return false;    
    }

    function dpAddCopy(btn, dynpropID, ownerID)
    {
        btn.disabled = true;
        btn.value = 'подождите...'; 
        bff.ajax('<?= $aData['url_action']; ?>&act=inherit_copy&dynprop='+dynpropID+'&owner='+ownerID, {}, 
            function(data) { 
                location.reload();
            }, '#progress-inherit');
        return false;    
    }
    
    function dpShowInherits(link)
    {
        $(link).fadeOut();
        bff.ajax('<?= $aData['url_action']; ?>&act=inherit_list&owner=<?= $aData['owner_id']; ?>', {}, 
            function(data) {
                $('#inherit_listing').html(data);
                $('#inherit_block').removeClass('hidden');
            }, '#progress');
        return false;
    }
</script>
<? endif; //$this->isInheritParticular() ?>