<?php
/**
 * 扩展的加载方式,加载优先级为classMap > psr-4 > psr-0
 * 所有的类名,命名空间前缀都不需要加根命名空间'\',后边也不需要加'\'
 * 推荐用classMap和psr-4标准来加载文件
 */
return [
    'classMap' => [
        //类名 => 类文件绝对路径
        //e.g. 'PHPExcel' => DIR_EXTEND . '/Excel/PHPExcel.php',
    ],
    'psr-4' => [
        //命名空间前缀 => 目录
        //目录可以是一个数组,参考 http://www.php-fig.org/psr/psr-4/examples/
        //e.g. 'module' => DIR_MODULE,
        'module' => DIR_MODULE,
    ],
    'psr-0' => [
        //跟目录列表,参见 https://gist.github.com/jwage/221634
        //e.g. DIR_MODULE
    ],
];
