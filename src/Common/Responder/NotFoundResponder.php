<?php declare(strict_types=1);

namespace Circli\ServerlessCore\Common\Responder;

use Circli\WebCore\Common\Responder\JsonResponderTrait;
use PayloadInterop\DomainPayload;
use Polus\Adr\EmptyDomainPayload;
use Polus\Adr\Interfaces\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NotFoundResponder implements Responder
{
    use JsonResponderTrait;

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        DomainPayload $payload,
    ): ResponseInterface {
        if ($payload instanceof EmptyDomainPayload) {
            return $response->withStatus(404);
        }
        return $this
            ->jsonEncode($response, $payload)
            ->withStatus(404);
    }
}
