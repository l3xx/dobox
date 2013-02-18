
<div class="greyForm" id="user-profile-block">
    <div class="top"><span class="left">&nbsp;</span><span class="bord">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
    <div class="center">
        <div class="leftSide">
            <span class="caption">информация и контакты<span id="up-progress-info" class="progress" style="margin-left:7px; display:none;"></span></span>
            <form action="" method="post" id="up-form-info">
                <div class="error up-error-info hidden"></div>
                <input type="hidden" name="act" value="info" />
                <div class="padTop">Фамилия и имя:</div>
                <div class="padTop"><input type="text" value="{$aData.name}" name="name" class="inputText"/></div>
                <div class="padTop">E-mail для связи:</div>
                <div class="padTop"><input type="text" value="{$aData.email2}" name="email" class="inputText"/></div>
                <div class="padTop">Телефон:</div>
                <div class="padTop"><input type="text" value="{$aData.phone}" name="phone" class="inputText"/></div>
                <div class="padTop">Другие контакты:</div>
                <div id="up-info-contacts"> 
                    {assign var="contacts_i" value=1}
                    {foreach from=$aData.contacts item=c}           
                        <div class="padTop">
                            <select class="inputText left" style="width:106px; :margin-top:3px;" name="contacts[{$contacts_i}][type]"><option value="1"{if $c.type==1} selected="selected"{/if}>Skype</option><option value="2"{if $c.type==2} selected="selected"{/if}>ICQ</option><option value="3"{if $c.type==3} selected="selected"{/if}>Телефон</option></select>
                            <input type="text" class="inputText left contact-data" name="contacts[{$contacts_i}][data]" value="{$c.data|escape:'html'}" style="width:160px;" />
                            <a href="#" class="del2 up-info-contacts-del"></a>
                            <div class="clear"></div>
                        </div> 
                        {assign var="contacts_i" value=$contacts_i+1}
                    {/foreach}
                </div>
                <a href="#" class="add" id="up-info-contacts-add"></a>
                <div class="padTop2">
                    <div class="button left">
                        <span class="left">&nbsp;</span>
                        <input type="submit" value="сохранить изменения" />
                    </div>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
        <div class="rightSide">
            <span class="caption">Смена пароля<span id="up-progress-pass" class="progress" style="margin-left:7px; display:none;"></span></span>
            <form action="" method="post" id="up-form-pass">
                <div class="error up-error-pass hidden"></div>
                <input type="hidden" name="act" value="pass" />
                <div class="padTop">Введите текущий пароль:</div>
                <div class="padTop"><input type="password" name="old" class="inputText"/></div>
                <div class="padTop">Введите новый пароль:</div>
                <div class="padTop"><input type="password" name="new" class="inputText"/></div>
                <div class="padTop2">
                    <div class="button left">
                        <span class="left">&nbsp;</span>
                        <input type="submit" value="изменить пароль" />
                    </div>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
        <div class="rightSide bordTop">
            <span class="caption">Основной e-mail профиля<span id="up-progress-email" class="progress" style="margin-left:7px; display:none;"></span></span>
            <div class="error up-error-email hidden"></div>
            <form action="" method="post" id="up-form-email">
            <input type="hidden" name="act" value="email" />
            <div class="padTop"><input type="text" value="{$aData.email}" name="email" class="inputText"/></div>
            <div id="up-email-curpass" class="hidden">
                <div class="padTop">Введите текущий пароль:</div>
                <div class="padTop"><input type="password" name="pass" autocomplete="off" class="inputText"/></div>            
            </div>
            <div class="padTop2">
                <div class="button left">
                    <span class="left">&nbsp;</span>
                    <input type="submit" value="изменить e-mail" />
                </div>
                <div class="clear"></div>
            </div>
            </form>
        </div>
        <div class="clear"></div>
    </div>
    <div class="bottom"><span class="left">&nbsp;</span><span class="bord">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
</div>

