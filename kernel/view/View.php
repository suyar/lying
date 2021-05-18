<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\view;

use lying\service\Controller;
use lying\service\Service;

/**
 * Class View
 * @package lying\service
 */
class View extends Service
{
    /**
     * @var string 默认的模板文件后缀
     */
    protected $suffix = 'php';

    /**
     * @var array|Render[] 模板渲染引擎
     * @todo twig support
     */
    protected $render = [
        'tpl' => [],
        //'twig' => [],
    ];

    /**
     * @var array 后缀和类对应
     * @todo twig support
     */
    protected $renderMap = [
        'tpl' => 'lying\view\Smarty',
        //'twig' => 'lying\view\Twig',
    ];

    /**
     * @var Controller 上下文,一般用于控制器中渲染
     */
    private $_context;

    /**
     * 渲染模板
     * @param string $view 模板
     * @param array $params 模板参数
     * @param Controller $context 上下文
     * @return string 返回渲染后的模板
     */
    public function render($view, $params, Controller $context = null)
    {
        $oldContext = $this->_context;
        $context && ($this->_context = $context);
        $content = $this->renderFile($this->inc($view), $params);
        $this->_context = $oldContext;
        return $content;
    }

    /**
     * 渲染模板文件
     * @param string $file 模板文件绝对路径
     * @param array $params 模板参数
     * @param Controller $context 上下文
     * @return string 返回渲染后的模板
     * @throws \Throwable|\Exception
     */
    private function renderFile($file, $params, Controller $context = null)
    {
        if (false === is_file($file)) {
            throw new \Exception("The view file does not exist: $file");
        }

        $oldContext = $this->_context;
        $context && ($this->_context = $context);

        if (isset($this->render[$this->suffix])) {
            if (!$this->render[$this->suffix] instanceof Render) {
                $opt = isset($this->render[$this->suffix]) && is_array($this->render[$this->suffix]) ? $this->render[$this->suffix] : [];
                $this->render[$this->suffix] = new $this->renderMap[$this->suffix]($opt);
            }
            $render = $this->render[$this->suffix];
            $content = $render->render($file, $params, $this->_context);
        } else {
            $content = $this->renderPhp($file, $params);
        }

        $this->_context = $oldContext;
        return $content;
    }

    /**
     * 渲染php模板
     * @param string $file 模板文件
     * @param array $params 模板参数
     * @return string 返回渲染结果
     * @throws \Throwable|\Exception
     */
    private function renderPhp($file, $params)
    {
        //这一步是防止extract后变量名和$file冲突
        $fileHash = sha1($file);
        $$fileHash = $file;

        $_obInitialLevel_ = ob_get_level();
        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        try {
            require $$fileHash;
            return ob_get_clean();
        } catch (\Exception $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }

    /**
     * 解析视图路径
     * @param string $view 解析的视图
     * @param Controller $context 上下文
     * @return string 返回视图文件的绝对路径
     */
    public function inc($view, Controller $context = null)
    {
        $oldContext = $this->_context;
        $context && ($this->_context = $context);
        if ($this->_context) {
            $path = $this->_context->viewPath . DS;
            if (strncmp($view, '/', 1) === 0) {
                $path .= str_replace('/', DS, trim($view, '/'));
            } elseif ($view) {
                $path .= $this->_context->id . DS . str_replace('/', DS, trim($view, '/'));
            } else {
                $path .= $this->_context->id . DS . $this->_context->action;
            }
        } else {
            throw new \Exception("Unable to resolve view file for view '$view': no active view context.");
        }
        $this->_context = $oldContext;
        return $path . '.' . $this->suffix;
    }
}
