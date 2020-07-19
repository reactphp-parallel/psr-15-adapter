<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use parallel\Channel;
use Psr\Http\Message\ServerRequestInterface;

final class Request
{
    private ServerRequestInterface $request;
    private Channel $input;
    private Channel $output;

    public function __construct(ServerRequestInterface $request, Channel $input, Channel $output)
    {
        $this->request = $request;
        $this->input   = $input;
        $this->output  = $output;
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    public function input(): Channel
    {
        return $this->input;
    }

    public function output(): Channel
    {
        return $this->output;
    }
}
