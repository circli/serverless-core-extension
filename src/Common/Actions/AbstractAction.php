<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Common\Actions;

use Circli\WebCore\Common\Input\RawInput;
use Circli\WebCore\Common\Responder\ApiResponder;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\Resolver;
use Psr\Http\Message\ResponseFactoryInterface;

abstract class AbstractAction extends \Polus\Adr\Bref\Actions\AbstractAction
{
    public function __construct(
        Resolver $resolver,
        ResponseFactoryInterface $responseFactory,
        ActionDispatcher $actionDispatcher,
    ) {
        parent::__construct($resolver, $responseFactory, $actionDispatcher);
    }

    protected ?string $input = RawInput::class;
    protected ?string $responder = ApiResponder::class;
}
