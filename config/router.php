<?php
//可选参数如果不需要都应该被注释或者设置成null
//配置对大小写敏感
return [
    //默认域名配置;必须
    'default'=>[
        //绑定的module;可选,默认为index,如果定义了此参数,就没有办法通过path访问其他模块了
        'module'=>'index',
        //默认控制器;可选,默认为index
        'ctrl'=>'index',
        //默认方法;可选,默认为index
        'action'=>'index',
        //默认后缀;可选
        'suffix'=>'.html',
        //路由规则;必须,可以为空数组
        'rule'=>[
            //规则对应的路径应该为全部小写,并且是从module开始写,不管是否设置了默认module
            //设置规则后原url失效;规则匹配是从上到下,匹配到了就不会继续下一条匹配
            'u/:time/:id'=>['index/index/user-name', 'time'=>'/^\d{4}-\d{1,2}-\d{1,2}$/'],
        ],
    ],
    //指定域名配置;可选
    'admin.lying.com'=>[
        //绑定的module;可选,默认为index,如果定义了此参数,就没有办法通过path访问其他模块了
        'module'=>'admin',
        //默认控制器;可选,默认为index
        'ctrl'=>'index',
        //默认方法;可选,默认为index
        'action'=>'index',
        //默认后缀;可选
        'suffix'=>'.html',
        //路由规则;必须,可以为空数组
        'rule'=>[
            
        ],
    ],
];
