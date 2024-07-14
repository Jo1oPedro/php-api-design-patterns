<?php

declare(strict_types=1);

namespace App\Http\Middleware\ContentNegotiation;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ContentTypeMiddleware implements MiddlewareInterface
{
    public function __construct(
       private ContentTypeNegotiator $contentTypeNegotiator
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $request = $this->contentTypeNegotiator->negotiate($request);

        $response = $handler->handle($request);

        return $response->withHeader(
            "Content-Type",
            $request->getAttribute("content-type")->value
        );
    }
}