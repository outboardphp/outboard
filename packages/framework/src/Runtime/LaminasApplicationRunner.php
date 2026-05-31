<?php

declare(strict_types=1);

namespace Outboard\Framework\Runtime;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Outboard\Framework\Contract\ApplicationRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LaminasApplicationRunner implements ApplicationRunner
{
    /**
     * A factory capable of generating a Psr\Http\Message\ServerRequestInterface instance.
     * The factory will not receive any arguments.
     *
     * @var callable(): ServerRequestInterface
     */
    private $serverRequestFactory;

    /**
     * A factory capable of generating an error response in the scenario that
     * the $serverRequestFactory raises an exception during generation of the
     * request instance.
     *
     * The factory will receive the Throwable or Exception that caused the error,
     * and must return a Psr\Http\Message\ResponseInterface instance.
     *
     * @var callable(\Throwable): ResponseInterface
     */
    private $serverRequestErrorResponseGenerator;

    public function __construct(
        private readonly RequestHandlerInterface $application,
        private readonly EmitterInterface $emitter,
        callable $serverRequestFactory,
        callable $serverRequestErrorResponseGenerator,
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->serverRequestErrorResponseGenerator = $serverRequestErrorResponseGenerator;
    }

    public function run(): void
    {
        new RequestHandlerRunner(
            handler: $this->application,
            emitter: $this->emitter,
            serverRequestFactory: $this->serverRequestFactory,
            serverRequestErrorResponseGenerator: $this->serverRequestErrorResponseGenerator,
        )->run();
    }
}
