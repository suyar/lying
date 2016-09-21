<?php
return [
    'database' => require(__DIR__.'/database.php'),
    'log' => [
        //单个log文件限制为500K
        'maxSize' => 0.5*1024*1024,
        //log收集20条的时候写出文件
        'maxLenth' => 20,
    ],
    'cookie' => [
        //cookie加密的密钥
        'key' => 'your key'
    ],
];