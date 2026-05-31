<?php

declare(strict_types=1);

use Outboard\Framework\ConfigProvider;
use Outboard\Framework\Contract\ApplicationRunner;
use Outboard\Framework\Runtime\LaminasApplicationRunner;
use Outboard\Framework\Runtime\NullApplicationRunner;

it('selects the expected application runner substitute', function () {
    $definitions = (new ConfigProvider())->getDefinitions();

    $hasLaminasRunner = class_exists('Laminas\\HttpHandlerRunner\\RequestHandlerRunner');

    $expectedRunnerSubstitute = $hasLaminasRunner
        ? LaminasApplicationRunner::class
        : NullApplicationRunner::class;

    expect($definitions[ApplicationRunner::class]->substitute)->toBe($expectedRunnerSubstitute);
});

it('marks runtime seam ids as overrideable', function () {
    expect(ConfigProvider::OVERRIDEABLE_IDS)
        ->toContain(ApplicationRunner::class)
        ->toContain(LaminasApplicationRunner::class)
        ->toContain('$serverRequestFactory')
        ->toContain('$serverRequestErrorResponseGenerator');
});

it('wires laminas runner constructor arguments through definition params', function () {
    $definitions = (new ConfigProvider())->getDefinitions();
    $laminasRunner = $definitions[LaminasApplicationRunner::class];

    expect($laminasRunner->withParams)->toBe([
        'application' => \Outboard\Framework\Application::class,
        'serverRequestFactory' => '$serverRequestFactory',
        'serverRequestErrorResponseGenerator' => '$serverRequestErrorResponseGenerator',
    ]);
});
