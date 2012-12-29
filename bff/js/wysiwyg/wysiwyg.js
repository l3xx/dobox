/**
 * Bff.Wysiwyg 0.21
 */
 
var bffWysiwygActive;

(function( $ )
{
    //helpers
    
    // Returns the Parents or the node itself
    // jqexpr = a jQuery expression
    $.fn.parentsOrSelf = function(jqexpr) {
      var n = this;
      if (n[0].nodeType == 3)
        n = n.parents().slice(0,1); 
      if (n.filter(jqexpr).size() == 1)
        return n;
      else
        return n.parents(jqexpr).slice(0,1);
    };
    
    $.fn.bffWysiwyg = function( o, returnInstance )
    {              
        var _ua = navigator.userAgent.toLowerCase();
        if( /iphone|ipod|ipad|opera mini|opera mobi/i.test(_ua) ) {
            if(returnInstance)
                return new bffWysiwygMobile(this.get(0), o);
            else {
                return this.each(function() {
                    new bffWysiwygMobile(this.get(0), o);
                });
            }
        }

        var o = $.extend({
            css  : ['blank.css'],
            path : '/bff/js/wysiwyg/',
            direction    : 'ltr',
            resize       : true,
            autogrow     : true,
            autogrowMax  : 400,   
            autoSave     : true,         
            upload       : 'bffw_upload.php',
            uploadParams : '',
            uploadFunction : false,            
            controls : {},
            messages : {}
        }, o);  

        o.messages = $.extend(true, bffWysiwyg.MESSAGES, o.messages);
        o.controls = $.extend(true, bffWysiwyg.TOOLBAR, o.controls);
        
        if(o.controls_hide && o.controls_hide.length>0) {
            var hide = o.controls_hide;
            for(var i in hide) {
                if(o.controls[hide[i]])
                    delete o.controls[hide[i]];
            }
        }
        
        bff.js.include(o.path+'htmlparser');    
        
        // get path to script 
        o.path = o.path || $($.grep($('script'), function(s){
            return (s.src && s.src.match(/wysiwyg?\.js(\?.*)?$/ ))
            })).attr('src').replace(/wysiwyg?\.js(\?.*)?$/, '');
  
        if(returnInstance) {  
            return new bffWysiwyg(this.get(0), o);
        } else {
            return this.each(function()
            {       
                bffWysiwyg(this, o);
            });
        }
    };

    function bffWysiwyg( element, o )
    {
        return this instanceof bffWysiwyg
            ? this.init(element, o)
            : new bffWysiwyg(element, o);
    }

    $.extend(bffWysiwyg, {
                                        
        BODY   : 'body',
        DIV    : 'div',
        P      : 'p',   
        BR     : 'br',        
        FORMAT_BLOCK : 'FormatBlock', 
        MAIN_CONTAINERS : ['p','h1','h2','h3','h4','h5','h6','pre','blockquote'],        
        NODE : {
          ELEMENT: 1,
          ATTRIBUTE: 2,
          TEXT: 3
        },
    
        MESSAGES : {
            nonSelection : 'Выделите текст, который бы Вы хотели сделать ссылкой.'
        },

        TOOLBAR : {
            bold          : { visible : true, tip : 'Полужирный' },
            italic        : { visible : true, tip : 'Курсив' },
            strikeThrough : { visible : false, tip : 'Перечеркнуть' },
            underline     : { visible : true, tip : 'Подчерктнутый' },

            fontSize : {
                visible : true, dd: true, tip : 'Размер',
                func    : function()
                {                                  
                    return this.dropdownToggle( this.panel.find('a.fontSize'), this.ddFontSize ); 
                }
            },            
            
            fontColor : {
                visible : true, dd: true, tip : 'Цвет текста', 
                func    : function()
                { 
                    return this.dropdownToggle( this.panel.find('a.fontColor'),this.ddFontColor ); 
                } 
            },
            
            separator00 : { visible : true, separator : true },

            justifyLeft   : { visible : true, tip : 'Выровнять по левому краю' },
            justifyCenter : { visible : true, tip : 'Выровнять по центру' },
            justifyRight  : { visible : true, tip : 'Выровнять по правому краю' },
            justifyFull   : { visible : false, tip : 'Justify Full' },

            separator01 : { visible : true, separator : true },

            indent  : { visible : true, tip : 'Увеличить отступ' },
            outdent : { visible : true, tip : 'Уменьшить отступ' },
            blockquote : { visible : true, tip : 'Цитата',
                func: function() {
                    if( this.msie ) {
                        this.focus();   
                        this._exec('indent','indent'); 
                        this.doc.body.appendChild(this.doc.createElement('br'));
                    } else {
                        this._exec('formatBlock', '<blockquote>');  
                        this.doc.body.appendChild(this.doc.createElement('br'));   
                    }
                }
            },     
            
            separator02 : { visible : false, separator : true },

            subscript   : { visible : false },
            superscript : { visible : false },

            separator03 : { visible : false, separator : true },

            undo : { visible : false, tip : 'Отменить' },
            redo : { visible : false, tip : 'Повторить' },

            separator04 : { visible : true, separator : true },

            insertOrderedList    : { visible : true, tip : 'Нумерованный список' },
            insertUnorderedList  : { visible : true, tip : 'Маркированный список' },
            insertHorizontalRule : { visible : false, tip : 'Горизонтальный разделитель' },

            createLink : {
                visible : true, tip : 'Ссылка',
                func    : function()
                {                          
                    var sel = this.getSelectionBounds();
                    var a = this.getParentElement('A');              
                    if(sel.text.length > 0 || a != null)
                    {
                        var szURL = prompt('URL', (a ? a.href : "http://"));
                        if ( szURL === null ) return;
                        szURL = $.trim(szURL);
                        if ( szURL && szURL.length > 0 && szURL!='http://' )
                        {
                            if(a!=null) {
                                $(a).attr('href', szURL);
                            } else {
                                this._exec('unlink');
                                this._exec('createLink', szURL);
                            }
                        } else {
                            this._exec('unlink');
                        }  
                    }
                    else if ( !sel.text.length && this.o.messages.nonSelection )
                        alert(this.o.messages.nonSelection);
                }                
            },
            
            insertImageSimple : {
                visible : false, tip : 'Изображение',
                func    : function()
                {
                    if ( this.msie ) {
                        this.focus(); 
                        this.doc.execCommand('insertImage', true, null);
                    }
                    else
                    {   this.frameWin.focus();
                        var szURL = prompt('Укажите ссылку на изображение:', 'http://');   
                        if ( szURL && szURL.length > 0 && szURL!='http://' )
                            this._exec('insertImage', szURL);
                    }
                }
            },

            insertImage : {
                visible : false, tip : 'Изображение',
                func    : function()
                {
                    this.toggle(true);
                    this.spanid = this.uniqueStamp();
                    if(this.msie) {           
                        this.focus();
                        this.getSelectionBounds().range.pasteHTML( '<span id="span' + this.spanid + '"></span>' );
                    }                      
                    
                    bffWysiwygActive = this;

                    this.modalToggle(true, {ajax: true, ajaxCache: true, ajaxUrl: this.o.path + 'dialogs/insert_image.htm',  ajaxCallback: function(data, cont) {
                        cont.html(data);                       
                        var params = '';
                        if (this.o.uploadFunction) var params = this.o.uploadFunction();
                        this.uploadInit('bffwInsertImageForm', { 
                                url: this.o.upload + params, 
                                trigger: 'bffwUploadBtn'       
                            });
                    }.bind(this), busylayer: true });
                }
            },
            
            separator07 : { visible : false, separator : true },

            cut   : { visible : false },
            copy  : { visible : false },
            paste : { visible : false },

            separator08 : { separator : true },

            fullscreen : {
                visible : true, tip : 'Во весь экран',
                func    : function()
                {
                    this.fullscreenToggle();
                }
            },
            
            removeFormat : {
                visible : true, tip : 'Удалить форматирование',
                func    : function()
                {
                    if (this.msie) this.focus();
                    this._exec('removeFormat', []);
                    this._exec('unlink', []);
                }
            },

            html : {
                visible : true, tip : 'Код',
                func    : function()
                {                       
                    this.toggle( !this.visual ); 
                }
            },

            tableRemove: { visible: 1, tip: 'Удалить таблицу', className: 'tableRemove hidden', func: function () { this.tableActions('table-remove'); } }, 
            tableRowBefore: { visible: 1, tip: 'Вставить строку перед', className: 'tableRowBefore hidden', func: function () { this.tableActions('row-before'); } },
            tableRowAfter: { visible: 1, tip: 'Вставить строку после', className: 'tableRowAfter hidden', func: function () { this.tableActions('row-after'); } } ,
            tableColumnBefore: { visible: 1, tip: 'Вставить столбец перед', className: 'tableColumnBefore hidden', func: function () { this.tableActions('column-before'); } },
            tableColumnAfter: { visible: 1, tip: 'Вставить столбец после', className: 'tableColumnAfter hidden', func: function () { this.tableActions('column-after'); } },
            tableRowRemove: { visible: 1, tip: 'Удалить строку', className: 'tableRowRemove hidden', func: function () { this.tableActions('row-remove'); } },
            tableColumnRemove: { visible: 1, tip: 'Удалить столбец', className: 'tableColumnRemove hidden', func: function () { this.tableActions('column-remove'); } }
        
        }
    });

    bffWysiwyg.prototype = 
    {
        id       : 0,
        textarea : null, //original textarea
        box      : null, //container
        frame    : null, //frame
        frameWin : null, //frame window
        doc      : null, //document        
        visual   : true, //visual mode   
        fullscreen: false, //fullscreen
        sel      : false,

        focus : function(real)
        {       
            if(!this.visual) { 
                this.textarea.focus();
                return;
            }
            if(real){
                var self = this; 
                if ( this.msie ) {       
                    var sel = this.getSelectionBounds();           
                    setTimeout( function(){ if(sel.range) sel.range.select(); }, 0 ); 
                }                                                         
                else setTimeout( function(){ self.frameWin.focus(); }, 0 );
            } else {
                $(this.doc.body).focus();
            }
        },
        focusElement : function (elem, collapse)
        {
            if (collapse == undefined)
                collapse = false;

            if (this.frameWin.getSelection)
            {
                var sel = this.frameWin.getSelection(), r = this.doc.createRange();
                sel.removeAllRanges();
                r.selectNodeContents(elem);
                r.collapse(collapse);
                sel.addRange(r);
            }
            else
            {
                var r = this.doc.body.createTextRange();
                if (elem.nodeType != 3) {
                    try {            
                        r.moveToElementText(elem);
                        r.collapse(collapse);
                        r.select(); 
                    }
                    catch (e) {}
                }
            }
        },        
        
        init : function( element, o )
        {
            var self = this;            
            this.id = $(element).attr('id');
            _bffwysiwyg[this.id] = this;
            this.textarea = $(element);                 
            this.o = o || {};          
            
            //browsers
            var _ua = navigator.userAgent.toLowerCase();  
            this.opera  = /opera/i.test(_ua);
            this.msie   = (!this.opera && /msie/i.test(_ua));
            this.msie6  = (!this.opera && /msie 6/i.test(_ua)),
            this.msie8  = (!this.opera && /msie 8/i.test(_ua)),            
            this.mozilla= /firefox/i.test(_ua);
            this.chrome = /chrome/i.test(_ua);
            this.safari = (!(/chrome/i.test(_ua)) && /webkit|safari|khtml/i.test(_ua));            
                        
            $.data(element, 'wysiwyg', this);
                                           
            this.width = parseInt($(element).css('width'));
            this.height = element.height || element.clientHeight || parseInt($(element).css('height')); 

            if ( element.nodeName.toLowerCase() != 'textarea' ) return;

            //frame
            this.frame = $('<iframe frameborder="0" marginheight="0" marginwidth="0" scrolling="auto" vspace="0" hspace="0" \
                                    id="bffw_frame_'+ this.id + '" class="bffw-frame" style="height: '+(this.height)+'px;" tabindex="'+($(element).attr('tabindex'))+'">\
                                    </iframe>').css({minHeight : 50}).get(0);
            
            //panel
            this.panel = $('<ul class="panel"></ul>');
            this.buildToolbar();
                
            //container
            this.box = $('<div id="bffw_box_'+ this.id + '" style="width: ' + ( this.width > 0 ? (this.width)+'px' : '100%') + '; position:relative;" class="bffw-box"></div>');
            
            this.textarea.hide().css({width: '100%', height: this.height}).attr('spellcheck', false);    
            
            this.box.insertAfter(this.textarea);
            this.box.append(this.panel)                                      
                    .append( $('<div class="bffw-frame-box"></div>').append(this.frame).append(this.textarea) )
                    .append('<iframe id="bffw-dd-fontcolor'+self.id+'" rel="'+self.id+'" class="bffw-dropdown" scrolling="no" height="100" frameborder="0" width="150" style="visibility: hidden;" marginheight="0" marginwidth="0" src="'+o.path+'/dialogs/palette.htm"></iframe>')
                    .append('<iframe id="bffw-dd-fontsize'+self.id+'" rel="'+self.id+'" class="bffw-dropdown" scrolling="no" height="105" frameborder="0" width="165" style="visibility: hidden;" marginheight="0" marginwidth="0" src="'+o.path+'/dialogs/fontsize.htm"></iframe>');
                        
            // resizer
            if(this.o.resize){
                this.resizer = $('<div id="bffw_resize_' + this.id + '" class="bffw-resize"><div></div></div>');
                $(this.box).append(this.resizer);     
                this.resizer.mousedown(function(e) { self.initResize(e) } );
            }                    
                    
            //create dropdowns
            this.ddFontColor = $('#bffw-dd-fontcolor'+self.id);
            this.ddFontSize = $('#bffw-dd-fontsize'+self.id);

            //save pre-existing content
            this.initialContent = $(element).val();

            if (this.chrome && this.initialContent==''){
                this.initialContent='&nbsp;';
            }

            if (this.msie) {            $.extend(this, bffWysiwygExplorer); }
            else if (this.mozilla) {    $.extend(this, bffWysiwygMozilla);  }
            else if ($.browser.opera) { $.extend(this, bffWysiwygOpera);    }
            else if (this.safari || this.chrome) { $.extend(this, bffWysiwygSafari); }
            
            this.initIframe();      

            this.textarea.focus(function() {    
                if (!self.msie) 
                    $(self.doc.body).focus();
            });

            if( this.o.autoSave ) {
                //$(this.doc).bind('keydown keyup mousedown', 
//                    function(){ self.saveContent(); });
            }
                                                   
            var form = $(element).closest('form');
            form.submit(function() {  
                if(!self.visual) { 
                    //ta > iframe                                       
                    var html = self.textarea.val();
                    self.setContent( bffWysiwygProtectedSource.Protect( html ) );  
                }
                // iframe > ta
                self.textarea.val( self.getContent() );
            }).bind('reset', function() {
                self.setContent( self.initialContent, false );
                self.toVisual();
            });
        },

        initIframe : function()
        {
            var self = this;
            //init style includes
            var style = '';                                     
            if(this.o.css) {
                if( typeof(this.o.css) == 'string' ) {
                     style += '<style>' + this.o.css + '</style>'; 
                } 
                else {
                     $.each(this.o.css, function(){
                       style += '<link rel="stylesheet" type="text/css" href="' + self.o.path + this + '" />';
                    });                     
                }
            }
            
            this.frameWin = this.frame.window ? this.frame.window : this.frame.contentWindow;  
            this.doc = (this.frame.contentWindow ? this.frame.contentWindow.document : (this.frame.contentDocument ? this.frame.contentDocument : this.frame.document));
                       
            this.doc.open();
            this.doc.write(
                '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\
                    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">\
                    '+style+'</head><body class="bffw_body">'+(this.initialContent)+'</body></html>'
            );
            this.doc.close();  
            this.doc.contentEditable = 'true'; 
            this._init(this); //extra init, browsers specific
            
            $('html', this.doc).attr('dir', this.o.direction);      

            if( this.msie ) {
                //remove ie-border
                setTimeout(function() { $(self.doc.body).css({border: 'none'}); }, 0);
            }
                                                                                                                                                    
            $(this.doc).bind('mouseup keyup', function( e ) {
                self.updateToolbar();
                self.dropdownsHide();
                if (e.keyCode == 86 && (e.ctrlKey || e.metaKey)) { //ctrl+v
                    setTimeout(function () { self.clearWord(false); }, 800); 
                }
            }).bind('keydown', function(e){
                if(self.fullscreen && e.keyCode == 9) { //tab
                    self._exec('indent'); 
                    return false;
                }                
            });
            
            if(this.o.autogrow && !this.msie)
                this.agInit(); 
        },
        
        reinitIframe: function()
        {
            this.initialContent = this.getContent();            
            this.initIframe();  
        },
        
        uniqueStamp: function() {   
            return Math.floor(Math.random() * 99999);
        },


        /* HTML */                     
        getContent : function()
        {   
            if(!this.doc) return '';
            
            if(!this.visual) //if code-view, sync ta > iframe
                this.sync(); //sync, protect
                                       
            //convert extra <br> to &nbsp;
            this.cleanExtraBRs(this.doc.body);
            
            var html = $(this.doc.body).html() || "";
            if(this.visual) {  
                html = this.sanitizeHTML(html);
            }
            return bffWysiwygProtectedSource.Revert( html ); 
        },

        _firstPreview: true,
        getContentPreview : function()
        {        
            if(!this.doc) return '';
            if(this._firstPreview) {    
                this.setContent( this.sanitizeHTML( bffWysiwygProtectedSource.Protect( this.textarea.val() ) ) ); //protect html             
                this._firstPreview = false;
            }
            if(!this.visual) this.toggle(true); //toggle,sync,protect

            //convert extra <br> to &nbsp;
            this.cleanExtraBRs(this.doc.body);
                            
            //return protected code
            return $(this.doc.body).html() || "";
        },
        
        getContentText : function()
        {        
            if(!this.doc) return '';
            if(!this.visual) this.toggle(true); //toggle,sync,protect
                 
            var text = $(this.doc.body).html() || '';
            //remove tags and spaces
            return text.replace(/<(?:.|\s)*?>/g, '').replace(/\s/ig, '').replace(/&nbsp;/g, '');
        },
        
        getContentDim : function()
        {
            return {width: $(this.doc).width(), height: $(this.doc).height()};
        },

        setContent : function( newContent, append )
        {
            if(append)
                $(this.doc.body).append(newContent);
            else
                $(this.doc.body).html(newContent);
        },
        
        _toggles: false,
        toggle: function(visual)
        {
            if(!this.doc || this._toggles) return;             
            if(visual == this.visual)
                return;

            this._toggles = true;
            this.sync();
                        
            if(visual) //code => visual
            {                        
                this.textarea.hide();
                $(this.frame).show();                    
                $('a:not(.html)', this.panel).removeClass('disabled');
            }
            else //visual => code
            {                       
                $(this.frame).hide();
                this.textarea.show();  
                $('a:not(.html)', this.panel).addClass('disabled');
            }
            
            this.visual = visual;
            if(visual)
                $('.html', this.panel).removeClass('active');
            else
                $('.html', this.panel).addClass('active');
            this._toggles = false; 
        }, 
        
        sync: function()
        {
            if(this.visual) {
                this.textarea.val( this.getContent() ); // iframe > ta   
            } else {
                var html = this.sanitizeHTML( this.textarea.val() );
                this.setContent( bffWysiwygProtectedSource.Protect( html ) ); //ta > iframe
            }
        },
        
        sanitizeHTML: function(html)
        {
            try{
                return HTMLtoXML( this.clearWord( html ) );
            } catch(e) {  
                return html;
            }
        },
        
        clearWord: function(html)
        {   
            var setcont = false;
            if(html === false) {
                html = this.getContent();
                setcont = true;
            }
            var hasWordFormat = /<\w[^>]*(( class="?MsoNormal"?)|( class="?MsoPlainText"?)|(="mso-))/gi ;
            if ( hasWordFormat.test( html ) ) 
            {
                //language tags
                html = html.replace( /<(\w[^>]*) language=([^ |>]*)([^>]*)/gi, "<$1$3") ;
                //font tags
                html = html.replace( /<FONT\s*>([\s\S]*?)<\/FONT>/gi, '$1' ) ; 
                
                var rs = []; //remove:           
                rs.push(/(MsoNormal|MsoPlainText)/g); //mso classes
                rs.push(/<style[^>]*>[\s\S]*?<\/style[^>]*>/gi); //style tags with contents
                rs.push(/<(?:META|LINK)[^>]*>\s*/gi); //meta/link tags
                rs.push(/<w:[^>]*>[\s\S]*?<\/w:[^>]*>/gi); //w: tags with contents
                rs.push(/<\\?\?xml[^>]*>/gi); //XML elements and declarations   
                rs.push(/<\/?\w+:[^>]*>/gi); //Tags with XML namespace declarations: <o:p><\/o:p> 
                rs.push(/ v:.*?=".*?"/g); // Weird nonsense attributes
                rs.push(/\s*mso-[^:]+:[^;"]+;?/gi); //mso-xxx styles
                rs.push(/\s*(style|class)="\s*"/gi); //empty styles
                $.each(rs, function() {
                    html = html.replace(this, '');
                });

                //IE conditional comments
                html = html.replace( /<!--\[if[^<]*?\]-->[\S\s]*?<!--\[endif\]-->/gi, '' );   
                
                alert('Текст, который Вы собираетесь вставить, содержит неподдерживаемые типы форматирования.\n\n\
                       Рекомендуем вставлять простой текст (plain text) из программ Блокнот, NotePad и т.д.\n\n\
                       В форме для ответа Вы видите результат, который нам удалось отобразить. Возможно, он отображается некорректно, поэтому отредактируйте текст перед отправкой\n\n\
                       Приносим свои извинения за неудобства');
            }
            
            if(setcont) {
                this.setContent( html );
            } else {
                return html;
            }
        },
        
        toVisual: function()
        {
            if(!this.visual)
                this.toggle(true);
            else { this.sync(); }
        },   
        
        NonEmptyBlockElements : { p:1,div:1,form:1,h1:1,h2:1,h3:1,h4:1,h5:1,h6:1,address:1,pre:1,ol:1,ul:1,li:1,td:1,th:1 },
        cleanExtraBRs: function(node)
        {
            var nodeChild = node.firstChild;  
            while(nodeChild) {             
                this.cleanExtraBRs(nodeChild);
                nodeChild = nodeChild.nextSibling;
            }
            
            // This needed to avoid Firefox leaving extra BRs at the end of them.
            if(node && node.tagName) {
                var sNodeName = node.tagName.toLowerCase();  
                if( Boolean( this.NonEmptyBlockElements[ sNodeName ] ) && sNodeName && sNodeName != 'pre') {
                    var lastChild = node.lastChild;  
                    if ( lastChild && lastChild.nodeType == 1 && lastChild.nodeName == 'BR' ){
                        $(lastChild).replaceWith('&#160;');
                    }
                }
            }
        },
        
        
        /* Fullscreen */ 
        fullscreen_win_state: {},       
        fullscreenToggle: function()
        {    
            if (this.fullscreen === false)
            {
                this.fullscreen = true;
                if(this.resizer) this.resizer.hide();        
                
                this.height = $(this.frame).css('height');
                this.width = this.box.width() + 'px';

                this.box.css({ position: 'absolute', top: 0, left: 0, zIndex: 1000 }).after('<span id="fullscreen_' + this.id +  '"></span>');
                                
                this.fullscreen_win_state = {'scroll_top': $(window).scrollTop(), 'body_height': $(document.body).height() };
                $(document.body).css({'overflow': 'hidden', 'height': '1px'});

                $(document).scrollTop(0,0);
                                                 
                this.fullscreenResize();                 
                $(window).resize(function() { this.fullscreenResize(); }.bind(this));
            }
            else
            {
                this.fullscreen = false;                 

                $(window).unbind('resize', function() { this.fullScreenResize(); }.bind(this));    
                $(document.body).css({'overflow': '', 'height': this.fullscreen_win_state.body_height});
                $(document).scrollTop( this.fullscreen_win_state.scroll_top ,0);

                if (this.resizer) this.resizer.show();            
                
                this.box.css({ position: 'relative', top: 'auto', left: 'auto', zIndex: 1, width: this.width });
                
                $('#fullscreen_'+this.id).remove();            
                
                $(this.frame).css('height', this.height);                        
                this.textarea.css('height', this.height);                                    
            }                                    
                          
            $('a.fullscreen', this.panel).toggleClass('active');
        },
        fullscreenResize: function()
        {
            if (this.fullscreen === false) return;
            
            var height = $(window).height() - 42;
            
            this.box.width( $(window).width() );
            $(this.frame).height(height);  
            this.textarea.height(height);    
        },
        
        
        /* Resize */
        initResize: function(e)
        {    
            var self = this;
            e.preventDefault();
            this.splitter = e.target;
             
            if (this.visual)
            {
                this.element_resize = $(this.frame);
                this.element_resize.get(0).style.visibility = 'hidden';
                this.element_resize_parent = this.textarea;
            }
            else
            {
                this.element_resize = this.textarea;
                this.element_resize_parent = $(this.frame);
            }
            
            this.stopResizeHdl = function (e) { self.stopResize(e); };
            this.startResizeHdl = function (e) { self.startResize(e); };
            this.resizeHdl =  function (e) { self.resize(e); };
    
            $(document).mousedown(this.startResizeHdl);
            $(document).mouseup(this.stopResizeHdl);
            $(this.splitter).mouseup(this.stopResizeHdl);
    
            this.null_point = false;
            this.h_new = false;
            this.h = this.element_resize.height();
        },
        startResize: function()
        {
            $(document).mousemove(this.resizeHdl);
        },
        resize: function(e)
        {
            e.preventDefault();
            var y = e.pageY;
            if (this.null_point == false) this.null_point = y;
            if (this.h_new == false) this.h_new = this.element_resize.height();
    
            var s_new = (this.h_new + y - this.null_point) - 10;
    
            if (s_new <= 30) return true;    
            if (s_new >= 0)
            {
                this.element_resize.get(0).style.height = s_new + 'px';
                this.element_resize_parent.get(0).style.height = s_new + 'px';
            }
    
        },
        resizeTo: function(width, height)
        {
            this.element_resize.get(0).style.height = height + 'px';
            this.element_resize_parent.get(0).style.height = height + 'px';
            this.element_resize.get(0).style.width = width + 'px';
            this.element_resize_parent.get(0).style.width = width + 'px';            
        },
        stopResize: function(e)
        {
            $(document).unbind('mousemove', this.resizeHdl);
            $(document).unbind('mousedown', this.startResizeHdl);
            $(document).unbind('mouseup', this.stopResizeHdl);
            $(this.splitter).unbind('mouseup', this.stopResizeHdl);
            
            this.element_resize.get(0).style.visibility = 'visible';
        },  
        
        /* Autogrow */        
        agInit: function()
        {
            var self = this;
            this.agCheckHdl = function (e) { if(!self.fullscreen) self.agCheck(e); }; 
            
            this.agMinHeight = this.frame.offsetHeight;               
            $(this.doc).keyup( this.agCheckHdl );
            
        }, 
        
        agCheck : function()
        {
            if (!this.doc) return;

            var frame = this.frame;
            var newHeight = this.doc.body.clientHeight;

            newHeight = this.agGetEffectiveHeight( newHeight ) ;
            
            frame.style.height = newHeight + 'px'; 
        },   

        agGetEffectiveHeight : function( height )
        {
            var max = this.o.autogrowMax;
            
            if ( height < this.agMinHeight )
                height = this.agMinHeight;
            else if ( max && max > 0 && height > max ) {
                height = max;
            } else {
                height += 30;
            }

            return height;
        },        
       
        /* Selection */
        getSelectionBounds: ( window.getSelection ?
            function(selectionOnly) {  
                var range, selection, root, start, end;
                selection = this.frameWin.getSelection();
                if(selectionOnly) return selection;
                range = ( selection.rangeCount > 0 ? selection.getRangeAt(0).cloneRange() : this.doc.createRange() );
                root = range.commonAncestorContainer || null;
                start = range.startContainer;
                end = range.endContainer;
                if(3 == start.nodeType) start = start.parentNode;
                if(3 == end.nodeType) end = end.parentNode;
                return {
                    range: range, text: selection.toString(), selection: selection,
                    root: start == end ? start : root,
                    start: start, end: end
                }
            } :
            function(selectionOnly) { 
                if(selectionOnly) return this.doc.selection; 
                var range, selection, root, start, end,
                // возвращает узел DOM являющийся левой (при start == true) или правой (при start == false) границой выделения
                calcRangeBound = function(isStart) {
                    var duplicate = range.duplicate();
                    duplicate.collapse(isStart);
                    return duplicate.parentElement();
                };     
                range = (this.doc.selection ? this.doc.selection.createRange() : null);
                root = (range && range.parentElement ? range.parentElement() : null);
                start = calcRangeBound(true);
                end = calcRangeBound(false); 
                selection = range.text;
                return {
                    range: range, text: selection,
                    root: start == end ? start : root,
                    start: start, end: end
                };
            } 
        ),
        getSelectionContaining: function(selection, filterFunc) {
            var selection = selection || this.getSelectionBounds(), 
                getAncestor = function (elem, filter) {
                while (elem.tagName!='BODY') {
                    if (filter(elem)) return elem;
                    elem = elem.parentNode;
                } return null;
            };

            if(window.getSelection) {
                var anc = selection.range.commonAncestorContainer;
                if(anc.childElementCount > 0) {    
                    var elem = null;
                    $.each(anc.children, function(i, e){
                        if(filterFunc(e)) { elem = e; return false; }
                    }); return elem;
                }               
                return getAncestor(selection.range.commonAncestorContainer, filterFunc);
            } else {
                if (selection.type=='Control') {         
                    if (selection.range.length==1) { 
                        var elem = selection.range.item(0); 
                    }
                    else { // multiple control selection 
                        return null; 
                    }
                } else {
                    var elem = selection.range.parentElement();
                }
                return getAncestor(elem, filterFunc);
            }
        },
        getParentElement : function () {
            this.frameWin.focus();
            var found = false;
            var sel = this.getSelectionBounds();
            for (var e = sel.root; e && e.nodeName != "BODY" && !found; )
            {
                for (var c = 0; c < arguments.length; c++)
                {
                    if (arguments[c].toUpperCase() == e.nodeName.toUpperCase()) {
                        found = true;
                        break 
                    }
                    if (!found) {
                        e = e.parentNode;
                    }
                }
            }
            if (e && e.nodeName == 'BODY') {
                e = null;
            }
            return e;
        },
    
        
        insertHtml : function( szHTML, focus )
        {
            if( szHTML && szHTML.length > 0 )
               this._exec('insertHTML', this.sanitizeHTML( szHTML ) );

            if(focus)
                this.focus(true);
        },  
        
        setFontColor: function(color) {   
            if (this.msie) { 
                this.sel.range.select();
            }
            this._exec('ForeColor', color);
            this.dropdownsHide();
            this.focus(true);
            if(this.o.autoSave) this.sync();
        },
        setFontSize: function(size) {   
            if (this.msie) { //ie
                this.sel.range.select();
            }              
            this._exec('FontSize', size);
            this.dropdownsHide();
            this.focus(true);
            if(this.o.autoSave) this.sync();
        },        
     
        /* Dropdowns & Modal */
        dropdownToggle: function(btn, dd)
        {     
            if(this.msie) //save current range of selection
                this.sel = this.getSelectionBounds(); 
                               
            if (dd.css('visibility') == 'hidden') { 
                this.dropdownsHide();                            

                var offset = btn.parent().position(); 
                dd.css({left: offset.left+2, top: offset.top+18, visibility: 'visible'});
            } else {
                this.dropdownsHide();
            }
        },  
        dropdownsHide: function()
        {                    
            $('.bffw-dropdown').css('visibility', 'hidden');
        },           
        modalToggle: function(show, opts)
        {
            if(show) this.dropdownsHide();        
            return utils.popupErr('', show, opts);
        },

        /* Upload */    
        uploadInit: function(element, options)
        {
            this.uploadOpts = {
                url: false, success: false, start: false, trigger: false, auto: false, input: false
            };
      
            $.extend(this.uploadOpts, options);
    
            element = $('#' + element);   
            if(!element.length) return;
            if(element.get(0).tagName == 'INPUT') { //if input, get form
                this.uploadOpts.input = element;
                this.uploadOpts.form = $(element.get(0).form);
            }
            else {
                this.uploadOpts.form = element;
            }

            this.uploadOpts.formAction = this.uploadOpts.form.attr('action');
    
            //Auto or trigger
            if (this.uploadOpts.auto)
            {
                this.uploadOpts.form.submit(function(e) { return false; });
                this.uploadSubmit();
            }
            else if (this.uploadOpts.trigger)
            {
                $('#' + this.uploadOpts.trigger).click(function() { this.uploadSubmit(); }.bind(this)); 
            }
        },
        uploadSubmit : function()
        {
            if(!this.uploadOpts.input && $('#bffw_image_link').val()!='') {   
                this.imageUploadCallback('{"error": 0, "url":"'+($('#bffw_image_link').val())+'"}');
                return;    
            }
            
            this.uploadForm(this.uploadOpts.form, this.uploadFrame());
        },    
        uploadFrame : function()
        {
            this.uploadID = 'f' + Math.floor(Math.random() * 99999);
        
            var d = document.createElement('div');
            var iframe = '<iframe style="display:none" src="about:blank" id="'+this.uploadID+'" name="'+this.uploadID+'"></iframe>';
            d.innerHTML = iframe;
            document.body.appendChild(d);

            //upload start
            if (this.uploadOpts.start) this.uploadOpts.start();
            $('#' + this.uploadID).load(function () { this.uploadLoaded() }.bind(this));
            return this.uploadID;
        },
        uploadForm : function(f, name)
        {
            if (this.uploadOpts.input)
            {
                var formId = 'bffwUploadForm' + this.uploadID;
                var fileId = 'bffwUploadFile' + this.uploadID;
                var form = $('<form action="' + this.uploadOpts.url + '" method="POST" target="' + name + '" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');    
    
                var oldElement = this.uploadOpts.input;
                var newElement = $(oldElement).clone();
                $(oldElement).attr('id', fileId)
                             .before(newElement)
                             .appendTo(form);
                form.css({'position': 'absolute', 'top': '-1200px', 'left': '-1200px'})
                    .appendTo('body')
                    .submit();
            }
            else
            {
                f.attr({'target': name, 'method': 'POST', 'enctype': 'multipart/form-data', 'action': this.uploadOpts.url})
                 .submit();
            }
    
        },
        uploadLoaded : function()
        {
            var i = $('#' + this.uploadID);

            if (i.contentDocument) var d = i.contentDocument;
            else if (i.contentWindow) var d = i.contentWindow.document;
            else var d = window.frames[this.uploadID].document;
            if (d.location.href == "about:blank") return true;
    
            //upload success
            if (this.uploadOpts.success) this.uploadOpts.success( d.body.innerHTML );
            this.imageUploadCallback( d.body.innerHTML );
            this.uploadOpts.form.attr({'action': this.uploadOpts.formAction, 'target': ''}); //return prev action
        },
        imageUploadCallback: function(data)
        {
            var data = $.parseJSON(data);    
            if(data == 0) {
                alert('Ошибка загрузки файла');
                return;
            }
            
            if(data.error!=0 && data.error!=7) {
                alert('Ошибка: '+data.errormsg);
                return;
            }  
            
            var alt = $('#bffw_image_alt').val();
                alt = alt ? ' alt="'+alt+'"' : '';
            
        
            var self = bffWysiwygActive;
            self.focus(true);
            var imgID = self.uniqueStamp();
            
            var eAlign = $('#bffw_image_align');
            if(eAlign.length) {
                var align = eAlign.val();   
                if(align == 'left' || align == 'right')
                    align = 'align="'+align+'" style="margin-'+(align == 'right' ? 'left' : 'right')+': 10px; margin-bottom: 5px;" '; 
                else align = '';                    
                var html = '<img '+alt+' id="'+imgID+'" src="'+(data.url)+'" '+align+' />';
            }
            else  {
                var html = '<img '+alt+' id="'+imgID+'" src="'+(data.url)+'" />'; 
            }

            if (self.msie) {        
                $(self.doc.getElementById('span' + self.spanid)).after(html);
                $(self.doc.getElementById('span' + self.spanid)).remove();
            }    
            else {
                self._exec('inserthtml', html);
            }
            var img = $('#'+imgID, self.doc.body);
            if(img.length) { img.attr('src', data.url).removeAttr('id'); }
            
            if(data.error==7)
                alert('Ошибка: '+data.errormsg);
            else
                self.modalToggle(false);

        },         
        
        
        /* Toolbar */
        buildToolbar : function()
        {
            this.toolbarCmds = {};
            
            for( var name in this.o.controls )
            {
                var control = this.o.controls[name];
                if(control.separator) {
                    if ( control.visible !== false )
                        this.appendToolbarSeparator();
                } else if(control.visible) {
                    this.appendToolbarCommand(
                        control.command || name, 
                        (control.className || control.command || name || 'empty'), 
                        control
                    ); 
                }
            }
        },
        appendToolbarCommand : function( cmd, className, ctrl )
        {
            var self = this;
            var args = ctrl.arguments || [];

            (this.toolbarCmds[cmd] = $('<a class="'+(className || cmd)+'"><!-- --></a>')).get(0).unselectable = 'on'; // IE, prevent focus
            
            $('<li></li>').append( this.toolbarCmds[cmd] )
            .mousedown(function() {
                if(!self.visual && cmd!='html'){ self.toggle(true); return; }
                if(!ctrl.dd) self.dropdownsHide();
                if(ctrl.func) ctrl.func.apply(self); else {   
                    self._exec(cmd, args);
                    if(self.o.autoSave)
                        self.sync(); 
                }
                self.focus( true );
                self.updateToolbar();
            }).attr('title', ctrl.tip || '' ).appendTo( this.panel );
        },
        appendToolbarSeparator : function()
        {
            $('<li class="separator"></li>').appendTo( this.panel );
        },
        updateToolbar: function()
        {   
            if(!this.visual) return;         
            var cmds = ['bold', 'italic', 'underline', 'justifyLeft', 'justifyCenter', 'justifyRight', 'insertOrderedList', 'insertUnorderedList'], 
                state = false, cmd; 
            try
            {
                for (var i=0; i < cmds.length; i++) {
                    cmd = cmds[i];
                    if( this.doc.queryCommandState( cmd ) )
                         this.toolbarCmds[ cmd ].addClass('active');
                    else { 
                        this.toolbarCmds[ cmd ].removeClass('active');   
                    }
                    //if( !this.doc.queryCommandEnabled( cmd ) )
                    //     this.toolbarCmds[ cmd ].addClass('disabled');
                    //else this.toolbarCmds[ cmd ].removeClass('disabled'); 
                }                 
                //console.log( this.doc.queryCommandValue( 'forecolor' ) ); //fontname, fontsize, forecolor
                
            } catch ( e ) {}
            
            //table buttons
            var cmdsTable = ['tableRemove', 'tableRowBefore', 'tableRowAfter', 'tableColumnBefore', 'tableColumnAfter', 'tableRowRemove', 'tableColumnRemove'];
            var table = this.getParentElement('TABLE');
            for (var i=0; i < cmdsTable.length; i++) {
                cmd = cmdsTable[i];
                if( !table )
                     this.toolbarCmds[ cmd ].addClass('disabled');
                else this.toolbarCmds[ cmd ].removeClass('disabled hidden');
            } 
        }        
    };  


    var bffWysiwygTempBin =
    {
        Elements : new Array(),
        AddElement : function( element )
        {
            var iIndex = this.Elements.length ;
            this.Elements[ iIndex ] = element ;
            return iIndex ;
        },

        RemoveElement : function( index )
        {
            var e = this.Elements[ index ] ;
            this.Elements[ index ] = null ;
            return e ;
        },

        Reset : function()
        {
            var i = 0 ;
            while ( i < this.Elements.length )
                this.Elements[ i++ ] = null ;
            this.Elements.length = 0 ;
        },

        ToHtml : function()
        {
            for ( var i = 0 ; i < this.Elements.length ; i++ )
            {
                this.Elements[i] = '<div>&nbsp;' + this.Elements[i].outerHTML + '</div>' ;
                this.Elements[i].isHtml = true ;
            }
        }
    };


    bffWysiwygProtectedSource = new Object();
    bffWysiwygProtectedSource._CodeTag = (new Date()).valueOf() ;
    bffWysiwygProtectedSource.RegexEntries = [
        //comments
        /<!--[\s\S]*?-->/g ,
        //script tags
        /<script[\s\S]*?<\/script>/gi,
        //noscript tags
        /<noscript[\s\S]*?<\/noscript>/gi
    ] ;

    bffWysiwygProtectedSource.Add = function( regexPattern )
    {
        this.RegexEntries.push( regexPattern ) ;
    }

    bffWysiwygProtectedSource.Protect = function( html )
    {
        var codeTag = this._CodeTag ;
        function _Replace( protectedSource ) {
            var index = bffWysiwygTempBin.AddElement( protectedSource ) ;
            return '<!--{' + codeTag + index + '}-->' ;
        }

        for ( var i = 0 ; i < this.RegexEntries.length ; i++ )
            html = html.replace( this.RegexEntries[i], _Replace ) ;

        return html ;
    }

    bffWysiwygProtectedSource.Revert = function( html, clearBin )
    {
        function _Replace( m, opener, index )
        {
            var protectedValue = clearBin ? bffWysiwygTempBin.RemoveElement( index ) : bffWysiwygTempBin.Elements[ index ] ;
            // There could be protected source inside another one.
            return bffWysiwygProtectedSource.Revert( protectedValue, clearBin ) ;
        }

        var regex = new RegExp( "(<|&lt;)!--\\{" + this._CodeTag + "(\\d+)\\}--(>|&gt;)", "g" ) ;
        return html.replace( regex, _Replace ) ;
    }   
    
    
    
    
                                        
    var bffWysiwygExplorer = 
    {
        _init: function(self) 
        {
            this.doc.body.onfocus = function() { self.doc.designMode = "on"; self.doc = self.frame.contentWindow.document; };
            this.doc.onbeforedeactivate = function() { self.saveCaret(); };
            this.doc.onkeyup = function() {
              self.saveCaret();
              self.keyup();
            };
            this.doc.onclick = function() {self.saveCaret();};
            
            //init designMode
            this.doc.designMode="on";
            try{ this.doc = this.frame.contentWindow.document; }catch(e){}
        },

        _exec: function(cmd, param) {

            if(cmd == 'insertHTML') {
                this.focus();
                this.doc.selection.createRange().pasteHTML( param );
                return;
            }
            
            if(param) this.doc.execCommand(cmd,false,param);
            else this.doc.execCommand(cmd);
        },

        selected: function() {
            var caretPos = this.frame.contentWindow.document.caretPos;
                if(caretPos!=null) {
                    if(caretPos.parentElement!=undefined)
                      return caretPos.parentElement();
                }
        },

        saveCaret: function() {
            this.doc.caretPos = this.doc.selection.createRange();
        },

        //keyup handler
        keyup: function() {
          this._selected_image = null;
        }
    };

    
    //Mozilla                       
    var bffWysiwygMozilla = 
    {
        _init: function(self) {   
            this.enableDesignMode(); 
            
            $(this.doc).bind('keydown', function(e){ return self.keydown(e); }).
                        bind('keyup',   function(e){ return self.keyup(e); }).
                        bind('focus',   function() { return self.enableDesignMode(); });       
        },

        _exec: function(cmd,param) {

            if(!this.selected()){ return false;}

            if(param) this.doc.execCommand(cmd,'',param);
            else this.doc.execCommand(cmd,'',null);
            
            //set to P if parent = BODY
            var container = this.selected();
            if(container.tagName.toLowerCase() == bffWysiwyg.BODY)
                this._exec(bffWysiwyg.FORMAT_BLOCK, bffWysiwyg.P);
        },

        //returns selected container
        selected: function() {
            var sel = this.getSelectionBounds(true);
            var node = sel.focusNode;
            if(node) {
                if(node.nodeName == '#text') return node.parentNode;
                else return node;
            } else return null;
        },

        keydown: function(e) { 
          if((e.ctrlKey || e.metaKey) && e.keyCode != 13){
            if(e.keyCode == 66)  { this._exec('Bold'); return false; } //ctrl+b => bold 
            else if(e.keyCode == 73){ this._exec('Italic'); return false; } //ctrl+i => italic 
            else if(e.keyCode == 85)  { this._exec('Underline'); return false; } //ctrl+u => underline 
          }            
          else if(e.keyCode == 13) {
            if(!e.shiftKey){
              //fix PRE bug
              var container = this.selected(); 
              if(container){ 
                var tag = container.tagName.toLowerCase();
                if(tag == 'pre' || tag == bffWysiwyg.BODY) {
                    e.preventDefault();
                    this._exec('insertHTML', '<p></p>');
                }
              }
            }
          }
        },

        keyup: function(e) {
          
          this._selected_image = null;

          if(e.keyCode == 13 && !e.shiftKey) { //return and no shift
            //cleanup <br><br> between paragraphs
            $(this.doc.body).children(bffWysiwyg.BR).remove();
          }
          else if(e.keyCode != 8  //backspace
               && e.keyCode != 17 //ctrl
               && e.keyCode != 46 //delete
               && e.keyCode != 224 //ctrl
               && !e.metaKey  //command
               && !e.ctrlKey) //ctrl 
          {
            //text nodes replaced by P
            var container = this.selected();
            var name = container.tagName.toLowerCase();
            
            if( //fix forbidden main containers
              name == "strong" || name == "b" ||
              name == "em" || name == "i" ||
              name == "sub" || name == "sup" || name == "a"
            ) name = container.parentNode.tagName.toLowerCase();
            if(name == bffWysiwyg.BODY) { 
                this._exec(bffWysiwyg.FORMAT_BLOCK, bffWysiwyg.P);
            }
          }
        },

        enableDesignMode: function() {
            try {  
            if(this.doc.designMode == 'off') {
                this.doc.designMode = 'on';
                this.doc.execCommand('styleWithCSS', '', false);         
              }                                                            
              this.doc.execCommand("EnableInlineTableEditing", false, false);
            } catch(e) {}
        }
    };

    
    // Opera                       
    var bffWysiwygOpera = 
    {
        _init: function(self) {                                            
            this.doc.designMode = 'on';   
            $(this.doc).bind('keydown', function(e){ self.keydown(e); }).
                        bind('keyup', function(e){ self.keyup(e); }); 
        },

        _exec: function(cmd,param) {
            if(param) this.doc.execCommand(cmd,false,param);
            else this.doc.execCommand(cmd);
        },

        selected: function() {
            var sel  = this.getSelectionBounds(true);
            var node = sel.focusNode;
            if(node) {
                if(node.nodeName=="#text") return(node.parentNode);
                else return(node);
            } else return(null);
        },

        keydown: function(e) {                             
          var sel = this.getSelectionBounds(true);
          startNode = sel.start;

          //get P instead of no container
          if(!$(startNode).parentsOrSelf(bffWysiwyg.MAIN_CONTAINERS.join(","))[0]
              && !$(startNode).parentsOrSelf('li')
              && e.keyCode != 13 //enter
              && e.keyCode != 37 //left
              && e.keyCode != 38 //up
              && e.keyCode != 39 //right
              && e.keyCode != 40 //down
              && e.keyCode != 8  //backspace
              && e.keyCode != 46)//delete
              this._exec(bffWysiwyg.FORMAT_BLOCK, bffWysiwyg.P);
        },

        keyup: function(e) {
          this._selected_image = null;
        }
    };

    
    // Safari                     
    var bffWysiwygSafari = 
    {                       
        _init: function(self) 
        {
            this.doc.designMode = 'on';
            $(this.doc).bind('keydown', function(e){ self.keydown(e); }).
                        bind('keyup', function(e){ self.keyup(e); });
        },

        _exec: function(cmd,param) {  
            if(!this.selected()) return(false);
            if(param) this.doc.execCommand(cmd,'',param);
            else this.doc.execCommand(cmd,'',null);
            
            //set to P if parent = BODY
            var container = this.selected();
            if(container && container.tagName.toLowerCase() == bffWysiwyg.BODY)
                this._exec(bffWysiwyg.FORMAT_BLOCK, bffWysiwyg.P);
        },
                                         
        selected: function() { 
            var sel = this.getSelectionBounds(true);
            var node = sel.focusNode;
            if(node) {
                if(node.nodeName == '#text') return(node.parentNode);
                else return(node);
            } else return(null);
        },

        keyup: function(e) {   
          this._selected_image = null;

          //ctrl+u => underline
          if((e.ctrlKey || e.metaKey) && e.keyCode == 85){  
            this._exec('Underline'); return false; 
          }              
                                              
          if(e.keyCode == 13 && !e.shiftKey) { //enter and !shift
            //cleanup <br><br> between paragraphs
            $(this.doc.body).children(bffWysiwyg.BR).remove();
          }

          //safari makes no <br> on enter+shift  
          if(e.keyCode == 13 && e.shiftKey && !this.chrome) {
            this._exec('InsertLineBreak');
          }
          
          if( e.keyCode != 8  //backspace
           && e.keyCode != 17 //ctrl
           && e.keyCode != 46 //delete
           && e.keyCode != 224 //ctrl
           && !e.metaKey    //control
           && !e.ctrlKey) { //ctrl
              
            //text nodes replaced by P
            var container = this.selected();
            var name = container.tagName.toLowerCase();
            if( //fix forbidden main containers
              name == "strong" || name == "b" ||
              name == "em"  || name == "i" ||
              name == "sub" || name == "sup" ||
              name == "a" || name == "span"
            ) name = container.parentNode.tagName.toLowerCase();

            if(name == bffWysiwyg.BODY || name == bffWysiwyg.DIV) this._exec(bffWysiwyg.FORMAT_BLOCK, bffWysiwyg.P);
          }
        }, 
        
        keydown: function(e) {
          if(e.keyCode == 13 && !e.shiftKey) {
              //fix PRE bug
              var container = this.selected(); 
              if(container){ 
                var tag = container.tagName.toLowerCase();
                if(tag == bffWysiwyg.BODY || tag == 'pre') {
                    e.preventDefault();
                    this._exec(bffWysiwyg.FORMAT_BLOCK, bffWysiwyg.P);
              }
            }
          }
        }
        
    };
    
    //Mobile
    function bffWysiwygMobile(element, o) { this.init(element, o) };   
    bffWysiwygMobile.prototype = {
        txt: null,
        init: function(element, o)
        {
            this.txt = $(element); 
            this.txt.after('<input type="hidden" name="mobile" value="1" />');
            this.txt.width( this.txt.width()-5 );
            this.getContent = this.getContentText;
            var curHeight = this.txt.height();
            this.txt.css('minHeight', curHeight);                            
            this.txt.autogrow({minHeight: curHeight, lineHeight: 9});
        },
        getContentPreview: function() {
            var text = this.txt.val();
            return text.replace(/([^>])\n/g, '$1<br/>'); //nl2br   
        },
        getContentText: function() {
            return this.txt.val();
        },
        getContentDim: function() {
            return {width: $(this.txt).width(), height: $(this.txt).height()};  
        },
        setContent: function( text, append )
        {
            this.txt.val( (append ? (text + this.txt.val()) : text ) );
        },
        insertHtml: function( text, stripTags )
        {
            //strip tags
            if(stripTags !== false)
                text = text.replace(/<\/?[^>]+>/gi, '');
            
            var field = this.txt.get(0);
            // если opera, то не передаем фокус
            if(navigator.userAgent.indexOf('Opera')==-1) { field.focus(); }
            
            if(document.selection){ //ie
                document.selection.createRange().text = text + ' ';
            }
            else if (field.selectionStart || field.selectionStart == 0) 
            {
                var strFirst = field.value.substring(0, field.selectionStart);
                field.value = strFirst + text + field.value.substring(field.selectionEnd, field.value.length);

                // ставим курсор
                var pos = 0;
                if(!pos){
                    pos = (strFirst.length + text.length);
                    field.selectionStart = field.selectionEnd = pos;
                } else {
                    field.selectionStart = field.selectionEnd = (strFirst.length + pos);
                }
            } 
            else {
                field.value += text;
            } 
        },
        focus: function()
        {
            this.txt.focus();
        },
        toggle: function(){}, toVisual: function(){}, sync: function(){}
    };

})(jQuery);

//-------------------------------------------------------------------------------------------------------------
// Multifile

function multiFile(options)
{
    this.listTarget = document.getElementById(options.list); // Where to write the list 
    this.count = 0; // current elements count? 
    this.id = 0;
    this.name = options.name || 'file'; // input name 
    this.extensions = options.extensions || false; // allowed extensions      
    this.limit = options.limit || -1; // is there a maximum?
    this.delimg = options.delimg || '/img/icon-delete.gif'; // delete image src
    
    this.reset = function() {
        this.count = 0;
        this.id = 0;
        this.listTarget.style.display = 'none';
    };
  
    this.clearValue = function(tagId){
        document.getElementById(tagId).innerHTML = 
            document.getElementById(tagId).innerHTML;
    };

    var attachWords = ['прикреп*','вложен*','аттач*','атач*','atach*','attach*','прилага*'];
    for(var i=0; i<attachWords.length; i++) {
        attachWords[i] = attachWords[i].replace(/([\(\)\.\+\-\{\}\[\]])/g,'\\$1');
        attachWords[i] = attachWords[i].replace(/\*/g,'[^ \s\n\r\.\!\?,\:]*');
        
    }                          
    
    this.findAttachWord = function(sMessage){     
        for(var i=0;i<attachWords.length;i++){
            if((new RegExp('('+attachWords[i]+')')).test(sMessage)){
                return RegExp.$1;
            }
        }
        return false;
    } 
    
//    this.findAttachWord = function(sMessage) {                                 
//        var att = new RegExp ("(прикреп|вложен|аттач|атач|atach|attach|прилага)", 'i');  
//        return att.exec(sMessage);
//    };
  
    //Add a new file input element
    this.addElement = function( element )
    {
        // Make sure it's a file input element
        if( element.tagName == 'INPUT' && element.type == 'file' )
        {
            // Element name -- what number am I?
            element.name = this.name + '_' + this.id++;

            // Add reference to this object
            element.mf = this;
                
            // What to do when a file is selected
            element.onchange = function()
            {
                if(this.mf.extensions && !this.mf.checkExt(this.value))
                {
                    alert('Неверный тип файла'); 
                    return false;    
                }
                
                // New file input
                var new_element = document.createElement( 'input' );
                new_element.type = 'file';
                new_element.className = this.className;

                // Add new element
                this.parentNode.insertBefore( new_element, this );

                // Apply 'update' to element
                this.mf.addElement( new_element );

                new_element.size = element.size;
                
                // Update list
                this.mf.addListRow( this );           
                
                // Hide this: we can't use display:none because Safari doesn't like it
                this.style.position = 'absolute';
                this.style.left = '-1000px';     
            };
            
            // If we've reached maximum number, disable input element
            if( this.limit != -1 && this.count >= this.limit ){
                element.disabled = true;
            }

            // File element counter
            this.count++;
            // Most recent element
            this.current_element = element;
        }
    };

    this.checkExt = function (f) { 
        try
        {                              
             if(!f) return true;
             var ext = f.split(/\.+/).pop().toLowerCase();
             return (this.extensions.search(ext)!=-1);
        } catch(e) { return true; } 
    }
    
    /**
     * Add a new row to the list of files
     */
    this.addListRow = function( element )
    {
        // Row div
        var new_row = document.createElement( 'div' );
        new_row.className = 'clear';
        new_row.element = element;
            
        // Delete button
        var new_row_button = document.createElement( 'div' );
        new_row_button.className = 'link left';
        new_row_button.style.margin = '3px 5px 0 0';
        new_row_button.style.cursor = 'pointer';
        new_row_button.innerHTML = '<img src="'+this.delimg+'" title="удалить" />';    
        
        // References
        var new_row_path = document.createElement( 'div' ); 
        new_row_path.className = 'nowrap left';
        new_row_path.style.margin = '3px 0 0 0';            
        
        // Delete function
        new_row_button.onclick= function()
        {
            // Remove element from form
            this.parentNode.element.parentNode.removeChild( this.parentNode.element );

            // Remove this row from the list
            this.parentNode.parentNode.removeChild( this.parentNode );

            // Decrement counter
            this.parentNode.element.mf.count--;

            // Re-enable input element (if it's disabled)
            this.parentNode.element.mf.current_element.disabled = false;

            if(this.parentNode.element.mf.count <= 1)
                this.parentNode.element.mf.listTarget.style.display = 'none';
            
            // Appease Safari
            //    without it Safari wants to reload the browser window
            //    which nixes your already queued uploads
            return false;
        };

        // Set row value
        new_row_path.innerHTML = element.value.replace(/^([^\\\/]*(\\|\/))*/,"");  

        // Add button
        new_row.appendChild( new_row_button );

        new_row.appendChild ( new_row_path ) 
        // Add it to the list
        this.listTarget.appendChild( new_row );
        this.listTarget.style.display = 'block';
    };
    
    this.addElement( document.getElementById(this.name) );
}; 

_bffwysiwyg = [];
