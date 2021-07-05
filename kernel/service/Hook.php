<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Hook
 * @package lying\service
 */
class Hook extends Service
{
    /**
     * @var array 事件数组
     */
    protected $events = [];

    /**
     * @inheritdoc
     */
    protected function init()
    {
        foreach ($this->events as $event) {
            if (is_array($event)) {
                $event = array_values($event);
                $count = count($event);
                if ($count >= 2) {
                    array_push($event, null);
                    list($name, $handler, $data) = $event;
                    if (is_string($name) && is_callable($handler)) {
                        $this->on($name, $handler, $data);
                    }
                }
            }
        }
    }
}
