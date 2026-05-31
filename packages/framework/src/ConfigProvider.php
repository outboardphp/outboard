<?php

declare(strict_types=1);

namespace Outboard\Framework;

use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\ValueObject\Definition;
use Outboard\Framework\Contract\ApplicationRunner;
use Outboard\Framework\Contract\ResponseEmitter;
use Outboard\Framework\Contract\RouterInterface;
use Outboard\Framework\Runtime\DefaultServerRequestErrorResponseGenerator;
use Outboard\Framework\Runtime\LaminasApplicationRunner;
use Outboard\Framework\Runtime\NullApplicationRunner;
use Outboard\Framework\Runtime\NoopResponseEmitter;
use Outboard\Framework\Runtime\NullRouter;
use Outboard\Framework\Runtime\NullServerRequestErrorResponseGenerator;
use Outboard\Framework\Runtime\NullServerRequestFactory;

class ConfigProvider implements DefinitionProvider
{
    /**
     * Framework defaults may be replaced by app-level providers.
     *
     * @var string[]
     */
    public const array OVERRIDEABLE_IDS = [
        Application::class,
        ApplicationRunner::class,
        LaminasApplicationRunner::class,
        RouterInterface::class,
        ResponseEmitter::class,
        'Laminas\\HttpHandlerRunner\\Emitter\\EmitterInterface',
        '$serverRequestFactory',
        '$serverRequestErrorResponseGenerator',
    ];

    /**
     * @return array<string, Definition>
     */
    public function getDefinitions(): array
    {
        $hasLaminasRunner = \class_exists('Laminas\\HttpHandlerRunner\\RequestHandlerRunner');
        $hasDiactoros = \class_exists('Laminas\\Diactoros\\ServerRequestFactory');

        $runnerSubstitute = $hasLaminasRunner
            ? LaminasApplicationRunner::class
            : NullApplicationRunner::class;

        $serverRequestFactory = $hasDiactoros
            ? 'Laminas\\Diactoros\\ServerRequestFactory::fromGlobals'
            : static fn() => new NullServerRequestFactory()();

        $errorResponseGeneratorSubstitute = $hasDiactoros
            ? DefaultServerRequestErrorResponseGenerator::class
            : NullServerRequestErrorResponseGenerator::class;

        // The most basic things we need are a DI container object, its configuration, an application handler,
        // and a bootstrap runner.
        // We may want a Builder class to provide the DI config in order to simplify it, because I want to keep the
        // essential bits of config out of the app skeleton.
        // Configs we need to provide inside the framework:
        // - DI container
        // - Router
        // - PSR-17 factory
        // - PSR-15 middleware pieces
        // - PSR-7 request and response objects
        // - PSR-14 event dispatcher
        //
        // Configs the user needs to provide: (we should suggest)
        // - DB connections
        // - Routes
        // - Templating engine
        // - ORM
        // - Logger
        return [
            Application::class => new Definition(shared: true),
            ApplicationRunner::class => new Definition(shared: true, substitute: $runnerSubstitute),
            LaminasApplicationRunner::class => new Definition(
                shared: true,
                withParams: [
                    'application' => Application::class,
                    'serverRequestFactory' => '$serverRequestFactory',
                    'serverRequestErrorResponseGenerator' => '$serverRequestErrorResponseGenerator',
                ]
            ),
            RouterInterface::class => new Definition(shared: true, substitute: NullRouter::class),
            ResponseEmitter::class => new Definition(shared: true, substitute: NoopResponseEmitter::class),
            // The following are broken out to allow app configs to easily override them if needed.
            'Laminas\\HttpHandlerRunner\\Emitter\\EmitterInterface' => new Definition(
                shared: true,
                substitute: 'Laminas\\HttpHandlerRunner\\Emitter\\SapiEmitter',
            ),
            '$serverRequestFactory' => new Definition(
                shared: true,
                substitute: static fn() => $serverRequestFactory,
            ),
            '$serverRequestErrorResponseGenerator' => new Definition(
                shared: true,
                substitute: static fn() => static fn($error) => new $errorResponseGeneratorSubstitute()($error),
            ),
        ];
    }
}
