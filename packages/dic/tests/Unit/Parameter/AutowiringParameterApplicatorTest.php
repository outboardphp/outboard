<?php

use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Parameter\AutowiringParameterApplicator;
use Outboard\Di\ValueObject\Definition;

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

    it('applies numeric withParams to unresolved builtin params after class typehint resolution', function () {
        $applicator = new AutowiringParameterApplicator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return $id === stdClass::class; }
            public function get(string $id): mixed { return new stdClass(); }
        };

        $factory = $applicator->applyToCallable(
            static fn(stdClass $obj, int $count) => $count,
            new Definition(withParams: [42]),
            $container,
        );

        expect($factory())->toBe(42);
    });

    it('prefers named params, then class typehint resolution, then numeric fallback', function () {
        $applicator = new AutowiringParameterApplicator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return $id === stdClass::class; }
            public function get(string $id): mixed { return new stdClass(); }
        };

        $factory = $applicator->applyToCallable(
            static fn(string $name, stdClass $obj, int $retries) => [$name, $obj::class, $retries],
            new Definition(withParams: ['name' => 'worker', 3]),
            $container,
        );

        expect($factory())->toBe(['worker', stdClass::class, 3]);
    });

    it('throws when extra numeric withParams remain unused', function () {
        $applicator = new AutowiringParameterApplicator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id): mixed { return null; }
        };

        expect(static fn() => $applicator->applyToCallable(
            static fn(string $name) => $name,
            new Definition(withParams: ['alice', 'extra']),
            $container,
        ))->toThrow(ContainerException::class, 'were not consumed');
    });
});

