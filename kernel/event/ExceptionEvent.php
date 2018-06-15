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
 * Class ExceptionEvent
 * @package lying\event
 */
class ExceptionEvent extends Event
{
    /**
     * @var \Exception|\Throwable
     */
    public $e;
}
