

<script type="text/javascript">
{literal}  
    var cUpdateYCoordsProcess = false;
    function cUpdateYCoords(cityTitle){
        if(cUpdateYCoordsProcess) return;
        if(cityTitle.val()){
            cUpdateYCoordsProcess = true;              
            $('#progress-ycoords').show();
            //страна, город           
            var geocoder = new YMaps.Geocoder(app.yCountry.title+','+cityTitle.val(), {result: 5, boundedBy: new YMaps.GeoBounds(YMaps.GeoPoint.fromString(app.yCountry.bounds[0]),YMaps.GeoPoint.fromString(app.yCountry.bounds[1])) });
            YMaps.Events.observe(geocoder, geocoder.Events.Load, function () {
                if(this.length()){    
                    $('#ycoords').val( this.get(0).getGeoPoint().toString() ).blur();
                    $('#progress-ycoords').hide(); 
                }
                cUpdateYCoordsProcess = false;
            });   
        } else {
            cityTitle.focus();
        }
    }
{/literal} 
</script>

<div class="blueblock lightblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Города / {if $event=='cities_edit'}Редактировать{else}Добавить{/if} город</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">

            <form action="" name="citiesForm" method="post">   
            <table class="admtbl tbledit">  
            <tr class="required check-select">
                <td class="row1" width="100"><span class="field-title">Область</span>:</td>
                <td class="row2">
                    <select name="region_id" style="width:300px;">
                         <option value="0" class="bold">Не указана</option>
                         {$aData.regions_options}
                    </select>
                </td>
                {if $event=='cities_edit'}
                <td style="vertical-align:top;" align="right"><a href="index.php?s=items&amp;ev=items_listing&amp;adv=1&amp;city={$aData.id}">пользователи в городе ({$aData.users})</a></td>
                {/if}
            </tr>
            <tr class="required">
	            <td class="row1"><span class="field-title">Название</span>:</td>
	            <td class="row2">
                    <input style="width:290px;" type="text" maxlength="150" name="title" id="title" value="{$aData.title}" />
                </td>
            </tr>
            <tr class="required">
                <td class="row1"><span class="field-title">YКоординаты</span>:</td>
                <td class="row2">
                    <input type="text" value="{$aData.ycoords}" maxlength="30" name="ycoords" id="ycoords" class="desc" />
                    &nbsp;&nbsp;<a href="#" onclick="cUpdateYCoords($('#title')); return false;" class="ajax">обновить</a>
                    <span id="progress-ycoords" style="margin-left:5px; display:none;" class="progress"></span> 
                </td>
            </tr>
            <tr class="required">
                <td class="row1"><span class="field-title">Keyword</span>:</td>
                <td class="row2">
                    <input style="width:290px;" type="text" maxlength="150" name="keyword" value="{$aData.keyword}" />
                </td>
            </tr>
            <tr>
                <td class="row1"><span class="field-title">Основные города</span>:</td>
                <td class="row2" colspan="2"><input type="checkbox" name="main" {if $aData.main}checked="checked"{/if} /></td>
            </tr> 
            <tr>
                <td class="row1"><span class="field-title{if !$aData.enabled} bold{/if}">Включен</span>:</td>
                <td class="row2" colspan="2"><input type="checkbox" name="enabled" {if $aData.enabled}checked="checked"{/if} /></td>
            </tr>         
            <tr class="footer" >
                <td colspan="3">
                    <input type="submit"  class="button submit" value="Сохранить" />
                    {if $event=='cities_edit'}
                    <input type="button"  class="button delete" value="Удалить" onclick="if(confirm('Удалить город?')) document.location='index.php?s={$class}&amp;ev=cities_delete&amp;rec={$aData.id}'; " />
                    {/if}
                    <input type="button"  class="button cancel" value="Отмена" onclick="document.location='index.php?s={$class}&amp;ev=cities_listing{if $aData.main}_main{/if}';" />
                </td>
            </tr>

            </table>
            </form>
            
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

{if $event=='cities_edit'}

