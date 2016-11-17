<?php
return [
    'default'=>[//必须
        'module'=>'',//可选，如果不设置必须把module写进path里
        'default_module'=>'index',//必须，当没有path的时候默认的module
        'default_ctrl'=>'index',//必须
        'default_action'=>'index',//必须
        'suffix'=>'.html',//可选
        'rule'=>[
            '/^\/post\/(\d+)$/'=>'/post/detail/id/:id',
        ],
    ],
    'admin.lying.com'=>[//可选
        'module'=>'admin',//必须
        'default_ctrl'=>'index',//必须
        'default_action'=>'index',//必须
        'suffix'=>'.html',//可选
        'rule'=>[
            
        ],
    ],
];