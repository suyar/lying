<?php
return [
    //以下不要更改
    'config'=>'lying\service\Config',
    'request'=>'lying\service\Request',
    'router'=>'lying\service\Router',
    'secure'=>'lying\service\Secure',
    'session'=>'lying\service\Session',
    
    //以下可以更改
    'cookie'=>[
        'class'=>'lying\service\Cookie',
        'key'=>'123456',
    ],
    'cache'=>[
        'class'=>'lying\cache\FileCache',
    ],
    'db'=>[
        'class'=>'lying\db\Connection',
        'dsn'=>'mysql:host=127.0.0.1;dbname=e',
        'user'=>'root',
        'pass'=>'root',
    ],
    'logger'=>[
        'class'=>'lying\logger\FileLog',
        'file'=>'default',
        'maxLength'=>500,
        'maxSize'=>10240,
        'maxFile'=>5,
        'level'=>['debug', 'info', 'warning', 'error'],
    ],
    
    
];