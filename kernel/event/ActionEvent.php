<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\event;

use lying\service\Event;

/**
 * Class ActionEvent
 * @package lying\event
 */
class ActionEvent extends Event
{
    /**
     * @var string 方法名
     */
    public $action;

    /**
     * @var mixed 执行结果
     */
    public $response;
}
