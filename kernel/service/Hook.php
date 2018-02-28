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
        foreach ($this->events as $id => $event) {
            if (is_callable($event)) {
                $this->hook($id, $event);
            } elseif (is_array($event)) {
                foreach ($event as $e) {
                    is_callable($e) && $this->hook($id, $e);
                }
            }
        }
    }
}
