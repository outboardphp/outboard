<?php

declare(strict_types=1);

use Outboard\Framework\Application;
use Outboard\Framework\Contract\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

it('implements the PSR-15 request handler contract', function () {
    $router = new class implements RouterInterface {
        public function dispatch(ServerRequestInterface $request): ResponseInterface
        {
            return $this->response;
        }

        public ResponseInterface $response;
    };

    $router->response = $this->createStub(ResponseInterface::class);
    $application = new Application($router);

    expect($application)->toBeInstanceOf(RequestHandlerInterface::class);
});

it('dispatches the incoming request through the router', function () {
    $request = $this->createStub(ServerRequestInterface::class);
    $response = $this->createStub(ResponseInterface::class);

    $router = new class ($request, $response) implements RouterInterface {
        public function __construct(
            private readonly ServerRequestInterface $expectedRequest,
            private readonly ResponseInterface $response,
        ) {
        }

        public function dispatch(ServerRequestInterface $request): ResponseInterface
        {
            expect($request)->toBe($this->expectedRequest);

            return $this->response;
        }
    };

    $application = new Application($router);

    expect($application->handle($request))->toBe($response);
});
