<?php
class A
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __destruct()
    {
        echo __METHOD__;
    }

    public function name()
    {
        if (!$this->name) {
            exit(1);
        } else {
            echo $this->name;
        }
    }
}

$a = new A('');

$a->name();




