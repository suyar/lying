<?php
namespace lying\base;

use lying\service\Service;

class Ctrl extends Service
{
    /**
     * @var string layout参数
     */
    protected $layout = false;
    
    /**
     * 渲染
     * @param string $view 视图文件名称
     * @param array $params 视图参数
     * @param array $layoutParams layout的参数
     */
    final protected function render($view, $params= [], $layoutParams = [])
    {
        return (new View())->render($view, $params, $this->layout, $layoutParams);
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
