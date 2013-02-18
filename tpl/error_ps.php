<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?php echo $title; ?></title> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="<?php echo $site_url; ?>/css/main.css">
</head>
<body>

    <div id="wrapper">

        <div id="header">
            <div class="logo left">
                <a href="/"><img src="/img/logo.png" alt="" /></a>  
            </div>
        </div>

        <div id="main-side">  
            <div id="content-side" class="content-side-left">
                <h1><?php echo $title; ?></h1>
                <p><?php echo $message; ?></p>
            </div>
            <div class="clear-all"></div> 
        </div>
        <div id="push"></div>
    </div>
     
</body>
</html>