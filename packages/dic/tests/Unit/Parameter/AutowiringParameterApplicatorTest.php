<?php

use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Parameter\AutowiringParameterApplicator;
use Outboard\Di\ValueObjects\Definition;

describe('AutowiringParameterApplicator', static function () {
    it('autowires callable type hints', function () {
        $applicator = new AutowiringParameterApplicator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return $id === stdClass::class; }
            public function get(string $id): mixed { return new stdClass(); }
        };

        $factory = $applicator->applyToCallable(
            static fn(stdClass $obj) => $obj,
            new Definition(),
            $container,
        );

        expect($factory())->toBeInstanceOf(stdClass::class);
    });

    it('throws for union types without explicit params', function () {
        $applicator = new AutowiringParameterApplicator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id): mixed { return null; }
        };

        expect(static fn() => $applicator->applyToCallable(
            static fn(stdClass|array $param) => $param,
            new Definition(),
            $container,
        ))->toThrow(ContainerException::class, 'union or intersect type');
    });
});

