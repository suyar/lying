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
        $this->bindEvent(self::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);
        $this->bindEvent(self::EVENT_AFTER_ACTION, [$this, 'afterAction']);
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
     * 跳转
     * @param string $url 形如"post"、"post/index"、"admin/post/index"或者完整网址,否则报错
     * @param array $params 要带的参数，使用path模式/id/1/name/carol.
     * 此参数只接受数组+字母组成的键/值,包含非数字、字母的参数会被忽略.
     * 注意：如果此参数的键值为纯数字，则键值将会被忽略，如createUrl('post', [1])将会变成/path/1而不是/path/0/1.
     * @param array $query 接受一个数组，此数组的参数会被编码成get参数的形式放在"?"之后.
     * 所有带有特殊字符(+、空格、|、/、?、%、#、&、<、>等)的键/值对，都应该放在此参数.
     */
    final protected function redirect($url, $params = [], $query = [])
    {
        $url = $this->make()->getRouter()->createUrl(is_array($url) ? $url[0] : $url, $params, $query);
        $request = $this->make()->getRequest();
        while (ob_get_level() !== 0) ob_end_clean();
        if ($request->isPjax()) {
            header("X-Pjax-Url: $url");
        } else if ($request->isAjax()) {
            header("X-Redirect: $url");
        } else {
            header("Location: $url");
        }
    }
}
