<?php

use Outboard\Di\Parameter\ExplicitParameterApplicator;
use Outboard\Di\ValueObjects\Definition;

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
});

