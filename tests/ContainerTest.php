<?php
namespace luoluolzb\di\tests;

use PHPUnit\Framework\TestCase;
use luoluolzb\di\Container;
use luoluolzb\di\exception\EntityNotFoundException;
use luoluolzb\di\tests\testClass\A as ClassA;
use luoluolzb\di\tests\testClass\B as ClassB;
use luoluolzb\di\tests\testClass\C as ClassC;


class ContainerTest extends TestCase
{
    /**
     * 测试在容器中注入各种数组类型并获取
     */
    public function testSetAndGet()
    {
        $container = new Container();

        // 注入匿名函数（服务）
        $container->set('myClass', function ($c) {
            return new \stdClass();
        });
        $myClass = $container->get('myClass');
        $this->assertInstanceOf(\stdClass::class, $myClass);

        // 注入可调用的类
        $container->set('callableClass', new Class {
            public function __invoke(Container $container)
            {
                return new \stdClass();
            }
        });
        $class = $container->get('callableClass');
        $this->assertInstanceOf(\stdClass::class, $class);

        // 注入类实例
        $stdClass = new \stdClass();
        $container->set('stdClass', $stdClass);
        $this->assertEquals($stdClass, $stdClass);

        // 注入数组
        $configs = [
            'db' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'dbname' => 'test',
                'port' => '3306',
                'user' => 'root',
                'password' => '123456',
            ]
        ];

        // 注入标量类型
        $container->set('configs', $configs);
        $this->assertEquals($configs, $container->get('configs'));

        $container->set('number', 123);
        $this->assertEquals(123, $container->get('number'));

        $container->set('isGood', true);
        $this->assertTrue($container->get('isGood'));
    }

    /**
     * 测试可计数
     *
     * @depends testSetAndGet
     */
    public function testCountAble()
    {
        $container = new Container();
        $container->set('C', new ClassC());
        $container->set('B', new ClassB($container->get('C')));
        $container->set('A', new ClassA($container->get('B')));
        $this->assertCount(3, $container);
        $this->assertEquals(3, $container->count());
    }

    /**
     * 测试删除
     *
     * @depends testCountAble
     */
    public function testHasAndDelete()
    {
        $container = new Container();
        $this->assertFalse($container->has('name'));

        $container->set('name', 'zhangsan');
        $this->assertTrue($container->has('name'));

        $container->delete('name');
        $this->assertFalse($container->has('name'));
    }

    /**
     * 测试数组式访问
     *
     * @depends testHasAndDelete
     */
    public function testArrayAccess()
    {
        $container = new Container();
        // isset
        $this->assertFalse(isset($container['name']));

        // set
        $container['name'] = $name = 'container';
        $this->assertTrue(isset($container['name']));

        // get
        $this->assertEquals($name, $container['name']);

        // unset
        unset($container['name']);
        $this->assertFalse(isset($container['name']));
    }

    /**
     * 测试类注入及其类之间的依赖
     *
     * @depends testHasAndDelete
     */
    public function testClassDI()
    {
        $container = new Container();

        $container->set('A', function (Container $c) {
            return new ClassA($c->get('B'));
        });
        $container->set('B', function (Container $c) {
            return new ClassB($c->get('C'));
        });
        $container->set('C', function (Container $c) {
            return new ClassC();
        });

        $this->assertEquals('CBA', $container->get('A')->doSomething());
    }

    /**
     * 测试注入服务
     *
     * @depends testHasAndDelete
     */
    public function testService()
    {
        $container = new Container();
        $container->set('absService', function ($container) {
            return new Class {
                public function __invoke(float $x)
                {
                    return $x >= 0 ? $x : -$x;
                }
            };
        });
        $absService = $container->get('absService');
        $this->assertTrue(123 == $absService(123));
        $this->assertTrue(2.5 == $absService(-2.5));
//        $this->expected($absService('world'));
    }

    /**
     * 测试数组注入和pdo服务注入
     *
     * @depends testHasAndDelete
     */
    public function testPdo()
    {
        $container = new Container([
            'configs' => [
                'db' => [
                    'type' => 'mysql',
                    'host' => 'localhost',
                    'dbname' => 'test',
                    'port' => '3306',
                    'user' => 'root',
                    'password' => '123456',
                ],
            ],
        ]);

        $container->set('pdo', function (Container $c) {
            $c = $c->get('configs')['db'];
            $dsn = "{$c['type']}:host={$c['host']};port={$c['port']};dbname={$c['dbname']};";
            return new \PDO($dsn, $c['user'], $c['password']);
        });

        $this->assertInstanceOf(\PDO::class, $container->get('pdo'));
        $this->expectException(EntityNotFoundException::class);
        $good = $container['good'];
    }
}

