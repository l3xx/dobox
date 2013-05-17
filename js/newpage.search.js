$(function() {
    initSubCats();
    initCountryList();
    initImages();
    initCustomBusiness();
    initSort();
    initDropdown();
});


function initDropdown() {

    $(".selectBlock a").click(function() {

       var $dp = $(this).parent().find('.dropdown').toggleClass('hidden');
        $(".selectBlock .dropdown").not($dp).addClass('hidden');
    });
}

function initSort() {
    var select = $('#sort');
    var form = $('#search-form-form');
    select.change(function() {
        form.submit();
    })
}

function initImages() {
    $('.additional-images img').click(function(e){
        e.preventDefault();
        $('.main-image img').attr('src', $(this).attr('src'));
    });
}

function initSubCats() {
    var $cats = $('#search-cats');
    var $subCats = $('#search-sub-cats');
    $cats.change(function(){
        var $this = $(this);
        var $catNum = this.value;

        if ($catNum == 1) {
            $subCats.hide();
        }
        else {
            $.ajax({
                url: "/ajax/bbs?act=get-sub-cats",
                type: 'POST',
                dataType: "json",
                data: {
                    cat: $catNum
                },
                success: function(data) {
                    console.log(data);
                    $subCats.find("option").remove();
                    $subCats.append('<option value="0">Все объявления</option>');
                    $(data['data']).each(function() {
                        $subCats.append('<option value="' + this['id'] + '">' + this['title'] + '</option>');
                    });
                    $subCats.show();
                }
            });
        }
//        console.log(this.value);
    });
}

function initCountryList() {
    var $country_list = $('#search-country');
    var $region_list = $('#search-region');
    var $city_list = $('#search-city');

    $country_list.change(function(){
        var $this = $(this);
        var $country = this.value;
        $city_list.hide();

//        console.log($country);
        if ($country == 0) {
            $region_list.hide();
        }
        else {
            $.ajax({
                url: "/ajax/bbs?act=get-regions",
                type: 'POST',
                dataType: "json",
                data: {
                    country: $country
                },
                success: function(data) {
                    $region_list.find("option").remove();
                    $region_list.append('<option value="0">Все расположения</option>');
                    $(data['data']).each(function() {
                        $region_list.append('<option value="' + this['id'] + '">' + this['title'] + '</option>');
                    });
                    $region_list.show();
                }
            });
        }
//        console.log(this.value);
    });

    $region_list.change(function(){
        var $this = $(this);
        var $region = this.value;

//        console.log($country);
        if ($region == 0) {
            $city_list.hide();
        }
        else {
            $.ajax({
                url: "/ajax/bbs?act=get-cities",
                type: 'POST',
                dataType: "json",
                data: {
                    region: $region
                },
                success: function(data) {
                    $city_list.find("option").remove();
                    $city_list.append('<option value="0">Все расположения</option>');
                    $(data['data']).each(function() {
                        $city_list.append('<option value="' + this['id'] + '">' + this['title'] + '</option>');
                    });
                    $city_list.show();
                }
            });
        }
//        console.log(this.value);
    });
}

function initCustomBusiness() {
    var $ch = $('.business-custom input');
    $ch.change(function() {
        $ch.prop('disabled', false);
        console.log($ch.filter(':checked'));
        if ($ch.filter(':checked').length > 0) {
            $ch.not(':checked').prop('disabled', true);
        }
    });

}