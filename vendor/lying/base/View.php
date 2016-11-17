<?php
namespace lying\base;

class View
{
    /**
     * 渲染视图文件
     * @param string $view 视图文件名称
     * @param array $params 视图文件参数
     * @param string|boolean $layout layout文件
     * @param array $layoutParams layout参数
     * @return string
     */
    public function render($view, $params, $layout,$layoutParams)
    {
        $file = $this->findViewPath($view);
        $content = $this->renderFile($file, $params);
        if (false === $layout) {
            return $content;
        }else {
            $layoutFile = $this->findViewPath($layout, true);
            return $this->renderFile($layoutFile, array_merge($layoutParams, ['container'=>$content]));
        }
    }
    
    /**
     * 导入其他视图文件
     * @param string $view 视图文件名称
     * @param array $params 视图文件参数
     * @return string
     */
    public function import($view, $params = [])
    {
        return $this->render($view, $params, false, []);
    }
    
    /**
     * 渲染视图文件
     * @param string $file 视图文件绝对路径
     * @param array $params 视图文件参数
     * @return string
     */
    private function renderFile($file, $params)
    {
        ob_start();
        ob_implicit_flush(false);
        $params == [] ? '' : extract($params);
        require $file;
        return ob_get_clean();
    }
    
    /**
     * 查找视图文件、模板的路径
     * @param string $view 视图文件名称
     * @param boolean $layout 是否查找layout
     * @throws \Exception
     * @return string
     */
    private function findViewPath($view, $layout = false)
    {
        if (false !== strpos($view, '/')) {
            $tmp = explode('/', $view);
            $length = count($tmp);
            switch ($length) {
                case 2:
                    if ($layout) {
                        $m = $tmp[0];
                        $tmp[0] = 'layout';
                    }else {
                        $m = __MODULE__;
                    }
                    $file = __APP__ . "/$m/view/$tmp[0]/$tmp[1].php";
                    break;
                default:
                    $file = __APP__ . '/'  . $tmp[0] . "/view/$tmp[1]/$tmp[2].php";
            }
        }else {
            $file = __APP__ . '/' . __MODULE__ . "/view/" . ($layout ? 'layout' : __CTRL__) . "/$view.php";
        }
        if (file_exists($file)) {
            return $file;
        }else {
            throw new \Exception("The view file does not exist: $file", 500);
        }
    }
}