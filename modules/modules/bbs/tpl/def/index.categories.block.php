<div class="blockCat openBlock expand-<?= $aData['id']; ?>" style="display: none;">
  <div class="top"></div>
  <div class="center">
    <span class="caption"><?= $aData['title']; ?></span>
    <ul>
<?php  foreach($aData['sub'] as $v2) { ?>
        <li><a href="/search?c=<?= $v2['id'] ?>"><?= $v2['title']; ?></a> (<?= $v2['items']; ?>)</li>
<?php } ?>
      <li><a href="javascript:void(0);" onclick="indexCatExpand(<?= $aData['id'] ?>, false);" class="orangeBord">скрыть</a></li>
    </ul>
  </div>
  <div class="bottom"></div>
</div>

