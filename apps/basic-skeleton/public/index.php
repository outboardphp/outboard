<?php

declare(strict_types=1);

use Outboard\Framework\Contract\ApplicationRunner;

require_once __DIR__ . '/../vendor/autoload.php';

// Technically, any PSR-11-compatible DI container can be used,
// but if you want to use a different one, you'll at least need
// to reimplement the framework config.

// Merge configs from framework and app into a single provider
$definitionProvider = new \Outboard\Di\OverrideableDefinitionProvider(
    frameworkProvider: new \Outboard\Framework\ConfigProvider(),
    appProvider: new \App\ConfigProvider(),
    overrideableDefinitionIds: \Outboard\Framework\ConfigProvider::OVERRIDEABLE_IDS,
);

$container = new \Outboard\Di\ContainerFactory($definitionProvider)->build();
$container->get(ApplicationRunner::class)->run();
