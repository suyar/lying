<?php
return [
    'default' => [
        'module' => 'index',
        'controller' => 'index',
        'action' => 'index',
        'suffix' => '.html',
        'pathinfo' => false,
        'rule' => [
            'u/:time/:id'=>['index/index/user-name', 'time'=>'/^\d{4}-\d{1,2}-\d{1,2}$/'],
        ],
    ],
    'admin.lying.com' => [
        'module' => 'admin',
        'controller' => 'index',
        'action' => 'index',
        'suffix' => '.html',
        'pathinfo' => false,
        'rule' => [],
    ],
];