<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Районы города</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
        
            <table class="admtbl">  
                <tr class="header row0">
                    {if $fordev}<th width="80">ID</th>{/if}  
                    <th align="left">Название</th>
                    <th width="80">Пользователи</th> 
                    <th width="85">Действие</th>
                </tr>
            </table>
            <table class="admtbl tblhover">
                {foreach from=$aData.cregions item=v key=k}
                <tr class="row1">
                    {if $fordev}<td width="76">{$v.region_id}</td>{/if}
                    <td align="left">{$v.title}</td>                                    
                    <td width="76"><a>{$v.users}</a></td>
                    <td width="81">                                           
                        <a class="but edit" title="Редактировать" href="#" onclick="crEdit({$v.region_id}, 'index.php?s=sites&amp;ev=cities_regions&act=edit&city={$aData.id}', '#progress-cregions'); return false;"></a>
                        <a class="but del" title="Удалить" href="#" onclick="bff.ajaxDelete('Удалить район города?', {$v.region_id}, 'index.php?s=sites&ev=cities_regions&act=delete&city={$aData.id}', this, {ldelim}progress: '#progress-cregions', repaint: false{rdelim}); return false;"></a>
                    </td>
                </tr>
                {foreachelse}
                <tr align="center" class="row1">
                    <td colspan="{if $fordev}3{else}2{/if}" class="alignCenter">
                        <span class="admDescription">у данного города нет районов</span>
                    </td>
                </tr>                   
                {/foreach}
            </table>
            <div>
                <br />  
                <div class="left">
                    <!--<a class="but add" id="modifyRegionLink" title="Добавить район города" href="#" onclick="crToggleForm(1,false); return false;" ></a>-->
                    &nbsp; <a href="#" onclick="crSearchToggle(); return false;" class="ajax">найти при помощи Yandex.Карты</a><span class="admDescription">:&nbsp;&nbsp;'{$smarty.const.GEO_YMAPS_COUNTRY_TITLE},{$aData.title},район'</span>
                </div>
                <div class="right description" style="width:80px; text-align:right;">
                    <span id="progress-cregions" style="margin-left:5px; display:none;" class="progress"></span>
                </div>
                <br />
            </div> 
            
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

<div class="blueblock lightblock" id="modifyRegion" style="display:none;">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left caption">Добавить район города</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
            <form action="index.php?s={$class}&ev=cities_regions" method="post" name="modifyRegionForm">
                <input type="hidden" name="act" value="add" />
                <input type="hidden" name="city" value="{$aData.id}" />
                <input type="hidden" name="region" value="0" />
                <table class="admtbl tbledit">
                    <tr class="required">                          
                        <td style="width:80px;" class="row1"><span class="field-title">Название</span>:</td>
                        <td class="row2">
                             <input type="text" name="title" id="regionTitle" value="" class="stretch" />
                        </td>
                    </tr>                     
                    <tr>                          
                        <td class="row1"><span class="field-title">YBounds</span>:</td>
                        <td class="row2">
                             <input type="text" name="ybounds" id="regionYbounds" value="" class="stretch" />
                        </td>
                    </tr> 
                    <tr>
                        <td class="row1"><span class="field-title">YPolygon</span>:</td>
                        <td class="row2">
                            <input type="text" name="ypoly" id="regionYpoly" value="" readonly="readonly" class="desc stretch" />
                            &nbsp;&nbsp;<a href="#" onclick="crEditRegionPolygon(); return false;" class="ajax">редактировать</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="row2" colspan="2">
                            <div id="YMapRegion" style="height:380px"></div>  
                        </td>
                    </tr>  
                    <tr>
                        <td colspan="2" class="row1">
                            <input type="submit"  class="button submit" value="Сохранить" />
                            <input type="reset"  class="button cancel" value="Отмена" onclick="crToggleForm(0,false);" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div> 

<div class="blueblock whiteblock" id="searchRegion" style="display:none;">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left caption">Поиск районов города</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">    
            <div id="YMapSearch" style="height:380px"></div><br />
            <form action="index.php?s={$class}&ev=cities_regions" method="post" name="searchRegionForm"> 
            <input type="hidden" name="act" value="add_many" />
            <input type="hidden" name="city" value="{$aData.id}" />         
            <table class="admtbl">
                <tr class="header row0">
                    <th>Название</th>
                    <th width="250">YBounds</th>
                    <th width="60">Действие</th>
                </tr>
                <tbody id="searchRegionResults">
                    <tr id="searchRegionResultsEmpty"><td colspan="3" class="alignCenter admDescription">воспользуйтесь поиском Yandex, нажав 'Найти' и выбрав необходимые районы из найденных</td></tr>
                </tbody>
                <tr>
                    <td colspan="2" class="row1">
                        <input type="submit"  class="button submit" value="Добавить найденные" />
                        <input type="reset"  class="button cancel" value="Отмена" onclick="crSearchToggle(0,false);" />
                    </td>
                </tr>
            </table>
            </form>  
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  

