<?php

use Outboard\Di\ContainerFactory;
use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\EmptyDefinitionProvider;
use Outboard\Di\OverrideableDefinitionProvider;
use Outboard\Di\ValueObject\Definition;
use Outboard\Framework\ConfigProvider;
use Outboard\Framework\Contract\ApplicationRunner;
use Outboard\Framework\Runtime\LaminasApplicationRunner;
use Outboard\Framework\Runtime\NullApplicationRunner;

function buildFrameworkContainer(?DefinitionProvider $appProvider = null): \Psr\Container\ContainerInterface
{
    $mergedProvider = new OverrideableDefinitionProvider(
        frameworkProvider: new ConfigProvider(),
        appProvider: $appProvider ?? new EmptyDefinitionProvider(),
        overrideableDefinitionIds: ConfigProvider::OVERRIDEABLE_IDS,
    );

    return new ContainerFactory($mergedProvider)->build();
}

it('provides the expected default application runner for the current dependency set', function () {
    $runner = buildFrameworkContainer()->get(ApplicationRunner::class);
    $hasLaminasRunner = class_exists('Laminas\\HttpHandlerRunner\\RequestHandlerRunner');

    if ($hasLaminasRunner) {
        expect($runner)->toBeInstanceOf(LaminasApplicationRunner::class);
        return;
    }

    expect($runner)->toBeInstanceOf(NullApplicationRunner::class);
    expect(static fn() => $runner->run())
        ->toThrow(\LogicException::class, 'No application runner implementation is configured.');
});

it('allows apps to customize behavior by overriding the application runner dependency', function () {
    $appProvider = new class implements DefinitionProvider {
        public function getDefinitions(): array
        {
            return [
                ApplicationRunner::class => new Definition(
                    shared: true,
                    substitute: new class implements ApplicationRunner {
                        public function run(): void
                        {
                            echo 'App runner matched.';
                        }
                    },
                ),
            ];
        }
    };

    $runner = buildFrameworkContainer($appProvider)->get(ApplicationRunner::class);

    ob_start();
    $runner->run();
    $output = ob_get_clean();

    expect($output)->toBe('App runner matched.');
});
