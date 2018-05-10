<?php
class A
{
    protected $name = 'su';
}

class B extends A{

    public function getName()
    {
        return $this->name;
    }
}

$b = new B();