<script type="text/javascript">
    var crCityID = {$aData.id};
    var crCityCoords = '{$aData.ycoords}';
    {literal}
    var crPolygons   = {};   
    var oMapSearch = null; 
    var oMapRegion = null;
    var helper = null;

    // Кодирование точек ломанной
    function encodePoints (points) {
        var array = [],                     // Временный массив для точек
            prev = new YMaps.Point(0,0),    // Предыдущая точка
            coef = 1000000;                 // Коэффициент

        // Обработка точек
        for (var i = 0, geoVector, currentPoint; i < points.length; i++) {
            currentPoint = points[i].copy();

            // Нахождение смещение относительно предыдущей точки
            geoVector = currentPoint.diff(prev).neg();

            // Умножение каждой координаты точки на коэффициент и кодирование
            array = array.concat(Base64.encode4bytes(geoVector.getX() * coef), Base64.encode4bytes(geoVector.getY() * coef));
            prev = currentPoint;
        }

        // Весь массив кодируется в Base64
        return Base64.encode(array);
    }

    // Класс для работы с Base64
    // За основу взят класс с http://www.webtoolkit.info/
    var Base64 = new function () {
        var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_=";

        this.encode4bytes = function (x) {
            var chr = [];
            for (var i = 0; i < 4; i++) {
                chr[i] = x & 0x000000ff;
                x = x >> 8;
            }
            return chr;
        }

        this.encode = function (input) {
            var output = "",
                chr1, chr2, chr3, enc1, enc2, enc3, enc4,
                i = 0,
                inputIsString = typeof input == "string";

            while (i < input.length) {
                chr1 = input[i++];
                chr2 = input[i++];
                chr3 = input[i++];
                
                enc1 = chr1 >> 2
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6)
                enc4 = chr3 & 63;
                
                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }      

                output +=
                    _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
                    _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
                    
            }

            return output;
        }
    }
    
    function crShowRegionPolygon(regionID, encodedPoints, name)
    {
        oMapRegion.removeAllOverlays();  
        
        if(encodedPoints){     
            if(!crPolygons[regionID]){
                crPolygons[regionID] = YMaps.Polygon.fromEncodedPoints(encodedPoints,
                    '',{ style: "polygon#CityRegion", hasHint: 1, hasBalloon: 0});
                crPolygons[regionID].name = name;
                YMaps.Events.observe(crPolygons[regionID], crPolygons[regionID].Events.PositionChange, function (poly) {
                    $("#regionYpoly").attr('value', encodePoints(poly.getPoints()));
                });  
            }
            oMapRegion.addOverlay(crPolygons[regionID]);  
            crPolygons[regionID].stopEditing();       
        }  
    }
    
    function crEditRegionPolygon()
    {
        var regionID = document.forms.modifyRegionForm.elements['region'].value;
        if(regionID){
            if(crPolygons[regionID]._editor)
                crPolygons[regionID].stopEditing();
            else
                crPolygons[regionID].startEditing();
        }
    }
    
    function crToggleForm(show, edit)
    {                    
        if(show) {
            $('#modifyRegionLink').hide();
            if(edit) {            
                  $('#modifyRegion span.caption').html(edit.caption);
                  $('#regionTitle').val(edit.title);
                  $('#regionYbounds').val(edit.ybounds); 
                  document.forms.modifyRegionForm.elements['region'].value = edit.region_id;
                  document.forms.modifyRegionForm.elements['act'].value = 'edit';  
                  
                  if(!edit.ypoly)
                  {
                    //create polygon from bounds
                    var b = edit.ybounds.split(';');
                    b = new YMaps.GeoBounds( YMaps.GeoPoint.fromString(b[0]), YMaps.GeoPoint.fromString(b[1]) );
                    edit.ypoly = encodePoints([b.getLeftTop(), b.getRightTop(), b.getRightBottom(), b.getLeftBottom()]);
                    $('#regionYpoly').val(edit.ypoly); 
                  } else {
                    $('#regionYpoly').val(edit.ypoly);
                  }
                  
                  crShowRegionPolygon(edit.region_id, edit.ypoly, edit.title);   
            }
             
            $('#modifyRegion').slideDown('fast');
            helper.check();
            
            oMapRegion.redraw();
        } else {
            
            $('#modifyRegion').slideUp('fast', function(){ 
                $('#modifyRegionLink').fadeIn('fast');  
                $(this).find('span.caption').html('Добавить район города');
                document.forms.modifyRegionForm.elements['region'].value = 0; 
                document.forms.modifyRegionForm.elements['act'].value = 'add'; 
            });
        }
    }
    
    function crEdit(regionID, url, progress) {                     
        bff.ajax(url, {region: regionID}, function(data){
            if(data) {            
                crToggleForm(1, $.extend({caption: 'Редактирование района города'}, data) );
            }  
        }, progress);
    }
    
    function crSearchToggle()
    {
        var vis = $('#searchRegion').slideToggle().is(':visible');
        if(vis){            
            oMapSearch.redraw();
            $(oMapSearch.getContainer()).find('.YMaps-search-control-text').val(app.yCountry.title+',{/literal}{$aData.title}{literal},район').focus().blur();
        }
    }

    var csSearchCounter = 0, csSearchIndex = 0; 
    function crSearchRegionAdd( geoResult ){

        if($('#searchRegionResults > tr').length == 1)
            $('#searchRegionResultsEmpty').hide();

        var b = geoResult.getBounds(); 
        var ybounds = [b.getLeftBottom().toString(), b.getRightTop().toString()].join(';'); 
        var name = geoResult.AddressDetails.Country.AdministrativeArea.Locality.DependentLocality.DependentLocalityName;
        var ypoly = encodePoints([b.getLeftTop(), b.getRightTop(), b.getRightBottom(), b.getLeftBottom()]);
        
        var sRegionRow = '<tr id="crsrch'+csSearchIndex+'"><td>'+ geoResult.text +'</td>\
                    <input type="hidden" name="regionbounds['+name+']" value="'+ybounds+'">\
                    <input type="hidden" name="regionpoly['+name+']" value="'+ypoly+'">\
                 <td>'+ybounds+'</td>\
                 <td><a class="but del" href="#" style="margin-left:15px;" onclick="crSearchRegionRemove('+csSearchIndex+'); return false;"></a></td>\
                 </tr>';
        csSearchIndex++; 
        
        $('#searchRegionResults').append( sRegionRow );
    }
    function crSearchRegionRemove(index){
        $('#crsrch'+index).remove();
        if($('#searchRegionResults > tr').length == 1){
            $('#searchRegionResultsEmpty').show();
        }
    }

    $(document).ready( function(){
        helper = new bff.formChecker( document.forms.modifyRegionForm );
                 new bff.formChecker( document.forms.citiesForm );

        YMaps.load(function(){
            //карта поиска районов
            oMapSearch = new YMaps.Map($('#YMapSearch')[0]);
            oMapSearch.setCenter(YMaps.GeoPoint.fromString(crCityCoords), 11);
            oMapSearch.addControl(new YMaps.TypeControl([YMaps.MapType.MAP, YMaps.MapType.HYBRID]));
            oMapSearch.addControl(new YMaps.Zoom());    
            var oMapSearchCtrl = new YMaps.SearchControl({width:432, resultsPerPage:8, noPlacemark:true, noCentering:true});
            YMaps.Events.observe(oMapSearchCtrl, oMapSearchCtrl.Events.Select, function(searchCtrl, geoResult) {
                  if(csSearchCounter++)
                     crSearchRegionAdd( geoResult );
            });
            oMapSearch.addControl(oMapSearchCtrl);  

            //карта региона
            oMapRegion = new YMaps.Map(document.getElementById('YMapRegion'));
            oMapRegion.setCenter(YMaps.GeoPoint.fromString(crCityCoords), 11);
            oMapRegion.addControl(new YMaps.Zoom());    
            
            var style = new YMaps.Style("default#greenPoint");
            style.polygonStyle = new YMaps.PolygonStyle();
            style.polygonStyle.fill = 1;
            style.polygonStyle.outline = 1;
            style.polygonStyle.strokeWidth = 3;
            style.polygonStyle.strokeColor = "ffff0088";
            style.polygonStyle.fillColor = "ff000055";
            YMaps.Styles.add("polygon#CityRegion", style);
        });
        
    }).keydown(function(e) {
            if (e.keyCode == 27) { 
                nothing(e);
                crToggleForm(0);
            }
    });
    
    {/literal} 
</script>

{/if}
