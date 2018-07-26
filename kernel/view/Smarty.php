<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\view;

use lying\service\Controller;

/**
 * Class Smarty
 * @package lying\view
 */
class Smarty extends Render
{
    /**
     * @var bool 是否使用SmartyBC类
     */
    protected $useBC = false;

    /**
     * @var string 编译模板的路径
     */
    protected $compileDir;

    /**
     * @var string 缓存模板的路径
     */
    protected $cacheDir;

    /**
     * @var array Smarty额外的选项设置
     */
    protected $options = [];

    /**
     * @var array 插件的绝对路径
     */
    protected $pluginsDirs = [];

    /**
     * @var array 注册类
     */
    protected $imports = [];

    /**
     * @var \Smarty|\SmartyBC Smarty实例
     */
    private $_smarty;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();

        $this->compileDir = rtrim($this->compileDir, '/\\') ?: (DIR_RUNTIME . DS . 'cache' . DS . 'Smarty' . DS . 'compile');

        $this->cacheDir = rtrim($this->cacheDir, '/\\') ?: (DIR_RUNTIME. DS . 'cache' . DS . 'Smarty' . DS . 'cache');

        $this->_smarty = $this->useBC ? new \SmartyBC() : new \Smarty();

        $this->_smarty->setCompileDir($this->compileDir);

        $this->_smarty->setCacheDir($this->cacheDir);

        foreach ((array)$this->options as $key => $value) {
            $this->_smarty->$key = $value;
        }

        $this->_smarty->addPluginsDir((array)$this->pluginsDirs);

        foreach ((array)$this->imports as $tag => $class) {
            $this->_smarty->registerClass($tag, $class);
        }
    }

    /**
     * @inheritDoc
     */
    public function render($file, $params, Controller $context = null)
    {
        $tplDir = [dirname($file)];

        $context && ($tplDir[] = $context->viewPath);

        $this->_smarty->setTemplateDir($tplDir);

        $template = $this->_smarty->createTemplate($file, null, null, $params ?: null, null);

        $template->assign('this', \Lying::$maker->view);

        return $template->fetch();
    }
}
