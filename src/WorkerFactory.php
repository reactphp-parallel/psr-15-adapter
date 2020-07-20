<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use Psr\Http\Server\MiddlewareInterface;
use ReactParallel\Pool\Worker\Work\Worker as WorkerContract;
use ReactParallel\Pool\Worker\Work\WorkerFactory as WorkerFactoryContract;

use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

final class WorkerFactory implements WorkerFactoryContract
{
    private string $middleware;

    public function __construct(MiddlewareInterface $middleware)
    {
        $this->middleware = serialize($middleware);
    }

    public function construct(): WorkerContract
    {
        return new Worker(unserialize($this->middleware));
    }
}
