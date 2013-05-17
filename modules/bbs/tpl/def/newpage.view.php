<?php
extract($aData);
$get = $_REQUEST;
?>

<div class="row-fluid">
    <div class="span5">

        <?php
        $imgURL = bff::buildUrl('items', 'images');
        $imgDefaultURL = bff::buildUrl('default', 'images');
        $imgDefault = $imgDefaultURL . 'no-photo-300.png';
        $imgArray = explode(',', $img);
        ?>

        <div class="main-image">
            <?php if ($imgfav): ?>
                <img src="<?= $imgURL . $id . $imgfav?>" class="img-rounded">
            <?php else: ?>
                <img src="<?= $imgDefault ?>" class="img-rounded" style="margin: 0 auto;">
            <?php endif; ?>
        </div>
        <?php if ($img): ?>
        <div class="additional-images row-fluid">
            <?php foreach ($imgArray as $img_item): ?>
                <div class="span3 add-image">
                    <img src="<?= $imgURL . $id . $img_item ?>" class="img-rounded">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="info">
            <h4>Информация о продавце:</h4>
            <ul>
                <?php if ($contacts_name): ?><li><b>ФИО</b>: <?= $contacts_name ?></li><?php endif; ?>
                <?php if ($contacts_phone): ?><li><b>Телефон</b>: <?= $contacts_phone ?></li><?php endif; ?>
                <?php if ($contacts_email): ?><li><b>Email</b>: <?= $contacts_email ?></li><?php endif; ?>
                <?php if ($descr_regions): ?><li><b>Город</b>: <?= $descr_regions ?></li><?php endif; ?>
            </ul>
        </div>

        <?php if ($info): ?>
            <div class="description">
                <h4>Описание:</h4>
                <p><?= $info ?></p>
            </div>
        <?php endif; ?>

    </div>
    <div class="span7">
        <h2><?= $title; ?></h2>
        <h3>$<?= $price; ?></h3>


        <h4>Статистика:</h4>
        <ul>
            <li>Добавлено: <?= $created ?></li>
            <li>Всего просмотров: <?= $views_total ?></li>
        </ul>
        <?= $aDynprops ?>

    </div>
</div>
