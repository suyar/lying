<?php
namespace lying\base;

use lying\service\Service;

class Ctrl extends Service
{
    /**
     * @var string 方法执行前事件id
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';
    
    /**
     * @var string 方法执行后事件id
     */
    const EVENT_AFTER_ACTION = 'afterAction';
    
    /**
     * @var string layout参数
     */
    protected $layout = false;
    
    /**
     * @var array 设置不被访问的public方法,用正则匹配,此属性必须设置为public
     */
    public $deny = [];
    
    /**
     * 绑定事件,子类需要在init方法里的首行调用parent::init();
     */
    protected function init()
    {
        $this->hook(self::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);
        $this->hook(self::EVENT_AFTER_ACTION, [$this, 'afterAction']);
    }
    
    /**
     * 在执行action之前执行
     * @param string $action 执行的方法
     */
    public function beforeAction($action) {}
    
    /**
     * 在执行action之后执行
     * @param string $action 执行的方法
     */
    public function afterAction($action) {}
    
    /**
     * 渲染页面
     * @param string $view 视图文件名称
     * @param array $params 视图参数
     * @param string $layout 布局文件
     * @param array $subparams 布局文件的参数
     * @return string 渲染的HTML代码
     */
    final protected function render($view, $params= [], $layout = false, $subparams = [])
    {
        return (new View())->render($view, $params, $layout ? $layout : $this->layout, $subparams);
    }
    
    /**
     * 重定向到某个URL
     * @param string $url
     * redirect('get', ['id' => 100]); 跳转到当前模块当前控制器下get方法
     * redirect('admin/post', ['id' => 100]); 跳转到当前模块admin控制器post方法
     * redirect('lying/index/name', ['id' => 100]); 跳转到lying模块index控制器name方法
     * redirect('https://www.baidu.com'); 必须带协议头,跳转到百度
     * @param array $params
     * @return \lying\base\Ctrl
     */
    final protected function redirect($url, $params = [])
    {
        if (preg_match('/^https?:\/\/\S+\.\S+/', $url)) {
            $q = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            $url .= empty($q) ? '' : (strpos($url, '?') === false ? "?$q" : "&$q");
        } else {
            $url = maker()->router()->createUrl($url, $params);
        }
        
        $request = maker()->request();
        while (ob_get_level() !== 0) ob_end_clean();
        http_response_code(302);
        if ($request->isPjax()) {
            header("X-Pjax-Url: $url");
        } else if ($request->isAjax()) {
            header("X-Redirect: $url");
        } else {
            header("Location: $url");
        }
        return $this;
    }
}
