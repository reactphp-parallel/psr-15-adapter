<?php declare(strict_types=1);

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
        $rnd = bin2hex(random_bytes(1024));
        $loop = Factory::create();
        $eventLoopBridge = new EventLoopBridge($loop);
        $pool = new Infinite($loop, $eventLoopBridge, 10);
        $stub = new Psr15MiddlewareStub();
        $middleware = new ReactMiddleware(new StreamFactory($eventLoopBridge), $pool, $stub);
        $request = new ServerRequest('GET', 'https://example.com/');
        $request = $request->withAttribute('body', $rnd);

        $promise = $middleware(
            $request,
            static function (ServerRequestInterface $request): PromiseInterface {
                return resolve(new Response(666, [], new ReadOnlyStringStream($request->getAttribute('body'))));
            }
        );

        /** @var ResponseInterface $response */
        $response = $this->await($promise, $loop, 3.3);

        self::assertSame(666, $response->getStatusCode());
        self::assertSame($rnd, $response->getBody()->getContents());
        self::assertSame(Psr15MiddlewareStub::class, $response->getHeaderLine('__CLASS__'));
    }
}
