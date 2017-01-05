<?php
return [
    //缓存类
    'FileCache'=>[
        'class'=>'lying\cache\FileCache',
        'dir'=> DIR_RUNTIME . '/cache',
        'gc'=>0.5,
    ],
    'ApcCache'=>[
        'class'=>'lying\cache\ApcCache',
        'apcu'=>true,
    ],
    'Memcached'=>[
        'class'=>'lying\cache\MemCached',
        'servers'=>[
            ['127.0.0.1', 11211, 50],
        ],
        /*'options'=>[
            \Memcached::OPT_BINARY_PROTOCOL=>true,
        ],
        'username'=>'user',
        'password'=>'pass',*/
    ],
    
    
    
    
    
    'session'=>[
        'class'=>'lying\session\Session',
        'cache'=>'db',
    ],
    'cookie'=>[
        'class'=>'lying\service\Cookie',
        'key'=>'123456',
    ],
    //以上session和cookie的id都不可变更
    
    
    
    
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