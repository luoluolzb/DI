<?php

namespace luoluolzb\di\exception;

use \RuntimeException;
use Psr\Container\ContainerExceptionInterface;

/**
 * 容器异常基类
 *
 * @package luoluolzb\di\exception
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{

}
