<?php

declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use parallel\Channel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

final class Psr15RequestHandlerAdapter implements RequestHandlerInterface
{
    private Channel $input;
    private Channel $output;

    public function __construct(Channel $input, Channel $output)
    {
        $this->input  = $input;
        $this->output = $output;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->output->send(serialize($request));

        return unserialize($this->input->recv());
    }
}
