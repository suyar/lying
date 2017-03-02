![L](https://box.kancloud.cn/8e58155c9787bf4f9d733941a1eb88df_48x48.png)![Y](https://box.kancloud.cn/bdcd2fcf24c101e52b03fda375f8cf6e_48x48.png)![I](https://box.kancloud.cn/b7a823224b5c836fe27eab8804fe57d4_48x48.png)![N](https://box.kancloud.cn/21d716329fbddfc83f5fc850970df7c9_48x48.png)![G](https://box.kancloud.cn/2ed8f2b1963d88f0c3a7ceb3868d44be_48x48.png)

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
[Lying](http://www.kancloud.cn/carol/lying)

TODO
----
* CLI控制器基类
* REDIS缓存（还没想好怎么弄，但是并不想弄成和memcached一样的key => value形式的缓存，因为这太埋没redis了）
* 数据库主从分离（这个再说吧，暂时还没用到，暂时用多个db组件勉强能实现）
* 路由反向解析优化（嗯...这个是痛点，我并不知道有什么高效又简便的实现方法，看来有空得去看看大框架的路由实现源码了）

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
* MAIL：<me@suyaqi.cn>

