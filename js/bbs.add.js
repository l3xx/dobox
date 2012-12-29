
function bbsAddClass(o){ $(document).ready(function(){ this.init(o); }.bind(this)); }
bbsAddClass.prototype = 
{
    $dynprops: 0, $infoText: 0, $prices: {}, $regions: 0, $err: 0, $errPublicate: 0, $adText: 0, $adTextCounter: 0,
    $viewPhoto:0, $viewType: 0, $viewText: 0, $viewRegion: 0, $viewPrices: {}, 
    formAdd: null, formPublicate: null, id: 0, pass:'', o: {plmt: 0, ssid:''}, $instructions:{b:0,s:{}},
    steps: {}, // status: -1 = не инициировался, 0 = инициировался, 1 = редактируется, 2 = завершен   
    striptags: function(str) { return str.replace(/<\/?[^>]+>/gi, ''); },
    init: function(o)
    {
        var self = this;
        $.extend(this.o, o || {});     

        this.$err = $('#add-error');
        this.$instructions.b = $('#add-intructions'); 
        for(var i = 1; i<=3; i++) {
            this.steps[i] = {e:$('#add-step-'+i),status:-1};
            this.$instructions.s[i] = $('.add-instruction-'+i, this.$instructions.b);
        }

        var $progressAdd = $('#add-form-progress');
        this.formAdd = $('#add-form');
        this.formAdd.submit(function () {  
            
            var error = false;
            $('input.req:visible, select.req:visible', this).each(function() {
                var val = $(this).val();
                if( $.trim(val) == '' || val == 0 || val === undefined || val == $(this).attr('placeholder')) {
                    error = true;
                }
            });            
            
            if(error) { 
                self.showError(self.$err, 'Заполните все необходимые поля');   
            } else {
               // self.$adText.removeAttr('disabled');
                this.info.value = self.striptags( this.info.value );
                this.descr.value = self.striptags( this.descr.value );
                this.uid.value = app.uid();
                bff.ajax('/items/add', self.formAdd.serialize(), function(data, errors) {  
                    if(data && data.res) { self.onPp(data.pp);
                        if(self.id) {
                            self.stepReady( 2 );
                        } else {
                            var _formAdd = self.formAdd.get(0);
                            _formAdd.elements['id'].value = self.id = intval(data.id);
                            _formAdd.elements['pass'].value = self.pass = data.pass;
                            self.stepReady( 2 );
                            self.photos.updateAfterSave( data.id );
                        }
                    } else {
                        if(errors && errors.length) {
                            self.showError(self.$err, errors.join('<br/>'));
                        }
                    }
                    //self.$adText.attr('disabled', 'disabled');
                }, $progressAdd); 
            }
            return false;
        });
        
        this.$errPublicate = $('#publicate-error', this);
        ( this.formPublicate = $('#publicate-form') ).submit(function () {
            
            bff.ajax('/items/publicate', 'id='+self.id+'&p='+self.pass+'&'+$(this).serialize(), function(data, errors) {  
                if(data && data.res) { 
                    if(data.pay) app.pay(data.form);
                    else document.location = '/items/publicate';
                } else {
                    if(errors && errors.length) {
                        self.showError(self.$errPublicate, errors.join('<br/>'));
                    }
                }
            }, '#publicate-form-progress', this); 
            
            return false;
        });
        
        this.$dynprops = $('#add-step-dynprops', this.formAdd);
        this.$infoText = $('#add-step-infotext', this.formAdd);
        this.$adText = $('#add-ad-text', this.formAdd);                
        this.$adTextCounter = $('#add-ad-text-counter', this.formAdd); 
        
        var $pricesBlock = $('#add-step-prices', this.formAdd);
        this.$prices = {
                state: {p:false,t:false,b:false},
                block: $pricesBlock,
                p: $('#add-step-prices-price', $pricesBlock),
                t: $('#add-step-prices-torg>input', $pricesBlock),
                b: $('#add-step-prices-bart>input', $pricesBlock)
        }
        
        //debug \/
//        var cat1_sel = $('select:first', this.steps[1].e);
//        cat1_sel.val(3);
//        this.categorySelect(cat1_sel, 1);
        
    }, 
    
    onLogin: function()
    {
        var self = this;
        $('.login', this.formPublicate).remove();
        if(this.id>0) {
            bff.ajax('/ajax/bbs?act=item-u-update', {id: this.id, p: this.pass, uid: app.uid()}, function(data, errors) {  
                if(errors && errors.length) {
                    this.showError(this.$errPublicate, errors.join('<br/>'));
                } self.onPp(data.pp);
            }.bind(this));
        }
    },
          
    stepReady: function( step )
    {
        this.steps[step].status = 2; //помечаем завешение редактирования шага
        var $step = this.steps[step].e;
        
        switch(step)
        {
            case 1: { //cats
                var cats = [];
                $('select.cat', $step).each(function(i,e){
                    cats.push( e.options[e.selectedIndex].text );
                });
                
                $('div.selects-edit', $step).fadeOut(0,function(){
                    $('div.selects-view', $step).html( '<span class="text"><b>'+cats.join('</b></span><span class="arrow"><img src="/img/arrowRight.png" /></span><span class="text"><b>')+'</b></span>' ).fadeIn(0);
                });
                
            } break;
            case 2: { //dynprops, price, geo, images
                $('div.add-step-content', $step).addClass('hidden'); 
                   
            } break;
            case 3: //publication
            {
                $('div.add-step-content', $step).addClass('hidden');
            } break;
        }

        $step.addClass('done').removeClass('active') //mark as done          
            .find('span.add-step-edit').removeClass('hidden'); //show edit link
                
        var stepNext = step+1;
        if(this.steps[stepNext])
            this.stepEdit( stepNext );
            
        if(step == 2) {
            $.scrollTo(this.steps[1].e, { duration:500, offset:-50 } );
            this.$err.addClass('hidden').html('');
        }
    }, 
    
    stepEdit: function( step )
    {
        var $step = this.steps[step].e;
         
        if(step == 1 && this.steps[2].status!=-1) //"раздел и тип"
        {
            //if(!confirm('Продолжить?'))
            //    return false;
            
            $('div.selects-view', $step).hide();
            $('div.selects-edit', $step).show();
                        
            this.steps[2].e.removeClass('done active').addClass('done')
                .find('span.add-step-edit').addClass('hidden').end()
                .find('div.add-step-content').addClass('hidden');
            this.steps[2].status = 0;
            
            if(this.steps[3].status>0) {
                this.steps[3].e.removeClass('done active').addClass('done')
                    .find('span.add-step-edit').addClass('hidden').end()
                    .find('div.add-step-content').addClass('hidden');
                this.steps[3].status = 0;
            }
        }
                                              
        if(step == 2){
            $('div.add-step-content', $step).removeClass('hidden'); 
            if(this.steps[3].status==1) this.stepReady(3);
            
        }
        else if(step == 3){
            
            this.stepInit(step);
            
            //build preview
            this.$viewPhoto.attr('src', this.photos.getPreviewPhoto(this.id) );
            
            var type = '';
            var sel = $('select.cat[name="cat[type]"]', this.steps[1].e); 
            if(sel.length) { sel = sel.get(0); type = sel.options[sel.selectedIndex].text; }
            this.$viewType.html( (type!==''?type+':':'') );              
            
            if(this.$regions.hasClass('hidden')) {
                this.$viewRegion.hide();
            } else {
                var regs = [];
                $('select[value!=0]', this.$regions).each(function(i,e){
                    regs.push( e.options[e.selectedIndex].text );
                });
                if(regs.length == 3 ) regs.splice(0,1);
                this.$viewRegion.html( regs.join(', ') ).show();
            }

            this.$viewText.html( (this.$adText.val() + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br/>$2') );
            
            if(this.$prices.state.p) {
                this.$viewPrices.p.html( this.$prices.p.val() );
                var hasT = false;
                if(this.$prices.state.t && this.$prices.t.is(':checked')) { this.$viewPrices.t.show(); hasT = true; } else { this.$viewPrices.t.hide(); }
                if(this.$prices.state.b && this.$prices.b.is(':checked')) { this.$viewPrices.b.html( hasT? ', бартер':'бартер' ).show(); } else { this.$viewPrices.b.hide(); }
                this.$viewPrices.p.parent().show();
            } else {
                this.$viewPrices.p.parent().hide();
            }
            
            $('div.add-step-content', $step).removeClass('hidden'); 
        }
        
        this.stepInit(step);
        
        //show intruction
        $('>div', this.$instructions.b).addClass('hidden');
        this.$instructions.s[step].removeClass('hidden');
        
        $step.removeClass('done').addClass('active') //mark as in-process      
            .find('span.add-step-edit').addClass('hidden'); //hide edit link

        this.steps[step].status = 1; //помечаем редактирование

        return false;
    },
    
    stepInit: function(step)
    {
        if(this.steps[step].status != -1)
            return;
                       
        var $block = this.steps[step].e;
        switch(step)
        {
            case 1: {
            } break;
            case 2: {
                this.photos.init($block, this.o.plmt, this.o.ssid);
            } break;
            case 3: {
                this.$viewPhoto = $('#add-view-image>img', $block);
                this.$viewText= $('#add-view-text', $block);
                this.$viewPrices= {p: $('#add-view-price', $block),t:$('#add-view-price-torg', $block),b:$('#add-view-price-bart', $block)};
                this.$viewType = $('#add-view-type', this.formPublicate);
                this.$viewRegion = $('#add-view-region', this.formPublicate);                
            } break;
        }
    },
    
    regionSelect: function(select, type)
    {
        $select = $(select);
        $select.parent().parent().nextAll('div.left').addClass('hidden');
        
        var id = intval($select.val()); 
        if(id <= 0) return false;
            
        this.regionBuild(type+1, id, 'bbsAdd.regionSelect');
    },

    regionBuild: function(type, id)
    {
        if(app.cache.region[id] || type == 2) {
            if(!app.cache.region[id]) {
                var opts = '<option value="0">Выбрать...</option>';
                for(var i in bbsAddRegions[id]['sub']) {
                    opts += '<option value="'+(bbsAddRegions[id]['sub'][i]['id'])+'">'+(bbsAddRegions[id]['sub'][i]['title'])+'</option>';
                }
                app.cache.region[id] = opts;
                
            }
            this.regionSelectFill(app.cache.region[id], type);
        } 
        else  {
            bff.ajax('/ajax/bbs?act=regions', {pid: id, form:'options', empty: 'Выбрать...'}, function (data) {                                                                                       
                if(data) { this.regionSelectFill( (app.cache.region[id] = data), type ); }    
            }.bind(this));
        }
    },
    
    regionSelectFill: function(options, type)
    {
        var $blockReg = $('#add-step-region-'+type);
        $('select', $blockReg).html( options );
        $blockReg.removeClass('hidden');
    },
    
    categorySelect: function(select, type) 
    {
        $select = $(select);
        $select.nextAll().remove();
        
        var id = intval($select.val()); 
        if(id <= 0) return;
        
        if(type == 'type') {
            this.stepReady(1);    
            return;
        }    
        
        this.categoryBuild($select, type, id, 'bbsAdd.categorySelect');
    },
    
    categoryBuild: function($select, type, id, objFunc)
    {
        var html = '';                                    
        
        if(app.cache.category[id]) {
            html = this.categoryBuildHTML(app.cache.category[id], type, objFunc);
            if(html === false)
                return this.stepReady(1);
            $select.after(html); 
        } 
        else 
        {
            bff.ajax('/ajax/bbs?act=sub-cats', {pid: id, dp:1, dp_form:'add'}, function (data)
            {                                                                                       
                if(data) {   
                    html = this.categoryBuildHTML( (app.cache.category[id] = data), type, objFunc);
                    if(html === false)
                        return this.stepReady(1);
                }       
                
                $select.after(html);                
            }.bind(this));
        }
    },
    
    categoryBuildHTML: function(data, typePrev, objFunc)
    {
        var type = (intval(data.is_types) ? 'type' : typePrev+1);    
        
        if(type && data.dp) {
            bffDynpropsTextify.clear();
            this.$dynprops.html(data.dp.form);
        }
        
        if(!this.$regions) this.$regions = $('#add-step-regions');
        if(intval(data.regions)) {
            this.$regions.removeClass('hidden');
        } else {
            this.$regions.addClass('hidden');
        }

        if(intval(data.prices)) {
            this.$prices.block.show();
            this.$prices.state = {p:true,t:intval(data.prices_sett.torg),b:intval(data.prices_sett.bart)};
            if(this.$prices.state.t) this.$prices.t.parent().show(); else this.$prices.t.parent().hide();
            if(this.$prices.state.b) this.$prices.b.parent().show(); else this.$prices.b.parent().hide();
        } else {
            this.$prices.block.hide();
            this.$prices.state = {p:false,t:false,b:false};
            this.$prices.p.val(''); this.$prices.t.removeAttr('checked'); this.$prices.b.removeAttr('checked');
        }

        if(this.steps[2].status != -1) {
            this.txtBuild();
        }
                
        if(!data.cats.length)
            return false;

        var html = '<span class="arrow"><img src="/img/arrowRight.png" /></span>\
                <select name="cat['+type+']" class="cat" onchange="bbsAdd.categorySelect(this, '+(type=='type'?'\''+type+'\'':type)+');">\
                    <option value="0">выбрать</option>';
                    for(var i in data.cats) {
                          html += '<option value="'+data.cats[i].id+'">'+data.cats[i].title+'</option>';
                    }                    
        html +='</select>';   
                    
        return html;
    },    

    showError: function($block, message)
    {
        $block.html(message).removeClass('hidden');
        $.scrollTo($block, { duration:500, offset:-61 } ); 
    },
    
    onPp: function(pp)
    {
        $('input[type=submit]', this.formPublicate).val( (pp ? 'ОПЛАТИТЬ и ОПУБЛИКОВАТЬ' : (app.m?'РАЗМЕСТИТЬ ОБЪЯВЛЕНИЕ':'РАЗМЕСТИТЬ ОБЪЯВЛЕНИЕ БЕЗ РЕГИСТРАЦИИ')) ).
            css('width', (!pp && !app.m ? '280px' : '185px'));
        
    },
    
    txtLastCharsLeft: 0,  
    txtBuild: function()
    {
        var txt = '', delimiter = ', ';                
        
        //dynprops
        if(window['txtDynprops']!==undefined) txt = txtDynprops(txt);
        //price
        if(this.$prices.state.p) {
            //var price = this.$prices.p.val();
            //if(price) txt += (txt ? delimiter : '') + 'цена ' + price + ' руб.';
            if(this.$prices.state.t && this.$prices.t.is(':checked'))
                txt = txt + (txt ? delimiter : '') + 'торг';
            if(this.$prices.state.b && this.$prices.b.is(':checked'))
                txt = txt + (txt ? delimiter : '') + 'бартер';
        }
                                                       
        //info
        txt += (txt ? ' ' : '') + this.$infoText.val();        
        
        if(txt && ($.trim(txt).lastIndexOf('.') != txt.length - 1)) txt += '.'; 
           
        if(this.o.txtMaxLength>0) {
            var tooLong = false;
            var adTextLength = txt.length;
            var charsLeft = this.o.txtMaxLength - adTextLength;
            if (this.txtLastCharsLeft >= 0 && charsLeft < 0) {
                tooLong = true;
            }
            this.txtLastCharsLeft = charsLeft;

            this.$adTextCounter.html( bff.declension(charsLeft, ['символ','символа','символов']) );
            
            if (tooLong) {
                alert('Превышена максимальная длина объявления');
            }
        }
        
        this.$adText.val(txt);
    }, 
        
    addVideo: function()
    {                       
        var video = $('#add-video-code').val();
        if (!video) {
            alert('Введите ссылку на видео с www.youtube.com!');
            return;
        }
        if (video.indexOf("www.youtube.com") == -1)  {
            alert('Введите ссылку на видео с www.youtube.com!');
            return;
        }
        if (video.lastIndexOf("?v=") == -1)  {
            if (video.lastIndexOf("&v=") == -1)  {
                alert('Введите ссылку на видео с www.youtube.com!');
                return;
            }
            else {
                var arr = video.substr(video.lastIndexOf("&v=") + 3).split('&');
                $('#embVideo').attr({movie: "http://www.youtube.com/v/"+arr[0]+"&hl=ru_RU&fs=1&rel=0",
                                     src: "http://www.youtube.com/v/"+arr[0]+"&hl=ru_RU&fs=1&rel=0"});
                $('#add-video-preview, #add-video-del').removeClass('hidden');
            }
        }
        else {
            var arr = video.substr(video.lastIndexOf("?v=") + 3).split('&');
            $('#embVideo').attr({movie: "http://www.youtube.com/v/"+arr[0]+"&hl=ru_RU&fs=1&rel=0",
                                 src: "http://www.youtube.com/v/"+arr[0]+"&hl=ru_RU&fs=1&rel=0"});
            $('#add-video-preview, #add-video-del').removeClass('hidden');
        }
    },
    
    delVideo: function(btn)
    {
        $('#add-video-code').val( '' );
        $('#add-video-preview').addClass('hidden');
        $(btn).parent().addClass('hidden');
    },
    
    photos : (function() 
    {
        var inited = false, swfu = null, uploaded = 0, lmt = 0, path = '/files/images/items/',
            $block = null, $tip = null, $fav = null, $progress = null;
        function init(cont, _lmt, ssid)
        {   
            if(inited) return;
            inited = true;
            $block    = $('#add-images', cont);   
            $tip      = $('#add-images-tip', cont);  
            $fav      = $('#add-images-fav', cont);
            $progress = $('#add-images-progress', cont);
            lmt = _lmt;    
                                     
            swfu = new SWFUpload({
                upload_url: '/ajax/bbs?act=img-upload', 
                flash_url : '/bff/js/swfupload/swfupload.swf',
                post_params: {edit: 0, sessid: ssid},
                file_size_limit : '3 MB',
                file_types : "*.jpg; *.jpeg; *.gif;",
                file_types_description : "Файлы изображений",
                file_upload_limit : lmt,
                                                                     
                file_queued_handler : fileQueued,
                file_queue_error_handler : fileQueueError,
                file_dialog_complete_handler : fileDialogComplete,
                upload_progress_handler : uploadProgress,
                upload_error_handler : uploadError,
                upload_success_handler : uploadSuccess,
                upload_complete_handler : uploadComplete,
                swfupload_loaded_handler : loaded,
                
                button_image_url : '/img/upload-button.png',
                button_placeholder_id : 'add-images-button',
                button_width: 90, button_height: 21,     
                button_text : 'Обзор...', button_text_top_padding: 1, button_text_left_padding: 25,
                button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
                button_cursor: SWFUpload.CURSOR.HAND,
                
                debug: false 
            }); 
                              
            $('a.del', $block).live('click', function (e) {
                nothing(e);
                var lnk = $(this);
                if(confirm('Удалить фото?')) {
                    var fn = lnk.attr('rel');
                    bff.ajax('/ajax/bbs?act=img-delete', {filename: fn, id: bbsAdd.id}, function(data) {
                        if(data) {
                            lnk.parents('.picCont').remove(); 
                            updateStats(0);
                            toggleTip(true);
                        }
                    });
                } return false;
            });

            $('a.star', $block).live('click', function (e) {
                nothing(e);
                var lnk = $(this);
                $('div.picCont a.star', $block).removeClass('select');
                lnk.addClass('select');
                $fav.val( lnk.attr('rel') );
                return false;   
            });   

        }
        
        function uploadComplete (file)
        {   try {  
                if (this.getStats().files_queued > 0) {
                    this.startUpload();
                } else {
                    $progress.hide();
                }
            } catch (ex) {
                this.debug(ex);
            }
        }

        //обработчик ошибки, в случае HTTP/1.1 500
        function uploadError(file, errorCode, message) 
        {
        }
        
        function fileDialogComplete(numFilesSelected, numFilesQueued)
        {
            if (numFilesQueued > 0) {
                this.startUpload();
                $progress.show();
            }
        }   
        function fileQueued(file)
        {
            this.addFileParam(file.id, 'id', bbsAdd.id);
        }
        function fileQueueError(file, errorCode, message)
        {
            switch(errorCode)
            {
                case -100: { errorName = 'Для загрузки Вам доступно '+bff.declension(lmt, ['фотография','фотографии','фотографий']); } break;                
                case -110: { errorName = 'Файл слишком большой';      } break;
                case -120: { errorName = 'Недопустимый размер файла'; } break;
                case -130: { errorName = 'Неверный формат файла';     } break;
                default:     errorName = 'Произошла ошибка при выборе файла, попробуйте повторить свой выбор';
            }                  
            alert( errorName );
        }
        
        function uploadSuccess(file, data)
        {
            data = $.parseJSON( data ); 
            if(data.success) 
            {                       
                $block.append('<div class="picCont">\
                                 <input type="hidden" name="img[]" value="'+data.filename+'"/>\
                                 <div class="pic"><img rel="'+data.filename+'" src="' + path + bbsAdd.id + 's' + data.filename + '" /></div>\
                                 <div class="padTop"><a class="star" rel="'+data.filename+'" href="#"></a>\
                                                     <a class="del" href="#" rel="'+data.filename+'"></a></div>\
                               </div>'); 
                if($fav.val()=='') {
                    $fav.val( data.filename );
                }
                toggleTip();
                uploaded++;
            }
        }
        
        function uploadProgress(file, bytesLoaded)
        {
            return true;
        }
        
        function toggleTip(favUpdate) {
            var picBlocks = $('div.picCont', $block);
            $tip.css({display: (picBlocks.length>=2?'':'none')});
            if(favUpdate) $fav.val( ( picBlocks.length>0 ?  $('input:first', picBlocks.get(0)).val() : '' ) );
        }
        function loaded() { updateStats(1); }
        function updateStats(type) {
            var stats = swfu.getStats();
            switch(type) {
                case 0: stats.successful_uploads--; uploaded--; break;
                case 1: stats.successful_uploads = uploaded; break;
            }
            swfu.setStats(stats);                      
        }
        
        return {init: init, 
            getPreviewPhoto: function(id) {
                var fav = $fav.val();
                return (fav!=='' ? path + id + 't' + fav : '/img/noImage.gif');
            },
            updateAfterSave: function(id)
            {
                $('div.picCont img', $block).each(function(){
                    $(this).attr('src', path + id + 's'+$(this).attr('rel'));
                });
            }
        };
    }())
            
};
