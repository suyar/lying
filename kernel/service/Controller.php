<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Controller
 * @package lying\service
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
     * @var View 视图实例
     */
    private $view;

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
     * @throws \Exception 当CSRF验证未通过的时候抛出400
     */
    public function beforeAction($action) {
        if ($this->request->validateCsrfToken() === false) {
            throw new \Exception('Unable to verify your data submission.', 400);
        }
    }
    
    /**
     * 在执行action之后执行
     * @param string $action 执行的方法名称
     * @param mixed $response action执行后的返回值,可能会是一个页面
     */
    public function afterAction($action, $response) {}

    /**
     * 重定向
     * ```
     * redirect('get', ['id' => 100]);跳转到[当前模块/当前控制器/get]
     * redirect('admin/post', ['id' => 100]);跳转到[当前模块/admin/post]
     * redirect('lying/index/name', ['id' => 100]);跳转到[lying/index/name],参见URL生成
     * redirect('https://www.baidu.com') 必须带协议头,跳转到百度
     * ```
     * @param string $url 跳转的多少
     * @param array $params 要携带的参数,为一个关联数组
     * @param bool $normal 是否把参数设置成?a=1&b=2,默认否,优先pathinfo(此参数对完整的URL无效)
     */
    final public function redirect($url, $params = [], $normal = false)
    {
        if (preg_match('/^https?:\/\/\S+\.\S+/i', $url)) {
            $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            $url = rtrim($url, '?&');
            empty($query) || ($url .= (strpos($url, '?') === false ? "?$query" : "&$query"));
        } else {
            $url = \Lying::$maker->router()->createUrl($url, $params, true, $normal);
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
     * 获取视图实例
     * @return View
     */
    private function getView()
    {
        return $this->view ?: ($this->view = new View());
    }

    /**
     * 渲染输出参数
     * @param string|array $key 参数名,如果为数组,则判断为批量输出数据
     * @param mixed $value 参数值,如果key为数组,此参数可不填写
     */
    final public function assign($key, $value = null)
    {
        $this->getView()->assign($key, $value);
    }

    /**
     * 渲染页面
     * @param string $view 视图文件名称
     * @param string|bool $layout 布局文件
     * @return string 渲染的HTML代码
     */
    final public function render($view, $layout = false)
    {
        return $this->getView()->render($view, $layout ? $layout : $this->layout);
    }
}
