<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

use lying\event\ActionEvent;
use lying\exception\HttpException;

/**
 * Class Controller
 * @package lying\service
 */
class Controller extends Service
{
    /**
     * @var string 方法执行前事件ID
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';
    
    /**
     * @var string 方法执行后事件ID
     */
    const EVENT_AFTER_ACTION = 'afterAction';

    /**
     * @var string 所属模块,用户不应该修改此变量
     */
    protected $module;

    /**
     * @var string 此控制器ID,用户不应该修改此变量
     */
    protected $id;

    /**
     * @var string 当前被执行的方法,用户不应该修改此变量
     */
    protected $action;

    /**
     * @var array 设置不被访问的方法,用正则匹配,此属性必须设置为public
     */
    public $deny = [];

    /**
     * 在执行action之前执行
     * @param ActionEvent $event 执行事件
     * @throws \Exception 当CSRF验证未通过的时候抛出400
     */
    public function beforeAction(ActionEvent $event) {
        $this->action = $event->action;
        if (\Lying::$maker->request()->validateCsrfToken() === false) {
            throw new HttpException('Unable to verify your data submission.', 400);
        }
    }
    
    /**
     * 在执行action之后执行
     * @param ActionEvent $event 执行的方法名称
     */
    public function afterAction(ActionEvent $event) {}

    /**
     * 渲染输出参数
     * @param string|array $key 参数名,如果为数组,则判断为批量输出数据
     * @param mixed $value 参数值,如果key为数组,此参数可不填写
     * @return View
     */
    final public function assign($key, $value = null)
    {
        return \Lying::$maker->view->assign($key, $value);
    }

    /**
     * 渲染页面
     * @param string $view 视图文件名称,默认为当前方法名
     * @return string 渲染后的HTML代码
     */
    final public function render($view = '')
    {
        return \Lying::$maker->view->render($view, [$this->module, $this->id, $this->action]);
    }
}
