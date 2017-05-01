<?php
namespace lying\service;

/**
 * 负责渲染视图文件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://github.com/carolkey/lying
 * @license MIT
 */
class View
{
    /**
     * 渲染视图文件
     * @param string $view 视图文件名称
     * @param array $params 视图文件参数
     * @param string|boolean $layout 布局文件
     * @param array $subparams 布局文件参数
     * @return string 返回渲染后的HTML
     */
    public function render($view, $params = [], $layout = false, $subparams = [])
    {
        $content = $this->renderFile($this->findViewPath($view), $params);
        return empty($layout) ? $content : $this->renderFile(
            $this->findViewPath($layout),
            array_merge($subparams, ['container'=>$content])
        );
    }
    
    /**
     * 渲染视图文件
     * @param string $file 视图文件绝对路径
     * @param array $params 视图文件参数
     * @return string 返回渲染后的页面
     */
    private function renderFile($file, $params)
    {
        ob_start();
        ob_implicit_flush(false);
        empty($params) || extract($params);
        require $file;
        return ob_get_clean();
    }
    
    /**
     * 查找视图文件的路径
     * @param string $view 视图文件名称
     * @throws \Exception 视图文件不存在抛出异常
     * @return string 返回视图文件的绝对路径
     */
    private function findViewPath($view)
    {
        $view = trim($view, '/');
        $viewArr = explode('/', $view);
        list($m, $c, ) = \Lying::$maker->router()->path();
        switch (count($viewArr)) {
            case 1:
                $file = DIR_MODULE . "/$m/view/$c/$view.php";
                break;
            case 2:
                $file = DIR_MODULE . "/$m/view/$view.php";
                break;
            case 3:
                $file = DIR_MODULE . "$viewArr[0]/view/$viewArr[1]/$viewArr[2].php";
                break;
            default:
                throw new \Exception("Unknown view path: $view", 500);
        }
        if (file_exists($file)) {
            return $file;
        } else {
            throw new \Exception("View file not found: $file", 500);
        }
    }
}
