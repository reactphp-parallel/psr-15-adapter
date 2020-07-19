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
        $channel  = new Channel(Channel::Infinite);
        $request  = $this->prophesize(ServerRequestInterface::class)->reveal();
        $stub     = new ResponseMiddlewareStub();
        $response = (new WorkerFactory($stub))->construct()->perform(new Work(new Request($request, $channel, $channel)));
        assert($response instanceof Response);
        self::assertSame(200, $response->result()->getStatusCode());
    }
}
