<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Controller
 * @package lying\service
 * @since 2.0
 */
class Controller extends Service
{
    /**
     * @var string 方法执行前事件ID
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';
    
    /**
     * @var string 方法执行后事件ID
     */
    const EVENT_AFTER_ACTION = 'afterAction';
    
    /**
     * @var string 布局文件
     */
    protected $layout = false;

    /**
     * @var array 布局文件参数,此参数会和render()函数里的$subparams合并
     */
    protected $subparams = [];

    /**
     * @var Request 请求类
     */
    protected $request;
    
    /**
     * @var array 设置不被访问的方法,用正则匹配,此属性必须设置为public
     */
    public $deny = [];

    /**
     * 绑定事件,子类需要在init方法里的首行调用parent::init();
     */
    protected function init()
    {
        $this->request = \Lying::$maker->request();
        $this->hook(self::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);
        $this->hook(self::EVENT_AFTER_ACTION, [$this, 'afterAction']);
    }

    /**
     * 在执行action之前执行
     * @param string $action 执行的方法名称
     */
    public function beforeAction($action) {}
    
    /**
     * 在执行action之后执行
     * @param string $action 执行的方法名称
     * @param mixed $response action执行后的返回值,可能会是一个页面
     */
    public function afterAction($action, $response) {}
    
    /**
     * 重定向
     * @param string $url
     * redirect('get', ['id' => 100]);跳转到[当前模块/当前控制器/get]
     * redirect('admin/post', ['id' => 100]);跳转到[当前模块/admin/post]
     * redirect('lying/index/name', ['id' => 100]);跳转到[lying/index/name],参见URL生成
     * redirect('https://www.baidu.com')必须带协议头,跳转到百度
     * @param array $params 要携带的参数,为一个关联数组
     */
    final public function redirect($url, $params = [])
    {
        if (preg_match('/^https?:\/\/\S+\.\S+/', $url)) {
            $query = http_build_query($params, '', '&');
            $url .= empty($query) ? '' : (strpos($url, '?') === false ? "?$query" : "&$query");
        } else {
            $url = \Lying::$maker->router()->createUrl($url, $params);
        }
        
        while (ob_get_level() !== 0) ob_end_clean();
        http_response_code(302);
        if ($this->request->isPjax()) {
            header("X-Pjax-Url: $url");
        } else if ($this->request->isAjax()) {
            header("X-Redirect: $url");
        } else {
            header("Location: $url");
        }
        exit(0);
    }

    /**
     * 渲染页面
     * @param string $view 视图文件名称
     * @param array $params 视图参数
     * @param string|boolean $layout 布局文件
     * @param array $subparams 布局文件的参数
     * @return string 渲染的HTML代码
     */
    final public function render($view, $params= [], $layout = false, $subparams = [])
    {
        return (new View())->render($view, $params, $layout ? $layout : $this->layout, array_merge($this->subparams, $subparams));
    }
}
