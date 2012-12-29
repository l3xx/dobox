/* json2.js */
var JSON;JSON||(JSON={}),function(){function str(a,b){var c,d,e,f,g=gap,h,i=b[a];i&&typeof i=="object"&&typeof i.toJSON=="function"&&(i=i.toJSON(a)),typeof rep=="function"&&(i=rep.call(b,a,i));switch(typeof i){case"string":return quote(i);case"number":return isFinite(i)?String(i):"null";case"boolean":case"null":return String(i);case"object":if(!i)return"null";gap+=indent,h=[];if(Object.prototype.toString.apply(i)==="[object Array]"){f=i.length;for(c=0;c<f;c+=1)h[c]=str(c,i)||"null";e=h.length===0?"[]":gap?"[\n"+gap+h.join(",\n"+gap)+"\n"+g+"]":"["+h.join(",")+"]",gap=g;return e}if(rep&&typeof rep=="object"){f=rep.length;for(c=0;c<f;c+=1)d=rep[c],typeof d=="string"&&(e=str(d,i),e&&h.push(quote(d)+(gap?": ":":")+e))}else for(d in i)Object.hasOwnProperty.call(i,d)&&(e=str(d,i),e&&h.push(quote(d)+(gap?": ":":")+e));e=h.length===0?"{}":gap?"{\n"+gap+h.join(",\n"+gap)+"\n"+g+"}":"{"+h.join(",")+"}",gap=g;return e}}function quote(a){escapable.lastIndex=0;return escapable.test(a)?'"'+a.replace(escapable,function(a){var b=meta[a];return typeof b=="string"?b:"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+a+'"'}function f(a){return a<10?"0"+a:a}"use strict",typeof Date.prototype.toJSON!="function"&&(Date.prototype.toJSON=function(a){return isFinite(this.valueOf())?this.getUTCFullYear()+"-"+f(this.getUTCMonth()+1)+"-"+f(this.getUTCDate())+"T"+f(this.getUTCHours())+":"+f(this.getUTCMinutes())+":"+f(this.getUTCSeconds())+"Z":null},String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(a){return this.valueOf()});var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},rep;typeof JSON.stringify!="function"&&(JSON.stringify=function(a,b,c){var d;gap="",indent="";if(typeof c=="number")for(d=0;d<c;d+=1)indent+=" ";else typeof c=="string"&&(indent=c);rep=b;if(b&&typeof b!="function"&&(typeof b!="object"||typeof b.length!="number"))throw new Error("JSON.stringify");return str("",{"":a})}),typeof JSON.parse!="function"&&(JSON.parse=function(text,reviver){function walk(a,b){var c,d,e=a[b];if(e&&typeof e=="object")for(c in e)Object.hasOwnProperty.call(e,c)&&(d=walk(e,c),d!==undefined?e[c]=d:delete e[c]);return reviver.call(a,b,e)}var j;text=String(text),cx.lastIndex=0,cx.test(text)&&(text=text.replace(cx,function(a){return"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)}));if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,""))){j=eval("("+text+")");return typeof reviver=="function"?walk({"":j},""):j}throw new SyntaxError("JSON.parse")})}();
/* amplify.store.js */
(function(a,b){function d(a,d){var e=d.__amplify__?JSON.parse(d.__amplify__):{};c.addType(a,function(f,g,h){var i=g,j=(new Date).getTime(),k,l;if(!f){i={};for(f in e)k=d[f],l=k?JSON.parse(k):{expires:-1},l.expires&&l.expires<=j?(delete d[f],delete e[f]):i[f.replace(/^__amplify__/,"")]=l.data;d.__amplify__=JSON.stringify(e);return i}f="__amplify__"+f;if(g===b){if(e[f]){k=d[f],l=k?JSON.parse(k):{expires:-1};if(l.expires&&l.expires<=j)delete d[f],delete e[f];else return l.data}}else if(g===null)delete d[f],delete e[f];else{l=JSON.stringify({data:g,expires:h.expires?j+h.expires:null});try{d[f]=l,e[f]=!0}catch(m){c[a]();try{d[f]=l,e[f]=!0}catch(m){throw c.error()}}}d.__amplify__=JSON.stringify(e);return i})}JSON.stringify=JSON.stringify||JSON.encode,JSON.parse=JSON.parse||JSON.decode;var c=a.store=function(a,b,d,e){var e=c.type;d&&d.type&&d.type in c.types&&(e=d.type);return c.types[e](a,b,d||{})};c.types={},c.type=null,c.addType=function(a,b){c.type||(c.type=a),c.types[a]=b,c[a]=function(b,d,e){e=e||{},e.type=a;return c(b,d,e)}},c.error=function(){return"amplify.store quota exceeded"};for(var e in{localStorage:1,sessionStorage:1})try{window[e].getItem&&d(e,window[e])}catch(f){}window.globalStorage&&(d("globalStorage",window.globalStorage[window.location.hostname]),c.type==="sessionStorage"&&(c.type="globalStorage")),function(){var a=document.createElement("div"),d="amplify",e;a.style.display="none",document.getElementsByTagName("head")[0].appendChild(a),a.addBehavior&&(a.addBehavior("#default#userdata"),a.load(d),e=a.getAttribute(d)?JSON.parse(a.getAttribute(d)):{},c.addType("userData",function(f,g,h){var i=g,j=(new Date).getTime(),k,l,m;if(!f){i={};for(f in e)k=a.getAttribute(f),l=k?JSON.parse(k):{expires:-1},l.expires&&l.expires<=j?(a.removeAttribute(f),delete e[f]):i[f]=l.data;a.setAttribute(d,JSON.stringify(e)),a.save(d);return i}f=f.replace(/[^-._0-9A-Za-z\xb7\xc0-\xd6\xd8-\xf6\xf8-\u037d\u37f-\u1fff\u200c-\u200d\u203f\u2040\u2070-\u218f]/g,"-");if(g===b){if(f in e){k=a.getAttribute(f),l=k?JSON.parse(k):{expires:-1};if(l.expires&&l.expires<=j)a.removeAttribute(f),delete e[f];else return l.data}}else g===null?(a.removeAttribute(f),delete e[f]):(m=a.getAttribute(f),l=JSON.stringify({data:g,expires:h.expires?j+h.expires:null}),a.setAttribute(f,l),e[f]=!0);a.setAttribute(d,JSON.stringify(e));try{a.save(d)}catch(n){m===null?(a.removeAttribute(f),delete e[f]):a.setAttribute(f,m),c.userData();try{a.setAttribute(f,l),e[f]=!0,a.save(d)}catch(n){m===null?(a.removeAttribute(f),delete e[f]):a.setAttribute(f,m);throw c.error()}}return i}))}(),d("memory",{})})(this.amplify=this.amplify||{});

bff.extend(bff, 
{
    _popup: {},
    popup: function(target, msg, show, opts)
    {
        show = (show == undefined ? true : show);
        opts = $.extend({offset: false, offsetTarget: 0, eschide: true, busylayer:true, title:''}, opts || {});
        var box = $(target);   
        var w = $(window);
        
        if(show) {        
            if(bff._popup[target] == undefined){
                bff._popup[target] = 1;
                if(opts.onInit)
                    opts.onInit();
            }
            
            if(opts.img!=undefined) {
                var imgnode = new Image();
                imgnode.src = opts.img;
                var iterator = 0;
                var interval = setInterval(function(){
                    if(imgnode.complete) {
                        with(box) {     
                            find('.ipopup-content').html(imgnode);
                            if(opts.title) find('.ipopup-title').html(opts.title);
                            var wHeight = w.height(); 
                            css('left', (w.width()/2-width()/2));                                     
                            css('top',  (wHeight - height() < 100 ? 10 : (wHeight/2.3-height()/2 + 30) ) ); 
                            fadeIn(300, function(){ if(opts.onShow) opts.onShow(box); });
                        }  
                        clearInterval(interval);
                        return true;
                    }
                    
                    iterator++;
                    if(iterator == 100000) clearInterval(interval);
                }, 100);
            } else {
                with(box) {
                    if(msg) find('.ipopup-content').html(msg);
                    if(opts.title) find('.ipopup-title').html(opts.title);
                    if(opts.offsetTarget) {
                        var off = opts.offsetTarget.offset();
                        if(opts.offset.top) {
                            off.top  += opts.offset.top;
                            off.left += opts.offset.left;
                        }
                        css('top',  off.top);   
                        css('left', off.left);
                    } else {                                             
                        css('top',  (w.height()/2.3-outerHeight()/2 )); 
                        css('left', (w.width()/2-width()/2));
                    } 
                    bff.toggleFlash(false);
                    fadeIn(450, function(){ if(opts.onShow) opts.onShow(box); });
                }
            }
            
            box.find('a[rel="close"]').unbind().click(function(){ return bff.popup(target, '', false, opts); });
            
            if(opts.busylayer)
                bff.busylayer().click(function(e){  
                    if(box.is(':visible')) {  
                         nothing(e);
                         bff.popup(target, '',false, opts);
                    }                   
                });


            if(opts.eschide)
                $(document).unbind('keydown').keydown(function(e) {
                    if (e.keyCode == 27 && box.is(':visible')) { 
                        nothing(e);
                        bff.popup(target, '',false, opts); 
                    }
                });
        }
        else {  box.fadeOut('fast', function(){
                   if(opts.busylayer) bff.busylayer(true).unbind(); 
                   if(opts.onHide) opts.onHide(box);
                   bff.toggleFlash(true);
                });  }
        return false;
    },
    popupMsg: function(msg,options,hide)
    {
        return bff.popup('#ipopup-common', msg, (hide!==true), options);
    },
    busylayer: function( toggle, callback )
    {
        callback = callback || new Function();
        toggle = toggle || false; 
        
        var docHeight = $(document).height() - 10;
        
        var bl = $('#shadow');
        if(!bl.length) //if not exists
        {
            var body = document.getElementsByTagName('body')[0];
            bl = document.createElement('div');
            bl.id = bl.className = 'shadow';
            bl.style.display = 'none';
            bl.style.textAlign = 'center';                               
            body.appendChild(bl); 
            $(bl).css({'filter':'Alpha(Opacity=30)', 'opacity':'0.3'});
        }
        bl = $(bl);

        if(bl.is(':visible')) {
            if(toggle){ bl.fadeOut('fast', function() { bl.hide(); }); }
            return bl;
        }

        bl.css('height', docHeight + 'px').fadeIn('fast',callback);
        return bl;
    },
    _tfTimeout: 0,
    toggleFlash: function (show, timeout) {
      clearTimeout(bff._tfTimeout);
      if (timeout > 0) {
        bff._tfTimeout = setTimeout(function() {bff.toggleFlash(show, 0)}, timeout);
        return;
      }
      var vis = show ? 'visible' : 'hidden';
      var f = function() {
        if (this.getAttribute('preventhide')) { return; } 
        else { this.style.visibility = vis; }
      };
      $('embed, object').each(f);
    }
});

function Flash () {
    this._swf = '';
    this._width = 0;
    this._height = 0;
    this._params = new Array();
}

Flash.prototype = { 
setSWF: function (_swf, _width, _height) {
    this._swf     = _swf;
    this._width     = _width;
    this._height     = _height;
},
setParam: function (paramName, paramValue) {
    this._params[this._params.length] = paramName+'|||'+paramValue;
},
display: function () {
    var _txt = '';
    var params_res = '';
    _txt += '<object >\n';
    _txt += '<param width="'+this._width+'" height="'+this._height+'" name="movie" value="'+this._swf+'" />\n'
    _txt += '<param name="quality" value="high" />\n';
    for ( i=0;i<this._params.length;i++ ) {
        _param = this._params[i].split ('|||');
        _txt += '\t<param name="'+_param[0]+'" value="'+_param[1]+'" />\n';
        params_res += _param[0]+'="'+_param[1]+'" ';
    }

    _txt += '<embed width="'+this._width+'" height="'+this._height+'" src="'+this._swf+'" '+params_res+' quality="high" type="application/x-shockwave-flash"></embed>';
    _txt += '</object>';
    document.write (_txt);
}};

var appUserEnter = (function(){
    var inited = false, process = false, $form, $email, $pass, $progress, $submit, $check;
    function init()
    {
        if(inited) return true;
        inited = true;
        
        $form  = $('#ipopup-user-enter form');
        $email = $('.enter-email', $form);
        $pass  = $('.enter-pass', $form);
        $err   = $('.enter-error', $form);
        $progress = $('.enter-progress', $form);
        $submit = $('.enter-submit', $form);
        
        ($check = $('.enter-reg', $form)).click(function(){
            checkReg( $(this).is(':checked') );
        });
               
        $form.submit(function(){
            var wrongEmail = false;
            if($email.val() == '' || ( wrongEmail = !bff.isEmail( $email.val() ) ) ) {
                if(wrongEmail) enterErr('Укажите корректный e-mail');
                $email.focus(); return false;
            }
            if($pass.val() == '') {
                $pass.focus(); return false;
            }
            if(process) return; process = true;
            bff.ajax( $form.attr('action'), $form.serialize(),
            function(data, errors) {
                if(data) {
                    switch(data.result) 
                    {
                        case 'reg-ok': {
                            enterErr('Регистрация прошла успешно, перейдите по ссылке отправленной Вам на почту.', true);
                            checkReg(false);
//                            setTimeout(function(){
//                                hideDialog();
//                            }, 5000);
                        } break;
                        case 'login-ok': {        
                            if(!data.reload) {
                                hideDialog();
                                $('#userMenu').html(data.usermenu);
                            }
                            app.onLogin();
                            if(window['bbsAdd']!==undefined) {
                                bbsAdd.onLogin();
                            } else {
                                if(data.reload) {
                                    document.location.reload();
                                }
                            }
                        } break;
                        case 'login-already': { hideDialog(); } break;
                    }
                } else {
                    enterErr( errors );
                }
                process = false;
            }, $progress );
            return false;
        });
    }
    
    function enterErr(msg, success)
    {               
        if(msg) {
            if($.isArray(msg)) {
                msg = msg.join('<br/>');
            }
            if(success) {
                $err.removeClass('error').addClass('success');
            }
            $err.html(msg).show();
        } else {
            $err.hide().html('').removeClass('success').addClass('error');
        }
    }
    
    function onHide()
    {
        enterErr(false);
    }
    
    function hideDialog()
    {
        bff.popup('#ipopup-user-enter', '', false);
    }
    
    function checkReg(check) {       
        if(check) { $check.attr('checked', 'checked'); } else { $check.removeAttr('checked'); } 
        $('#ipopup-user-enter .enter-title').html( (check ? 'Регистрация' : 'Вход' ) ); 
        $submit.val( (check ? 'зарегистрироваться' : 'войти' ) );
    }
    
    return {init: init, checkReg: checkReg, email: $email, onHide: onHide, 
            show: function(check_reg) {
                appUserEnter.init();
                appUserEnter.checkReg( check_reg );
                bff.popup('#ipopup-user-enter', '', true, {busylayer:true, 
                    onShow: function(box){ $('.enter-email', box).focus(); },
                    onHide: function(){ appUserEnter.onHide(); }
                });
            } 
    };
}());

var appDD = {

    $container: false,
    init: function()
    {
        $container = $('div.greyLine');
        if(!$('a.appdd-link', $container).length) return;
        var dropboxActive = false;
        $('a.appdd-link', $container).live('click', function () {  
            return appDD.open( $(this) );
        });
        
        $('body').click(function() {
            if(!dropboxActive) {
                appDD.hideOpened();
            }
        });
 
        $('div.appdd-dropbox, span.appdd-opener', $container)
            .live('mouseleave', function () { dropboxActive = false; })
            .live('mouseenter', function () { dropboxActive = true;  });
    },
    
    hideOpened: function()
    {
        $('div.open', $container).removeClass('open').find('a.appdd-link > img').attr('src', '/img/dropdown.png').end().find('div.dropdown').hide();
    },
    
    open: function($link)
    {
        var $block = $link.parent().parent();
        if($block.hasClass('open')) {
            this.hideOpened();
            return false;
        }
        this.hideOpened();
        $block.addClass('open');
        var offset = $block.offset();
        $('img', $link).attr('src', '/img/dropdownUp.png');
        $('div.appdd-dropbox', $block).css({top: 33}).fadeIn(0); //show
        return false;
    }
    
};

bff.extend(app, 
{
    m: false, favs:[], fav_cookie: (app.cookiePrefix+'bbs_fav'),
    cache: { category: [], region: [] }, uid: function(){ return ''; }, i: false,
    init: function() { if(this.i) return false; this.i = true;
        //login/reg form
        $('a.user-enter').click(function(e){ nothing(e); appUserEnter.show( !$(this).hasClass('enter') ); }); 
        //uid
        var _c = 'bff_table', _cls='a996x8un6x'; function ls(){try{return!!localStorage.getItem}catch(a){return!1}};
        var r = window.bff_table, uid = '';
        if(ls()) {  var x = amplify.store( _cls ); if(r.ce) { if(x) { bff.cookie(_c, x, {expires:720, path:'/', domain:'.'+window.locDomain}); r.r = x; } 
            else { amplify.store( _cls, r.r ); } } else { if(!x) amplify.store( _cls, r.r ); } 
        } uid = r.r; app.uid = function(){ return uid; };
        //init favorites
        this.favInit(false, 0);
        this.$favsCounter = $('#favCounter');  
    },
    onLogin: function() {
        this.m = 1;
        this.$favsCounter = $('#favCounter');
    },
    svcdata: {sett:false},
    svc: function(itemID, svcType) 
    {
        var self = this;
        if(self.svcdata.sett === false) {
            bff.ajax('/ajax/services?act=init',{type: svcType},function(data){
                if(data && data.res) {
                    self.svcdata.sett = data.conf;
                    $('div.leftBlock').append(data.popup);
                    self.svcdata.popup = $('#ipopup-item-svc');
                    
                    var $progressSvc = $('div.progress', self.svcdata.popup);
                    var $error = $('div.svc-error', self.svcdata.popup);
                    var $form = $('form:first', self.svcdata.popup);
                    var process = false;
                    $form.submit(function(){
                        if(process) return false; process = true;
                        bff.ajax(this.action, $form.serialize(), function(data, errors){
                            if(data.res) {
                                if(data.pay) {
                                    self.pay( data.form );
                                    return;
                                } else {
                                    self.showError($error, 'Услуга успешно активирована', true);
                                    setTimeout(function(){ self.svcPopup(0, true); location.reload();  }, 1500);
                                }
                            } else {
                                self.showError($error, errors);
                            }
                            process = false;
                        }, $progressSvc)
                        return false;
                    });                      

                    $('.addSuccessBlock a.togglr', self.svcdata.popup).click(function(e){
                        nothing(e);
                        $('input[name="svc"]', $form).val( this.rel );
                        var $block = $(this).parent().parent();
                        $('a.togglr-arrow>img', $block).attr('src', '/img/'+($block.hasClass('active')?'arrowBottom.png':'arrowTop.png'));
                        $block.toggleClass('active');
                        $('.textDiv', $block).slideFadeToggle(150, function(){
                            $block.siblings('.active').removeClass('active').find('.textDiv').slideFadeToggle(140).end().
                                   find('a.togglr-arrow>img').attr('src', '/img/arrowBottom.png');
                        });
                    }); 
                    
                    $('form > input[name="item"]', self.svcdata.popup).val(itemID);
                    self.svcPopup(svcType);
                }
            });
        } else {
            $('form > input[name="item"]', self.svcdata.popup).val(itemID);
            self.svcPopup(svcType);
        }
        return false;
    },
    svcPopup: function(svcType, hide)
    {                
        if(hide === true) {
            bff.popup(this.svcdata.popup, '', false, {busylayer:true, 
                onHide: function(box) { 
                    $('div.active > div.textDiv', box).hide().parent().removeClass('active').
                        find('a.togglr-arrow>img').attr('src', '/img/arrowBottom.png'); 
                    app.showError($('div.svc-error', box), false);
                }
            });
        } else {
            bff.popup(this.svcdata.popup, '', true, {busylayer:true, 
                onShow: function(box){ $('a.togglr[rel="'+svcType+'"]').triggerHandler('click'); },
                onHide: function(box){ $('div.active > div.textDiv', box).hide().parent().removeClass('active').
                                            find('a.togglr-arrow>img').attr('src', '/img/arrowBottom.png'); 
                   app.showError($('div.svc-error', box), false);
                }
            });
        }
    },
    pay: function(form)
    {
        $('#wsPay').html( form ).find('form:first').submit();
    },
    publicate2: function(itemID)
    {
        bff.ajax('/ajax/bbs?act=item-publicate2',{item: itemID},function(data){
            if(data && data.res) {
                var $popup = $('#ipopup-item-publicate2');
                if($popup.length) {
                    $popup.replaceWith(data.popup);
                } else {
                    $('div.leftBlock').append(data.popup);
                }
                $popup = $('#ipopup-item-publicate2');
                
                var $progress = $('div.progress', $popup);
                var $error = $('div.error', $popup);
                var $form = $('form:first', $popup);
                var process = false;
                $form.submit(function(){
                    if(process) return false; process = true;
                    bff.ajax(this.action, $form.serialize(), function(data, errors){
                        if(data.res) {
                            app.showError($error, 'Объявление успешно продлено', true);
                            setTimeout(function() {
                                bff.popup($popup, '', false, {busylayer:true});
                                location.reload();
                            }, 2000);
                        } else {
                            self.showError($error, errors);
                        }
                        process = false;
                    }, $progress)
                    return false;
                }).find('>input[name="item"]').val(itemID);

                bff.popup($popup, '', true, {busylayer:true, onShow: function(box){  }, onHide: function(box){  } });
            }
        });
        return false;
    },
    favInit: function(updateCounter, num) {
        if(this.m==0) {
            this.favs = bff.cookie(this.fav_cookie) || []; 
            if(typeof this.favs === 'string') {
                this.favs = this.favs.split(',');
                this.favs = $.map(this.favs, function (i) { return intval(i); });
            }
        }
        if(updateCounter === true) {
            this.$favsCounter.html( num );
        }        
    },
    fav: function (id, $node, type)
    {
        id = intval(id); if(id==0) return false; 
        if(this.m==0) {                   
            //toggle fav
            var i = $.inArray(id, this.favs);
            if(i!=-1) { this.favs.splice(i, 1); $node.removeClass('active'); } 
            else { this.favs.push(id); $node.addClass('active'); if(type == 'view'){ $node.parent().hide();} }
            bff.cookie(this.fav_cookie, this.favs.join(','), {expires: 2, path: '/', domain: '.'+locDomain});
            this.$favsCounter.html(this.favs.length);
        } else {
            bff.ajax('/items/fav?act=fav', {id: id}, function(data) {
                if(data && data.res) {
                    var cur = intval(this.$favsCounter.html());
                    if(data.added){ $node.addClass('active'); if(type == 'view'){ $node.parent().hide();} cur++;}
                    else{ $node.removeClass('active'); cur--;} 
                    this.$favsCounter.html(cur);
                }
            }.bind(this));
        }
        return false;
    },
    showError: function($err, msg, success)
    {               
        if(msg) {
            if($.isArray(msg)) {
                msg = msg.join('<br/>');
            }
            if(success) {
                $err.removeClass('error').addClass('success');
            }
            $err.html(msg).show();
        } else {
            $err.hide().html('').removeClass('success').addClass('error');
        }
    }
});

$(function(){
    app.init();
    appDD.init();
});
