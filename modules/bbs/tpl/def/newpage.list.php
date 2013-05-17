<?php
extract($aData);
$currentId = ($f['item'] != 0) ? $f['item'] : $items[0]['id'];
$get = $_REQUEST;
$imgURL = bff::buildUrl('items', 'images');
$imgDefaultURL = bff::buildUrl('default', 'images');
$imgDefault = $imgDefaultURL . 'no-photo-100.png';

$imgArray = explode(',', $item['img']);

if (count($_REQUEST) <= 4) {
    $listText = 'Новые объявления';
}
else {
    $listText = 'Найденные объявления';
}

?>
<div class="ad-list">
    <h5 class="ad-list-title"><?= $listText ?></h5>
    <?php foreach ($items as $item): ?>
        <?php $get['item'] = $item['id']; ?>
        <a href="<?php print bff::buildUrl('', 'newpage').'?'.http_build_query($get); ?>"
           class="row-fluid ad-list-item <?php if ($currentId == $item['id']) print 'current' ?>"
        >
            <div class="span2">
                <?php if ($item['imgcnt']): ?>
                    <img src="<?= $imgURL . $item['id'] . 't' . $item['imgfav']?>">
                <?php else: ?>
                    <img src="<?= $imgDefault ?>">
                <?php endif; ?>
            </div>
            <div class="span10">
                <?php print $item['title']; ?>
            </div>
        </a>

    <?php endforeach; ?>
</div>

<?php if ($pages > 1): ?>
    <div class="pagination pagination-small">
        <ul>
            <?php $get['page'] = 1; ?>
            <?php $get['item'] = $currentId; ?>
            <li class=""><a href="<?php print bff::buildUrl('', 'newpage').'?'.http_build_query($get); ?>">&laquo;</a></li>
            <?php foreach ($paginatorItems as $item): ?>
                <li class="<?php if ($item == $currentPage) print 'active'; ?>">
                    <?php $get['page'] = $item; ?>
                    <a href="<?php print bff::buildUrl('', 'newpage').'?'.http_build_query($get); ?>"><?=$item?></a>
                </li>
            <?php endforeach; ?>

            <?php $get['page'] = $pages; ?>
            <li class=""><a href="<?php print bff::buildUrl('', 'newpage').'?'.http_build_query($get); ?>">&raquo;</a></li>
        </ul>
    </div>
<?php endif; ?>