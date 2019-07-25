<?php

namespace luoluolzb\di\exception;

use luoluolzb\di\exception\ContainerException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 实体未找到异常
 *
 * @package luoluolzb\di\exception
 */
class EntityNotFoundException extends ContainerException implements NotFoundExceptionInterface
{

}
