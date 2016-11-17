<?php
namespace lying\base;

class Ctrl
{
    /**
     * layout参数
     * @var string
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
    
    
    final protected function redirect($url, $params = [], $after = false)
    {
        if (is_array($url)) {
            $url = \Lying::$container->get('router')->createUrl($url[0], $params, $after);
        }else if (is_string()) {
            
        }
        header("Location: $url");
        exit;
    }
    
    
}