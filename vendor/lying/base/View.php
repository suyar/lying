<?php
namespace lying\base;

class View
{
    /**
     * 渲染视图文件
     * @param string $view 视图文件名称
     * @param array $params 视图文件参数
     * @param string|boolean $layout 布局文件
     * @param array $layoutParams 布局文件参数
     * @return string 返回渲染后的HTML
     */
    public function render($view, $params, $layout, $layoutParams)
    {
        $content = $this->renderFile($this->findViewPath($view), $params);
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
     * 查找视图文件的路径
     * @param string $view 视图文件名称
     * @throws \Exception
     * @return string
     */
    private function findViewPath($view)
    {
        $path = explode('/', trim($view, '/'));
        list($m, $c, $a) = maker()->router()->router();
        switch (count($path)) {
            case 1:
                $file = DIR_MODULE . '/' . $m . '/view/' . $c . '/' . $view . '.php';
                break;
            case 2:
                $file = DIR_MODULE . '/' . $m . '/view/' . $view . '.php';
                break;
            case 3:
                $file = DIR_MODULE . '/' . $path[0] . '/view/' . $path[1] . '/' . $path[2] . '.php';
                break;
            default:
                throw new \Exception("Unknown view path: $view", 500);
        }
        return $file;
    }
}
