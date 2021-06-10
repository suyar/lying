~~~
     __        __
    / / __ __ /_/__  __ ____
   / / / // // //  \/ // _  \
  / /_/ // // // /\  // // /
 /____\_  //_//_/ /_/_\_  /
    /____/          \____/
~~~

> Yii2是我的PHP的入门老师，我心怀尊敬。我看过Yii2的绝大部分核心源码，大轮子虽然功能完善，但是在写小项目的时候有点拖沓的感觉。怀着“PHP是世界上最好的语言”的信念，我自以为是的造了个轮子—Lying。

> 有兴趣的同学可以fork去看看代码，如果它能给你的PHP之路带来一点启发，那是再好不过了。当然，如果你觉得Lying的某些地方有缺陷，你可以提Issue或者PR，我会根据你的意见对Lying进行完善。如果你喜欢Lying，欢迎你来学习、使用。但是如果你不喜欢它，也请你不要玷污它，因为每个人心中都有一门世界上最好的语言（比如PHP）和一个世界上最好的框架（比如Lying）。

> 因为采用了Yii2的部分设计，所以Lying和Yii2的使用方式有诸多的相同点。

INSTALL
-------
`git clone git@github.com:carolkey/lying.git`  

REQUIREMENTS
------------
> * php : >= 5.5.0
> * pdo : *
> * apcu : *（可选）
> * memcached : *（可选）
> * redis : (可选)

DOCUMENTATION
-------------
<http://www.kancloud.cn/carol/lying>

FEATURES
--------
* 单入口经典MVC。
* 代码库0依赖。
* 代码遵循PSR-2规范。
* 基于PSR-0，PSR-4，classMap多种自动加载方式。
* 基于Service Locator的设计，懒加载、配置和逻辑代码分离，扩展方便。
* 统一功能接口，功能相同的服务类在配置文件即可自由无缝切换，不影响逻辑代码。
* PDO实现的MySQL QueryBuilder + ActiveRecord支持，数据库支持主从分离。
* Apcu/Memcached/File/Redis多种缓存实现。
* 高性能FileLog日志实现。
* 路由支持正反向解析（路由仅支持rewrite模式，如果服务器不支持，请移步其他框架）。
* 完善的CLI制度。
* 模块<->域名绑定。
* 工厂`\Lying::$maker`简便使用服务类。
* 原生PHP模板，渲染更快（实际上是懒得写模板引擎）。
* Apache，Nginx，IIS，虚拟主机简易部署。
* And so on...

LICENCE
-------
[MIT](https://opensource.org/licenses/MIT)

FEEDBACK
--------
* MAIL：<su@revoke.cc>
