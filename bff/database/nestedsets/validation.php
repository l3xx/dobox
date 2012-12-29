<table class="admtbl">
<tr class="header">
    <th width="65">#</th>
    <th width="400" align="left">Правило</th>
    <th align="left">Результат</th>
</tr>     
<tr class="row0"> 
    <td>1</td>
    <td align="left">Левый ключ ВСЕГДА меньше правого</td>
    <? if($aData[0]=='') { ?><td class="clr-success" align="left">OK</td><? } else { ?><td class="clr-error" align="left">ошибка: <?= $aData[0] ?></td><? } ?>
</tr>
<tr class="row1">
    <td>2</td>
    <td align="left">Наименьший левый ключ ВСЕГДА равен 1</td>
    <? if($aData[1]=='') { ?><td class="clr-success" align="left">OK</td><? } else { ?><td class="clr-error" align="left">ошибка: <?= $aData[1] ?></td><? } ?>
</tr>
<tr class="row0">
    <td>3</td>
    <td align="left">Наибольший правый ключ ВСЕГДА равен двойному числу узлов</td>
    <? if($aData[2]=='') { ?><td class="clr-success" align="left">OK</td><? } else { ?><td class="clr-error" align="left">ошибка: <?= $aData[2] ?></td><? } ?>
</tr>
<tr class="row1">
    <td>4</td>
    <td align="left">Разница между правым и левым ключом ВСЕГДА нечетное число</td>
    <? if($aData[3]=='') { ?><td class="clr-success" align="left">OK</td><? } else { ?><td class="clr-error" align="left">ошибка: <?= $aData[3] ?></td><? } ?>
</tr>
<tr class="row0">
    <td>5</td>
    <td align="left">Если уровень узла нечетное число то тогда левый ключ ВСЕГДА четное число, то же самое и для четных узлов</td>
    <? if($aData[4]=='') { ?><td class="clr-success" align="left">OK</td><? } else { ?><td class="clr-error" align="left">ошибка: <?= $aData[4] ?></td><? } ?>
</tr>
<tr class="row1">
    <td>6</td>
    <td align="left">Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый</td>
    <? if($aData[5]=='') { ?><td class="clr-success" align="left">OK</td><? } else { ?><td class="clr-error" align="left">ошибка: <?= $aData[5] ?></td><? } ?>
</tr>
</table>
