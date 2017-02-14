![LOGO](web/favicon.ico "Lying") $Lying = ['PHP Framework'] :cn:
===============================================================
>Lying是我在学习PHP的时候写的一个MVC框架，第一个版本它非常的简陋，不规范，没有设计模式，没有方便的QueryBuilder和ActiveRecord，杂乱的目录结构等。近期我对Lying进行了一次大的重构，完善它的工作机制，使它成为一个真正可用在生产环境的PHP框架。如果有兴趣的同学可以fork去看看代码，如果它能给你的PHP之路带来一点启发，那是再好不过了。当然，如果你觉得Lying的某些地方有缺陷，你可以提Issues或者PR，我会根据你的意见考虑对Lying进行完善:blush:。

DEPENDENCIES
------------
* PHP >= 5.5.0
* 如果要使用`apc/apcu/memcached`缓存，需要安装PHP的`apc/apcu/memcached`扩展

DOCUMENTATION
-------------
TODO

FEATURES
--------
* 单入口
* 经典MVC
* 遵循psr-0,psr-1,psr-2,psr-4规范
* 多种代码加载方式：psr-0，psr-4(推荐)，classMap(推荐)
* PDO实现的QueryBuilder + ActiveRecord支持
* 核心类基于服务容器的设计，按需加载，配置和逻辑代码分离
* 统一功能接口，在配置文件即可自由切换功能类实现机制，不影响逻辑代码
* ApcCache/ApcuCache/DbCache/Memcached/FileCache多种缓存实现，在配置按需加载
* DbLog/FileLog日志实现，在配置按需加载
* 路由支持正反向解析(不是非常完善，考虑重构)
* cookie加密，session，加密类，请求类等类的封装
* 核心类扩展实现方便
* 工厂函数maker简便实例化服务类
* 基于layout的原生模板引擎，渲染更快，适合pjax，ajax开发
* 一些辅助函数帮助你更快开发
* 

## FAQ

>
### Lying是什么鬼？
>Lying是我学习PHP的时候写的一个MVC小框架。
### Lying好用吗？
>不好用。没有强大的功能（Active Record、SQL Builder等等等）也没有详细的文档，但是勉强能用。
###Lying有什么优点？
>没有。如果你不喜欢这个框架，请移步Yii等其他框架。如果非要说有，就是代码量少，灵活（你爱怎么改怎么改，完全重写也没问题）。
###Lying为什么叫Lying？
>因为Flying = Framework Lying啊（简直是歪理）。
###Lying源码能拿来学习用吗？
>既然放github来就是让你用的，随便拿（世界上怎么会有我这么大方的人）。但是如果你拿来使用，请尊重作者，保留作者信息，或者给个github源码链接。
###和我交流PHP的心得
>_↓ ↓ ↓ ↓ ↓_

## 反馈与建议
- Q Q：<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=296399959&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:296399959:52" alt="点击这里给我发消息" title="点击这里给我发消息"/></a>
- 微博：[@宝宝左手边](http://weibo.com/514070127)
- 邮箱：<a target="_blank" href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=tIaNgoeNjY2BjfTFxZrX29k" style="text-decoration:none;"><img src="http://rescdn.qqmail.com/zh_CN/htmledition/images/function/qm_open/ico_mailme_01.png"/></a>
