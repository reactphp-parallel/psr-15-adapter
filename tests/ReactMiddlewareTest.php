<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr15Adapter;

use Ancarda\Psr7\StringStream\ReadOnlyStringStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Promise\PromiseInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Psr15Adapter\ReactMiddleware;
use ReactParallel\Streams\Factory as StreamFactory;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\PoolInfo\Info;

use function assert;
use function bin2hex;
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
        $middleware      = new ReactMiddleware(new StreamFactory($eventLoopBridge), $pool, $stub, $loop, $eventLoopBridge);
        $request         = new ServerRequest('GET', 'https://example.com/');
        $request         = $request->withAttribute('body', $rnd);

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE  => 0,
            Info::SIZE  => 0,
        ], iterator_to_array($pool->info()));

        $promise = $middleware(
            $request,
            static function (ServerRequestInterface $request): PromiseInterface {
                return resolve(new Response(666, [], new ReadOnlyStringStream($request->getAttribute('body'))));
            }
        );

        self::assertSame([
            Info::TOTAL => 1,
            Info::BUSY => 1,
            Info::CALLS => 0,
            Info::IDLE  => 0,
            Info::SIZE  => 1,
        ], iterator_to_array($pool->info()));

        $response = $this->await($promise, $loop, 3.3);
        assert($response instanceof ResponseInterface);

        self::assertSame([
            Info::TOTAL => 1,
            Info::BUSY => 1,
            Info::CALLS => 0,
            Info::IDLE  => 0,
            Info::SIZE  => 1,
        ], iterator_to_array($pool->info()));

        self::assertSame(666, $response->getStatusCode());
        self::assertSame($rnd, $response->getBody()->getContents());
        self::assertSame(Psr15MiddlewareStub::class, $response->getHeaderLine('__CLASS__'));

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
