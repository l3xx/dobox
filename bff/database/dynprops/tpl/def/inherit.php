<div class="actionBar">     
    <span class="bold">Динамические свойства доступные для наследования:</span>
</div>
                                                  
<table class="admtbl">
    <tr class="header nodrop nomove">
    <? if(FORDEV): ?><th width="30">DF</th><? endif; ?>
        <th align="left">Название<span id="progress-inherit" style="margin-left:5px; display:none;" class="progress"></span></th>   
        <th width="200">Тип</th>
        <th width="170">Действие</th>
    </tr>
    <? 
    if(!empty($aData['dynprops'])) 
    {
        foreach($aData['dynprops'] as $k=>$v) { ?>   
        <tr align="center" class="row<?= ($k%2); ?>" id="dnd-<?= $v['id']; ?>">
        <?  if(FORDEV): ?><td>f<?= $v['data_field']; ?></td><? endif; ?>
            <td align="left"><?= $v['title']; ?><? if($v['is_search']): ?> <span class="desc">[поиск]</span><? endif; ?></td>
            <td><?= dbDynprops::getTypeTitle($v['type']); ?></td>
            <td>              
                <? if($v['inherited']){ ?><a class="but"></a>
                <? } else { ?>
                    <a class="but add disabled" title="наследовать" href="#" onclick="return dpAddInherit(this, <?= $v['id']; ?>, <?= $aData['owner_id']; ?>);"></a>
                <? } ?>
                <a class="but edit disabled" target="_blank" href="<?= $aData['url_action']; ?>&act=edit&dynprop=<?= $v['id']; ?>&owner=<?= $v[ $this->ownerColumn ]; ?>"></a>
                <input type="button" class="button submit" value="копировать" onclick="dpAddCopy(this, <?= $v['id']; ?>, <?= $aData['owner_id']; ?>);" />
            </td>
        </tr>
        <?
        } 
    } else {
    ?>
    <tr class="norecords">
        <td colspan="<?= (FORDEV?4:3); ?>">нет доступных динамических свойств для наследования</td>
    </tr>
    <? } ?>
</table>