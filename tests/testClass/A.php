<?php
namespace luoluolzb\di\tests\testClass;

class A
{
    private $b;

    public function __construct(B $b)
    {
        $this->b = $b;
    }

    public function doSomething()
    {
        return $this->b->doSomething() . 'A';
    }
}