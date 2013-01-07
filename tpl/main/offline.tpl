<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf=8">
<title>{$config.title}</title>
<meta name="keywords" content="{$config.keywords}" />
<meta name="description" content="{$config.description}"  />
<style> 
{literal} 
* {margin: 0; padding: 0;}
 body {background-color:#000; color: #fff; font: bold 12px Arial, Verdana, sans-serif; text-align: center; min-width: 995px;}
 a {color: #0078F2; text-decoration: none;}
 a:hover {text-decoration: underline;}
 div.content {width:100%; min-height: 500px; padding-top: 300px;}
{/literal}  
</style>
</head>
<body>
<div class="content">
{$config.offline_reason|default:''}
</div>
</body>
</html>      