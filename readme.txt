
0) Требования к серверу:
-  php 5.3.1+, pdo_mysql, gd2, mbstring, apc, short open tag = on
-  mysql 5+

1) База данных: /install.sql
Доступ к базе прописывается в /general.config.php
+ Прописать домен в SITEHOST

2) Доступ в админ панель:
логин: admin
пароль: test

поменять можно будет вот тут:
 http://example.com/admin/index.php?s=users&ev=profile

3) Крон задачи, необходимые для запуска описаны тут: cron/crontab.txt

4) Разрешить следующие папки на запись из php:
/files/bnnrs
/files/cache
/files/images/items
/files/logs
/files/mail
/files/pages
/tpl_c
