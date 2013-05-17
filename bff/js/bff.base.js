var app = {
   fordev: 0, adm: false,
   cookiePrefix: 'bff_',
   themeUrl: ''
};

var bff = {
//ie : ( /*@cc_on!@*/ false) && (parseInt(navigator.userAgent.match(/msie\s(\d+)/i)[1])),

extend: function(destination, source) {
    if(destination.prototype) {
        for (var property in source)
            destination.prototype[property] = source[property];
    } else {
        for (var property in source)
            destination[property] = source[property];
    }
    return destination;
},

redirect: function(url, ask, timeout)
{
    if(!ask || (ask && confirm(ask)))
        window.setTimeout('window.location.href = "' + url + '"', (timeout || 0) * 1000);
    
    return false;
},    

isEmail: function(str)
{
    var re = /^\s*[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\s*$/i;
    return re.test(str);
},

ajax: function(url, params, callback, progressSelector, o)
{
    o = o || {async: false}; 
    var bffAjxProcessErrors = function(errors, system){
        if(!app.adm) return;
        if(system) {
             bff.error('Ошибка передачи данных'+(errors?'('+errors+')':''));
        } else {            
            if(!errors.length) return;
            bff.error( errors.join('<br/>') ); 
        }
    };                             
    
    var timer_reached = false, response_received = false, progress = null;
    var bffAjxFinishProgress = function() {
        if(timer_reached && response_received && progress)
            progress.hide();
    };
    
    if(progressSelector!=undefined && progressSelector!=false) {
        progress = $(progressSelector);
        if(!progress.length) progress = false;
    }
    
    if(progress) {  
        progress.show();  
        setTimeout(function() { 
            timer_reached = true;  
            bffAjxFinishProgress();
        }, 900);
    }
    
    return $.ajax({
        url: url, data: params, dataType: 'json', type: 'POST', async: (o.async || false),
        success: function(resp, status, xhr) {
            response_received = true; 
            bffAjxFinishProgress();  
        
            if(resp == undefined) {
                if(status!=='success')
                    bffAjxProcessErrors(0, true);
                if(callback) callback(false); return;
            }
                
            if(resp.errors && resp.errors.length)
                bffAjxProcessErrors(resp.errors, false);
                
            if(callback) callback(resp.data, resp.errors);
        },
        error: function(xhr, status, e){  
            response_received = true; 
            bffAjxFinishProgress();                
            bffAjxProcessErrors(xhr.status, true);
            if(o.onError) o.onError(xhr, status, e);
            if(callback) callback(false);
        }
    });
},

placeholder: (function(){   //@author Sergey Chikuyonok 
    var data_key = 'plc-label',
        fields_key = 'bindedFiles';
    // is placeholder supported by browser (Webkit, Firefox 3.7) 
    var nativePlaceholder = ('placeholder' in document.createElement('input'));
         
    /**
     * Функция, отвечающая за переключение отображения заполнителя.
     * Срабатывает автоматически при фокусе/блюре с элемента ввода.
     * @param {Event} evt
     */
    function placeholderSwitcher(evt) {
        var input = $(this),
            label = input.data(data_key);

        if (!$.trim(input.val()) && evt.type == 'blur')
            label.show();
        else
            label.hide();
    }

    function focusOnField(evt) {
        var binded_fields = $(this).data(fields_key);

        if (binded_fields) {
            $(binded_fields).filter(':visible:first').focus();
            evt.preventDefault();
        }
    }

    function linkPlaceholderWithField(label, field) {
        label = $(label);
        field = $(field);
                   
        if (!label.length || !field.length)
            return;     
            
        /** @type Array */
        var binded_fields = label.data(fields_key);

        if (!binded_fields) {
            binded_fields = [];
            label
                .data(fields_key, binded_fields)
                .click(focusOnField);
        }

        binded_fields.push(field[0]);
        field.data(data_key, label)
            .bind('focus blur', placeholderSwitcher) 
            .blur();             
    }

    return {
        init: function(context) {                     
            $(context || document).find('label.placeholder').each(function(){
                if(nativePlaceholder) return;
                linkPlaceholderWithField(this, '#'+$(this).attr('for'));
                $( $(this).data(fields_key) ).blur();
            }); 
        },

        linkWithField: linkPlaceholderWithField
    };
})(),
            
js: {                      
    versioninig: false,
    _included: {},
    include: function(path, version) {
        version = version || 1.0;
        if(this._included[path] && this._included[path] >= version) return false;

        var transport = (window.XMLHttpRequest && (window.location.protocol !== "file:" || !window.ActiveXObject) ?
            function() { return new window.XMLHttpRequest(); } :
            function() { try { return new window.ActiveXObject("Microsoft.XMLHTTP"); } catch(e) {} } )();

        transport.open('GET', (path + (this.versioninig ? '.v' + version : '') + '.js'), false);
        transport.send(null);
        
        var code = transport.responseText;
        (typeof execScript != 'undefined') ? execScript(code) : eval(code);
        this._included[path] = version;
        return true;
    }
},

input: {
    file: function(self, id) {
        var file = self.value.split("\\");
        file = file[file.length-1];
        var html = '<a href="#delete" onclick="bff.input.reset(\''+self.id+'\'); $(\'#'+id+'\').html(\'\'); $(this).blur(); return false;"></a>' + file;
        $('#'+id).html(html);
    },
    
    reset: function(id) {
        var o = document.getElementById(id);
        if (o.type == 'file') {
            try{
                o.parentNode.innerHTML = o.parentNode.innerHTML;
            } catch(e){}
        } else {
            o.value = '';
        }
    }
},

declension: function(count, forms, add) {
    var prefix = (add!==false ? count+' ':'');
    var n = Math.abs(count) % 100; var n1 = n % 10;  
    if (n > 10 && n < 20) { return prefix+forms[2]; }
    if (n1 > 1 && n1 < 5) { return prefix+forms[1]; }
    if (n1 == 1) { return prefix+forms[0]; }
    return prefix+forms[2];
},

setCookie: function(name, value, expiredays, path, domain, secure)
{
    path = path || '/';
    var exdate = new Date(); exdate.setDate(exdate.getDate() + expiredays);
    var curCookie = name+'='+escape(value)+((expiredays)?'; expires='+exdate.toGMTString():'')+
                    ((path)?'; path='+path:'')+
                    ((domain)?'; domain='+domain:'')+
                    ((secure)?'; secure':'');
    document.cookie = curCookie;  
},

getCookie: function(name)
{
   if (document.cookie.length>0) {
       c_start = document.cookie.indexOf(name + "=");
       if (c_start!=-1)
       {
           c_start = c_start + name.length+1;
           c_end = document.cookie.indexOf(";",c_start);
           if (c_end == -1) c_end = document.cookie.length;
           return unescape(document.cookie.substring(c_start, c_end));
       }
   }
   return;
},

/**
 * Создает куки или возвращает значение.
 * @examples:
 * bff.cookie('the_cookie', 'the_value'); - Задает куки для сессии.
 * bff.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'site.com', secure: true }); Создает куки с опциями.
 * bff.cookie('the_cookie', null); - Удаляет куки.
 * bff.cookie('the_cookie'); - Возвращает значение куки.
 *
 * @param {String} name Имя куки.
 * @param {String} value Значение куки.
 * @param {Object} options Объект опций куки.
 * @option {Number|Date} expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                               If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                               If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                               when the the browser exits.
 * @option {String} path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option {String} domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option {Boolean} secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                          require a secure protocol (like HTTPS).
 *
 * @return {mixed} значение куки
 * @author Klaus Hartl (klaus.hartl@stilbuero.de), Vlad Yakovlev (red.scorpix@gmail.com)
 * @version 1.0.1, @date 2009-11-12
 */
cookie: function(name, value, options) {                
    if ('undefined' != typeof value) {
        options = options || {};                        
        if (null === value) {
            value = '';
            options.expires = -1;
        }                                               
        // CAUTION: Needed to parenthesize options.path and options.domain in the following expressions,
        // otherwise they evaluate to undefined in the packed version for some reason…
        var path = options.path ? '; path=' + options.path : '',
            domain = options.domain ? '; domain=' + options.domain : '',
            secure = options.secure ? '; secure' : '',
            expires = '';

        if (options.expires && ('number' == typeof options.expires || options.expires.toUTCString)) {
            var date; 
            if ('number' == typeof options.expires) {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 86400000/*24 * 60 * 60 * 1000*/));
            } else {
                date = options.expires;
            }   
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }   
        window.document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
        return true;
    }

    var cookieValue = null;
    if (document.cookie && '' != document.cookie) {
        $.each(document.cookie.split(';'), function() {
            var cookie = $.trim(this);
            // Does this cookie string begin with the name we want?
            if (cookie.substring(0, name.length + 1) == (name + '=')) {
                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                return false;
            }
        });
    }

    return cookieValue;
}, 

