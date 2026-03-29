<?php

use Outboard\Di\Container;
use Outboard\Di\Resolver;
use Outboard\Di\Parameter\ExplicitParameterApplicator;
use Outboard\Di\ValueObject\Definition;

describe('ExplicitParameterApplicator', static function () {
    it('passes explicit params to callable factories', function () {
        $applicator = new ExplicitParameterApplicator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id): mixed { return null; }
        };

        $factory = $applicator->applyToCallable(
            static fn($a, $b) => $a + $b,
            new Definition(withParams: [2, 3]),
            $container,
        );

        expect($factory())->toBe(5);
    });

    it('resolves container references in withParams', function () {
        $applicator = new ExplicitParameterApplicator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return $id === 'dep'; }
            public function get(string $id): mixed { return (object) ['ok' => true]; }
        };

        $factory = $applicator->applyToCallable(
            static fn($dep) => $dep,
            new Definition(withParams: ['dep']),
            $container,
        );

        expect($factory()->ok)->toBeTrue();
    });

    it('delegates callable invocation to Container::call() when available', function () {
        $applicator = new ExplicitParameterApplicator();
        $container = new Container([
            new Resolver([
                stdClass::class => new Definition(substitute: static fn() => new stdClass()),
            ]),
        ]);

        $factory = $applicator->applyToCallable(
            static fn(stdClass $obj) => $obj,
            new Definition(),
            $container,
        );

        expect($factory())->toBeInstanceOf(stdClass::class);
    });

    it('prefers explicit withParams over Container::call typehint resolution', function () {
        $applicator = new ExplicitParameterApplicator();
        $container = new Container([
            new Resolver([
                stdClass::class => new Definition(substitute: static function () {
                    $obj = new stdClass();
                    $obj->source = 'container';
                    return $obj;
                }),
            ]),
        ]);

        $explicit = new stdClass();
        $explicit->source = 'explicit';

        $factory = $applicator->applyToCallable(
            static fn(stdClass $obj) => $obj,
            new Definition(withParams: ['obj' => $explicit]),
            $container,
        );

        expect($factory()->source)->toBe('explicit');
    });
});

