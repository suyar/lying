<?php
return [
    //COOKIE组件
    'cookie' => ['key' => 'lying'],
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
            'admin.lying.work' => [
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
        'pass' => '',
        'options' => [],
        'prefix' => '',
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
        'file' => 'runtime',
        'maxItem' => 500,
        'maxSize' => 10240,
        'maxFile' => 5,
        'level' => 5,
    ],
    //缓存组件
    'cache' => [
        'class' => 'lying\cache\FileCache',
        'dir' => DIR_RUNTIME . DS . 'cache',
        'gc' => 50,
        'suffix' => 'bin',
        'serialize' => true,
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
        'events' => [
            ['frameworkError', function ($event) {}], //注册自定义全局错误处理
            ['frameworkBegin', function () {}],
            ['frameworkEnd', function () {}],
        ],
    ],
    //上传组件
    'upload' => [
        'class' => 'lying\upload\Upload',
        'ext' => ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'zip', 'rar'], //允许上传的扩展名
        'size' => 8388608, //允许上传的文件大小
        'type' => [], //允许上传的MIME
    ],
    //验证码组件
    'captcha' => [
        'class' => 'lying\captcha\Captcha',
        'length' => 4, //验证码长度
        'width' => 120, //宽
        'height' => 40, //高
        'lines' => 10, //干扰线条数
        'fonts' => [], //额外的字体文件绝对路径地址
        'bg' => [255, 255, 255], //背景色
        'fontSize' => 20, //字体大小
        'noisy' => 50, //噪点
        'expire' => 120, //验证码有效期
    ],
    //视图设置
    'view' => [
        'suffix' => 'php',
        'cache' => 'tplCache',
    ],
    //缓存组件(用于模板缓存)
    'tplCache' => [
        'class' => 'lying\cache\FileCache',
        'dir' => DIR_RUNTIME . DS . 'compile',
        'gc' => 80,
        'suffix' => 'php',
        'serialize' => false,
    ],
];
