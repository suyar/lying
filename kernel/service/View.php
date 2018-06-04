<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class View
 * @package lying\service
 */
class View extends Service
{
    /**
     * @var array 要渲染输出的参数
     */
    private $_params = [];

    /**
     * @var mixed 上下文,一般用于控制器中渲染
     */
    private $_context;

    /**
     * 输出数据
     * @param string|array $key 参数名,如果为数组,则判断为批量输出数据
     * @param mixed $value 参数值,如果key为数组,此参数可不填写
     * @return $this
     */
    public function assign($key, $value = null)
    {
        if (is_array($key)) {
            $this->_params = array_merge($this->_params, $key);
        } else {
            $this->_params[$key] = $value;
        }
        return $this;
    }

    /**
     * 渲染模板
     * @param string $view 模板
     * @param array $context 上下文
     * @return string 返回渲染后的模板
     */
    public function render($view, array $context = null)
    {
        $oldContext = $this->_context;
        $context && ($this->_context = $context);
        $content = $this->renderFile($this->inc($view));
        $this->_context = $oldContext;
        return $content;
    }

    /**
     * 渲染模板文件
     * @param string $file 模板文件
     * @param array $context 上下文
     * @return string 返回渲染后的模板
     */
    public function renderFile($file, array $context = null)
    {
        if (is_file($file)) {
            $oldContext = $this->_context;
            $context && ($this->_context = $context);

            //这一步是防止extract后变量名和$file冲突
            $fileHash = sha1($file);
            $$fileHash = $file;

            $_obInitialLevel_ = ob_get_level();
            ob_start();
            ob_implicit_flush(false);
            extract($this->_params, EXTR_OVERWRITE);
            try {
                require $$fileHash;
                $content = ob_get_clean();
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

            $this->_context = $oldContext;
            return $content;
        } else {
            throw new \Exception("The view file does not exist: $file");
        }
    }

    /**
     * 解析视图路径
     * @param string $view 解析的视图
     * @param array $context 上下文
     * @return string 返回视图文件的绝对路径
     */
    public function inc($view, array $context = null)
    {
        $oldContext = $this->_context;
        $context && ($this->_context = $context);
        if ($this->_context) {
            list($m, $c, $a) = $this->_context;
            $path = DIR_MODULE . DS;
            if (strncmp($view, '/', 1) === 0) {
                $path .= $m . DS . 'view' . str_replace('/', DS, rtrim($view, '/')) . '.php';
            } elseif ($view == '') {
                $path .= $m . DS . 'view' . DS . $c . DS . $a . '.php';
            } else {
                $cview = trim($view, '/');
                $viewArr = explode('/', $cview);
                switch (count($viewArr)) {
                    case 1:
                        $path .= $m . DS . 'view' . DS . $c . DS . $cview . '.php';
                        break;
                    case 2:
                        $path .= $m . DS . 'view' . DS . str_replace('/', DS, $cview) . '.php';
                        break;
                    case 3:
                        $path .= $viewArr[0] . DS . 'view' . DS . $viewArr[1] . DS . $viewArr[2] . '.php';
                        break;
                    default:
                        throw new \Exception("Unable to locate view file for view '$view'.");
                }

            }

        } else {
            $path = $view;
        }
        $this->_context = $oldContext;
        return $path;
    }

    /**
     * 清空上一次渲染的数据及上下文关系
     */
    public function clear()
    {
        $this->_params = [];
        $this->_context = null;
    }
}
