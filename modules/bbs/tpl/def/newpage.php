<?php

extract($aData);
$get = $_REQUEST;
$buy = (bool)$f['buy'];
$with_photo = (bool)$f['with_photo'];
$in_title = (bool)$f['in_title'];
$private = (bool)$f['private'];
$business = (bool)$f['business'];
$h_price = $f['h_price'] == 0 ? '' : $f['h_price'];
$l_price = $f['l_price'] == 0 ? '' : $f['l_price'];
?>

<form action="#" method="get" style="margin-bottom: 0px;" id="search-form-form">

<div id="search-form" class="" style="padding-bottom: 0px;">
    <div class="container">

            <div class="row-fluid">
                <div class="span4">
                    <input type="text" name="text" placeholder="Поиск..." class="span12"/>
                </div>
                <div class="span3">
                    <select class="span12" name="rubric" id="search-cats">
                        <option value="1">Выберите категорию</option>
                        <?php foreach ($cats as $cat) : ?>
                            <option value="<?= $cat['id'] ?>" <? if ($cat['id'] == $f['rubric']) {
                                print 'selected';
                            } ?>>
                                <?= $cat['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="span3">
                    <? if (isset($sub_cats)): ?>
                        <select class="span12" name="subrubric" id="search-sub-cats">
                            <?php foreach ($sub_cats as $cat) : ?>
                                <option value="0">Все объявления</option>
                                <option
                                    value="<?= $cat['id'] ?>" <? if ($cat['id'] == $f['subrubric']) {
                                    print 'selected';
                                } ?>>
                                    <?= $cat['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <? else: ?>
                        <select class="span12" name="subrubric" id="search-sub-cats" style="display: none">
                        </select>
                    <? endif; ?>
                </div>
                <div class="span2">
                    <button type="submit" class="btn btn-primary">Найти</button>
                    <a href="<?= $_SERVER['REDIRECT_URL'] ?>">Сброс</a>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span6">
                    <select class="span4" name="country" id="search-country">
                        <option value="0">Все расположения</option>
                        <?php foreach ($countries_list as $country) : ?>
                            <option
                                value="<?= $country['id'] ?>" <? if ($country['id'] == $f['country']) {
                                print 'selected';
                            } ?>>
                                <?= $country['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="span4" name="region" id="search-region"
                            <?php if (!isset($regions_items)): ?>style="display: none"<?php endif;?>>
                        <?php if (isset($regions_items)): ?>
                            <option value="0">Все расположения</option>
                            <?php foreach ($regions_items as $region) : ?>
                                <option
                                    value="<?= $region['id'] ?>" <? if ($region['id'] == $f['region']) {
                                    print 'selected';
                                } ?>>
                                    <?= $region['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif;?>
                    </select>
                    <select class="span4" name="city" id="search-city"
                            <?php if (!isset($cities_items)): ?>style="display: none"<?php endif;?>>
                        <?php if (isset($cities_items)): ?>
                            <option value="0">Все расположения</option>
                            <?php foreach ($cities_items as $city) : ?>
                                <option
                                    value="<?= $city['id'] ?>" <? if ($city['id'] == $f['city']) {
                                    print 'selected';
                                } ?>>
                                    <?= $city['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif;?>
                    </select>
                </div>
                <div class="span6 form-inline">
                    <label>Цена:</label>
                    <label>от</label>
                    <input type="text" class="input-mini" name="l_price" value="<?= $l_price ?>"/>
                    <label>до</label>
                    <input type="text" class="input-mini" name="h_price" value="<?= $h_price ?>"/>
                    <label>USD</label>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span3">
                    <label class="checkbox">
                        <input type="checkbox" name="in_title"  <?php if ($in_title) {
                            print 'checked="checked"';
                        } ?>/>
                        Поиск в заголовках
                    </label>
                </div>
                <div class="span3">
                    <label class="checkbox">
                        <input type="checkbox" name="with_photo" <?php if ($with_photo) {
                            print 'checked="checked"';
                        } ?>/>
                        Только с фото
                    </label>
                </div>
                <div class="span3 form-inline business-custom">
                    <label class="checkbox">
                        <input type="checkbox" name="private"  <?php if ($private) {
                            print 'checked="checked"';
                        } ?>/>
                        Только частные объявления
                    </label>
                </div>
                <div class="span3 form-inline business-custom">
                    <label class="checkbox">
                        <input type="checkbox" name="business" <?php if ($business) {
                            print 'checked="checked"';
                        } ?> />
                        Только бизнес объявления
                    </label>
                </div>
            </div>

            <div class="row-fluid">
              <div class="span12">
                <?=$dpform['form']?>
              </div>
            </div>

            <div class="row-fluid">
                <div class="span6">
                    <ul class="nav nav-tabs" style="margin-bottom: 0px;">
                        <li class="<?php if (!$buy) {
                            print 'active';
                        } ?>">
                            <a href="<?php
                            $get_copy = $get;
                            $get_copy['buy'] = 0;
                            unset($get_copy['item']);
                            print bff::buildUrl('', 'newpage') . '?' . http_build_query($get_copy);
                            ?>">Продам</a>
                        </li>
                        <li class="<?php if ($buy) {
                            print 'active';
                        } ?>">
                            <a href="<?php
                            $get_copy = $get;
                            $get_copy['buy'] = 1;
                            unset($get_copy['item']);
                            print bff::buildUrl('', 'newpage') . '?' . http_build_query($get_copy);
                            ?>">Куплю</a>
                        </li>
                        <?php if ($buy): ?><input type="hidden" value="1" name="buy"/><? endif; ?>
                    </ul>
                </div>
            </div>

    </div>
</div>


<div class="container content">

    <?php if ($no_view): ?>

        <h3>Поиск не дал результатов</h3>

    <?php else: ?>

        <div class="row-fluid">

        </div>

        <div class="row-fluid">
            <div class="span6">
                <b><?=$item_view_obj['cat1_title']?> > <?=$item_view_obj['cat2_title']?></b>
            </div>
            <div class="span6 form-horizontal">
                <fieldset>
                    <div class="control-group pull-right">
                        <label class="control-label">Сортировка:</label>
                        <div class="controls">
                            <select name="sort" id="sort">
                                <option value="0" <?php if ($f['sort']==0) print 'selected' ?>>Самые новые</option>
                                <option value="1" <?php if ($f['sort']==1) print 'selected' ?>>Самые старые</option>
                                <option value="2" <?php if ($f['sort']==2) print 'selected' ?>>Самые дорогие</option>
                                <option value="3" <?php if ($f['sort']==3) print 'selected' ?>>Самые дешёвые</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span3">
                <?php print $list; ?>
            </div>
            <div class="span9">
                <div class="row-fluid">
                    <div class="span6 offset6">
                        <div class="pull-right">
                            <?php if ($item_view_obj['previous'] != 0): ?>
                                <a href="
                        <?php
                                $get['item'] = $item_view_obj['previous'];
                                print bff::buildUrl('', 'newpage') . '?' . http_build_query($get);
                                ?>
                        "><<< Предыдущее объявление</a>
                            <?php endif; ?>
                            |
                            <?php if ($item_view_obj['next'] != 0): ?>
                                <a href="
                        <?php
                                $get['item'] = $item_view_obj['next'];
                                print bff::buildUrl('', 'newpage') . '?' . http_build_query($get);
                                ?>
                        ">Следующее объявление >>></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php print $item_view; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

</form>
