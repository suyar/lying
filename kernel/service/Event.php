<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Event
 * @package lying\service
 */
class Event
{
    /**
     * @var string 事件名称
     */
    public $_name;

    /**
     * @var object 出发事件的对象
     */
    public $_sender;

    /**
     * @var mixed 绑定时候传输的数据
     */
    public $_data;

    final public static function hook()
    {

    }

    final public static function trigger()
    {

    }

    final public static function unhook()
    {

    }
}
