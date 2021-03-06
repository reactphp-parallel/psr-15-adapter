<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use ReactParallel\Pool\Worker\Work\Work;

final class WorkRequest implements Work
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function work(): Request
    {
        return $this->request;
    }
}
