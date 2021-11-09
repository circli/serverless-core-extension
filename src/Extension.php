<?php declare(strict_types=1);

namespace Circli\ServerlessCore;

use Circli\Contracts\ExtensionInterface;
use Circli\Contracts\PathContainer;
use Circli\ServerlessCore\Http\MiddlewareDispatcher;
use Circli\WebCore\ActionResolver;
use Circli\WebCore\Exception\ExceptionHandler as DefaultExceptionHandler;
use Circli\WebCore\Middleware\Container as MiddlewareContainer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Polus\Adr\ActionDispatcher\HandlerActionDispatcher;
use Polus\Adr\ActionHandler\EventActionHandler;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\ExceptionHandler;
use Polus\Adr\Interfaces\Resolver;
use Psr\EventDispatcher\EventDispatcherInterface;
use function DI\autowire;
use function DI\get;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;

class Extension implements ExtensionInterface
{
    public const SERVERLESS_DISPATCHER = 'core.serverless.dispatcher';
    public const SERVERLESS_MIDDLEWARES = 'serverless.middlewares';

    /**
     * @return array<string, mixed>
     */
    public function configure(PathContainer $paths = null): array
    {
        return [
            'adr.relay_resolver' => function (ContainerInterface $container) {
                return function ($middleware) use ($container) {
                    if ($middleware instanceof MiddlewareInterface) {
                        return $middleware;
                    }

                    return $container->get($middleware);
                };
            },
            Psr17Factory::class => autowire(Psr17Factory::class),
            ResponseFactoryInterface::class => get(Psr17Factory::class),
            RequestFactoryInterface::class => get(Psr17Factory::class),
            Resolver::class => static function (ContainerInterface $container) {
                return new ActionResolver($container);
            },
            self::SERVERLESS_DISPATCHER => static function (ContainerInterface $container) {
                $resolver = $container->get(Resolver::class);
                $defaultDispatcher = HandlerActionDispatcher::default(
                    $resolver,
                    $container->get(ResponseFactoryInterface::class),
                );
                if ($container->has(ExceptionHandler::class)) {
                    $defaultDispatcher->setExceptionHandler($container->get(ExceptionHandler::class));
                }
                else {
                    $defaultDispatcher->setExceptionHandler(new DefaultExceptionHandler());
                }

                $defaultDispatcher->addHandler(new EventActionHandler(
                    $resolver,
                    $container->get(EventDispatcherInterface::class)
                ));

                if ($container->has(self::SERVERLESS_MIDDLEWARES) || $container->has('middlewares')) {
                    if ($container->has(self::SERVERLESS_MIDDLEWARES)) {
                        /** @var MiddlewareContainer $middlewareContainer */
                        $middlewareContainer = $container->get(self::SERVERLESS_MIDDLEWARES);
                    }
                    else {
                        /** @var MiddlewareContainer $middlewareContainer */
                        $middlewareContainer = $container->get('middlewares');
                    }
                    if (count($middlewareContainer)) {
                        $middlewares = iterator_to_array($middlewareContainer);
                        return new MiddlewareDispatcher(
                            $middlewares,
                            $defaultDispatcher,
                            $container,
                        );
                    }
                }

                return $defaultDispatcher;
            },
        ];
    }
}
