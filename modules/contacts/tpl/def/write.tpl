<div class="greyForm" id="contact-block">                                                                                                         
    <div class="top"><span class="left">&nbsp;</span><span class="bord">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
    <div class="center" style="padding:6px 15px; border:none; background:none;">
        <div class="caption">Связь с редактором<div id="contact-progress" class="progress" style="margin-left:7px; display:none;"></div></div>
        <form action="" method="post" id="contact-form">
            <div class="error contact-error hidden"></div>       
            {if !$userLogined}
            <div class="padTop">Введите ваш e-mail:</div>
            <div class="padTop"><input type="text" name="email" class="inputText2" maxlength="70" tabindex="1" /></div>
            {/if}
            <div class="padTop">Введите ваш телефон:</div>
            <div class="padTop"><input type="text" name="phone" class="inputText2" maxlength="70" tabindex="2" /></div>
            <div class="padTop">Текст сообщения:</div>
            <div class="padTop"><textarea name="message" class="inputText2" style="width:652px; height:80px;" tabindex="3"></textarea></div>
            {if !$userLogined}
            <div class="padTop">Введите результат с картинки:</div>
            <div class="padTop"><input type="text" name="captcha" class="inputText2" style="width:122px; float:left;" tabindex="4" /> <img src="/captcha2.php?bg=E5E3E2" style="cursor: pointer;" onclick="$(this).attr('src', '/captcha2.php?bg=E5E3E2&r='+Math.random(1));" class="captcha"/></div>            
            <div class="clear"></div>
            {/if}
            <div class="padTop2">
                <div class="button left">
                  <span class="left">&nbsp;</span>
                  <input type="submit" class="submit" value="ОТПРАВИТЬ СООБЩЕНИЕ" tabindex="5" />
                </div>
                <div class="clear"></div>
            </div>
        </form>
        <div class="clear"></div>
    </div>
    <div class="bottom"><span class="left">&nbsp;</span><span class="bord">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
</div>
                     
<script type="text/javascript"> 
{literal}
var appContact = (function(){
    var process = false, $block, $form, $btn, $progress, $err;
    function init()
    {
        $block    = $('#contact-block');
        $progress = $('#contact-progress', $block);
        $err      = $('div.contact-error', $block);  
        $form     = $('#contact-form', $block);
        $btn      = $('input.submit', $form);
        $form.submit(function(){
            if(process) return false;
            do
            {
                if(!app.m) 
                {
                    var wrongEmail = false;                      
                    var val = $.trim( this.email.value);      
                    if(val=='' || ( wrongEmail = !bff.isEmail( val ) ) ) {
                        if(wrongEmail) app.showError($err, 'Укажите корректный e-mail');
                         this.email.focus(); break;
                    }
                }

                var val = $.trim(  this.phone.value);
                if(val=='') {  
                    app.showError($err, 'Укажите ваш номер телефона');
                    this.phone.focus(); break; 
                }

                var val = $.trim( this.message.value);
                if(val=='') {  
                    app.showError($err, 'Укажите текст сообщения');
                    this.message.focus(); break; 
                }

                var cap = this.captcha;
                if(!app.m) {
                    if($.trim(cap.value) == '') {
                        app.showError($err, 'Укажите результат с картинки');
                        cap.focus(); break;
                    }
                }            

                process = true;
                $btn.val('ПОДОЖДИТЕ...');
                bff.ajax( document.location, $form.serialize(), function(data, errors) {
                    if(data) {
                        app.showError($err, 'Ваше сообщение было успешно отправлено', true);
                        $form.get(0).reset();
                        $(cap).next().triggerHandler('click');
                    } else {
                        app.showError( $err, errors );
                    }    
                    process = false;
                    $btn.val('ОТПРАВИТЬ СООБЩЕНИЕ');
                }, $progress); 
                
            } while(false);
            return false;        
        });
    }
    
    return {init: init};
}());

$(function(){
     appContact.init();
});

{/literal}
</script>