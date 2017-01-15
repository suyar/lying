<?php
namespace lying\service;

class Dispatch extends Service
{
    /**
     * 程序执行入口
     * @throws \Exception
     */
    public function runAction()
    {
        list($m, $c, $a) = maker()->router()->parse();
        $class = "module\\$m\\ctrl\\$c";
        if (class_exists($class) && method_exists($class, $a) && (new \ReflectionMethod($class, $a))->isPublic()) {
            $instance = new $class();
            $this->trigger(\lying\base\Ctrl::EVENT_BEFORE_ACTION, $a);
            echo $instance->$a();
            $this->trigger(\lying\base\Ctrl::EVENT_AFTER_ACTION, $a);
        } else {
            throw new \Exception('Page not found.', 404);
        }
    }
}
