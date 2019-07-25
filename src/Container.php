<?php

namespace luoluolzb\di;

use Psr\Container\ContainerInterface;
use luoluolzb\di\exception\ContainerException;
use luoluolzb\di\exception\EntityNotFoundException;

/**
 * 依赖注入容器
 *
 * 此函数实现了三个接口：
 * - ContainerInterface  psr-11容器接口
 * - ArrayAccess         数组式访问接口
 * - Countable           可count()接口
 *
 * @package luoluolzb\di
 */
class Container implements ContainerInterface, \ArrayAccess, \Countable
{
    /**
     * 容器中的实体列表
     *
     * @var array
     */
    protected $entities;

    /**
     * 创建一个容器
     *
     * @param array|null $entities 初始注入的实体列表[id => entity]
     */
    public function __construct(array $entities = null)
    {
        $this->entities = [];
        if ($entities) {
            foreach ($entities as $id => $entry) {
                $this->set((string)$id, $entry);
            }
        }
    }

    /**
     * 向容器中注入一个实体，实体可以是任何类型
     *
     * @param string $id    设置的实体标识符，便于之后取出
     * @param mixed  $entry 要注入的实体
     */
    public function set(string $id, $entry): void
    {
        $this->entities[$id] = $entry;
    }

    /**
     * 从容器中删除一个实体
     *
     * @param string $id    设置的实体标识符，便于之后取出
     *
     * @return void
     */
    public function delete(string $id): void
    {
        if ($this->has($id)) {
            unset($this->entities[$id]);
        }
    }

    /**
     * 在容器中查找并返回实体标识符对应的实体
     *
     * @param string $id 查找的实体标识符
     *
     * @throws EntityNotFoundException  容器中没有实体标识符对应对象时抛出的异常
     * @throws ContainerException       查找对象过程中发生了其他错误时抛出的异常
     *
     * @return mixed 查找到的实体
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new EntityNotFoundException("Entity '{$id}' Not Found");
        }
        
        $entity = $this->entities[$id];

        // 如果是可调用的
        // 将此容器实例传入第一个参数
        if (is_callable($entity)) {
            return $entity($this);
        }

        // 其他类型直接返回
        return $entity;
    }

    /**
     * 判断容器内是否有某个实体
     *
     * @param string $id 查找的实体标识符字符串。
     *
     * @return bool 是否有某个实体
     */
    public function has($id): bool
    {
        return isset($this->entities[$id]);
    }

    /**
     * @see set 注入一个实体
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @see has 判断一个实体是否存在
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @see delete 删除一个实体
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * @see get 获取一个实体
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * 获取容器中实体数量
     *
     * 此函数为 Countable 接口的实现，因此可以直接使用
     * count($container) 获取实体数量
     *
     * @return int 容器中实体数量
     */
    public function count(): int
    {
        return count($this->entities);
    }
}
