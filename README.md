# DI
一个简单而实用的`psr-11`依赖注入容器。
A simple dependency injection container for psr-11.


## 安装
使用 `composer` 进行安装：
```
composer require luoluolzb/di
```


## 使用
### 实例化
使用容器之前需要实例化：
```php
<?php
use luoluolzb\di\Container;

$container = new Container();
```


### 注入实体
使用`set($id, $entity)`方法注入一个实体，实体可以是一个函数（注入服务），可以是一个类实例（不推荐直接注入类实例，应该使用在函数中注册），还可以是其他任何类型：
```php
// 注入匿名函数
$container->set('logger', function($c) {
    $logger = new Monolog\Logger;
    $logger->pushHandler(new Monolog\Handler\StreamHandler('app.log', Logger::WARNING));
    return $logger;
});

// 注入类实例
$container->set('myClass', new MyClass());

// 注入数组
$container->set('configs', ([
    'db' => [
        'type' => 'mysql',
        'host' => 'localhost',
        'dbname' => 'test',
        'port' => '3306',
        'user' => 'root',
        'password' => '123456',
    ],
]);

// 注入标量
$container->set('isExists', true);
```

如果注入的实体为一个匿名函数，那么我们可以使用匿名函数的第一个参数访问容器实例：
```php
$container->set('pdo', function($c) {
    // $c 为容器实例
    $c = $c->get('configs')['db'];
    $dsn = "{$c['type']}:host={$c['host']};port={$c['port']};dbname={$c['dbname']};";
    return new PDO($dsn, $c['user'], $c['password']);
});
```

还可以在实例化的时候一次注入多个实体：
```php
$container = new Container([
    'logger' => function($c) {
        $logger = new Monolog\Logger;
        $logger->pushHandler(new Monolog\Handler\StreamHandler('app.log', Logger::WARNING));
        return $logger;
    },

    'myClass' => new MyClass(),

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
```


### 工厂方式注入实体
使用 `set` 或者数组式注入实体时，每次取出的都为同一对象实例，想要每次取出时都重新创建实例使用 `factory` 方法：
```php
// 注入匿名函数
$container->factory('myClass', function($c) {
    return MyClass();
});
```

如果是通过构造函数一次注入了多个实体，想要使用工厂方式需提供第二个参数指定实体：
```php
$container = new Container([
    'logger' => function($c) {
        $logger = new Monolog\Logger;
        $logger->pushHandler(new Monolog\Handler\StreamHandler('app.log', Logger::WARNING));
        return $logger;
    },

    'myClass' => function($c) {
        return MyClass();
    },
], [
    'logger' => true,
]);
```

这样就将 `myClass` 指定为工厂创建模式，其他的仍然为单一实例模式。


### 获取实体
使用`get($id)`方法从容器中获取一个实体：
```php
$logger = $container->get('logger');
$logger->info('some info');
```

注意：如果要取出的实体为一个匿名函数或者其他可调用的类型，那么该匿名函数每次取出时都会调用。如果需要使用单例模式应该这样注入：
```php
$container->set('db', function($c) {
    return Db::getInstance();
});
```

如果要取出的实体为其他类型（非可调用类型），那么会直接返回原来注入的实体。


### 删除实体
从容器中删除一个实体使用`delete($id)`方法：
```php
$container->delete('logger');
```


### 判断实体是否存在
判断容器中是否存在某个实体使用`has($id)`方法：
```php
if ($container->has('logger')) {
    // ...
} else {
    // ...
}
```


### 像数组一样访问容器
容器实现了`ArrayAccess`接口，因此可以像访问数组的方式来访问容器：
```php
// 注入实体
$container['configs'] = [
    'db' => [
        'type' => 'mysql',
        'host' => 'localhost',
        'dbname' => 'test',
        'port' => '3306',
        'user' => 'root',
        'password' => '123456',
    ],
];

// 获取实体
$dbConf = $container['configs']['db'];

// 删除实体
unset($container['configs']);

// 判断实体是否存在
$ret = isset($container['configs']);
```


### 容器中实体数量
使用容器的`count()`方法获取容器中实体数量：
```php
$count = $container->count();
```

容器实现了`Countable`接口，还可以直接使用PHP的`count()`函数（）获取实体数量：
```php
$count = count($container);
```
