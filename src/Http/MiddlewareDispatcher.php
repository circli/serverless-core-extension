<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Http;

use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\ActionDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareDispatcher implements ActionDispatcher
{
    /**
     * @param MiddlewareInterface[]|class-string<MiddlewareInterface>[] $middlewares
     */
    public function __construct(
        private array $middlewares,
        private ActionDispatcher $actionDispatcher,
        private ContainerInterface $container,
    ) {}

    public function dispatch(Action $action, ServerRequestInterface $request): ResponseInterface
    {
        if (!count($this->middlewares)) {
            return $this->actionDispatcher->dispatch($action, $request);
        }

        $middlewareHandler = new MiddlewareHandler(new class ($action, $this->actionDispatcher) implements RequestHandlerInterface {
            public function __construct(
                private Action $action,
                private ActionDispatcher $dispatcher,
            ) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->dispatcher->dispatch($this->action, $request);
            }
        }, $this->middlewares, $this->container);

        return $middlewareHandler->handle($request);
    }
}
