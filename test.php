<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Socket\Server;
use ReactParallel\FutureToPromiseConverter\FutureToPromiseConverter;
use ReactParallel\Psr15Adapter\ReactMiddleware;
use ReactParallel\Runtime\Runtime;
use WyriHaximus\Psr15\Cat\CatMiddleware;

require_once 'vendor/autoload.php';

$loop = Factory::create();
$socket = new Server('0.0.0.0:1337', $loop);
$http = new \React\Http\Server([
    new ReactMiddleware($loop, Runtime::create(new FutureToPromiseConverter($loop)), new CatMiddleware(true)),
    static function (ServerRequestInterface $request): ResponseInterface {
        return new Response(200, [], 'Hoi!');
    }
]);
$http->on('error', function (Throwable $throwable) {
    echo $throwable;
});

$http->listen($socket);

$loop->addSignal(SIGINT, function () use ($loop) {
    $loop->stop();
});
$loop->addSignal(SIGKILL, function () use ($loop) {
    $loop->stop();
});

$loop->run();
