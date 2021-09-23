<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Common;

use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\ActionDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WrapperActionHandler implements RequestHandlerInterface
{
    public function __construct(
        private Action $action,
        private ActionDispatcher $actionDispatcher,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->actionDispatcher->dispatch($this->action, $request);
    }
}
