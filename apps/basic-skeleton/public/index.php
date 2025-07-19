<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

$config = require '../config/di.php';

// Technically, any PSR-11-compatible DI container can be used,
// but implementing a different one is on you.
new \Outboard\Di\Container([
    new \Outboard\Di\ExplicitResolver($config),
])
    ->get(\Outboard\Framework\Application::class)();
