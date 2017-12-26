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
 * Class ActiveRecordEvent
 * @package lying\event
 */
class ActiveRecordEvent extends Event
{
    /**
     * @var int|bool 受影响的行数,失败被设置为false
     */
    public $rows;
}
