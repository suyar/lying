<?php
return [
    //数据库服务
    'db' => [
        'class' => 'lying\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8',
        'user' => 'root',
        'pass' => 'root',
    ],
    //日志服务
    'logger' => [
        'class' => 'lying\logger\FileLog',
        'path' => DIR_RUNTIME . '/log', //存储日志文件的文件夹,默认'runtime/log'
        'file' => 'default', //文件名,默认'default'
        'maxItem' => 500, //当日志条数大于这个的时候,输出到文件,默认500条
        'maxSize' => 1024, //单个日志文件的大小(kb),默认10240kb
        'maxFile' => 5, //备份日志文件的个数,默认5个
        'level' => LOG_DEBUG, //当日志等级比这个严重的日志才输出,默认LOG_NOTICE
    ],
    'dblog' => [
        'class' => 'lying\logger\DbLog',
        'maxItem' => 500, //当日志条数大于这个的时候,写入到数据库,默认500条
        'level' => LOG_DEBUG, //当日志等级比这个严重的日志才输出,默认LOG_NOTICE
        'connection' => 'db', //日志要写入的数据库,写数据库连接的id,默认'db'
        'table' => 'log', //存储日志的表名,默认'log'
    ],
    //缓存服务
    'cache' => [
        'class' => 'lying\cache\FileCache',
        'dir' => DIR_RUNTIME . '/cache', //缓存文件存放的目录,默认'runtime/cache'
        'gc' => 50, //垃圾清除的频率,数值为0到100之间,越小回收的越频繁,默认50
    ],
    'ApcCache' => [
        'class' => 'lying\cache\ApcCache',
        'apcu' => true, //是否使用apcu,默认false
    ],
    'Memcached' => [
        'class' => 'lying\cache\MemCached',
        'servers' => [ //Memcached服务器连接列表,必填
            ['127.0.0.1', 11211, 50],
        ],
        'username' => 'user', //用户名,选填
        'password' => 'pass', //密码,选填
    ],
    'dbCache' => [
        'class' => 'lying\cache\DbCache',
        'connection' => 'db', //缓存要写入的数据库,写数据库连接的id,默认'db'
        'table' => 'cache', //存储缓存的表名,默认'cache'
        'gc' => '50', //垃圾清除的频率,数值为0到100之间,越小回收的越频繁,默认50
    ],
];
