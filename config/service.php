<?php
return [
    //COOKIE组件
    'cookie' => [
        'class' => 'lying\service\Cookie',
        'key' => '123456',
    ],
    //路由组件
    'router' => [
        'class' => 'lying\service\Router',
        'binding' => false,
        'module' => 'index',
        'controller' => 'index',
        'action' => 'index',
        'suffix' => '.html',
        'rule' => [
            'blog/<id:\d+>/<name>$' => ['admin/blog/get', '.htm'],
            'user/<name>/<id>$' => ['user/info/name'],
            'yoyo' => ['index/index/index'],
        ],
        'host' => [
            'admin.lying.work:8080' => [
                'module' => 'admin',
                'controller' => 'index',
                'action' => 'index',
                'suffix' => '.html',
                'rule' => [
                    'user/<id>$' => ['index/user', '.html'],
                ],
            ],
        ],
    ],
    //数据库组件
    'db' => [
        'class' => 'lying\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8',
        'user' => 'root',
        'pass' => '123456',
        'options' => [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT],
        'prefix' => 'b_',
        'cache' => false,
        'slave' => [
            //['dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8', 'user' => 'root', 'pass' => 'root'],
            //['dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8', 'user' => 'root', 'pass' => 'root'],
            //['dsn' => 'mysql:host=127.0.0.1;dbname=lying;charset=utf8', 'user' => 'root', 'pass' => 'root'],
        ],
        'master' => [

        ],
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
        'class' => 'lying\cache\Memcached',
        'persistentId' => false,
        'servers' => [
            ['127.0.0.1', 11211, 1],
        ],
        'username' => '',
        'password' => '',
        'options' => [],
    ],
    'redis' => [
        'class' => 'lying\service\Redis',
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0,
        'persistentId' => false,
        'password' => '',
        'select' => 0,
        'prefix' => 'lying',
    ],
    //全局事件
    'hook' => [
        'class' => 'lying\service\Hook',
        'events' => [
            Lying::EVENT_BEFORE_REQUEST => function () {

            },
            Lying::EVENT_AFTER_REQUEST => function () {

            },
        ],
    ],

    'helper' => 'lying\service\Helper',
];
