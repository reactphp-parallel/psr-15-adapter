<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use parallel\Channel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Worker\Worker as WorkerPool;
use ReactParallel\Streams\Factory;

use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

final class ReactMiddleware
{
    private Factory $streamFactory;

    private WorkerPool $workerPool;

    public function __construct(
        LoopInterface $loop,
        EventLoopBridge $eventLoopBridge,
        Factory $streamFactory,
        LowLevelPoolInterface $pool,
        MiddlewareInterface ...$middleware
    ) {
        $this->streamFactory = $streamFactory;
        $this->workerPool    = new WorkerPool(
            $loop,
            $eventLoopBridge,
            $pool,
            new WorkerFactory(
                ...$middleware
            ),
            (int) '13'
        );
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        return new Promise(function (callable $resolve, callable $reject) use ($request, $next): void {
            $input  = new Channel(Channel::Infinite);
            $output = new Channel(Channel::Infinite);

            $this->streamFactory->single($output)->then(static function (string $request): ServerRequestInterface {
                return unserialize($request);
            }, $reject)->then($next)->then(static function (ResponseInterface $response): string {
                return serialize($response);
            }, $reject)->then(static function (string $response) use ($input): void {
                $input->send($response);
            }, $reject);

            /** @psalm-suppress UndefinedInterfaceMethod */
            $this->workerPool->perform(new Work(new Request($request, $input, $output)))->then($resolve, $reject)->always(static function () use ($input, $output): void {
                $input->close();
                $output->close();
            });
        });
    }

    public function __destruct()
    {
        $this->workerPool->close();
    }
}
