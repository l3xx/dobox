var bbsSearch = (function(){
    var $form, $doc = document, $filter, $filterContent, $filterShowLink, 
        $list, $sett, $catTypes, $catSubTypes, //$ph,
        $viewTypeTable, $viewTypeList,
        $viewPPLink, $viewPPDropdown, 
        $progress, process = false, f = {}, child_cache = {}, child_tmpl, 
        dd_mouse_in = false, dd_fb_active = 0, lastQuery, History, filters_cache={};
    var types = {text: 1, textarea: 2, wysiwyg: 3, radioyesno: 4, checkbox: 5, 
                 select: 6, multipleselect: 7, radiogroup: 8, checkboxgroup: 9, 
                 number: 10, range: 11, country:12, state:13, date: 14};
        
    function init(o) { 
        History = window.History;
        
        f = o.f; pr = o.pr;     
 
        $form = $('#searchForm');
        $catTypes = $('#searchCatTypes', $form);
        $catSubTypes = $('#searchCatSubTypes', $form);
        $filter = $('#searchFilter', $form);
        $filterContent = $('>div>div.filterContent', $filter);
        $list = $('#searchResults');  
        $sett = $('#searchSett', $form);
        $progress = $('.progress', $sett);
        
        $list.delegate( 'div.my', 'mouseenter mouseleave click', function(e){
            switch(e.type) {
                case 'mouseover': {
                    $(this).addClass('advanced');
                    $('>.actionsLink', this).removeClass('hidden');
                } break;
                case 'mouseout': {
                    $(this).removeClass('advanced');
                    $('>.actionsLink', this).addClass('hidden');
                } break;
                case 'click': { 
                    if(!(!e.target || $(e.target).is('a') /*|| $(e.target).hasClass('actionsLink')*/ || $(e.target.parentNode).is('a'))) {
                        $doc.location = '/item/'+$(this).attr('rel');
                    }
                } break;
            }
        });
        $list.delegate( '.item', 'click', function(e){
            if(!(!e.target || $(e.target).is('a') || $(e.target.parentNode).is('a'))) {
                $doc.location = '/item/'+$(this).attr('rel');
            }
        });
        
        $('a', $catTypes).click(function(e){
            nothing(e);
            var ct = intval( this.rel );
            if(f.ct!=ct && !process) {
                $(this).addClass('active');
                $(this).siblings().removeClass('active');
                setFormParam('ct', ct);
                update();
            }
        });

        $('a', $catSubTypes).click(function(e){
            nothing(e);
            var sct = intval( this.rel );
            if(f.sct!=sct && !process) {
                $(this).addClass('active');
                $(this).siblings().removeClass('active');
                setFormParam('sct', sct);
                update();
            }
        });
        
        ($filterShowLink = $('.filter-show', $filter)).click(function(e){
            nothing(e); showFilter( $filterShowLink );
        }); 
        var $on = $('.filter-on', $filter), $off = $('.filter-off', $filter);
        $on.click(function(e){ nothing(e); if(enableFilter(true, $on, $off)){ showFilter( $filterShowLink, true );  update(); } }); 
        $off.click(function(e){ nothing(e); if(enableFilter(false, $on, $off)){ showFilter( $filterShowLink, false ); update();} }); 
        
        $ph = $('input[name="ph"]', $form);
        
        $viewTypeTable = $('>div.viewTypes a.table', $sett); 
        $viewTypeList = $('>div.viewTypes a.list', $sett);
        $viewTypeTable.click(function(e){ nothing(e); if(setViewType(2)) update(); });
        $viewTypeList.click(function(e){ nothing(e); if(setViewType(1)) update(); });
        
        var $viewPP = $('>div.viewPP', $sett); 
        $viewPPLink = $('a.down', $viewPP); 
        $viewPPDropdown = $('.dropdown', $viewPP);
        $viewPPLink.click(function(e){ nothing(e);
            $viewPPDropdown.toggle();
        });
        $('a', $viewPPDropdown).click(function(e){ nothing(e);
            var num = intval(this.rel);
            $viewPPDropdown.hide();
            if(f.pp==num) return false;      
            setViewPerpage(num);
            update();
        });
        
        //filter dropdowns
        $filterContent.delegate('.selectBlock a', 'click', function(e){
            nothing(e);
            var $block = $(this).parent();
            if($block.hasClass('open')) { 
                $('.submit', $block).triggerHandler('click');
                return;
            } else {
                dd_fb_active++;
                fbToggle($block, true);
            }     
            $('.selectBlock', $filterContent).not($block).each(function(){ if($(this).hasClass('open')){ $('.submit', this).triggerHandler('click'); }  fbToggle($(this), false); });
        });
        
        $filterContent.delegate('.selectBlock input:checkbox', 'click', function(e){  
            var $c = $(this); var isParent = $c.hasClass('p');
            if( $c.is(':checked') ) { 
                var $ul = $c.parents('div:first');
                if( $c.hasClass('checkAll') ) {
                    $('input:checkbox:not(.checkAll)', $ul).removeAttr('checked');
                    $c.attr('disabled', 'disabled').parent().addClass('select');
                    if( isParent ) updateFilterChild($c, false);
                } else {
                   $('input.checkAll', $ul).removeAttr('checked').removeAttr('disabled').parent().removeClass('select'); 
                   if( isParent ) updateFilterChild($c, true);
                }
            } else {
                if(isParent) {
                    updateFilterChild($c, false);
                }
            } 
            
            if (e.stopPropagation) e.stopPropagation(); 
            e.cancelBubble = true;
        });
        
        $form.submit(function(){
            update(true);
            return false;
        });
        
        $(window).bind('statechange', function(e) {
            var state = History.getState(); 
            //console.log( 'statechange: ' + (state.data.query == lastQuery ? 'ok' : 'update') );
            if(!state.data || $.isEmptyObject(state.data)) {
                return updateByReload( ( History.emulated.pushState ? '/'+History.getHash() : state.hash ) );
            }            
            if(state.data.query !== lastQuery) {
                if(!filters_cache[state.id]) {
                    return updateByReload( '/search?'+state.data.query );
                }
                var p = state.data.data; 
                if(!$.isPlainObject(p)) return;
                if(p.ct && f.ct!=intval(p.ct)) {
                    $('a[rel="'+p.ct+'"]', $catTypes).addClass('active').siblings().removeClass('active');
                    setFormParam('ct', intval(p.ct));
                }
                setFormParam('q', p.q || '');
                $('select[name="p"]', $form).val(p.p || 0);
                enableFilter(intval(p.fe||0),$on,$off);
                $filterContent.html(filters_cache[state.id]); 
                showFilter($filterShowLink, intval(p.fh) == 0);
                //$ph = $('input[name="ph"]', $form);
                //if(p.ph) { $ph.attr('checked','checked'); } else { $ph.removeAttr('checked'); }
                setViewType(p.v);
                setViewPerpage(p.pp);
                search();
            }
        });
        
        $('body').mouseup(hideDropdowns);
        $('.selectBlock > .dropdown, .selectBlock > a', $filterContent).live('hover', function(e){ 
            switch(e.type) {
                case 'mouseover': dd_mouse_in = true;  break;
                case 'mouseout':  dd_mouse_in = false; break;
            }
        });
  
        var q = prepareQuery(); lastQuery = q.query;
        child_tmpl = bff.tmpl('child_filter_tmpl');
    }
    
    function onPage(page)
    {
        setFormParam('page', page || 1);
        update(false);
        return false;
    }
    
    function update(submit)
    {   
        //console.log( 'update' );
        var q = prepareQuery();
        if(submit === true) {
            updateByReload('/search?'+q.query);
        } else {
            if( lastQuery == q.query ) { return; }
            History.pushState(q, $doc.title, '?'+(lastQuery = q.query) ); 
            var state = History.getLastSavedState();
            filters_cache[state.id] = $filterContent.formhtml();
            search();
        }
    } 
       
    function updateByReload(url)
    {                   
        $doc.location = url;
    }
    
    function search()
    {   
        if(process) return; process = true;
        bff.ajax('/search', lastQuery, function(data) {
            if(data && data.res) {
                $list.html(data.list);
            }    
            process = false;    
        }, $progress);
    }
    
    function query2obj(qa) 
    {
        if (!qa) return {};
        var query = {}, dec = function (str) {
            try {
                return decodeURIComponent(str);
            } catch (e) { return str; }
        };             
        qa = qa.split('&');
        $.each(qa, function(i, a) {
            var t = a.split('=');
            if (t[0]) {
                var v = dec(t[1] + '');
                if (t[0].substr(t.length - 2) == '[]') {
                    var k = dec(t[0].substr(0, t.length - 2));
                    if (!query[k]) {
                        query[k] = [];
                    }
                    query[k].push(v);
                } else {
                    query[dec(t[0])] = v;
                }
            }
        });
        return query;
    }
    
    function prepareQuery()
    {
        var x = {}, y = {};
        $.each($(':input', $form).serializeArray(), function(i, field){
            var v = field.value; y[field.name] = v;
            if(v && v!=0 && v!='')
                x[field.name] = v;
        });
        return {query: $.param(x), data: y};
    }
    
    function submitFilter(type, id, blockSelector, isParent, unit, btn)
    {
        var form = $form.get(0);
        var $block = $(blockSelector, $filterContent), sel=false, selPlus = false;
        switch(type)
        {
            case types.number: 
            case types.range:
            {
                var from = $('.from', $block), fromto = false;
                if(from.length) {
                    var from = toFloat(from.val()), to = toFloat($('.to', $block).val());
                    if(from>0 || to>0) {
                        if(from>0 && to>0) { sel = from+' - '+to; }
                        else if(from>0) { sel = 'от '+from; }
                        else{ sel = 'до '+to; }
                        sel += ' '+unit;
                        fromto = true;
                    }
                }                     
                //ranges
                var rangesSel = $('input[type="checkbox"]:checked:not(.checkAll)', $block).parent();
                if(rangesSel.length>0) {
                    if(!fromto) sel = rangesSel.first().find('span').html();
                    if(fromto || rangesSel.length>1) selPlus = true;
                }
                
                fbSel($block, sel, selPlus);                
            } break;
            case types.select:     
            case types.multipleselect:
            case types.radiogroup:
            case types.radioyesno:
            case types.checkbox:
            case types.checkboxgroup:
            {
                var valuesSel = $('input[type="checkbox"]:checked:not(.checkAll)', $block);
                if(valuesSel.length>0) {
                    sel = valuesSel.first().parent().find('span').html();
                    if(valuesSel.length>1) selPlus = true;
                } 
                fbSel($block, sel, selPlus);                
            } break;
            case 'r': {
                var regs = [];
                $('select[value!=0]', $block).each(function(i,e){
                    regs.push( e.options[e.selectedIndex].text );
                    
                });
                if(regs.length == 3 ) regs.splice(0,1);
                fbSel($block, (regs.length>0 ? regs.join(', ') : false), false);
            } break;
        }
        dd_fb_active--;        
        fbToggle($block, false);
        if(f.fe==0) {
            enableFilter(true);
        }
        if(!dd_fb_active) {
           update(); 
        }
    }
    
    function updateFilterChild($i, add)
    {
        var pid = $i.attr('rel'), pval = intval($i.val()), key = pid+'-'+pval,
            $block = $('.d'+pid+'_child', $filterContent),
            $values = $('.dropdown > .values', $block);

        if(add) {
            if(child_cache[key]) {
                updateFilterChildHTML(pid, $block, $values);
            } else {
                var vname = $i.parent().find('span').html();
                bff.ajax('/ajax/bbs?act=dp-child-filter', {dp_id:pid, dp_value:pval}, function(data){
                    if(data && data.res) {
                        data.form['vname'] = vname;
                        child_cache[key] = data.form;
                    }
                    updateFilterChildHTML(pid, $block, $values);
                });
            }
        } else {
            updateFilterChildHTML(pid, $block, $values, (pval>0?1:false));           
        }
    }
    
    function updateFilterChildHTML(pid, $blockChild, $blockChildValues, noSel)
    {
        var $selected = (noSel===false ? [] : $('.d'+pid+' input[type="checkbox"]:checked:not(.checkAll)', $filterContent));

        if($selected.length > 0) {
            var key, pval, html = '';
            $selected.each(function(){
                pval = intval($(this).val());
                key = pid+'-'+pval;
                if(child_cache[key])
                    html += child_tmpl( child_cache[key] );
            });  
            $blockChildValues.html( html );
            $blockChild.removeClass('hidden');
        } else {
            $blockChildValues.html('');
            $blockChild.addClass('hidden');
            fbSel($blockChild, false, false);
        }
        
    }
    
    function regionSelect(select, type)
    {
        var $select = $(select);
        $select.parent().nextAll('div').addClass('hidden').find('select').val(0);

        var id = intval($select.val()); 
        if(id <= 0) return false;
        regionBuild(type+1, id, 'bbsSearch.regionSelect');
    }
    
    function regionBuild(type, id)
    {
        if(app.cache.region[id]) {
            regionSelectFill(app.cache.region[id], type);
        } 
        else  {
            bff.ajax('/ajax/bbs?act=regions', {pid: id, form:'options', empty: (type==2?'все регионы':'все города / ст. метро')}, function (data) {                                                                                       
                if(data) { regionSelectFill( (app.cache.region[id] = data), type ); }    
            });
        }
    }
    
    function regionSelectFill(options, type)
    {
        var $block = $('>div.reg div.r'+type, $filterContent);
        $('select', $block).html( options ).removeAttr('disabled');
        $block.removeClass('hidden');
    }
                                 
    function hideDropdowns()
    {
        //console.log('hide all - '+(dd_mouse_in == true?' fail':' success'));
        if(dd_mouse_in == true) return;
        $viewPPDropdown.hide(); 
        $('.selectBlock', $filterContent).each(function(e, i){
            var $b = $(this);
            if($b.hasClass('open')) { 
                dd_mouse_in = true; 
                $('.submit', $b).triggerHandler('click');
            } else {
                fbToggle($b, false);
            }
        });   
    }

    function fbSel($block, sel, plus)
    {
        var $sel = $('.sel', $block).html( !sel?'не важно':sel );
        if(sel) { 
            $block.addClass('active');
        } else {
            $block.removeClass('active');
        }
        if(plus) $sel.addClass( 'plus' ); else $sel.removeClass( 'plus' );
    }
    
    function fbToggle($block, show)
    {
        var $dd = $('.dropdown', $block);
        if(show===undefined) {
            $block.toggleClass('open');
            $dd.toggleClass('hidden');
        } else if(show===true) {  
            $block.addClass('open');
            $dd.removeClass('hidden');
        } else {          
            $block.removeClass('open');
            $dd.addClass('hidden'); 
        }
    }
    
    function enableFilter(on, $on, $off)
    {
        if(f.fe==(on?1:0)) return false;
        
        if(!$on) {
            $on = $('.filter-on', $form); 
            $off = $('.filter-off', $form)
        }
        
        if(on) {
            $on.html('включен').parent().removeClass('off').addClass('on');
            $off.html('отключить').parent().removeClass('on').addClass('off');
        } else {
            $on.html('включить').parent().removeClass('on').addClass('off');
            $off.html('отключен').parent().removeClass('off').addClass('on');
        }
        return setFormParam('fe', (on?1:0) );
   }
    
    function showFilter($link, show)
    {   
        var res = false;
        if(show===undefined) {
            res = $filterContent.toggleClass('hidden').is(':visible');
            $filter.toggleClass('open');
        } else if(show===true) {
            if(f.fh==0) return false;
            $filterContent.removeClass('hidden'); $filter.addClass('open'); res = true;
        } else if(show===false) {
            if(f.fh==1) return false;
            $filterContent.addClass('hidden'); $filter.removeClass('open'); res = false;
        }
        $link.html( res ? 'свернуть' : 'развернуть' );
        bff.cookie(app.cookiePrefix+'fh', (res?0:1), {expires:100, domain: '.'+locDomain});
        return setFormParam('fh', (res?0:1) ); 
    }
    
    function setViewType(type)
    {   
        if(f.v==type) return false;           
        if(type==2) { 
            $viewTypeTable.addClass('active table-active');
            $viewTypeList.removeClass('active list-active');
        } else if(type==1) {
            $viewTypeList.addClass('active list-active');
            $viewTypeTable.removeClass('active table-active');
        }
        return setFormParam('v', type);
    }
    
    function setViewPerpage(perpage)
    {
        if(f.pp==perpage) return false;
        $viewPPDropdown.hide(); dd_mouse_in = false;
        $('a', $viewPPDropdown).removeClass('select').filter('a[rel="'+perpage+'"]').addClass('select');
        $viewPPLink.html('<span>по '+perpage+'</span>');
        setFormParam('pp', perpage);
    }
    
    function setFormParam(name, val)
    {
        $('input[name="'+name+'"]', $form).val(val);
        f[name] = val;
        return true;
    }
    
    function toFloat(val)
    {
        val = parseFloat(val);
        if(isNaN(val) ) return 0;
        return val;
    }
    
    return {init: init, update: update, filter: submitFilter, regionSelect: regionSelect, onPage: onPage,
        saveChildCache: function(pid,vid,vname){  
            var data = {'form':$('#'+pid+'_c'+vid).find('ul').parent().html(),'vname':vname};
            data['pid'] = pid; data['vid'] = vid;
            child_cache[pid+'-'+vid] = data; 
        },
        enableFilter: function(){
            enableFilter(true); 
        }
    };
})();

(function($) {
  var oldHTML = $.fn.html;

  $.fn.formhtml = function() {
    if (arguments.length) return oldHTML.apply(this,arguments);
    $("input,textarea,button", this).each(function() {
      this.setAttribute('value',this.value);
    });
    $(":radio,:checkbox", this).each(function(i,e) {
      if (this.checked) { this.setAttribute('checked', 'checked'); setTimeout(function(){ $(e).attr('checked', 'checked'); }, 1500);  }
      else this.removeAttribute('checked');
    });
    $("option", this).each(function() {      
      if (this.selected) this.setAttribute('selected', 'selected');
      else this.removeAttribute('selected');
    });
    return oldHTML.apply(this);
  };

  //optional to override real .html() if you want
  // $.fn.html = $.fn.formhtml;
})(jQuery);