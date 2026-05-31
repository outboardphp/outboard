<?php

declare(strict_types=1);

namespace Outboard\Framework\Runtime;

use LogicException;
use Outboard\Framework\Contract\ApplicationRunner;

class NullApplicationRunner implements ApplicationRunner
{
    public function run(): void
    {
        throw new LogicException('No application runner implementation is configured.');
    }
}
