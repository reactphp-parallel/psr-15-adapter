# ReactPHP/HTTP PSR 15 adapter

![Continuous Integration](https://github.com/Reactphp-parallel/psr-15-adapter/workflows/Continuous%20Integration/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/React-parallel/psr-15-adapter/v/stable.png)](https://packagist.org/packages/React-parallel/psr-15-adapter)
[![Total Downloads](https://poser.pugx.org/React-parallel/psr-15-adapter/downloads.png)](https://packagist.org/packages/React-parallel/psr-15-adapter)
[![Code Coverage](https://scrutinizer-ci.com/g/Reactphp-parallel/psr-15-adapter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Reactphp-parallel/psr-15-adapter/?branch=master)
[![Type Coverage](https://shepherd.dev/github/Reactphp-parallel/psr-15-adapter/coverage.svg)](https://shepherd.dev/github/Reactphp-parallel/psr-15-adapter)
[![License](https://poser.pugx.org/React-parallel/psr-15-adapter/license.png)](https://packagist.org/packages/React-parallel/psr-15-adapter)

### Installation ###

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require react-parallel/psr-15-adapter 
```

# Usage

The middleware adapter accepts any PSR-15 middleware instance that meets for following criteria:
* Only uses scalars and userland classes
* Doesn't hold references to anything but scalars and userland classes
* Doesn't have internal state or relies on external state

```php
use ReactParallel\Factory as ParallelFactory;

$loop = Factory::create();
$factory = new ParallelFactory($loop);
$psr15Middleware = new ThePsr15MiddlewareOfYourChoice();
$otherPsr15Middleware = new TheOtherPsr15MiddlewareOfYourChoice();
$server = new React\Http\Server(
    $loop,
    new ReactMiddleware(
        $factory, 
        $psr15Middleware, 
        $otherPsr15Middleware
    )
);
```

## Contributing ##

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License ##

Copyright 2020 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
