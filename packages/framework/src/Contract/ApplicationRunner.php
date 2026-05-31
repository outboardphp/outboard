<?php

declare(strict_types=1);

namespace Outboard\Framework\Contract;

interface ApplicationRunner
{
    /**
     * Bootstrap and run the application.
     */
    public function run(): void;
}
