<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use Psr\Http\Server\MiddlewareInterface;
use ReactParallel\Pool\Worker\Work;
use ReactParallel\Pool\Worker\Work\Worker as WorkerContract;

use function assert;

final class Worker implements WorkerContract
{
    private MiddlewareInterface $middleware;

    public function __construct(MiddlewareInterface $middleware)
    {
        $this->middleware = $middleware;
    }

    public function perform(Work $workWrapper): Response
    {
        $work = $workWrapper->work();
        assert($work instanceof Request);
        $request = $work->request();
        $input   = $work->input();
        $output  = $work->output();

        return new Response($this->middleware->process($request, new Psr15RequestHandlerAdapter($input, $output)));
    }
}
