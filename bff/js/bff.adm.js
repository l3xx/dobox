
//bff admin utilites
bff.extend(bff, {

confirm: function(q, o)
{
    switch(q) {
        case 'sure': return confirm('Вы уверены?'); break;
    }
    return confirm(q);
},

userinfo: function(userID)
{
    if(userID) {
        $.fancybox('', {ajax:true, href:'index.php?s=users&ev=user_ajax&action=user-info&rec='+userID});
    }
    return false;
},

error: function(msg,o)
{                   
    o = o || {};
    $('#warning .warns').html('<li>'+msg+'</li>'); 
    var block = $('#warning'), cont = block.find('.warnblock-content');
    if(o.success) { cont.addClass('success').removeClass('error'); }
    else { cont.addClass('error').removeClass('success'); }
    if(!block.is(':visible'))
        block.fadeIn();  
         
    var errClicked = false;
    block.click(function(){ 
        if(!errClicked){ errClicked = true; $(this).unbind(); } 
    }); 
    setTimeout(function(){ 
        if(!errClicked) block.fadeOut(); 
    }, 5000); 
       
    return false;
},
    
busylayer: function( toggle, callback )
{
    callback = callback || new Function();
    toggle = toggle || false; 
    
    var layer = $('#busyLayer'), doc = document;
    if(!layer || !layer.length) //if not exists
    {
        var body = doc.getElementsByTagName('body')[0];           
    
        layer = doc.createElement('div');
        layer.id = 'busyLayer';
        layer.className = 'busyLayer';
        layer.style.display = 'none';
        layer.style.textAlign = 'center';
        //layer.innerHTML = '<img src="/img/progress-large.gif" />';       
        body.appendChild(layer); 
        layer = $(layer);
        
        layer.css({'filter':'Alpha(Opacity=65)', 'opacity':'0.65'});

//        $(doc).keydown(function(e) {
//            if (e.keyCode == 27 && layer.is(':visible')) { 
//                nothing(e);
//                layer.fadeOut(500); 
//            }
//        }); 
    }    

    if(layer.is(':visible')) {
        if(toggle){
            layer.fadeOut(500, callback);
        }
        return false;
    }
    
    var height = $(doc).height();
    layer.css({'height': height+'px', 'paddingTop': (height/2)+'px'}).fadeIn(500, callback);
    return false;
},

ajaxToggleWorking: false,
ajaxToggle: function(nRecordID, sURL, _options)
{
    if(bff.ajaxToggleWorking)
        return;
    
    bff.ajaxToggleWorking = true;
    
    var options = { };
        options = $.extend({
            link: '#lnk_',
            block: 'block', unblock: 'unblock',
            progress: false,
            toggled: false //return toggled records ids 
        }, _options || {});

    if(sURL == '' || sURL == undefined) {
        $.assert(false, 'ajaxToggle: empty URL');
        return;
    }
    
    if(nRecordID<=0) {
        $.assert(false, 'ajaxToggle: empty record_id');
        return;
    }
    
    if(options.progress)
        $(options.progress).show();
    
    var eLink = null;
    bff.ajax(sURL, {'rec': nRecordID, 'toggled': options.toggled }, 
        function(data) {
            if(data) {
                if(options.toggled)
                {
                   data.toggled.each( function(t){
                        eLink = !$(options.link+t).length || $(options.link); 
                        if( eLink!=undefined) {
                            eLink.removeClass( (result.status ? options.block : options.unblock) );
                            eLink.addClass( (result.status ? options.unblock : options.block) );
                        }   
                   });
                }
                else {
                    eLink = ( typeof(options.link) == 'object' ? $(options.link) : $(options.link+nRecordID) );
                    if( eLink!=undefined) {
                        var has = eLink.hasClass( options.unblock);
                        eLink.removeClass( (has? options.unblock : options.block) );
                        eLink.addClass( (has? options.block : options.unblock) ); 
                    }
                }
            } 
             
            if(options.progress)
                $(options.progress).hide();
            
            bff.ajaxToggleWorking = false; 
        }
    );
},

ajaxDeleteWorking: false,
ajaxDelete: function(sQuestion, nRecordID, sURL, link, options)
{
    if(bff.ajaxDeleteWorking)
        return;

    if(sQuestion!==false)
        if(!bff.confirm(sQuestion))
            return;
    
    bff.ajaxDeleteWorking = true;

    var o = $.extend({ 
            paramKey: 'rec',
            progress: false,
            remove: true,
            repaint: true
        }, options || {});
    
    if(sURL == '' || sURL == undefined) {
        $.assert(false, 'ajaxDelete: empty URL');
        return;
    }

    if(nRecordID<=0)
        $.assert(false, 'ajaxDelete: empty recordID');
    
    var params = {}; params[o.paramKey] = nRecordID;
    bff.ajax(sURL, params, function(data) {
        if(data) {
            if(o.onComplete)
                o.onComplete(data, o);
            
            if(o.remove && link) 
            {    
                $link  = $(link);
                var $table = $link.parents('table.admtbl');
                if($table) {
                    $link.parents('tr:first').remove();
                    //repaint rows
                    if(o.repaint) {
                        $table.find('tr[class^=row]').each(function (key, value) {
                            $(value).attr('class', 'row'+(key%2))
                        });
                    }
                }
            }
        }        
        bff.ajaxDeleteWorking = false;
   }, o.progress);
},   

initTableDnD: function(list, url, progressSelector, callback, addParams, rotateClass)
{
    var aOldOrder = [];
    var aNewOrder = [];
    var nOrderChanged = 0;  
    
    callback    = callback || $.noop;
    rotateClass = rotateClass || 'rotate';
    addParams   = addParams || {};
    
    $(list).tableDnD({
        onDragClass: rotateClass,
        onDragStart: function(table, row)
        {
            var rows = table.tBodies[0].rows;
            for(var i=0; i<rows.length; i++) {
                aOldOrder[i] = rows[i].id;
            }
        },
        onDrop: function(table, dragged, target, position)
        {
            if(dragged && target && jQuery.inArray(position, ['after', 'before']) != -1) {
                var rows = table.tBodies[0].rows;
                
                for(var i=0; i<rows.length; i++) {
                    aNewOrder[i] = rows[i].id;
                }
                
                for(var i=0; i<aOldOrder.length; i++) {
                    if(aOldOrder[i] != aNewOrder[i]){
                        nOrderChanged = 1;
                        break;
                    }
                }
                
                if(nOrderChanged == 1) {    
                    bff.ajax(url, 
                       $.extend({ dragged : dragged.id, target : target.id, position : position }, addParams), 
                       callback, progressSelector);
                }
            }
            
            aOldOrder = [];
            aNewOrder = [];
            bOrderChanged = 0;
        }
    });
},

textLimit: function(ta, count, counter) 
{
  var text = document.getElementById(ta);
  if(text.value.length > count) {
    text.value = text.value.substring(0,count);
  }
  if(counter) { // id of counter is defined
    document.getElementById(counter).value = text.value.length;
  }
},

textInsert: function(fieldSelector, text) 
{
    var field = $(fieldSelector);
    if(!field.length) return;
    field = field.get(0);

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
        if(!pos){
            var pos = (strFirst.length + text.length);
            field.selectionStart = field.selectionEnd = pos;
        } else {
            field.selectionStart = field.selectionEnd = (strFirst.length + pos);
        }
    } 
    else {
        field.value += text;
    } 
},

formSelects: 
{
    MoveAll: function(source_id, destination_id)
    {
        var source      = document.getElementById(source_id);
        var destination = document.getElementById(destination_id);
        
        for(var i=0; i<source.options.length; i++)
        {
            var opt = new Option(source.options[i].text, source.options[i].value, false);
            opt.style.color = source.options[i].style.color;
            destination.options.add(opt);
        }
        source.options.length = 0;
    },

    MoveSelect: function (source_id, destination_id)
    {
        var source      = document.getElementById(source_id);
        var destination = document.getElementById(destination_id);
        
        for(var i=source.options.length-1; i>=0; i--)
        {
            if(source.options[i].selected==true)
            {
                var opt = new Option(source.options[i].text, source.options[i].value, false);
                opt.style.color = source.options[i].style.color;
                destination.options.add(opt);
                source.options[i] = null;
            }
        }  
    },

    SelectAll: function(sel_id)
    {
        var sel = document.getElementById(sel_id);
        for(i=0; i<sel.options.length; i++)
        {
            sel.options[i].selected = true;
        }     
    },
    
    hasOptions: function(sel_id)
    {
        return document.getElementById(sel_id).options.length;
    }
},    

formChecker: function(form,options,onLoad){
    $(document).ready(function(){
        this.initialize(form,options);  
        if(onLoad) onLoad();
    }.bind(this));
},

pgn: function(form,options){
    $(document).ready(function(){
        this.initialize(form,options);     
    }.bind(this));
}

});

