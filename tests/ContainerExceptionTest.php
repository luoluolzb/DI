<?php
namespace luoluolzb\di\tests;

use PHPUnit\Framework\TestCase;
use luoluolzb\di\exception\ContainerException;
use luoluolzb\di\exception\EntityNotFoundException;

class ContainerExceptionTest extends TestCase
{
    /**
     * 测试抛出容器异常
     */
    public function testContainerException()
    {
        $this->expectException(ContainerException::class);
        throw new ContainerException("Container Error");
    }

    /**
     * 测试抛出实体未找到异常
     */
    public function testEntityNotFoundException()
    {
        $this->expectException(EntityNotFoundException::class);
        throw new EntityNotFoundException("Entity 'abc'Not Found");
    }
}


