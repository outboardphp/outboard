<?php

use Outboard\Di\Support\PostCallDecorator;
use Outboard\Di\ValueObject\Definition;

describe('PostCallDecorator', static function () {
    it('returns object unchanged when no post-call is configured', function () {
        $decorator = new PostCallDecorator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function get(string $id): mixed { return null; }
            public function has(string $id): bool { return false; }
        };
        $object = (object) ['plain' => true];

        $factory = $decorator->decorate(static function () use ($object) {
            return $object;
        }, new Definition(), $container);

        expect($factory())->toBe($object);
    });

    it('replaces object when post-call returns a value', function () {
        $decorator = new PostCallDecorator();
        $container = new class implements \Psr\Container\ContainerInterface {
            public function get(string $id): mixed { return null; }
            public function has(string $id): bool { return false; }
        };

        $factory = $decorator->decorate(
            static fn() => (object) ['plain' => true],
            new Definition(call: static fn() => (object) ['decorated' => true]),
            $container,
        );

        expect($factory()->decorated)->toBeTrue();
    });
});