/*@cc_on bff.ie=true;@*/ 

bff.formChecker.prototype = 
{
    initialize: function(form, options)
    {
        this.form = $(form);
        this.submiting = false; 
        $.assert(this.form, 'formChecker: unable to find form');     
        this.options  = { 
            scroll: false, ajax: false, progress: false,
            errorMessage: true, errorMessageBlock: '#warning', errorMessageText: '#warning .warns',  
            password: '#password', passwordNotEqLogin: true, passwordMinLength: 3, 
            login: '#login', loginMinLength: 4};
        
        if(options) { for (var o in options) { 
            this.options[o] = options[o]; } }
        
        //init error message
        if(this.options.errorMessage){
            this.errorMessageBlock = $(this.options.errorMessageBlock);
            this.errorMessageText  = $(this.options.errorMessageText);
        }
        
        this.initInputs();   
         
        //var formOrigSubmit = this.form.get(0).submit;
        //this.form.get(0).submit = function(){ return (onSubmit()? false : formOrigSubmit()); };
        //console.log( this.form.get(0) ); 
        
        this.check();
    },
    
    initInputs: function()
    {
        var t = this;
        t.required_fields = t.form.find('.required');
        t.required_fields.bind('blur keyup change', $.debounce(function(){ return t.check(); }, 400));
        t.submit_btn = t.form.find('input:submit');
        t.submit_btn_text = t.submit_btn.val();
        t.form.submit(function(){ 
            return t.onSubmit(); 
        });
    },

    onSubmit: function()
    {
        var t = this;
        var res = t.check(); 
        if(this.submitCheck)
            res = this.submitCheck();
            
        if(res)
        {   
            t.submiting = true;
            if(t.options.ajax != false) {
                t.disableSubmit();
                bff.ajax(t.form.attr('action'), t.form.serializeArray(), function(data){
                    t.enableSubmit();
                    if(data){ 
                        t.form[0].reset(); 
                        if(typeof t.options.ajax === 'function') {
                            t.options.ajax(data);
                        }
                    }
                    t.submiting = false;
                    t.check();
                }, t.options.progress);
                return false;
            }
           t.disableSubmit();  
        }        
        return res; 
    },
    
    enableSubmit: function(){
        this.submit_btn.removeAttr('disabled').val( this.submit_btn_text );
    },
    disableSubmit: function(){
        this.submit_btn.attr('disabled', 'disabled').val('Подождите...');
    },

    showMessage: function( text ){
        if(this.options.errorMessage) {
            this.errorMessageText.html('<li>'+text+'</li>'); 
            if(!this.errorMessageBlock.is(':visible'))     
                this.errorMessageBlock.fadeIn();
            
            this.errorMessageShowed = true; 
        }
    },  
    
    check: function(focus, reinit){ 
        this.errorMessageShowed = false;   
        var ok_fields = 0;
        var me = this;
        if(reinit === true) {
            this.initInputs();
        }

        this.required_fields.each(function() {
            var obj = $(this), fld = obj.find('input:visible, textarea:visible, select:visible'), result = false;
            
            if(!fld.length) {
                result = 1;
            }
            else {
                if(obj.is('.check-email')){
                    result = me.checkEmail(fld);
                }
                else if(obj.is('.check-password')){
                    result = me.checkPassword(fld);
                }
                else if(obj.is('.check-login')){
                    result = me.checkLogin(fld);
                }
                else if(obj.is('.check-select')){
                    result = me.checkSelect(fld);
                }            
                else{
                    result = me.checkEmpty(fld);
                }
            }

            if(result)
                obj.removeClass('clr-error');
            else {
                obj.addClass('clr-error');
                if(focus) fld.focus();
            }

            if(!result) return false;
            ok_fields += Number(result);  
        });

        var is_ok = (ok_fields == this.required_fields.length);
        if(is_ok && this.additionalCheck) {
            is_ok = this.additionalCheck();
        }
        
        //if(this.options.errorMessage && !this.errorMessageShowed)
        //    this.errorMessageBlock.fadeOut(); 
            
        //if(this.submiting)
        //    this.submit_btn.attr('disabled', !is_ok);

        if(this.afterCheck)
            this.afterCheck();
            
        return is_ok;
    },

    checkSelect: function(fld){                                 
        return parseInt(fld.val())!=0;
    },
    
    checkEmpty: function(fld){  
        return Boolean($.trim( fld.val() ));
    },

    checkLogin: function(fld){
        if(!this.checkEmpty(fld)) {
            return false;
        }

        var login = fld.val();
        if(login.length < this.options.loginMinLength) {
            this.showMessage('<b>логин</b> слишком короткий');  
            return false;
        }     
        
        var re = /^[a-zA-Z0-9_]*$/i;
        if(!re.test(login)) {
            this.showMessage('<b>логин</b> должен содержать только латиницу и цифры');  
            return false;
        }
        return true;
    },
    
    checkPassword: function(fld){
        if(!this.checkEmpty(fld)) {
            return false;
        }

        var pass = fld.val();
                
        if(fld.hasClass('check-password2'))
        {
            if(pass != $(this.options.password).val()) {
                this.showMessage('ошибка <b>подтверждения пароля</b>');  
                return false;
            }
            return true;
        }
        if(pass.length < this.options.passwordMinLength) {
            this.showMessage('<b>пароль</b> слишком короткий');  
            return false;
        }                
        if(this.options.passwordNotEqLogin && this.options.hasOwnProperty('login') && 
           (pass == this.options.login || pass == $(this.options.login).val() ) ) {
            this.showMessage('<b>логин</b> и <b>пароль</b> не должны совпадать');  
            return false;
        }
        return true;
    },
    
    checkEmail: function(fld){
        var re = /^\s*[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\s*$/i;
        if(this.checkEmpty(fld)) {
            var is_correct = re.test(fld.val());
            if(is_correct)
                fld.removeClass('clr-error');
            else
                fld.addClass('clr-error');

            return is_correct;
        }
        return false;
    }
};

bff.pgn.prototype = 
{
    initialize: function(form, options)
    {
        this.form = $(form).get(0);
        this.process = false;    
        this.options  = { progress: false, ajax: false };
        
        if(options) { for (var o in options) { 
            this.options[o] = options[o]; } }
        
        this.options.targetList = $(options.targetList);
        this.options.targetPagenation = $(options.targetPagenation);
    },
    prev: function(offset)
    {
        if(this.process) return;
        this.form['offset'].value = offset;
        this.update();
    },
    next: function(offset)
    {
        if(this.process) return;
        this.form['offset'].value = offset;  
        this.update();
    },
    update: function()
    {
        if(!this.options.ajax) {
            this.form.submit();
            return;
        }
        
        if(this.process)
            return;                            
            
        this.process = true;
        
        this.options.targetList.animate({'opacity': 0.65}, 300);
        bff.ajax($(this.form).attr('action'), $(this.form).serializeArray(), function(data){
            if(data) {
                this.options.targetList.animate({'opacity': 1}, 100).html(data.list);
                this.options.targetPagenation.html(data.pgn); 
            }    
            this.process = false;
        }.bind(this), this.options.progress);
    }
};
