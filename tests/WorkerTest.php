<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr15Adapter;

use parallel\Channel;
use Psr\Http\Message\ServerRequestInterface;
use ReactParallel\Psr15Adapter\Request;
use ReactParallel\Psr15Adapter\Response;
use ReactParallel\Psr15Adapter\Work;
use ReactParallel\Psr15Adapter\WorkerFactory;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

use function assert;
use function implode;

/**
 * @internal
 */
final class WorkerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function handle(): void
    {
        $channel                = new Channel(Channel::Infinite);
        $request                = $this->prophesize(ServerRequestInterface::class)->reveal();
        $responseMiddlewareStub = new ResponseMiddlewareStub();
        $stub                   = new Psr15MiddlewareStub();
        $response               = (new WorkerFactory($stub, $stub, $responseMiddlewareStub))->construct()->perform(new Work(new Request($request, $channel, $channel)));
        assert($response instanceof Response);
        self::assertSame(200, $response->result()->getStatusCode());
        self::assertSame(
            implode(', ', [ResponseMiddlewareStub::class, Psr15MiddlewareStub::class, Psr15MiddlewareStub::class]),
            $response->result()->getHeaderLine('__CLASS__')
        );
    }
}
