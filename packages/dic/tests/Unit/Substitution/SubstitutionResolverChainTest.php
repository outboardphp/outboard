<?php

use Outboard\Di\Enums\SubstitutionMode;
use Outboard\Di\Substitution\SubstitutionResolverChain;
use Outboard\Di\ValueObjects\Definition;

describe('SubstitutionResolverChain', static function () {
    it('resolves callable substitutes', function () {
        $resolver = new SubstitutionResolverChain();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function get(string $id): mixed { return null; }
            public function has(string $id): bool { return false; }
        };

        $result = $resolver->resolve('service', new Definition(substitute: fn() => 'ok'), $container);

        expect($result->mode)->toBe(SubstitutionMode::Callable)
            ->and(($result->factory)())->toBe('ok');
    });

    it('resolves object substitutes as raw factories', function () {
        $resolver = new SubstitutionResolverChain();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function get(string $id): mixed { return null; }
            public function has(string $id): bool { return false; }
        };
        $object = (object) ['id' => 'raw'];

        $result = $resolver->resolve('service', new Definition(substitute: $object), $container);

        expect($result->mode)->toBe(SubstitutionMode::Raw)
            ->and(($result->factory)())->toBe($object);
    });

    it('resolves string substitutes to container aliases', function () {
        $resolver = new SubstitutionResolverChain();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function get(string $id): mixed { return 'alias-hit'; }
            public function has(string $id): bool { return $id === 'other'; }
        };

        $result = $resolver->resolve('service', new Definition(substitute: 'other'), $container);

        expect($result->mode)->toBe(SubstitutionMode::Raw)
            ->and(($result->factory)())->toBe('alias-hit');
    });

    it('resolves null substitutes to constructor mode', function () {
        $resolver = new SubstitutionResolverChain();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function get(string $id): mixed { return null; }
            public function has(string $id): bool { return false; }
        };

        $result = $resolver->resolve(stdClass::class, new Definition(), $container);

        expect($result->mode)->toBe(SubstitutionMode::Constructor)
            ->and($result->targetId)->toBe(stdClass::class);
    });
});
