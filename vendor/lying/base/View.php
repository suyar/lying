<?php
namespace lying\base;

class View
{
    public $layout = false;
    
    /**
     * 渲染视图文件
     * @param string $view
     * @param array $params
     * @param array $layoutParams
     * @return string
     */
    final public function render($view, $params= [], $layoutParams = [])
    {
        $file = $this->findViewPath($view);
        $content = $this->renderFile($file, $params);
        if (false === $this->layout) {
            return $content;
        }else {
            $layoutFile = $this->findLayoutFile($this->layout);
            return $this->renderFile($layoutFile, array_merge($layoutParams, ['container'=>$content]));
        }
    }
    
    final public function import()
    {
        
    }
    
    
    private function renderFile($file, $params)
    {
        ob_start();
        ob_implicit_flush(false);
        $params == [] ? '' : extract($params);
        require $file;
        return ob_get_clean();
    }
    
    /**
     * 查找模板的路径
     * @param string $layout
     * @return string
     */
    private function findLayoutFile($layout)
    {
        return $this->findViewPath($layout, true);
    }
    
    /**
     * 查找视图文件、模板的路径
     * @param string $view
     * @param string $layout
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