browser: {}

};

var _ua = navigator.userAgent.toLowerCase();
bff.browser = {
  version: (_ua.match( /.+(?:me|ox|on|rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [0,'0'])[1],
  opera: /opera/i.test(_ua),
  msie: (/msie/i.test(_ua) && !/opera/i.test(_ua)),
  msie6: (/msie 6/i.test(_ua) && !/opera/i.test(_ua)),
  msie7: (/msie 7/i.test(_ua) && !/opera/i.test(_ua)), 
  mozilla: /firefox/i.test(_ua),
  chrome: /chrome/i.test(_ua),
  safari: (!(/chrome/i.test(_ua)) && /webkit|safari|khtml/i.test(_ua))
};

function nothing(e) {
    //var e = e.originalEvent || e;
    if (e.stopPropagation) e.stopPropagation(); 
    if (e.preventDefault) e.preventDefault();
    e.cancelBubble = true;
    e.returnValue = false;
    return false;
}
    
function intval(number) 
{
    return number && + number | 0 || 0;
}


//Exceptions
function bff_report_exception(msg, url, line) {
    $.ajax({ url: '/bff/logs/js.error.php', data: {'e': msg, 'f': url || window.location, 'l': line}, dataType: 'json', type: 'POST' });
}
window.onerror = function (msg, url, line) { bff_report_exception(msg, url, line); };

//Placeholder
$(function(){ 
    bff.placeholder.init(); 
});

// Simple JavaScript Templating, John Resig - http://ejohn.org/ - MIT Licensed
(function(){
  var cache = {};
  bff.tmpl = function (str, data){   
    var fn = !/\W/.test(str) ? cache[str] = cache[str] || bff.tmpl(document.getElementById(str).innerHTML) :
      new Function("obj", "var p=[],print=function(){p.push.apply(p,arguments);};with(obj){p.push('" +
        str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r")
           .replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'")
      + "');}return p.join('');");
    return data ? fn( data ) : fn;
  };
})();


//Text length
(function(){
  var lastLength = 0;
  window.checkTextLength = function(max_len, val, warn, nobr, limit){
    if(lastLength==val.length)return;
    lastLength=val.length;
    var n_len = replaceChars(val, nobr).length;
    warn.style.display = (n_len > max_len - 100) ? '' : 'none';
    if (n_len > max_len) {
      //if(limit && n_len + 50 > max_len) { limit.value = val.substr(0, max_len); return; }
      warn.innerHTML = 'Допустимый объем превышен на '+bff.declension(n_len - max_len, ['символ','символа','символов'])+'.';
    } else if (n_len > max_len - 50) {
      warn.innerHTML = 'Осталось: '+bff.declension(max_len - n_len, ['символ','символа','символов'])+'.';
    } else {
      warn.innerHTML = '';
    }
  };

  window.replaceChars = function(text, nobr) {
    var res = "";
    for (var i = 0; i<text.length; i++) {
      var c = text.charCodeAt(i);
      switch(c) {
        case 0x26: res += "&amp;"; break;
        case 0x3C: res += "&lt;"; break;
        case 0x3E: res += "&gt;"; break;
        case 0x22: res += "&quot;"; break;
        case 0x0D: res += ""; break;
        case 0x0A: res += nobr?"\t":"<br>"; break;
        case 0x21: res += "&#33;"; break;
        case 0x27: res += "&#39;"; break;
        default:   res += ((c > 0x80 && c < 0xC0) || c > 0x500) ? "&#"+c+";" : text.charAt(i); break;
      }
    }
    return res;
  };
})();

(function($) {

$.extend({
   
/* Debounce and throttle function's decorator plugin 1.0.4 Copyright (c) 2009 Filatov Dmitry (alpha@zforms.ru)
 * Dual licensed under the MIT and GPL licenses: http://www.opensource.org/licenses/mit-license.php, http://www.gnu.org/licenses/gpl.html
 */
    debounce : function(fn, timeout, invokeAsap, context) {
        if(arguments.length == 3 && typeof invokeAsap != 'boolean') {
            context = invokeAsap;
            invokeAsap = false;
        }
        var timer;
        return function() {
            var args = arguments;
            if(invokeAsap && !timer) { fn.apply(context, args); }
            clearTimeout(timer);
            timer = setTimeout(function() { if(!invokeAsap) { fn.apply(context, args); } timer = null; }, timeout);
        };
    },

    throttle : function(fn, timeout, context) {
        var timer, args;
        return function() {
            args = arguments;
            if(!timer) {
                (function() {
                    if(args) { fn.apply(context, args); args = null; timer = setTimeout(arguments.callee, timeout); }
                    else { timer = null; }
                })();
            }
        };
    },
    
    assert : function(cond, msg, force_report) {
        if (!cond) {
            if (app.fordev) {
                alert("Assertion Error: " + msg);
            }
            bff_report_exception(msg, window.location.href, window.location.href);
            //throw msg;
        }
    }
    
});

})(jQuery); 

//------------------------------------------------------------------------------
Function.prototype.bind=function(a){var b=this,c=[].slice.call(arguments,1);return function(){b.apply(a,c.concat([].slice.call(arguments,0)))}};

/*jQuery Simple Effects
  http://www.learningjquery.com/2008/02/simple-effects-plugins?utm_source=feedburner&utm_medium=feed&utm_campaign=Feed:+LearningJquery+(Learning+jQuery)
*/
jQuery.fn.fadeToggle=function(speed,easing,callback){return this.animate({opacity:"toggle"},speed,easing,callback)};
jQuery.fn.slideFadeToggle=function(speed,easing,callback){return this.animate({opacity:"toggle",height:"toggle"},speed,easing,callback)};

/* 
 * Auto Expanding Text Area (1.2.2) by Chrys Bader (www.chrysbader.com) chrysb@gmail.com
 * Copyright (c) 2008 Chrys Bader (www.chrysbader.com)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 */
(function(b){b.fn.autogrow=function(a){return this.each(function(){new b.autogrow(this,a)})};b.autogrow=function(a,c){this.options=c||{};this.interval=this.dummy=null;this.line_height=this.options.lineHeight||parseInt(b(a).css("line-height"));this.min_height=this.options.minHeight||parseInt(b(a).css("min-height"));this.max_height=this.options.maxHeight||parseInt(b(a).css("max-height"));this.textarea=b(a);if(this.line_height==NaN)this.line_height=0;if(this.min_height==NaN||this.min_height==0)this.min_height= this.textarea.height();this.init()};b.autogrow.fn=b.autogrow.prototype={autogrow:"1.2.2"};b.autogrow.fn.extend=b.autogrow.extend=b.extend;b.autogrow.fn.extend({init:function(){var a=this;this.textarea.css({overflow:"hidden",display:"block"});this.textarea.bind("focus",function(){a.startExpand()}).bind("blur",function(){a.stopExpand()});this.checkExpand()},startExpand:function(){var a=this;this.interval=window.setInterval(function(){a.checkExpand()},400)},stopExpand:function(){clearInterval(this.interval)}, checkExpand:function(){if(this.dummy==null){this.dummy=b("<div></div>");this.dummy.css({"font-size":this.textarea.css("font-size"),"font-family":this.textarea.css("font-family"),width:this.textarea.css("width"),padding:this.textarea.css("padding"),"line-height":this.line_height+"px","overflow-x":"hidden",position:"absolute",top:0,left:-9999}).appendTo("body")}var a=this.textarea.val().replace(/(<|>)/g,"");a=$.browser.msie?a.replace(/\n/g,"<BR>new"):a.replace(/\n/g,"<br>new");if(this.dummy.html()!= a){this.dummy.html(a);if(this.max_height>0&&this.dummy.height()+this.line_height>this.max_height)this.textarea.css("overflow-y","auto");else{this.textarea.css("overflow-y","hidden");if(this.textarea.height()<this.dummy.height()+this.line_height||this.dummy.height()<this.textarea.height())this.textarea.animate({height:this.dummy.height()+this.line_height+"px"},100)}}}})})(jQuery);

/**
 * Copyright (c) 2007-2013 Ariel Flesler - aflesler<a>gmail<d>com | http://flesler.blogspot.com
 * Dual licensed under MIT and GPL.
 * @author Ariel Flesler
 * @version 1.4.5b
 */
;(function($){var h=$.scrollTo=function(a,b,c){$(window).scrollTo(a,b,c)};h.defaults={axis:'xy',duration:parseFloat($.fn.jquery)>=1.3?0:1,limit:true};h.window=function(a){return $(window)._scrollable()};$.fn._scrollable=function(){return this.map(function(){var a=this,isWin=!a.nodeName||$.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!isWin)return a;var b=(a.contentWindow||a).document||a.ownerDocument||a;return/webkit/i.test(navigator.userAgent)||b.compatMode=='BackCompat'?b.body:b.documentElement})};$.fn.scrollTo=function(e,f,g){if(typeof f=='object'){g=f;f=0}if(typeof g=='function')g={onAfter:g};if(e=='max')e=9e9;g=$.extend({},h.defaults,g);f=f||g.duration;g.queue=g.queue&&g.axis.length>1;if(g.queue)f/=2;g.offset=both(g.offset);g.over=both(g.over);return this._scrollable().each(function(){if(e==null)return;var d=this,$elem=$(d),targ=e,toff,attr={},win=$elem.is('html,body');switch(typeof targ){case'number':case'string':if(/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)){targ=both(targ);break}targ=$(targ,this);if(!targ.length)return;case'object':if(targ.is||targ.style)toff=(targ=$(targ)).offset()}$.each(g.axis.split(''),function(i,a){var b=a=='x'?'Left':'Top',pos=b.toLowerCase(),key='scroll'+b,old=d[key],max=h.max(d,a);if(toff){attr[key]=toff[pos]+(win?0:old-$elem.offset()[pos]);if(g.margin){attr[key]-=parseInt(targ.css('margin'+b))||0;attr[key]-=parseInt(targ.css('border'+b+'Width'))||0}attr[key]+=g.offset[pos]||0;if(g.over[pos])attr[key]+=targ[a=='x'?'width':'height']()*g.over[pos]}else{var c=targ[pos];attr[key]=c.slice&&c.slice(-1)=='%'?parseFloat(c)/100*max:c}if(g.limit&&/^\d+$/.test(attr[key]))attr[key]=attr[key]<=0?0:Math.min(attr[key],max);if(!i&&g.queue){if(old!=attr[key])animate(g.onAfterFirst);delete attr[key]}});animate(g.onAfter);function animate(a){$elem.animate(attr,f,g.easing,a&&function(){a.call(this,e,g)})}}).end()};h.max=function(a,b){var c=b=='x'?'Width':'Height',scroll='scroll'+c;if(!$(a).is('html,body'))return a[scroll]-$(a)[c.toLowerCase()]();var d='client'+c,html=a.ownerDocument.documentElement,body=a.ownerDocument.body;return Math.max(html[scroll],body[scroll])-Math.min(html[d],body[d])};function both(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);
