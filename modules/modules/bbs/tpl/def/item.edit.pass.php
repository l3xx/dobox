
<div align="center" id="itemEditPassBlock">
    <form action="/ajax/bbs?act=item-edit-pass">
        <input type="hidden" name="id" value="<?= $aData['item_id']; ?>" />
        <table style="margin:15px;">
            <tr>
                <td class="text" align="center" valign="top" style="padding-bottom:5px;">Укажите пароль, для доступа<br/> к редактированию объявления:</td>
            </tr>          
            <tr>
                <td style="padding-bottom:7px;">
                    <div align="center" class="error hidden"></div>
                    <input class="inputText" name="pass" style="text-align:center; width:170px;" type="password" />
                </td>
            </tr>         
            <tr>        
                <td>    
                    <div style="margin:10px 0 10px 35px;">
                        <div class="button left">
                            <span class="left">&nbsp;</span>
                            <span class="btCont"><input type="submit" style="width: 110px;" value="Продолжить" /></span>
                        </div>
                        <br/><br/>
                        <div class="progress" style="margin:8px 0 0 40px; display:none;"></div>
                        <div class="clear"></div>
                    </div>
                </td>
            </tr>                                                                                    
        </table>
    </form>
</div>

<script type="text/javascript">  
$(function()
{
    var $block = $('#itemEditPassBlock'), $form, $error, $progress, process = false;
    $progress = $('div.progress', $block);
    $error = $('div.error', $block);

    function showError($err, msg, success)
    {
        if(msg) {
            if($.isArray(msg)) {
                msg = msg.join('<br/>');
            }
            if(success) {
                $err.removeClass('error').addClass('success');
                setTimeout(function(){
                    $err.hide();
                }, 2000)
            }                         
            $err.html(msg).show();
        } else {
            $err.hide().html('').removeClass('success').addClass('error');
        }
    }
    
    ($form = $('form:first', $block)).submit(function(){
        if(process) return false; 
        if(this.pass.value == '') {
            this.pass.focus(); return false;
        }
        process = true;
        bff.ajax( $form.attr('action'), $form.serialize(),
        function(data, errors) {
            if(data && data.result) {
                document.location = '/items/edit?id=<?= $aData['item_id']; ?>';
            } else {
                showError($error, errors);
            }
            process = false;
        }, $progress );
        return false;
    });
});
</script>