<script type="text/javascript"> 
{literal}
var wsUserProfile = (function(){
    var process = false, $block, email_cur = '{/literal}{$aData.email}{literal}';
    function init()
    {               
        $block = $('#user-profile-block');
        
        $('#up-form-info',  $block).submit(function(){ return check(this, 'info'); });
        $('#up-form-pass',  $block).submit(function(){ return check(this, 'pass'); });
        $('#up-form-email', $block).submit(function(){ return check(this, 'email'); });
    }
    
    function check(form, act)
    {           
        switch(act)
        {
            case 'info': {  
                if(process) return false; process = true; 
                bff.ajax( document.location, $(form).serialize(),
                function(data, errors) {
                    if(data) {
                        showErr( 'Информация сохранена', act, true );
                    } else {
                        showErr( errors, act );
                    }
                    process = false;
                }, $('#up-progress-'+act, $block) ); 
            } break;
            case 'pass': {
                if($.trim(form.elements['old'].value)=='') {
                    form.elements['old'].focus(); break;
                }
                if($.trim(form.elements['new'].value)=='') {
                    form.elements['new'].focus(); break;
                }
                if(form.elements['new'].value.length<3) {
                    showErr('Пароль должен содержать не менее 3-х символов', act); break;
                }      
                
                if(process) return false; process = true;                      
                bff.ajax( document.location, $(form).serialize(),
                function(data, errors) {
                    if(data) {
                        showErr( 'Пароль успешно изменён', act, true );
                    } else {
                        showErr( errors, act );
                    }
                    process = false;
                }, $('#up-progress-'+act, $block) ); 

            } break;
            case 'email': {
                var wrongEmail = false;                      
                var email_new = $.trim(form.elements['email'].value);
                if(email_new == email_cur) return false;
                if(email_new=='' || ( wrongEmail = !bff.isEmail( email_new ) ) ) {
                    if(wrongEmail) showErr('Укажите корректный e-mail', act);
                    form.elements['email'].focus(); break;
                }
                
                $pass = $('#up-email-curpass', $block);
                if($pass.is(':hidden')) {
                    $pass.slideDown('fast', function(){
                        form.elements['pass'].focus();
                    });
                    return false;
                } else {
                    var val = $.trim(form.elements['pass'].value);
                    if(val=='') { form.elements['pass'].focus(); break; } 
                }
                
                if(process) return false; process = true;  
                bff.ajax( document.location, $(form).serialize(),
                function(data, errors) {
                    if(data) {
                        showErr( 'E-mail успешно изменён', act, true );
                        email_cur = email_new;
                    } else {
                        showErr( errors, act );
                    }
                    process = false;
                }, $('#up-progress-'+act, $block) ); 
                                
            } break;
        }
        return false;
    }
    
    function showErr(msg, act, success)
    {      
        $err = $('div.up-error-'+act, $block);
        if(msg) {
            if($.isArray(msg)) {
                msg = msg.join('<br/>');
            }
            if(success) {
                $err.removeClass('error').addClass('success').html(msg).show();
                setTimeout(function(){
                    $err.slideUp(500, function(){ $err.removeClass('success').addClass('error'); });    
                }, 3000);
            } else {
                $err.html(msg).show();
            }
        } else {
            $err.hide().html('');
        }
    }
    
    return {init: init};
}());

var wsUserProfileContacts = (function(){
    var $block, iterator = 0;
    function init()
    {
        $block = $('#up-info-contacts');
        
        $('.padTop', $block).each(function(){
            initContact(++iterator, $(this), false);
        });
        
        $('#up-info-contacts-add').click(function(e){
            nothing(e);
            addContact(++iterator);
        });            
        
        $('.up-info-contacts-del', $block).live('click', function(e){
            nothing(e);
            $(this).parent().remove();
        });
    }
        
    function initContact(i, blockGroup, dyn)
    {                                                                   
        //
    }
    
    function addContact(i)
    {                  
        $block.append('<div class="padTop contact-'+i+'">\
                            <select class="inputText left" style="width:106px; :margin-top:3px;" name="contacts['+i+'][type]"><option value="1">Skype</option><option value="2">ICQ</option><option value="3">Телефон</option></select>\
                            <input type="text" class="inputText left contact-data" name="contacts['+i+'][data]" value="" style="width:160px;" />\
                            <a href="#" class="del2 up-info-contacts-del"></a>\
                            <div class="clear"></div>\
                        </div>');
        $('.contact-'+i+' > .contact-data', $block).focus(); 
    }
    
    return { init: init };
}());

$(function(){
     wsUserProfile.init();
     wsUserProfileContacts.init();
});

{/literal}
</script>