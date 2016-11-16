<?php
namespace lying\base;

class View
{
    public $layout = false;
    
    public $layoutParams = [];
    
    final public function render($view, $params= [])
    {
        $file = $this->findViewPath($view);
        $content = $this->renderFile($file, $params);
        if (false === $this->layout) {
            return $content;
        }else {
            $layoutFile = $this->findLayoutFile($this->layout);
            return $this->renderFile($layoutFile, array_merge($this->layoutParams, ['container'=>$content]));
        }
    }
    
    private function renderFile($file, $params)
    {
        ob_start();
        ob_implicit_flush(false);
        $params == [] ? '' : extract($params);
        require $file;
        return ob_get_clean();
    }
    
    
    private function findLayoutFile($layout)
    {
        return $this->findViewPath($layout, true);
    }
    
    
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