<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use parallel\Channel;
use Psr\Http\Message\ServerRequestInterface;

final class Request
{
    private ServerRequestInterface $request;
    private string $input;
    private string $output;

    public function __construct(ServerRequestInterface $request, Channel $input, Channel $output)
    {
        $this->request = $request;
        $this->input   = (string) $input;
        $this->output  = (string) $output;
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    public function input(): Channel
    {
        return Channel::open($this->input);
    }

    public function output(): Channel
    {
        return Channel::open($this->output);
    }
}
