
<div class="page_err">    
		<h2>{if !$sForbiddenTitle}Функция недоступна{else}{$sForbiddenTitle}{/if}</h2><br/>
        <div class="text">
            {if !$sForbiddenDescription}
            Этой страницей может пользоваться только зарегистрированный пользователь. <br />
            Вы должны <a href="{$site_url}/client/login">Авторизоваться</a> (ввести логин и пароль) или пройти <a href="{$site_url}/client/registration">Регистрацию</a>.<br /> 
            Спасибо!
            {else}
                {$sForbiddenDescription}
            {/if}
        </div>
</div>