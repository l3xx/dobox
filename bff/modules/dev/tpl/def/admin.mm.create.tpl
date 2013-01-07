<form method="post" action="">
<table class="admtbl tbledit">
<tr>
	<td class="row1 field-title" width="120">Модуль</td>
	<td class="row2">
    {html_options name="module" id="module" style="width:204px;" options=$aModules selected=$aData.module|default:'0'}
    </td>
</tr>
<tr>
	<td class="row1 field-title">Метод</td>
	<td class="row2"><input type="text" class="text-field" name="method" id="method" size="25" value="{$aData.method}" /></td>
</tr>
<tr>
	<td class="row1 field-title">Название</td>
	<td class="row2"><input type="text" class="text-field" name="title" size="25" value="{$aData.title}" /></td>
</tr>
<tr>
    <td class="row1 field-title">Генератор кода:</td>
    <td class="row2 desc" id="msg"></td>
</tr>
<tr class="footer">
	<td colspan="2">
        <input type="submit" class="button submit" value="Добавить" />
        <input type="button" class="button cancel" onclick="bff.redirect('index.php?s={$class}&amp;ev=mm_listing');" value="Отмена" />  
    </td>
</tr>
</table>
</form>

<script type="text/javascript">
{literal}
//<![CDATA[ 
$(function(){ 
    var $module = $('#module'); 
    var $method = $('#method');
    var $msg = $('#msg');
    
    function checkMethod( )
    {
        $msg.html( $method.val().length > 0 ? '$this->haveAccessTo(\'<b>'+($method.val())+'</b>\')' : '<b>укажите метод</b>' );
    }
    $method.keyup(function(){
        checkMethod( );
    });                                                                                                                        
    $module.focus(); 
    checkMethod();
}); 

//]]>  
{/literal}
</script>