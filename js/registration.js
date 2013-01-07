
/* :::::::::: registrationClass :::::::::::::: */
var helper = null;
function clientRegistrationHelper()
{
    this.checking = 0; 
    this.form = null;
    this.inputClassError  = 'text-field-error';
    this.inputClassOk     = 'text-field-success';
    this.inputClassNormal = 'text-field';
    this.init = function()
    {
        this.message  = $('#regError');
        this.login    = $('#login');
        this.loginExists = false;    
        this.progress = $('#progress-reg');
        var _this = this;
        var $url  = $('#login_url');
        $('#login').bind('keyup blur', $.debounce( function()
        {
            if(_this.checkLogin(_this.form.elements['login'], 'логин', false)) {
            _this.progress.show();
            $.ajax({url: '/client/ajax', data: {login: _this.login.val(), act: 'login-check'}, type: 'POST',
                success: function(data){ data = parseInt(data);
                    if(data == 2) { 
                        _this.checking = 0; _this.loginExists = true;
                        _this.setInputError(_this.login, _this.inputClassError);
                        $url.addClass('exists'); $('#login_exists').show();
                    } else{ $url.removeClass('exists'); $('#login_exists').hide();
                        if(data == 0) _this.setInputError(_this.login, _this.inputClassOk);
                        _this.loginExists = false;     
                    } 
                    _this.progress.hide(); 
                }});
            }
        }, 1000)).bind('keyup blur',function(){
            var login = _this.login.val(); 
            //update counter      
            $('#login_counter').html( _this.declension((15 - login.length), ['символ','символа','символов']));
            if (login.length) $url.html(login.toLowerCase());
                else $url.html('mycompany');                
        });
    }
    
    this.msg = function( text, type )
    {
        switch(type) {
            case 'pass-error': $('#pass-mess').html('<span class="error">'+text+'</span>'); break;
            case 'pass-success': $('#pass-mess').html('<span>'+text+'</span>'); break;
        } 
       //this.message.html(text);
    } 
    
    this.setInputError = function( input, classname )
    {                          
        $(input).parent().
            removeClass(this.inputClassError+' '+this.inputClassOk).
            addClass(classname);            
    }
    
    this.check = function( )
    {
        if(this.checking)
            return false;
    
        this.checking = 1;
        
        do
        {
            if(!this.checkLogin( this.form.elements['login'], 'логин', true)) break; 
            if(!this.checkEmail( this.form.elements['email'], 'email' )) break;
            if(!this.checkPassword1( this.form.elements['password'], 'пароль' )) break; 
            if(!this.checkPassword2( this.form.elements['password2'], 'повтор пароля' )) break; 
            if(!this.checkCaptcha( this.form.elements['captcha'] )) break;
            
            this.msg('');  
            this.checking = 2;
            $(this.form).find('.submit').attr('disabled', 'disabled').val('Подождите...');
            this.form.submit();
        
        }while(false);
           
        this.checking = 0;
        
        return false;
    }

    this.checkLogin = function( input, title, highlight )
    {    
         if(highlight && this.loginExists) {
             this.setInputError(input, this.inputClassError);
             input.focus();
             return false;
         }
            
         do
         {
            if(!this.checkNotEmpty(input, title, highlight))
                return false;
                
            if(input.value.length<3)
            {
                this.msg('<b>'+title+'</b> должен содержать не менее 3-х символов'); 
                break;
            }
            
            var regx = new RegExp (/^[a-zA-Z0-9_]+$/);
            if( regx.test(input.value) == false )
            {
                this.msg('<b>'+title+'</b> должен содержать только латиницу и цифры');  
                break;
            }                   
            
            if(highlight) this.setInputError(input, this.inputClassOk);      
            return true;
         }while(false);
         
         if(highlight) {
             this.setInputError(input, this.inputClassError);
             input.focus();
         }
                  
         return false;       
    }

    this.checkEmail = function( input, title )
    {
         do
         {
            if(!this.checkNotEmpty(input, title))
                return false;
                
            if(!bff.isEmail(input.value))
            {
                this.msg('<b>'+title+'</b> указан некорректно');  
                break;
            }                      
            
            this.setInputError(input, this.inputClassOk);                  
            return true;
         }while(false);
         
         this.setInputError(input, this.inputClassError);
         input.focus();
         return false;       
    }
    
    this.checkPassword1 = function( input, title )
    {
         do
         {
            if(!this.checkNotEmpty(input, title))
                return false;
                
            if(input.value.length<3)
            {
                this.msg('<b>'+title+'</b> должен содержать не менее 3-х символов', 'pass-error');  
                break;
            }
            if(input.value == this.form.elements['login'].value)
            {
                this.msg('<b>логин</b> и <b>'+title+'</b> не должны совпадать', 'pass-error');  
                break;
            } 
  
            this.setInputError(input, this.inputClassOk);
            return true;
         }while(false);

         this.setInputError(input, this.inputClassError);
         
         input.focus();
         return false;       
    }

    this.checkPassword2 = function( input, title )
    {
         do
         {
            if(!this.checkNotEmpty(input, title, false))
                return false;
                
            if(input.value != this.form.elements['password'].value)
            {
                this.msg('ошибка подтверждения пароля', 'pass-error');  
                break;
            }
            this.msg('пароли совпадают', 'pass-success');
            this.setInputError(input, this.inputClassOk);
            return true;
         }while(false);
         
         this.msg('пароли не совпадают', 'pass-error');
         this.setInputError(input, this.inputClassError);
         input.focus();
         return false;       
    }
    
    this.onPassword2Change = function()
    {
        if(this.checkPassword1( this.form.elements['password'], 'пароль'))
            this.checkPassword2( this.form.elements['password2'], 'повтор пароля');
    }
    
    this.checkCaptcha = function( input )
    {
         do
         {
            if(input.value.length == 0)
            {
                this.msg('не указан <b>код с картинки</b>');  
                break;
            }
            this.setInputError(input, this.inputClassOk);                     
            return true;
         }while(false);
         
         this.setInputError(input, this.inputClassError);
         input.focus();                                  
         return false;       
    }
    
    this.refreshCaptcha = function( url )
    {
        $('#captcha_img').attr('src', url+'?r='+Math.random());
    }
    
    this.checkNotEmpty = function( input, title, highlight )
    {
        if(input.value.length == 0) {
            this.msg('не заполнено поле <b>'+title+'</b>');
            if(highlight!==false) {
                this.setInputError(input, this.inputClassError);  
                input.focus();
            }
            return false;
        }                     
        return true;    
    }

    this.declension = function(count, forms)
    {
        n = Math.abs(count) % 100;
        n1 = n % 10;  
        if (n > 10 && n < 20) {
            return count+' '+forms[2];
        }
        if (n1 > 1 && n1 < 5) {
            return count+' '+forms[1];  
        }
        if (n1 == 1) {
            return count+' '+forms[0];  
        }
        return count+' '+forms[2];
    }
        
}