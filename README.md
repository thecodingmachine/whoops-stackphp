Whoops middleware for StackPHP
==============================

This package contains a [StackPHP middleware](http://stackphp.com/) that catches all exceptions and redirects those to the [Whoops error handling library](http://filp.github.io/whoops/).

Installation
------------

Through [Composer](https://getcomposer.org/) as [mouf/whoops-stackphp](https://packagist.org/packages/mouf/whoops-stackphp).

Usage
-----

Simply use the `WhoopsMiddleWare` class in your middleware stack:

```php
use Whoops\StackPhp\WhoopsMiddleWare;

$router = new WhoopsMiddleWare(
	new MyOtherRouter(
		new YetAnotherRouter()));

```

If an exception is thrown, or an error is raised, Whoops will display a nice error message:

[![Sample error screen](http://filp.github.io/whoops/screen.png)](http://filp.github.io/whoops/demo/)

The `WhoopsMiddleWare` constructor accepts 3 parameters:

```php
public function __construct(HttpKernelInterface $router, $catchExceptions = true, $catchErrors = true);
```

- **$router**: this is the next router to be called on the Stack
- **$catchExceptions**: Set to true to catch exception. Set to false to ignore exceptions (for production servers)
- **$catchErrors**: Set to true to catch raised errors. Set to false to ignore exceptions (for production servers)

Note: `$catchExceptions` and `$catchErrors` can be passed a boolean, a callable (that returns a boolean) or a [ConditionInterface](http://mouf-php.com/packages/mouf/utils.common.conditioninterface/README.md) that evaluates to true or false.

