<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use Psr\Http\Server\MiddlewareInterface;
use ReactParallel\Pool\Worker\Work\Work;
use ReactParallel\Pool\Worker\Work\Worker;

use function array_reverse;

/**
 * @implements Worker<WorkRequest>
 */
final class Handler implements Worker
{
    /** @var MiddlewareInterface[] */
    private array $middleware;

    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->middleware = $middleware;
    }

    public function perform(Work $workWrapper): Response
    {
        $work    = $workWrapper->work();
        $request = $work->request();
        $input   = $work->input();
        $output  = $work->output();

        $requestHandler = new Psr15RequestHandlerAdapter($input, $output);
        foreach (array_reverse($this->middleware) as $middleware) {
            $requestHandler = new Next($middleware, $requestHandler);
        }

        return new Response($requestHandler->handle($request));
    }
}
