<?php declare(strict_types=1);

namespace ReactParallel\Psr15Adapter;

use Opis\Closure\SerializableClosure;
use parallel\Channel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\PoolInterface;
use ReactParallel\Streams\Factory;
use Throwable;
use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;
use function React\Promise\reject;
use function React\Promise\resolve;

final class ReactMiddleware
{
    private Factory $streamFactory;

    private PoolInterface $runtime;

    private MiddlewareInterface $middleware;

    public function __construct(Factory $streamFactory, PoolInterface $pool, MiddlewareInterface $middleware)
    {
        $this->streamFactory = $streamFactory;
        $this->runtime       = $pool;
        $this->middleware    = $middleware;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        return new Promise(function (callable $resolve, callable $reject) use ($request, $next): void {
            $middleware = $this->middleware;

            $input  = new Channel(Channel::Infinite);
            $output = new Channel(Channel::Infinite);

            $this->streamFactory->single($output)->then(static function (string $request): ServerRequestInterface {
                return unserialize($request);
            }, $reject)->then($next)->then(static function (ResponseInterface $response): string {
                return serialize($response);
            }, $reject)->then(static function (string $response) use ($input): void {
                $input->send($response);
            }, $reject);

            $fun     = serialize(new SerializableClosure(static function (ServerRequestInterface $request, Channel $input, Channel $output) use ($middleware): PromiseInterface {
                try {
                    return resolve($middleware->process($request, new Psr15RequestHandlerAdapter($input, $output)));
                } catch (Throwable $throwable) {
                    return reject($throwable);
                }
            }));
            $request = serialize($request);
            $this->runtime->run(static function (string $fun, string $request, Channel $input, Channel $output): PromiseInterface {
                $request = unserialize($request);

                return resolve((unserialize($fun))($request, $input, $output));
            }, [$fun, $request, $input, $output])->then($resolve, $reject);
        });
    }
}
