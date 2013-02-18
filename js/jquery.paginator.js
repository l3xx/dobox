/**
 * jQuery Plugin Paginator 3000 + v 1.1 
 * 
 * JavaScript Paginator based on Paginator 3000 
 * 
 * Copyright (C) 2010 Web Sites Dewelopment Laboratory. All rights reserved. 
 * Site http://www.cyberapp.ru/
 */

;(function($){

    $.fn.paginator = function (s){            
        var options = {
            pagesTotal  : 1,   //общее количество страниц
            pagesSpan   : 15,  //количество показываемых страниц
            pageCurrent : 1,   //текущая страница
            baseUrl     : document.location+'&page=%number%', //шаблон ссылки
            builCounter : function (page){ //функция построения счетчика
                return page;    
            },
            clickHandler: null,
            returnOrder : false, //обратный порядок вывода страниц
            lang        : { 
                next  : "Следующая",
                last  : "Последняя",
                prior : "Предыдущая",
                first : "Первая",
                arrowRight : 'вперед',
                arrowLeft  : 'назад'
            }
        };
                
        $.extend(options, s);
        
        options.pagesSpan = options.pagesSpan < options.pagesTotal ? options.pagesSpan : options.pagesTotal;   
        options.pageCurrent = options.pagesTotal < options.pageCurrent ? options.pagesTotal : options.pageCurrent;
        if (!options.baseUrl.match(/%number%/i)) options.baseUrl += '%number%';
            
         var html = {};
            
        function prepareHtml(el){
            $(el).html(makePagesTableHtml());
            if (options.pagesTotal == options.pagesSpan) {
                $(el).addClass('fulsize');
            };
            
            html = {
                holder: el,
                table:$(el).find('table:last'),
                trPages: $(el).find('table:last tr:first'),
                tdsPages: $(el).find('tr.pages td'),
                scrollBar: $(el).find('div.scroll_bar'),
                scrollThumb: $(el).find('div.scroll_thumb'),
                pageCurrentMark: $(el).find('div.current_page_mark')
            };
        };
            
        function getLink(page){
            return options.baseUrl.replace(/%number%/i, options.builCounter(page));
        }         
            
        function makePagesTableHtml(){                
            var next_page = (parseInt(options.pageCurrent)< parseInt(options.pagesTotal)) ? parseInt(options.pageCurrent) + 1 : options.pagesTotal; 
            var next  = '<a href="' + getLink(next_page) + '" class="arrowNext" rel="' + next_page + '">%next%</a>';
            
            var last  = '<a href="' + getLink(options.pagesTotal) + '" rel="' + options.pagesTotal + '">%last%</a>';
            
            var prev_page = (parseInt(options.pageCurrent) > 1) ? parseInt(options.pageCurrent) - 1 : 1;
            var prev = '<a href="' + getLink(prev_page) + '" class="arrowPrev" rel="' + prev_page + '">%prev%</a>';
                
            var first = '<a href="' + getLink(1) + '" rel="' + 1 + '">%first%</a>';
            
            var top_left       = options.lang.arrowLeft;
            var top_right      = options.lang.arrowRight;
            var bottom_left    = options.lang.first;
            var bottom_right   = options.lang.last;
            
            if (options.pageCurrent !== options.pagesTotal){
                top_right    = next.replace(/%next%/, top_right);
                bottom_right = last.replace(/%last%/, bottom_right);
            } else {
                top_right = '<span class="arrowNext">'+top_right+'</span>';
            }
                
            if (options.pageCurrent !== 1){
                top_left     = prev.replace(/%prev%/, top_left);
                bottom_left  = first.replace(/%first%/, bottom_left);
            } else {
                top_left = '<span class="arrowPrev">'+top_left+'</span>';
            }
            
            tdWidth = (100 / (options.pagesSpan)) + '%';
                                                                   
            code =
                '<table style="width:'+((15*35)+140)+'px;">'+
                    '<tr>' +
                        '<td align="center">' +
                            '<table width="542px"><tr class="pages">';
                                    for (i=1; i<=options.pagesSpan; i++){
                                        code += '<td width="'+tdWidth+'"></td>';
                                    }
                        code += '</tr></table>' +
                        '</td>' +                                        
                        '<td width="110" rowspan="2" class="arrowsBlock">' + top_left + '' + top_right + '<div class="clear"></div></td>' +
                    '</tr>' + 
                    '<tr>' +                                
                        '<td>' +
                            '<div class="clear"></div><div class="scrollBar">' + 
                                '<div class="scroll_trough" />' + 
                                '<div class="scroll_thumb">' + 
                                    '<div class="scroll_knob" />' + 
                                '</div>' + 
                                '<div class="current_page_mark" />' + 
                            '</div>' +
                        '</td><td></td>' +
                    '</tr>' +
                '</table>';
                    
            return code;
        };
    
        function initScrollThumb(){
            html.scrollThumb.widthMin = '8';
            html.scrollThumb.widthPercent = options.pagesSpan/options.pagesTotal * 100;
            html.scrollThumb.xPosPageCurrent = (options.pageCurrent - Math.round(options.pagesSpan/2))/options.pagesTotal * $(html.table).width();
            if (options.returnOrder) {
                html.scrollThumb.xPosPageCurrent = $(html.table).width() - (html.scrollThumb.xPosPageCurrent + Math.round(options.pagesSpan/2)/options.pagesTotal * $(html.table).width());
            }
            html.scrollThumb.xPos = html.scrollThumb.xPosPageCurrent;
            html.scrollThumb.xPosMin = 0;
            html.scrollThumb.xPosMax;
            html.scrollThumb.widthActual;
            setScrollThumbWidth();  
        };
            
        function setScrollThumbWidth(){
            $(html.scrollThumb).css({width : html.scrollThumb.widthPercent + "%"});
            html.scrollThumb.widthActual = $(html.scrollThumb).width();
            
            if (html.scrollThumb.widthActual < html.scrollThumb.widthMin)
                $(html.scrollThumb).css('width', html.scrollThumb.widthMin + 'px');
            
            html.scrollThumb.xPosMax = $(html.table).width - html.scrollThumb.widthActual;   
        };
        
        function moveScrollThumb(){
            $(html.scrollThumb).css("left", html.scrollThumb.xPos + "px");
        }
        
        function initPageCurrentMark(){
            html.pageCurrentMark.widthMin = '3';
            html.pageCurrentMark.widthPercent = 100 / options.pagesTotal;
            html.pageCurrentMark.widthActual;
            setPageCurrentPointWidth();
            movePageCurrentPoint();
        };
        
        function setPageCurrentPointWidth(){
            $(html.pageCurrentMark).css("width", html.pageCurrentMark.widthPercent + '%');
                    
            html.pageCurrentMark.widthActual = $(html.pageCurrentMark).width();
                
            if(html.pageCurrentMark.widthActual < html.pageCurrentMark.widthMin)
                $(html.pageCurrentMark).css("width", html.pageCurrentMark.widthMin + 'px');
        };
  
        function movePageCurrentPoint(){
            var pos = 0;
                
            if(html.pageCurrentMark.widthActual < $(html.pageCurrentMark).width()){
                pos = (options.pageCurrent - 1) / options.pagesTotal * $(html.table).width() - $(html.pageCurrentMark).width() / 2;
            } else {
                pos = (options.pageCurrent - 1)/options.pagesTotal * $(html.table).width();
            };
                
            if (options.returnOrder) pos = $(html.table).width() - pos - $(html.pageCurrentMark).width(); 
                
            $(html.pageCurrentMark).css({left: pos + 'px'});     
        };
        
        function initEvents (){
            moveScrollThumb();
            options.returnOrder ? drawReturn() : drawPages();
            //drag    
            $(html.scrollThumb).bind('mousedown', function(e){                                           
                var dx = e.pageX - html.scrollThumb.xPos;        
                    
                $(document).bind('mousemove', function(e){
                    html.scrollThumb.xPos = e.pageX - dx;
                        
                    moveScrollThumb();
                    options.returnOrder ? drawReturn() : drawPages();
                });
                
                $(document).bind('mouseup', function(){
                    $(document).unbind('mousemove');
                    enableSelection();
                });
                    
                disableSelection();
            });    
            //callback click    
            $(html.holder).find('a[rel!=""]').bind('click', function (e){
                return ($.isFunction(options.clickHandler) ? options.clickHandler($(this).attr('rel')) : true);
            });
            //fix resize    
            $(window).resize(function (){
                setPageCurrentPointWidth();
                movePageCurrentPoint();
                setScrollThumbWidth();
            });
            //execute link function
            function executeLink(el){
                if ($.isFunction(options.clickHandler)){
                    $(el).click();
                } else{
                    document.location = $(el).attr('href');    
                };
            }
            //keyboard navigation    
            $(document).keydown(function (e){                
                if (e.ctrlKey){
                    switch (e.keyCode ? e.keyCode : e.which ? e.which : null){
                        case 0x25:    //previous page
                                    executeLink($(options.returnOrder ? '.right.top a' : '.left.top a', html.holder));
                                    break;
                                      
                        case 0x27:    //next page
                                    executeLink($(options.returnOrder ? '.left.top a'  : '.right.top a', html.holder));  
                                    break;
                    }
                }
            })    
            //scroll navigation
            $(html.holder).bind('mousewheel', function (e){
                selector = (e.delta > 0) ? (options.returnOrder ? '.pageRight a' : '.pageLeft a')
                                         : (options.returnOrder ? '.pageLeft a'  : '.pageRight a');
                executeLink($(selector, this)); 
                return false;
            });
        };  
            
        function bindLink(el){
            if ($.isFunction(options.clickHandler)){
                $(el).find('a').bind('click', function (){
                    return options.clickHandler(options.builCounter($(this).text()));
                });  
            }
        }    
            
        function drawPages(){  
            var percentFromLeft = html.scrollThumb.xPos / $(html.table).width();
            var cellFirstValue = Math.round(percentFromLeft * options.pagesTotal);
                
            var data = "";
                
            if (cellFirstValue < 1){
                cellFirstValue = 1;
                html.scrollThumb.xPos = 0;
                moveScrollThumb();
            } else if (cellFirstValue >= options.pagesTotal - options.pagesSpan) {
                cellFirstValue = options.pagesTotal - options.pagesSpan + 1;
                html.scrollThumb.xPos = $(html.table).width() - $(html.scrollThumb).width();
                moveScrollThumb();
            };
    
            for(var i=0; i < html.tdsPages.length; i++){    
                var cellCurrentValue = cellFirstValue + i;
                if(cellCurrentValue == options.pageCurrent){
                    data = cellCurrentValue;
                } else {
                    data = '<a href="' + getLink(cellCurrentValue) + '">' + cellCurrentValue + '</a>';
                };
                
                $(html.tdsPages[i]).html(data).attr('class', (cellCurrentValue == options.pageCurrent ? 'selected' : ''));
                bindLink(html.tdsPages[i]);
            };
        };
        
        function drawReturn(){  
            var percentFromLeft = html.scrollThumb.xPos / $(html.table).width();
            var cellFirstValue = options.pagesTotal - Math.round(percentFromLeft * options.pagesTotal);
                
            var data = "";
            if (cellFirstValue < options.pagesSpan){
                cellFirstValue = options.pagesSpan;
                html.scrollThumb.xPos = $(html.table).width() - $(html.scrollThumb).width();
                moveScrollThumb();
            } else if (cellFirstValue >= options.pagesTotal) {
                cellFirstValue = options.pagesTotal;
                html.scrollThumb.xPos = 0;
                moveScrollThumb();
            };
                
            for(var i=0; i < html.tdsPages.length; i++){    
                var cellCurrentValue = cellFirstValue - i;
                
                if(cellCurrentValue == options.pageCurrent){
                    data = '<b><span>' + cellCurrentValue + '</span></b>';
                } else {
                    data = '<a href="' + getLink(cellCurrentValue) + '"><span>' + cellCurrentValue + '</span></a>';
                };
                
                $(html.tdsPages[i]).html(data);
                
                bindLink(html.tdsPages[i]);
            };
        };
            
        function enableSelection(){
            document.onselectstart = function(){
                return true;
            };
        };
        
        function disableSelection (){
            document.onselectstart = function(){
                return false;
            };  
            $(html.scrollThumb).focus();    
        };
        
        prepareHtml(this);     
        initScrollThumb();
        initPageCurrentMark();
        initEvents();     
   };
})(jQuery);