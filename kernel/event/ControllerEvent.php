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
 * Class ControllerEvent
 * @package lying\event
 */
class ControllerEvent extends Event
{
    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $response;
}
