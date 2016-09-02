<?php
return [
    'database' => require __DIR__.'/database.php',
    'log' => [
        'maxSize' => 0.5*1024*1024,
    ],
    'cookie' => [
        'key' => 'your key'
    ],
];