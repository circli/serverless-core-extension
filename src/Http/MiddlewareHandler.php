<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareHandler implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @param MiddlewareInterface[]|class-string<MiddlewareInterface>[] $middlewares
     */
    public function __construct(
        private RequestHandlerInterface $handler,
        private array $middlewares,
        private ContainerInterface $container,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->handler);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Resolve middleware classes into instances using the container
        $middlewares = array_map(function (string|MiddlewareInterface $middleware): MiddlewareInterface {
            if ($middleware instanceof MiddlewareInterface) {
                return $middleware;
            }
            return $this->container->get($middleware);
        }, $this->middlewares);

        foreach (array_reverse($middlewares) as $middleware) {
            $handler = new class ($middleware, $handler) implements RequestHandlerInterface {
                public function __construct(
                    private MiddlewareInterface $middleware,
                    private RequestHandlerInterface $handler,
                ) {}

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->middleware->process($request, $this->handler);
                }
            };
        }

        // Invoke the root middleware
        return $handler->handle($request);
    }
}
