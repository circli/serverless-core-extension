<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Common\Actions;

use Circli\ServerlessCore\Common\Responder\NotFoundResponder;

final class NotFoundAction extends AbstractAction
{
    protected ?string $responder = NotFoundResponder::class;
}
