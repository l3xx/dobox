<form action="" method="post">
<table cellpadding="0" cellspacing="0" class="admtbl tbledit">
    <tr><td class="row1" width="200">Операционная система:</td><td class="row2">{$aData.os_version|default:''}</td></tr>
    <tr><td class="row1">Версия PHP:</td><td class="row2">{$aData.php_version|default:''}</td></tr>
    <tr><td class="row1">MySQL Client Library:</td><td class="row2">{$aData.mysql|default:''}</td></tr>
    <tr><td class="row1">Версия GD:</td><td class="row2">{$aData.gd_version|default:''}</td></tr>
    <tr><td class="row1">Путь к ImageMagick:</td><td class="row2">{$aData.img_imagick|default:'нет'}</td></tr>
    <tr><td class="row1">Расширение IMAP:</td><td class="row2">{$aData.extension_imap|default:''}</td></tr>
    <tr><td class="row1">Расширение PDO:</td><td class="row2">{$aData.extension_pdo|default:''}</td></tr>
    <tr><td class="row1">Расширение SPL:</td><td class="row2">{$aData.extension_spl|default:''}</td></tr>
    <tr><td class="row1">Расширение mbstring:</td><td class="row2">{$aData.mbstring|default:''}</td></tr>
    <tr><td class="row1">Расширение mcrypt:</td><td class="row2">{$aData.extension_mcrypt|default:''}</td></tr> 
    <tr><td class="row1">Модуль mod_rewrite:</td><td class="row2">{$aData.mod_rewrite|default:''}</td></tr>       
    <tr><td class="row1">Безопасный режим:</td><td class="row2">{$aData.safemode|default:''}</td></tr>
    <tr><td class="row1">Выделено оперативной памяти:</td><td class="row2">{$aData.maxmemory|default:''}</td></tr>
    <tr><td class="row1">Максимальный время исполнения скипта (сек):</td><td class="row2">{$aData.maxexecution|default:''}</td></tr>
    <tr><td class="row1">Максимальный размер загружаемого файла:</td>
        <td class="row2">
            upload_max_filesize: <b>{$aData.maxupload|default:''}</b><br />
            post_max_size: <b>{$aData.maxpost|default:''}</b><br />
        </td>
    </tr>
    <tr><td class="row1">Отключенные функции:</td><td class="row2">{$aData.disabledfunctions|default:''}</td></tr>
</table>
</form>