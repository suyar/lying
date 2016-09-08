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
    
    /**
     * 302跳转
     * @param string|array $url 要跳转的url;
     * 如果为数组：
     * ['admin/index/index'] 跳转到当前域名下admin/index/index.html;
     * ['admin/index'] 跳转到当前域名下__MODULE__/admin/index.html;
     * ['admin'] 跳转到当前域名下__MODULE__/__CONTROLLER_/admin.html;
     * 如果为字符串,直接填写url
     * @param array $params 要附加在网址后的参数,传入关联数组
     * @param boolean $checkAjax 判断是否PJAX或者AJAX
     * @throws \Exception
     */
    protected function redirect($url, $params = [], $checkAjax = false) {
        if (is_array($url)) {
            $path = explode('/', $url[0]);
            switch (count($path)) {
                case 1:$url = __MODULE__.'/'.__CONTROLLER__.'/'.$path[0].'.html';break;
                case 2:$url = __MODULE__.'/'.$path[0].'/'.$path[1].'.html';break;
                case 3:$url = $path[0].'/'.$path[1].'/'.$path[2].'.html';break;
                default:$url = __MODULE__.'/'.__CONTROLLER__.'/'.__ACTION__.'.html';
            }
            $url = '/'.$url;
        }
        $params = $params ? http_build_query($params, 'arg', '&', PHP_QUERY_RFC3986) : false;
        $url .= $params ? '?'.$params : '';
        http_response_code(302);
        if ($checkAjax) {
            if (Request::getInstance()->isPjax()) {
                header('X-Pjax-Url: '.$url);
            }elseif (Request::getInstance()->isAjax()) {
                header('X-Redirect: '.$url);
            }else {
                header('Location: '.$url);
            }
        }else {
            header('Location: '.$url);
        }
        exit;
    }
}