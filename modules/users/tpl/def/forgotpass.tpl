
{if $aErrors}
    {foreach from=$aErrors item=v}
        {$v.msg}
    {/foreach}
{else}

<div class="greyForm" id="user-forgot-block">
    <div class="top"><span class="left">&nbsp;</span><span class="bord">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
    <div class="center">
        <div class="leftSide">
            <div class="caption">восстановление пароля<div id="uf-progress-forgot" class="progress" style="margin-left:7px; display:none;"></div></div>
            <form action="" method="post" id="uf-form-forgot">
                <div class="error uf-error-forgot hidden"></div>
                <input type="hidden" name="act" value="changepass" /> 
                <input type="hidden" name="c" value="{$forgotData.c}" />
                <input type="hidden" name="uid" value="{$forgotData.user_id}" />
                <div class="padTop">Укажите новый пароль доступа к аккаунту <b>{$forgotData.email}</b></div>
                <div class="padTop"><input type="text" name="pass" class="inputText" /></div>
                <div class="padTop2">
                    <div class="button left">
                      <span class="left">&nbsp;</span>
                      <input type="submit" value="изменить пароль" />
                    </div>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
        <div class="rightSide"></div>
        <div class="clear"></div>
    </div>
    <div class="bottom"><span class="left">&nbsp;</span><span class="bord">&nbsp;</span><span class="right">&nbsp;</span><div class="clear"></div></div>
</div>

<script type="text/javascript"> 
{literal}
var wsUserForgot = (function(){
    var process = false, $block, $form, changed = false, errTimeout = null;
    function init()
    {
        $block = $('#user-forgot-block');
        $('#uf-form-forgot',  $block).submit(function(){ return check(this, 'forgot'); });
    }

    function check(form, act)
    {
        switch(act)
        {
            case 'forgot': {
                var pass = form.elements['pass'];
                var val = $.trim(pass.value);      
                if(val=='') {
                   pass.focus(); break;
                } else if(val.length <3) {
                   showErr('Пароль слишком короткий', act); 
                   pass.focus();
                   break;
                }
                if(changed) {
                    showChanged();
                    break;
                }
                
                if(process) return false; process = true;
                bff.ajax( document.location, $(form).serialize(),
                function(data, errors) {
                    if(data) {
                        showChanged();
                        changed = true;
                    } else {
                        showErr( errors, act );
                    }
                    process = false;
                }, $('#uf-progress-'+act, $block) ); 
                                
            } break; 
        }
        return false;
    }
    
    function showChanged() {
        showErr( 'Пароль успешно изменен', 'forgot', true );
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
                clearTimeout( errTimeout );
                errTimeout = setTimeout(function(){
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

{/if}