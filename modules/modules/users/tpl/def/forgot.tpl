<div class="greyForm" id="user-forgot-block">
    <div class="top"><span class="left">&nbsp;</span><span class="bord">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
    <div class="center">
        <div class="leftSide">
            <div class="caption">восстановление пароля<div id="uf-progress-forgot" class="progress" style="margin-left:7px; display:none;"></div></div>
            <form action="" method="post" id="uf-form-forgot">
                <div class="error uf-error-forgot hidden"></div>
                <input type="hidden" name="act" value="forgot" />            
                <div class="padTop">Введите e-mail, который указывали при регистрации:</div>
                <div class="padTop"><input type="text" name="email" class="inputText" /></div>
                <div class="padTop2">
                    <div class="button left">
                      <span class="left">&nbsp;</span>
                      <input type="submit" value="отправить пароль" />
                    </div>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
        <div class="rightSide">
            <div class="caption">регистрация<div id="uf-progress-reg" class="progress" style="margin-left:7px; display:none;"></div></div>
            <form action="" method="post" id="uf-form-reg">
                <div class="error uf-error-reg hidden"></div>
                <input type="hidden" name="act" value="reg" />
                <div class="padTop">Введите ваш e-mail:</div>
                <div class="padTop"><input type="text" name="email" class="inputText" /></div>
                <div class="padTop">Введите пароль:</div>
                <div class="padTop"><input type="password" name="pass" class="inputText" /></div>
                <div class="padTop2">
                    <div class="button left">
                        <span class="left">&nbsp;</span>
                        <input type="submit" value="зарегистрироваться" />
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
var wsUserForgot = (function(){
    var process = false, $block, $form, s = [];
    function init()
    {
        $block = $('#user-forgot-block');
        $('#uf-form-forgot',  $block).submit(function(){ return check(this, 'forgot'); });
        $('#uf-form-reg',  $block).submit(function(){ return check(this, 'reg'); });   
    }

    function check(form, act)
    {
        switch(act)
        {
            case 'forgot': {
                var wrongEmail = false;                      
                var val = $.trim(form.elements['email'].value);      
                if(val=='' || ( wrongEmail = !bff.isEmail( val ) ) ) {
                    if(wrongEmail) showErr('Укажите корректный e-mail', act);
                    form.elements['email'].focus(); break;
                }
                if(s.length>0) {
                    for(var i=0; i<s.length; i++) {
                        if(s[i]===val){ showSended(); return false; }
                    }
                }
                
                if(process) return false; process = true;
                bff.ajax( document.location, $(form).serialize(),
                function(data, errors) {
                    if(data) {
                        showSended();
                        s.push( val );
                    } else {
                        showErr( errors, act );
                    }
                    process = false;
                }, $('#uf-progress-'+act, $block) ); 
                                
            } break;
            case 'reg': {
                var wrongEmail = false;                      
                var val = $.trim(form.elements['email'].value);      
                if(val=='' || ( wrongEmail = !bff.isEmail( val ) ) ) {
                    if(wrongEmail) showErr('Укажите корректный e-mail', act);
                    form.elements['email'].focus(); break;
                }
                
                var val = $.trim(form.elements['pass'].value);
                if(val=='') { form.elements['pass'].focus(); break; }
                if(val.length<3) { showErr('Пароль должен содержать не менее 3-х символов', act); form.elements['pass'].focus(); break; }
                
                if(process) return false; process = true;
                bff.ajax( document.location, $(form).serialize(),
                function(data, errors) {          
                    if(data) {
                        showErr( 'Регистрация прошла успешно, перейдите по ссылке отправленной Вам на почту.', act, true );
                    } else {
                        showErr( errors, act );
                    }
                    process = false;
                }, $('#uf-progress-'+act, $block) ); 

            } break;
        }
        return false;
    }
    
    function showSended() {
        showErr( 'Информация для восстановления успешно отправлена', 'forgot', true );
    }
    
    function showErr(msg, act, success)
    {      
        $err = $('div.uf-error-'+act, $block);
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

$(function(){
     wsUserForgot.init();
});

{/literal}
</script>