var bffDynprops = (function(){

    var o = {
            lang: { yes: 'Да', no: 'Нет', move_up: 'переместить вверх', move_down: 'переместить вниз', add: 'добавить', del: 'удалить', add_child: 'прикрепить',
                    null_val: 'Нулевое значение', child_add_after_save: 'Возможность прикрепления будет доступна <b>после сохранения свойства</b>',
                    default_val: 'Значение по умолчанию', no_val:'без значения',
                    dia_search_range_user: 'пользовательский вариант', dia_search_ranges: 'Диапазоны поиска', dia_from: 'от', dia_to: 'до', dia_step: 'шаг'},
            countries: {},
            states:    {},
            date:      {}
        };

    var types = {text: 1, textarea: 2, wysiwyg: 3, radio: 4, checkbox: 5, 
                 select: 6, multipleselect: 7, radiogroup: 8, checkboxgroup: 9, 
                 number: 10, range: 11, country:12, state:13, date: 14};
    var typesData  = { 1: {t: 'Однострочное текстовое поле'},
                       2: {t: 'Многострочное текстовое поле'},
                       3: {t: 'Текстовый редактор'},
                       4: {t: 'Выбор Да/Нет'},
                       5: {t: 'Флаг'},
                       6: {t: 'Выпадающий список'},                  
                       7: {t: 'Список с мультивыбором (ctrl)'},
                       8: {t: 'Группа св-в с единичным выбором'},
                       9: {t: 'Группа св-в с множественным выбором'},
                       10: {t: 'Число'},
                       11: {t: 'Диапазон'},
                       12: {t: 'Страны'},
                       13: {t: 'Штаты'},
                       14: {t: 'Дата'}
                       };

    function isMulti(type)   { return in_array(type, [types.select, types.multipleselect, types.radiogroup, types.checkboxgroup]); }
    function isSearch(type)  { return !in_array(type, [types.text,types.textarea,types.wysiwyg]); }

    var processing = false;
    
    /**
    * @param Boolean прикрепленное свойство (child)
    * @param Hash данные:
    *   Boolean edit: 0 - добавление, 1 - редактирование
    *   Hash data: данные   
    *   String url_save: URL для сохранения/редактирования Child свойства
    *   Array types_allowed: доступные типы свойств
    *   Array types_allowed_parent: типы свойств, c возможностью прикрепления
    * @param Hash доп.настройки
    */             
    function init(isChild, params, options)
    {
        $.extend(o, options);
        
        var objName = 'bffDynprops'+(isChild?'Child':'Main');

        var $form         = $('#'+objName+'Form');
        var $params       = $('.dynprop-params', $form); 
        var $searchBlock  = $('.dynprop-search-block', $form);
        var $search       = $('input.dynprop-search', $form);                
        var $req          = $('input.dynprop-req', $form);
        var $in_table     = $('input.dynprop-in_table', $form);
        var $txt          = $('input.dynprop-txt', $form); 
        var $txtBlock     = $('.dynprop-txt-block', $form); $txt.click(function(){ $txtBlock.toggle(); });
        
        var parentActive  = (params.data && params.data.parent == 1);
        var $parent       = $('input.dynprop-parent', $form);
        var $parentParams = $('.dynprop-parent-block', $form);  
                               
        var $multi;
        var multi_deleted = [];
        var multi_added   = [];
        
        var searchRangesIterator = 0;
        
        var isEdit = params.edit;
        
        $form.submit(function(){
            if(isEdit) {
                $('.multi-deleted', $form).val(multi_deleted.join(','));
                $('.multi-added', $form).val(multi_added.join(','));
            }
            if(isChild) {
                if(!processing) {
                    processing = true;
                    bff.ajax(o.url_action_owner+'child', $form.serialize(), function(data){
                        if(data) $.fancybox.close();
                        processing = false;
                    });
                }
                return false;
            }
        });
        
        if(isChild) {
            $('.dynprop-delete', $form).click(function(){
                if(bff.confirm('sure')) {
                    $('input[name="child_act"]', $form).val('del');
                    $form.submit();
                }
            });
        }
            
        var curType = initSelect(params.data);
        
        initDynpropParams(curType, params.data, isEdit); 

        function initSelect(data)
        {             
            var $select = $('select.dynprop-type-select', $form);
            
            if(data && data.title)
                $('input.dynprop-title', $form).val( data.title ); 
            
            if(data && data.description)
                $('input.dynprop-description', $form).val( data.description ); 
                
            if(data && data.txt_text)
                $('input.dynprop-txt-text', $form).val( data.txt_text );
            
            var selectOpts = '';
            if(isEdit) 
            {
                var type = intval(data.type);
                if( !isMulti(type) ) {
                    $select.attr('disabled', 'disabled');
                    $select.after('<input type="hidden" name="dynprop[type]" value="'+type+'" />');
                } 
                
                switch(type)
                {
                    case types.select:
                    case types.radiogroup:
                    {
                        selectOpts += '<option value="'+types.select+'" '+(type == types.select ? 'selected="selected"' : '')+'>'+typesData[types.select].t+'</option>'
                                   +  '<option value="'+types.radiogroup+'" '+(type == types.radiogroup ? 'selected="selected"' : '')+'>'+typesData[types.radiogroup].t+'</option>';
                    } break;
                    case types.multipleselect:
                    case types.checkboxgroup:
                    {
                        selectOpts += '<option value="'+types.multipleselect+'" '+(type == types.multipleselect ? 'selected="selected"' : '')+'>'+typesData[types.multipleselect].t+'</option>'
                                   +  '<option value="'+types.checkboxgroup+'" '+(type == types.checkboxgroup ? 'selected="selected"' : '')+'>'+typesData[types.checkboxgroup].t+'</option>';                    
                    } break;
                    default: {
                        selectOpts += '<option value="'+type+'" selected="selected">'+typesData[type].t+'</option>';
                    }
                }
                $select.html(selectOpts);        
            } else {
                if(params.types_allowed.length>0) {
                    for( var j in params.types_allowed ) {      
                        selectOpts += '<option value="'+params.types_allowed[j]+'">'+typesData[params.types_allowed[j]].t+'</option>';
                    }                          
                } else {
                    for( var j in types ) {
                        selectOpts += '<option value="'+types[j]+'">'+typesData[types[j]].t+'</option>';
                    }
                }
                $select.html(selectOpts).change(function(){
                    parentActive = false;
                    initDynpropParams( this.value, false, false );
                });    
                var type = ( data && data.type ? intval(data.type) : intval($select.val()) );
                $select.val(type);
            }   
            
            if(isChild && params.types_allowed.length == 1) {
                $select.parent().parent().hide();
            }  
                              
            return type; 
        }
        
        function initDynpropParams(type, data, edit) 
        {
            var type = intval(type);
            
            $params.html('');
            
            if(!isChild) {
                if(isSearch(type)) {
                    if(data && data.is_search == 1) {
                        $search.attr('checked', 'checked');
                    }  else {
                        $search.removeAttr('checked');
                    }
                    $searchBlock.removeClass('hidden');
                } else {
                    $search.removeAttr('checked');
                    $searchBlock.addClass('hidden');
                }
                if(data && data.req == 1) {
                    $req.attr('checked', 'checked');
                } else {
                    $req.removeAttr('checked');
                }      
                           
                if(data && data.in_table == 1) {
                    $in_table.attr('checked', 'checked');
                } else {
                    $in_table.removeAttr('checked');
                } 
                                
                if(data && data.txt == 1) {
                    $txt.attr('checked', 'checked');
                    $txtBlock.removeClass('hidden');
                } else {
                    $txt.removeAttr('checked');
                    $txtBlock.addClass('hidden');
                }
            }
            
            var html = '';

            if( isMulti(type) ) {
                html += '<table class="multi-fields-block"><tr class="hdr nodrag nodrop"><th width="10" style="height:1px;"></th><th width="220" style="height:1px;" colspan="2"></th></tr></table><br />'
                        + '<a class="but add but-text multi-add" href="#">добавить значение</a>';
                        
                $('.multi-default-clear', $form).click(function(e) {
                    nothing(e);
                    $('.dynprop-params input[type=radio], .dynprop-params input[type=checkbox]', $form).attr('checked', false); 
                });
            }
                             
            if(isParent(type)) {  
                $parent.unbind().click(function(){
                    var checked = $(this).is(':checked');  
                    if(checked) { 
                        $('.parent-child-link', $form).css('display', 'inline-block');
                        $parentParams.show(); 
                    } else {
                        $('.parent-child-link', $form).hide();
                        $parentParams.hide();
                    }
                    parentActive = checked;
                });    
                if(parentActive) {   
                    $('.dynprop-child-title', $parentParams).val( data.child_title );
                    $('.dynprop-child-default', $parentParams).val( data.child_default );
                    $('.parent-child-link', $form).css('display', 'inline-block');
                    $parentParams.show(); 
                    $parent.attr('checked', 'checked').parent().show();
                } else {
                    $parent.removeAttr('checked').parent().show();
                    $parentParams.hide();
                }
            } else {
                $parent.removeAttr('checked').parent().hide();      
                $parentParams.hide();
            }
                    
            switch(type) 
            {
                case types.text :
                        html += '<input type="text" name="dynprop[default_value]" style="width:233px;" value="'+(data ? data.default_value : '')+'" /></div>';
                break;
                case types.textarea :
                        html += '<textarea name="dynprop[default_value]" >'+(data ? data.default_value : '')+'</textarea></div>';
                break;
                case types.wysiwyg :
                        html += '<textarea name="dynprop[default_value]" >'+(data ? data.default_value : '')+'</textarea></div>';
                        createFCKEditor("dynprop[default_value]");
                break;
                case types.radio :
                        html += '<label>'+o.lang.yes+' <input type="radio" name="dynprop[default_value]" value="2" '+((data && data.default_value=='2') ? 'checked="checked"'  : '')+' /></label>&nbsp;<label>'+o.lang.no+' <input type="radio" name="dynprop[default_value]" value="1" '+((data && data.default_value=='1') ? 'checked="checked"'  : '')+' /></label></div>';
                break;
                case types.checkbox :
                        html += '<input type="checkbox" name="dynprop[default_value]" style="height:18px;" '+((data && data.default_value) ? 'checked="checked"'  : '')+' value="1" />';
                        if(isParent(type))  {
                            html += '<a class="parent-child-link but sett '+(!edit? ' disabled':'')+'" title="'+(o.lang.add_child)+'" href="#" style="margin-left:6px; display:none;" onclick="return '+objName+'.child('+(data ? data.value : 0)+', '+(!edit)+');"></a>'; 
                        }
                        html += '</div>';
                break;
                case types.select :    
                case types.multipleselect :
                case types.radiogroup :
                case types.checkboxgroup :  
                        var mindex = 1;
                        $params.html(html);
                        $multi = $('.multi-fields-block', $params);
                        
                        if(type == types.select && !data && !isChild) {
                            addMultipleField(0, {name: o.lang.null_val, value: 0}, '', type, 0);
                        }
                        
                        if(data && data.multi) {        
                            for(var i in data.multi ) {
                                mindex = Math.max(mindex, data.multi[i].value); 
                                addMultipleField(mindex, data.multi[i], data.default_value, type, 0);
                            }
                        } else {
                            addMultipleField(mindex, null, '', type, 1);
                        }

                        $('a.multi-add', $params).click(function(e) {
                            nothing(e);               
                            addMultipleField(++mindex, null, '', type, 1, true);
                            $multi.tableDnDUpdate();
                        });  
                        
                        bff.initTableDnD($multi, false);
                        
                        return;
                break;
                case types.number :
                case types.range :      
                        html += '<input type="text" name="dynprop[default_value]" value="'+(data ? data.default_value : '')+'" />';
                        if(isParent(type))  {
                            html += '<a class="parent-child-link but sett '+(!edit? ' disabled':'')+'" title="'+(o.lang.add_child)+'" href="#" style="margin-left:6px;'+(!parentActive ? ' display:none;':'')+'" onclick="return '+objName+'.child(0, '+(!edit)+');"></a>'; 
                        }                        
                        if(type == types.range) {
                            html += '<br /><br />'+(o.lang.dia_from)+':&nbsp;<input name="dynprop[start]" type="text" style="width:90px;" value="'+(data ? data.start : '')+'" />&nbsp;&nbsp;'+(o.lang.dia_to)+':&nbsp;<input name="dynprop[end]" type="text" style="width:90px;" value="'+(data ? data.end : '')+'" />&nbsp;'+(o.lang.dia_step)+':&nbsp;<input name="dynprop[step]" style="width:40px;" type="text" value="'+(data ? data.step : '')+'" /></div>';
                        }

                        //search ranges
                        html += '<br/><br/><b>'+o.lang.dia_search_ranges+'</b>:';
                        var html_ranges = '';
                        if(data && (data.search_ranges || data.search_ranges.length>0)) { 
                            for(var i in data.search_ranges) {
                                var r = data.search_ranges[i];
                                html_ranges += '<tr><td><input type="hidden" name="dynprop[search_ranges]['+r.id+'][id]" value="'+r.id+'" />'+(o.lang.dia_from)+':&nbsp;<input name="dynprop[search_ranges]['+r.id+'][from]" type="text" style="width:110px;" value="'+r.from+'" />\
                                 &nbsp;&nbsp;'+(o.lang.dia_to)+':&nbsp;<input name="dynprop[search_ranges]['+r.id+'][to]" type="text" style="width:110px;" value="'+r.to+'" />\
                                 </td><td><a class="but up" title="'+o.lang.move_up+'" href="#" onclick="'+objName+'.up($(this)); return false;"></a>\
                                 <a class="but down" title="'+o.lang.move_down+'" href="#" onclick="'+objName+'.down($(this)); return false;"></a>\
                                 <a class="but del" title="'+o.lang.del+'" href="#" onclick="$(this).parent().parent().remove(); return false;"></a></td></tr>';
                                searchRangesIterator = Math.max(r.id, searchRangesIterator);
                            }
                        }
                        html += '<table><tbody id="search_ranges">'+html_ranges+'</tbody></table><a href="#" class="but add but-text" onclick="'+objName+'.addDiaSearchRange(); return false;" style="margin: 8px 0;">добавить диапазон</a>';
                                                
                        //user search range                                                        
                        html += '<br/><label><input type="checkbox" value="1" name="dynprop[search_range_user]" id="search_range_user_check" '+( (data && intval(data.search_range_user) == 1)  ? 'checked="checked"' : '')+' /> '+(o.lang.dia_search_range_user)+'</label>';
                        
                break;
                case types.country :
                        if(o.countries) {
                            
                            var country_list = '<select name="dynprop[default_value]">';
                            for(var i in o.countries) {
                                country_list += '<option value="'+o.countries[i].id+'" '+((data && data.default_value == o.countries[i].id) ? 'selected="selected"' : '')+' >'+o.countries[i].title+'</option>';
                            }
                            country_list += '</select></div>';
                            
                            html+= country_list;
                        }
                break;
                case types.state :
                        if(o.states) {
                            
                            var state_list = '<select name="dynprop[default_value]">';
                            for(var i in o.states) {
                                state_list += '<option value="'+o.states[i].abbr+'" '+((data && data.default_value == o.states[i].abbr) ? 'selected="selected"' : '')+' >'+o.states[i].title+'</option>';
                            }
                            state_list += '</select></div>';
                            
                            html += state_list;
                        }
                break;
                case types.date :
                        html += '<input id="datepicker" type="text" /><input id="datepicker_timestamp" type="hidden" name="dynprop[default_value]" /></div>';
                        $params.html(html);
                        createDatepicker('datepicker', data);
                        return;
                break;    
            }
            
            $params.html(html);
        }
        
        function addDiaSearchRange()
        {
            var i = ++searchRangesIterator;
            $('#search_ranges', $form).append('<tr><td><input type="hidden" name="dynprop[search_ranges]['+i+'][id]" value="'+i+'" />'+(o.lang.dia_from)+':&nbsp;<input name="dynprop[search_ranges]['+i+'][from]" type="text" style="width:110px;" value="" />\
                                 &nbsp;&nbsp;'+(o.lang.dia_to)+':&nbsp;<input name="dynprop[search_ranges]['+i+'][to]" type="text" style="width:110px;" value="" />\
                                 </td><td><a class="but up" title="'+o.lang.move_up+'" href="#" onclick="'+objName+'.up($(this)); return false;"></a>\
                                 <a class="but down" title="'+o.lang.move_down+'" href="#" onclick="'+objName+'.down($(this)); return false;"></a>\
                                 <a class="but del" title="'+o.lang.del+'" href="#" onclick="$(this).parent().parent().remove(); return false;"></a></td></tr>');
        }
        
        function addMultipleField(mindex, data, default_value, type, dynamic, focus) 
        {
            html = '<tr class="'+(data && data.value == 0 ? 'hdr nodrag nodrop' : '')+(dynamic?'dynamic':'')+'">';

            switch(type) {
                case types.multipleselect :
                case types.checkboxgroup :
                    var dvalues = (default_value ? default_value.split(";") : '');
                    html += '<td>';
                    html += '<input name="dynprop[multi_default_value]['+(data ? data.value : mindex)+']" '+(isEdit && dynamic ? 'disabled="disabled"':'')+' title="'+(o.lang.default_val)+'" type="checkbox" value="'+(data ? data.value : mindex)+'" '+((data && (in_array(data.value, dvalues))) ? 'checked="checked"' : '' )+' />';
                break;

                case types.select :       
                case types.radiogroup :
                    html += '<td><input name="dynprop[multi_default_value]" type="radio" value="'+(data ? data.value : mindex)+'" '+((data && (data.value == default_value)) ? 'checked="checked"' : '' )+' />';
                break;
            }
            
            html += '</td>';
            html +='<td><input name="dynprop[multi]['+(data ? data.value : mindex)+']" type="text" value="'+(data ? data.name : '')+'" style="width: 209px;" />';
            html += '<td>';
            
            if( isChild || (!data || data.value != 0))
            {
                html += '<a class="but up" title="'+o.lang.move_up+'" href="#" onclick="'+objName+'.up($(this)); return false;"></a>\
                         <a class="but down" title="'+o.lang.move_down+'" href="#" onclick="'+objName+'.down($(this)); return false;"></a>\
                         <a class="but del" title="'+o.lang.del+'" href="#" onclick="return '+objName+'.del(this, '+(data ? data.value : mindex)+', '+dynamic+');"></a>'
            }
            if(isParent(type) && (data && data.value != 0 || dynamic)) 
            {
                html += '<a class="parent-child-link but sett '+(!isEdit || dynamic? ' disabled':'')+'" title="'+(o.lang.add_child)+'" '+(!parentActive ? 'style="display:none;"':'')+' href="#" onclick="return '+objName+'.child('+(data ? data.value : 0)+', '+(!isEdit || dynamic ? 1 : 0)+');"></a>'; 
            }

            html += '</td></tr>';
            
            $multi.append(html);
            
            if(isEdit && dynamic) {
                multi_added.push(mindex);
            }
                           
            if(isChild) {
                $.fancybox.resize();
            }
            
            if(focus) {                                             
                $('input[type="text"]:last', $multi).focus();
            }
        }
        
        function removeMultipleField(node, mindex, dynamic) 
        {   
            if(!dynamic && !bff.confirm('sure')) return false;
            
            if($multi.find('tr').length == 2) {
                return false;
            }
            
            $(node).parent().parent().remove();

            if(!dynamic) 
            {
                multi_deleted.push(mindex);
            } else {
                if(isEdit) {
                    for(var j in multi_added) {
                        if(multi_added[j] == mindex) {
                            multi_added.splice(j, 1);
                            break;
                        }
                    }
                }
            }
            
            if(isChild) {
                $.fancybox.resize();
            }
            
            return false;
        }  
        
        function childForm(value, dynamic) 
        {   
            if(dynamic) {
                bff.error( o.lang.child_add_after_save );
                return false;
            }
            
            bff.ajax(o.url_action_owner+'child', {parent_id: params.data.id, parent_value: value}, function(data){
                if(data) {
                    $.fancybox(data.form);
                }
            });

             return false;
        }
        
        function isParent(type) { 
            if(isChild) return false;
            if( params.types_allowed_parent && params.types_allowed_parent.length>0)
                return in_array(type, params.types_allowed_parent); 
            return false;
        }  
        
        function createFCKEditor(name) {
            var oFCKeditor = new FCKeditor(name);
            oFCKeditor.BasePath = "/bff/external/wysiwyg/FCKeditor2/";
            oFCKeditor.ToolbarSet = 'Average';
            oFCKeditor.ReplaceTextarea() ;
        }
        
        function createDatepicker (name, data) {
            $("#"+name).datepicker(o.date);
            if(data) {
                $("#"+name).datepicker('setDate', new Date(data.default_value*1000));
                $("#"+name+"_timestamp").val((new Date($("#"+name).datepicker('getDate')).getTime())/1000);
            }
        }
    
        return {
            up:up, down: down, del: removeMultipleField, child: childForm, addDiaSearchRange: addDiaSearchRange
        };        
    }
    
    function in_array(el, arr) {
        for(var i in arr) {
            if(arr[i] && arr[i] == el)
                return true;
        }
        return false;
    }
    
    function down($link) {   
        var current = $link.parent().parent();
        var next = current.next('tr:not(.hdr)');       
        if(next.length) current.before(next);                       
    }
    
    function up($link) { 
        var current = $link.parent().parent();
        var prev = current.prev('tr:not(.hdr)');
        if(prev.length) current.after(prev);
    }
    
    return { init: init };
    
}());
