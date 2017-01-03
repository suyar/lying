<?php
return [
    'session'=>[
        'class'=>'lying\session\Session',
        'cache'=>'db',
    ],
    'cookie'=>[
        'class'=>'lying\service\Cookie',
        'key'=>'123456',
    ],
    //以上session和cookie的id都不可变更
    
    'cache'=>[
        'class'=>'lying\cache\FileCache',
    ],
    'db'=>[
        'class'=>'lying\db\Connection',
        'dsn'=>'mysql:host=127.0.0.1;dbname=lying',
        'user'=>'root',
        'pass'=>'root',
    ],
    'logger'=>[
        'class'=>'lying\logger\FileLog',
        'file'=>'default',
        'maxItem'=>500,
        'maxSize'=>1,
        'maxFile'=>5,
        'level'=>LOG_DEBUG,
    ],
    'dblog'=>[
        'class'=>'lying\logger\DbLog',
        'maxItem'=>500,
        'level'=>LOG_DEBUG,
        'connection'=>'db',
        'table'=>'log',
    ],
    
    
    
];