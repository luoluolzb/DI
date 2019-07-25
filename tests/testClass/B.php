<?php
namespace luoluolzb\di\tests\testClass;

class B
{
    private $c;

    public function __construct(C $c)
    {
        $this->c = $c;
    }

    public function doSomething()
    {
        return $this->c->doSomething() . 'B';
    }
}