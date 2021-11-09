<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Common;

use Circli\ServerlessCore\Extension;
use Circli\ServerlessCore\Http\MiddlewareDispatcher;
use Polus\Adr\ActionDispatcher\HandlerActionDispatcher;
use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\Resolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class WrapperActionContainer implements ContainerInterface
{
    private ?ActionDispatcher $actionDispatcher = null;

    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function get(string $id): mixed
    {
        $entry = $this->container->get($id);

        if (!$entry instanceof Action) {
            return $entry;
        }

        if ($entry instanceof RequestHandlerInterface) {
            return $entry;
        }

        return $this->wrapAction($entry);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    private function wrapAction(Action $action): RequestHandlerInterface
    {
        if (!$this->actionDispatcher) {
            if ($this->container->has(Extension::SERVERLESS_DISPATCHER)) {
                $this->actionDispatcher = $this->container->get(Extension::SERVERLESS_DISPATCHER);
            }
            elseif ($this->container->has(ActionDispatcher::class)) {
                $this->actionDispatcher = $this->container->get(ActionDispatcher::class);
            }
            else {
                $this->actionDispatcher = HandlerActionDispatcher::default(
                    $this->container->get(Resolver::class),
                    $this->container->get(ResponseFactoryInterface::class),
                );
            }
        }
        return new WrapperActionHandler(
            $action,
            $this->actionDispatcher,
        );
    }
}
