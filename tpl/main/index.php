
<div class="leftBlock">
    <div class="greyBlock">
        <div class="top"><span class="left">&nbsp;</span><div class="clear"></div></div>
        <div class="center">
            <? $total = bff::i()->Bbs_getTotalItemsCount(); ?>
            поиск среди <b class="f18 orange"><?= $total ?></b> <?= tpl::declension($total, array('объявления','объявлений','объявлений'), false); ?>
            <div class="padTop">
                <form action="/search" method="get">
                    <input type="hidden" name="quick" value="1" />
                    <span class="left"><input type="text" class="searchInput" name="q" /></span>
                    <span class="left"><select class="inputText" name="p" style="width:160px; :margin-top:3px;"><?= bff::i()->Bbs_periodSelect(); ?></select></span>
                    <div class="button left">
                        <span class="left">&nbsp;</span>
                        <input type="button" value="НАЙТИ" onclick="if($.trim(this.form.q.value)=='') this.form.q.focus(); else this.form.submit();" />
                    </div>
                    <div class="clear"></div>
                </form>
            </div>
        </div>
        <div class="bottom"><span class="left">&nbsp;</span><div class="clear"></div></div>
    </div>
</div>
