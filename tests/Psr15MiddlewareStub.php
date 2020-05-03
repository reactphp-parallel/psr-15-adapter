<?php declare(strict_types=1);

namespace ReactParallel\Tests\Psr15Adapter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function Safe\sleep;

/**
 * @internal
 */
final class Psr15MiddlewareStub implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        sleep(1);
        return $handler->handle($request)->withAddedHeader('__CLASS__', __CLASS__);
    }
}
