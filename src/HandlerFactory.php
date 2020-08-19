<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use Psr\Http\Server\MiddlewareInterface;
use ReactParallel\Pool\Worker\Work\WorkerFactory;

use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

/**
 * @implements WorkerFactory<WorkRequest, Handler>
 */
final class HandlerFactory implements WorkerFactory
{
    private string $middleware;

    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->middleware = serialize($middleware);
    }

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function construct(): Handler
    {
        return new Handler(...unserialize($this->middleware));
    }
}
