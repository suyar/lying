![LOGO](web/favicon.ico "Lying") $Lying = ['PHP Framework'] :cn:
===============================================================
>Lying是我在学习PHP的时候写的一个MVC框架，第一个版本它非常的简陋，不规范，没有设计模式，没有方便的QueryBuilder和ActiveRecord，杂乱的目录结构等。近期我对Lying进行了一次大的重构，完善它的工作机制，使它成为一个真正可用在生产环境的PHP框架。如果有兴趣的同学可以fork去看看代码，如果它能给你的PHP之路带来一点启发，那是再好不过了。当然，如果你觉得Lying的某些地方有缺陷，你可以提Issue或者PR，我会根据你的意见考虑对Lying进行完善:blush:。

INSTALL
-------
`git clone git@github.com:carolkey/lying.git`  
`composer create-project carolkey/lying lying`

REQUIREMENTS
------------
* PHP >= 5.5.0
* PDO
* 根据需求安装PHP apc/apcu/memcached扩展

DOCUMENTATION
-------------
TODO

TODO
----
* 锁函数
* 工厂拆分成辅助函数
* redis缓存
* 数据库主从分离
* 路由反向解析优化

FEATURES
--------
* 单入口经典MVC
* 代码库0依赖，支持composer加载你想要的库
* 遵循PSR-0,PSR-1,PSR-2,PSR-4规范
* 多种类自动加载方式：PSR-0，PSR-4，classMap
* 核心类基于服务模式的设计，懒加载，配置和逻辑代码分离，扩展方便
* 统一功能接口，在配置文件即可自由切换功能类实现机制，不影响逻辑代码
* PDO实现的QueryBuilder + ActiveRecord支持，暂时只支持mysql和mariadb
* ApcCache/ApcuCache/DbCache/Memcached/FileCache多种缓存实现
* DbLog/FileLog日志实现
* Apache，Nginx，IIS，虚拟主机简易部署
* 支持pathinfo模和rewrite模式的路由，路由支持正反向解析(考虑重构)
* 模块<->域名绑定，快速实现前后台分离
* cookie加密，session，加密类，请求类等类的封装
* 工厂函数maker()简便实例化服务类
* 基于layout的PHP原生模板，渲染更快
* 一些辅助函数帮助你更快开发
* And so on...

LICENCE
-------
**MIT**

FEEDBACK
--------
* Issue：[Lying](https://github.com/carolkey/lying/issues)
* QQ：[296399959](http://wpa.qq.com/msgrd?v=3&uin=296399959&site=qq&menu=yes)
* MAIL：<296399959@qq.com>

