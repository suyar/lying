<?php
return [
    'default'=>[//必须
        'module'=>'index',//可选，如果不设置必须把module写进path里
        'default_ctrl'=>'index',//必须
        'default_action'=>'index',//必须
        'suffix'=>'.html',//必须，不要后缀可以写成false或者空字符串
        'rule'=>[
            '/^\/post\/(\d+)$/'=>'/post/detail/id/:id',
        ],
    ],
    'admin.lying.com'=>[//可选
        'module'=>'admin',//必须
        'default_ctrl'=>'index',//必须
        'default_action'=>'index',//必须
        'suffix'=>'.html',//必须，不要后缀可以写成false或者空字符串
        'rule'=>[
            
        ],
    ],
];