<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Psr15Adapter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\Http\Message\Response;

/**
 * @internal
 */
final class ResponseMiddlewareStub implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new Response(
            200,
            [
                '__CLASS__' => self::class,
            ]
        );
    }
}
