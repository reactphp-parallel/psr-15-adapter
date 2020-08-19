<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr15Adapter;

use Ancarda\Psr7\StringStream\ReadOnlyStringStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Psr15Adapter\ReactMiddleware;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\PoolInfo\Info;

use function assert;
use function bin2hex;
use function implode;
use function iterator_to_array;
use function random_bytes;
use function React\Promise\resolve;

/**
 * @internal
 */
final class ReactMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function handle(): void
    {
        $rnd             = bin2hex(random_bytes(1024));
        $loop            = Factory::create();
        $eventLoopBridge = new EventLoopBridge($loop);
        $pool            = new Infinite($loop, $eventLoopBridge, 10);
        $stub            = new Psr15MiddlewareStub();
        $anotherStub     = new AnotherPsr15MiddlewareStub();
        $middleware      = new ReactMiddleware(new \ReactParallel\Factory($loop), $stub, $stub, $anotherStub);
        $request         = new ServerRequest('GET', 'https://example.com/');
        $request         = $request->withAttribute('body', $rnd);

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE  => 0,
            Info::SIZE  => 0,
        ], iterator_to_array($pool->info()));

        $deferred = new Deferred();
        $loop->futureTick(static function () use ($deferred, $middleware, $request): void {
            $deferred->resolve($middleware(
                $request,
                static function (ServerRequestInterface $request): PromiseInterface {
                    return resolve(new Response(666, [], new ReadOnlyStringStream($request->getAttribute('body'))));
                }
            ));
        });
        $response = $this->await($deferred->promise(), $loop, 9.9);
        assert($response instanceof ResponseInterface);

        self::assertSame(666, $response->getStatusCode());
        self::assertSame($rnd, $response->getBody()->getContents());
        self::assertSame(
            implode(', ', [AnotherPsr15MiddlewareStub::class, Psr15MiddlewareStub::class, Psr15MiddlewareStub::class]),
            $response->getHeaderLine('__CLASS__')
        );

        $middleware->__destruct();

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE  => 0,
            Info::SIZE  => 0,
        ], iterator_to_array($pool->info()));
    }
}
