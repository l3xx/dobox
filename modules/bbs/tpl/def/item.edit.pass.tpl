
<div align="center">
    <form method="post" action="" name="passForm">
        <table style="margin:15px;">
            <tr>
                <td class="text" align="center" valign="top" style="padding-bottom:5px;">Укажите пароль, для доступа<br/> к редактированию объявления:</td>
            </tr>          
            <tr>
                <td style="padding-bottom:7px;">
                    <div align="center" class="error hidden">
                        {if $pass_wrong}Неверный пароль к объявлению{/if}
                    </div>
                    <input class="inputText" name="pass" style="text-align:center; width:170px;" type="text" value="{$pass}" />
                </td>
            </tr>         
            <tr>        
                <td>    
                    <div style="margin:10px 0 10px 35px;">
                        <div class="button left">
                            <span class="left">&nbsp;</span>
                            <span class="btCont"><input type="submit" style="width: 110px;" value="Продолжить"></span>
                        </div>
                    </div>
                </td>
            </tr>                                                                                    
        </table>
    </form>
</div>