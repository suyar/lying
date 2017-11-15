<?php
return [
    //路由组件
    'router' => [
        'class' => 'lying\service\Router',
        'binding' => false,
        'module' => 'index',
        'controller' => 'index',
        'action' => 'index',
        'pathinfo' => false,
        'suffix' => '.html',
        'rule' => [
            'blog/:id$' => ['admin/blog/get', 'id' => '/\d{50}/'],
        ],
        'host' => [
            'api.lying.com' => [
                'module' => 'index',
                'controller' => 'index',
                'action' => 'index',
                'pathinfo' => false,
                'suffix' => '.js',
                'rule' => [
                    'user/:id$' => ['index/user', 'id' => '/\d+/'],
                ],
            ],
        ],
    ],
    //数据库组件
    'db' => [
        'class' => 'lying\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8',
        'user' => 'root',
        'pass' => 'root',
        'prefix' => 'b_',
        'cache' => false,
        'slave' => [
            ['dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8', 'user' => 'root', 'pass' => 'root'],
            ['dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8', 'user' => 'root', 'pass' => 'root'],
            ['dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8', 'user' => 'root', 'pass' => 'root'],
        ],
        'master' => [

        ],
    ],
    //锁组件
    'lock' => [
        'class' => 'lying\service\Lock',
        'dir' => DIR_RUNTIME . DS . 'lock',
    ],
    //日志组件
    'logger' => [
        'class' => 'lying\service\Logger',
        'dir' => DIR_RUNTIME . DS . 'log',
        'file' => 'lying',
        'maxItem' => 500,
        'maxSize' => 1024,
        'maxFile' => 5,
        'level' => 5,
    ],
    //缓存组件
    'cache' => [
        'class' => 'lying\cache\FileCache',
        'dir' => DIR_RUNTIME . DS . 'cache',
        'gc' => 50,
    ],
    'apcu' => 'lying\cache\ApcuCache',
    'memcached' => [
        'class' => 'lying\cache\MemCached',
        'servers' => [
            ['127.0.0.1', 11211, 50],
        ],
        'username' => 'user',
        'password' => 'pass',
    ],
];
