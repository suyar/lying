<?php
return [
    //以下不要更改
    'config'=>'lying\service\Config',
    'cookie'=>[
        'class'=>'lying\service\Cookie',
        'key'=>'',
    ],
    'request'=>'lying\service\Request',
    'router'=>'lying\service\Router',
    'secure'=>'lying\service\Secure',
    'session'=>'lying\service\Session',
    
    //以下可以更改
    'cache'=>[
        'class'=>'lying\cache\FileCache',
    ],
    'db'=>[
        'class'=>'lying\db\Connection',
        'dsn'=>'mysql:host=127.0.0.1;dbname=test',
        'user'=>'root',
        'pass'=>'root',
    ],
    'logger'=>[
        'class'=>'lying\logger\FileLog',
        'file'=>'default',
        'maxLength'=>100,
        'maxSize'=>5,
    ],
    
    
];