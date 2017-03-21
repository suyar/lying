<?php
return [
    'default' => [
        'module' => 'index',
        'controller' => 'index',
        'action' => 'index',
        'suffix' => '.html',
        'pathinfo' => false,
        'rule' => [
            'admin/:id' => ['index/index'],
            'user' => ['index/name'],
            'u/:time/:id' => ['index/user-name', 'time'=>'/^\d{4}-\d{1,2}-\d{1,2}$/'],
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
