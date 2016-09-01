<?php
namespace core;
/**
 * 基类控制器
 * @author suyq
 * @version 1.0
 */
class Controller {
    /**
     * 默认布局
     * @var string|boolean
     */
    protected $layout = false;
    
    /**
     * 渲染并返回
     * @param string $view 视图文件
     * @param array $params 要传入的变量数组
     * @param string $layout 选择要使用的布局文件
     * @param array $layParams 要传入给布局文件的变量数组
     * @throws \Exception
     * @return string
     */
    public function render($view, $params = [], $layout = false, $layParams = []) {
        $layout = $layout ? $layout : $this->layout;
        $viewFile = $this->getView($view);
        if (!is_file($viewFile)) throw new \Exception("Template ".str_replace([ROOT, '\\'], ['', '/'], $viewFile)." not found");
        if ($layout) {
            $content = $this->renderFile($viewFile, $params);
            $layoutFile = $this->getLayout($layout);
            if (!is_file($layoutFile)) throw new \Exception("Layout ".str_replace([ROOT, '\\'], ['', '/'], $viewFile)." not found");
            return $this->renderFile($layoutFile, $layParams === [] ? ['content'=>$content] : array_merge($layParams, ['content'=>$content]));
        }
        return $this->renderFile($viewFile, $params);
    }
    
    /**
     * 导入变量到视图文件并返回
     * @param string $file 视图文件的绝对路径
     * @param array $params 要传入的变量
     * @return string
     */
    final private function renderFile($file, $params = []) {
        ob_start();
        ob_implicit_flush(false);
        $params === [] ? '' : extract($params);
        require $file;
        return ob_get_clean();
    }
    
    /**
     * 获取布局文件的绝对路径
     * @param string $layout
     * @return string
     */
    protected function getLayout($layout) {
        $fileName = $this->reflectionClass()->getFileName();
        return dirname(dirname($fileName)) . "/view/layout/$layout.php";
    }
    
    /**
     * 获取视图文件的绝对路径
     * @param string $view
     * @return string
     */
    protected function getView($view) {
        $class = $this->reflectionClass();
        $fileName = $class->getFileName();
        $className = strtolower($class->getShortName());
        return dirname(dirname($fileName)) . "/view/$className/$view.php";
    }
    
    /**
     * 返回本实例的类信息
     * @return \ReflectionClass
     */
    protected function reflectionClass() {
        return new \ReflectionClass($this);
    }
}