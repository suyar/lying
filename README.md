![L](https://box.kancloud.cn/8e58155c9787bf4f9d733941a1eb88df_48x48.png)![Y](https://box.kancloud.cn/bdcd2fcf24c101e52b03fda375f8cf6e_48x48.png)![I](https://box.kancloud.cn/b7a823224b5c836fe27eab8804fe57d4_48x48.png)![N](https://box.kancloud.cn/21d716329fbddfc83f5fc850970df7c9_48x48.png)![G](https://box.kancloud.cn/2ed8f2b1963d88f0c3a7ceb3868d44be_48x48.png)

> Lying是我在学习PHP的时候，为了理解MVC，设计模式等PHP知识而写的一个轻量级的PHP框架，经过几次大改版之后，Lying已经成为一个真正可用在生产环境的PHP框架。有兴趣的同学可以fork去看看代码，如果它能给你的PHP之路带来一点启发，那是再好不过了。当然，如果你觉得Lying的某些地方有缺陷，你可以提Issue或者PR，我会根据你的意见对Lying进行完善。如果你喜欢Lying，欢迎你来学习、使用。但是如果你不喜欢它，也请你不要玷污它，因为每个人心中都有一门世界上最好的语言（比如PHP）和一个世界上最好的框架（比如Lying）。

INSTALL
-------
`git clone git@github.com:carolkey/lying.git`  
`composer create-project carolkey/lying lying`

REQUIREMENTS
------------
* PHP >= 5.5.0
* pdo/pdo-mysql
* 根据需求安装Apc/Apcu/Memcached扩展

DOCUMENTATION
-------------
<http://www.kancloud.cn/carol/lying>

TODO
----
我想做的事情有太多太多……
* CLI控制器基类
* REDIS缓存（还没想好怎么弄，但是并不想弄成和memcached一样的key => value形式的缓存，因为这太埋没了redis）
* 数据库主从分离（这个再说吧，暂时还没用到，暂时用多个db组件勉强能实现）
* 路由反向解析优化（嗯...这个是痛点，我并不知道有什么高效又简便的实现方法，看来有空得去看看大框架的路由实现源码了）

FEATURES
--------
* 单入口经典MVC。
* 代码库0依赖，支持composer加载（默认没加载autoload），因为Lying没有使用第三方代码库。
* 代码遵循PSR-2，PSR-4规范。
* PSR-0，PSR-4，classMap多种自动加载方式。
* 基于Service Locator的设计，懒加载、配置和逻辑代码分离，扩展方便。
* 统一功能接口，功能相同的服务类在配置文件即可自由无缝切换，不影响逻辑代码。
* PDO实现的QueryBuilder + ActiveRecord支持，暂时只支持mysql和mariadb。
* ApcCache/DbCache/Memcached/FileCache多种缓存实现。
* DbLog/FileLog日志实现。
* Apache，Nginx，IIS，虚拟主机简易部署。
* 支持pathinfo模式和rewrite模式的路由，路由支持正反向解析（虽然弄的不太好）。
* 模块<->域名绑定，让你快速实现前后台分离。
* cookie，加密，请求，钩子等类的封装。
* 工厂`\Lying::$maker`简便使用服务类。
* 基于layout的PHP原生模板，渲染更快。
* `get()`、`post()`、`lock()`等一些辅助函数帮助你更快快速开发。
* And so on...

LICENCE
-------
[MIT](https://opensource.org/licenses/MIT)

FEEDBACK
--------
* Issue：[Lying](https://github.com/carolkey/lying/issues)
* QQ：[296399959](http://wpa.qq.com/msgrd?v=3&uin=296399959&site=qq&menu=yes)
* MAIL：<me@suyaqi.cn>
