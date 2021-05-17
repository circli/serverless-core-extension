<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Common\Actions;

use Circli\WebCore\Common\Responder\ApiResponder;

abstract class AbstractDomainAction extends \Polus\Adr\Bref\Actions\AbstractDomainAction
{
    protected ?string $responder = ApiResponder::class;
}
