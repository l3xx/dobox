
function bbsEditClass(o){ $(document).ready(function(){ this.init(o); }.bind(this)); }
bbsEditClass.prototype = 
{
    $infoText: 0, $prices: {}, $regions: 0, $errPublicate: 0, $adText: 0, $adTextCounter: 0, $videoCode:0,
    $block: null, formPublicate: null, id: 0, o: {plmt: 0}, cache: {region:{} },
    init: function(o)
    {
        var self = this;            
        $.extend(this.o, o || {}); this.id = o.id;
        
        var $progress = $('#edit-form-progress');
        ( this.$block = $('#edit-form') ).submit(function () {              
            var error = false;
            $('input.req, select.req', this).each(function() {
                var val = $(this).val();
                if( $.trim(val) == '' || val == 0 || val === undefined || val == $(this).attr('placeholder')) {
                    error = true;
                }
            });            
            
            if(error) { 
                self.showError('Заполните все необходимые поля');   
            } else {
                bff.ajax(document.location, self.$block.serialize(), function(data, errors) {  
                    if(data && data.res) { 
                        history.back();
                    } else {
                        if(errors && errors.length) {
                            self.showError( errors.join('<br/>') );
                        }
                    }
                }, $progress); 
            }
            return false;
        });

        this.$adText = $('#edit-ad-text', this.$block);
        this.$adTextCounter = $('#edit-ad-text-counter', this.$block);
        
        this.$infoText = $('#edit-infotext', this.$block);
        
        var $pricesBlock = $('#edit-prices', this.$block);
        this.$prices = {
            p: $('#edit-prices-price', $pricesBlock),
            t: $('#edit-prices-torg>input', $pricesBlock),
            b: $('#edit-prices-bart>input', $pricesBlock)
        }
        
        this.$videoCode = $('#edit-video-code', this.$block);
        this.addVideo(true);
        
        this.photos.init(this.$block, this.o.puploaded, this.o.plmt, this.o.ssid);
    }, 
    
    regionSelect: function(select, type)
    {
        var $select = $(select);
        $select.parent().nextAll('div.left').addClass('hidden');
        
        var id = intval($select.val()); 
        if(id <= 0) return false;
            
        this.regionBuild(type+1, id, 'bbsEdit.regionSelect');
    },

    regionBuild: function(type, id)
    {
        if(this.cache.region[id] || type == 2) {
            if(!this.cache.region[id]) {
                var opts = '<option value="0">Выбрать...</option>';
                for(var i in bbsEditRegions[id]['sub']) {
                    opts += '<option value="'+(bbsEditRegions[id]['sub'][i]['id'])+'">'+(bbsEditRegions[id]['sub'][i]['title'])+'</option>';
                }
                this.cache.region[id] = opts;
                
            }
            this.regionSelectFill(this.cache.region[id], type);
        } 
        else  {
            var self = this;
            bff.ajax('/ajax/bbs?act=regions', {pid: id, form:'options', empty: 'Выбрать...'}, function (data) {                                                                                       
                if(data) { this.regionSelectFill( (self.cache.region[id] = data), type ); }    
            }.bind(this));
        }
    },
    
    regionSelectFill: function(options, type)
    {
        var $blockReg = $('#edit-region-'+type);
        $('select', $blockReg).html( options );
        $blockReg.removeClass('hidden');
    },

    showError: function( message )
    {                               
        bff.error( message );
    },
    
    txtLastCharsLeft: 0,  
    txtBuild: function()
    {
        var txt = '', delimiter = ', ';
        //info
        txt = this.$infoText.val();
        //dynprops
        if(window['txtDynprops']!==undefined) txt = txtDynprops(txt);
        //price
        if(this.o.prices_sett.p) {   
            //var price = this.$prices.p.val();
            //if(price) txt += (txt ? delimiter : '') + 'цена ' + price + ' руб.';
            if(this.o.prices_sett.t && this.$prices.t.is(':checked'))                
                txt = txt + (txt ? delimiter : '') + 'торг';
            if(this.o.prices_sett.b && this.$prices.b.is(':checked'))
                txt = txt + (txt ? delimiter : '') + 'бартер';
        }

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
        
    addVideo: function(init)
    {                       
        var video = this.$videoCode.val();
        if(init && video == '') {
            return;
        }
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
                var src = "http://www.youtube.com/v/"+arr[0]+"&hl=ru_RU&fs=1&rel=0";
                $('#embVideo', this.$block).attr({movie: src, src: src});  
                $('#edit-video-preview', this.$block).removeClass('hidden');  
                this.$videoCode.val( "http://www.youtube.com/watch?v="+arr[0] );
                $('#edit-video-del', this.$block).removeClass('hidden');
            }
        }
        else {
            var arr = video.substr(video.lastIndexOf("?v=") + 3).split('&');
            var src = "http://www.youtube.com/v/"+arr[0]+"&hl=ru_RU&fs=1&rel=0";
            $('#embVideo', this.$block).attr({movie: src, src: src});
            $('#edit-video-preview', this.$block).removeClass('hidden');
            this.$videoCode.val( "http://www.youtube.com/watch?v="+arr[0] );
            $('#edit-video-del', this.$block).removeClass('hidden');
        }
    },
       
    delVideo: function(btn)
    {
        this.$videoCode.val( '' );
        $('#edit-video-preview', this.$block).addClass('hidden');
        $(btn).addClass('hidden');
    },
     
    photos : 
    {
        inited: false, swfu: null, uploaded: 0, lmt: 0, path: '/files/images/items/',
        $block: null, $fav:null, $progress: null,
        init: function (cont, uploaded, lmt, ssid)
        {   if(this.inited) return;
            var self = this;
            this.inited = true;
            this.uploaded = uploaded;
            this.$block    = $('#edit-form-images', cont);
            this.$progress = $('#edit-form-images-progress', cont);
            this.lmt = lmt;    
                                       
            this.swfu = new SWFUpload({
                upload_url: 'index.php?s=bbs&ev=ajax&act=item-img-upload', 
                flash_url : '/bff/js/swfupload/swfupload.swf',
                post_params: {edit: 1, sessid: ssid},
                file_size_limit : '3 MB',
                file_types : "*.jpg; *.jpeg; *.gif;",
                file_types_description : "Файлы изображений",
                file_upload_limit : lmt,
                                                                     
                file_queued_handler : this.fileQueued,
                file_queue_error_handler : this.fileQueueError.bind(this),
                file_dialog_complete_handler : this.fileDialogComplete,
                upload_progress_handler : this.uploadProgress,
                upload_error_handler : this.uploadError,
                upload_success_handler : this.uploadSuccess,
                upload_complete_handler : this.uploadComplete,
                swfupload_loaded_handler : this.loaded.bind(this),
                
                button_image_url : '/img/upload-button.png',
                button_placeholder_id : 'edit-form-images-button',
                button_width: 90, button_height: 21,     
                button_text : 'Обзор...', button_text_top_padding: 1, button_text_left_padding: 25,
                button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
                button_cursor: SWFUpload.CURSOR.HAND,
                
                debug: false 
            }); 
            this.swfu.parent = this;     
                              
            $('a.del', self.$block).live('click', function (e) {
                nothing(e);
                var lnk = $(this);
                if(confirm('Удалить фото?')) {
                    var fn = lnk.attr('rel');
                    bff.ajax('index.php?s=bbs&ev=ajax&act=item-img-delete', {filename: fn, id: bbsEdit.id}, function(data) {
                        if(data) {
                            lnk.parent().remove(); 
                            self.updateStats(0);
                        }
                    });
                } return false;
            });   
        
        },
        
        updateAfterSave: function(id) {
            var path = this.path;
            $('div.item img', this.$block).each(function(){
                $(this).attr('src', path+id+'t'+$(this).attr('rel'));
            });            
        },
        
        uploadComplete: function(file)
        {   try {  
                if (this.getStats().files_queued > 0) {
                    this.startUpload();
                } else {
                    this.parent.$progress.hide();
                }
            } catch (ex) {
                this.debug(ex);
            }
        },

        //обработчик ошибки, в случае HTTP/1.1 500
        uploadError: function(file, errorCode, message) 
        {
        },
        
        fileDialogComplete: function (numFilesSelected, numFilesQueued)
        {
            if (numFilesQueued > 0) {
                this.startUpload();
                this.parent.$progress.show();
            }
        },
        fileQueued: function(file)
        {
            this.addFileParam(file.id, 'id', bbsEdit.id);
        },
        fileQueueError: function (file, errorCode, message)
        {
            switch(errorCode)
            {
                case -100: { errorName = 'Для загрузки Вам доступно '+(this.lmt)+' фотографий'; } break;                
                case -110: { errorName = 'Файл слишком большой';      } break;
                case -120: { errorName = 'Недопустимый размер файла'; } break;
                case -130: { errorName = 'Неверный формат файла';     } break;
                default:     errorName = 'Произошла ошибка при выборе файла, попробуйте повторить свой выбор';
            }                  
            alert( errorName );
        },
        
        uploadSuccess: function(file, data)
        {
            data = $.parseJSON( data ); 
            var self = this.parent; 
            if(data.success) 
            {                       
                self.$block.append('<div class="left" style="margin-right: 5px;">\
                        <a href="javascript:$.fancybox(\''+ self.path + bbsEdit.id + data.filename + '\', {type:\'image\'});">\
                            <img src="' + self.path + bbsEdit.id + 't' + data.filename + '" />\
                        </a><br />\
                        <a href="#" class="txt del but but-text" rel="'+data.filename+'">Удалить</a>\
                    </div>');
            }
        },
        
        uploadProgress: function(file, bytesLoaded)
        {
            return true;
        },

        loaded: function() { this.updateStats(2); },
        updateStats: function(type) {
            var stats = this.swfu.getStats();
            switch(type) {
                case 0: stats.successful_uploads--; break;
                case 1: stats.successful_uploads = 0; break;  
                case 2: stats.successful_uploads = this.uploaded; break;
            }
            this.swfu.setStats(stats);                      
        }  
    }
            
};
