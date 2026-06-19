<?php

declare(strict_types=1);

use Outboard\Di\Substitution\SubstitutionResolverChain;
use Outboard\Di\Substitution\CallableSubstitutionHandler;
use Outboard\Di\Substitution\ObjectSubstitutionHandler;
use Outboard\Di\Substitution\StringSubstitutionHandler;
use Outboard\Di\Substitution\NullSubstitutionHandler;
use Outboard\Di\Enum\SubstitutionMode;
use Outboard\Di\ValueObject\Definition;

describe('SubstitutionResolverChain', static function () {
    it('selects correct handler based on substitute type', function () {
        $chain = new SubstitutionResolverChain([
            new CallableSubstitutionHandler(),
            new ObjectSubstitutionHandler(),
            new StringSubstitutionHandler(),
            new NullSubstitutionHandler(),
        ]);

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return false;
            }
            public function get(string $id)
            {
                return null;
            }
        };

        // Test callable substitute
        $resolution = $chain->resolve('id', new Definition(substitute: fn() => 'result'), $container);
        expect($resolution->mode)->toBe(SubstitutionMode::Callable);

        // Test object substitute
        $resolution = $chain->resolve('id', new Definition(substitute: new \stdClass()), $container);
        expect($resolution->mode)->toBe(SubstitutionMode::Raw);

        // Test string substitute (class)
        $resolution = $chain->resolve('id', new Definition(substitute: 'stdClass'), $container);
        expect($resolution->mode)->toBe(SubstitutionMode::Constructor);

        // Test null substitute
        $resolution = $chain->resolve('id', new Definition(substitute: null), $container);
        expect($resolution->mode)->toBe(SubstitutionMode::Constructor);
    });

    it('throws when no handler matches', function () {
        $chain = new SubstitutionResolverChain([
            new CallableSubstitutionHandler(),
        ]);

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return false;
            }
            public function get(string $id)
            {
                return null;
            }
        };

        expect(fn() => $chain->resolve('id', new Definition(substitute: 123), $container))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'No substitution handler');
    });
});

describe('CallableSubstitutionHandler', static function () {
    it('handles callable substitutes', function () {
        $handler = new CallableSubstitutionHandler();

        expect($handler->canHandle(new Definition(substitute: fn() => 'result')))->toBeTrue()
            ->and($handler->canHandle(new Definition(substitute: 'stdClass')))->toBeFalse()
            ->and($handler->canHandle(new Definition(substitute: null)))->toBeFalse();
    });

    it('resolves callable substitute', function () {
        $handler = new CallableSubstitutionHandler();
        $definition = new Definition(substitute: fn($a) => $a * 2);

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return $id === 'value';
            }
            public function get(string $id)
            {
                return 21;
            }
        };

        $resolution = $handler->resolve('id', $definition, $container);

        expect($resolution->mode)->toBe(SubstitutionMode::Callable)
            ->and($resolution->factory)->toBeCallable();
    });
});

describe('ObjectSubstitutionHandler', static function () {
    it('handles object substitutes', function () {
        $handler = new ObjectSubstitutionHandler();
        $obj = new \stdClass();
        $obj->value = 'test';

        expect($handler->canHandle(new Definition(substitute: $obj)))->toBeTrue()
            ->and($handler->canHandle(new Definition(substitute: fn() => $obj)))->toBeFalse()
            ->and($handler->canHandle(new Definition(substitute: 'stdClass')))->toBeFalse()
            ->and($handler->canHandle(new Definition(substitute: null)))->toBeFalse();
    });

    it('resolves object substitute', function () {
        $handler = new ObjectSubstitutionHandler();
        $obj = new \stdClass();
        $obj->value = 'stored';
        $definition = new Definition(substitute: $obj);

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return false;
            }
            public function get(string $id)
            {
                return null;
            }
        };

        $resolution = $handler->resolve('id', $definition, $container);

        expect($resolution->mode)->toBe(SubstitutionMode::Raw)
            ->and(($resolution->factory)())->toBe($obj);
    });
});

describe('StringSubstitutionHandler', static function () {
    it('handles string substitutes', function () {
        $handler = new StringSubstitutionHandler();

        expect($handler->canHandle(new Definition(substitute: 'SomeClass')))->toBeTrue()
            ->and($handler->canHandle(new Definition(substitute: fn() => 'result')))->toBeFalse();
    });

    it('resolves to Raw mode when string references existing container entry', function () {
        $handler = new StringSubstitutionHandler();
        $definition = new Definition(substitute: 'existing-service');

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return $id === 'existing-service';
            }
            public function get(string $id)
            {
                return 'resolved';
            }
        };

        $resolution = $handler->resolve('id', $definition, $container);

        expect($resolution->mode)->toBe(SubstitutionMode::Raw);
    });

    it('resolves to Constructor mode when string is a class name', function () {
        $handler = new StringSubstitutionHandler();
        $definition = new Definition(substitute: \stdClass::class);

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return false;
            }
            public function get(string $id)
            {
                return null;
            }
        };

        $resolution = $handler->resolve('id', $definition, $container);

        expect($resolution->mode)->toBe(SubstitutionMode::Constructor)
            ->and($resolution->targetId)->toBe(\stdClass::class);
    });

    it('throws for non-existent class and container entry', function () {
        $handler = new StringSubstitutionHandler();
        $definition = new Definition(substitute: 'NonExistentClass');

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return false;
            }
            public function get(string $id)
            {
                return null;
            }
        };

        expect(fn() => $handler->resolve('id', $definition, $container))
            ->toThrow(\Outboard\Di\Exception\NotFoundException::class);
    });
});

describe('NullSubstitutionHandler', static function () {
    it('handles null substitutes', function () {
        $handler = new NullSubstitutionHandler();

        expect($handler->canHandle(new Definition(substitute: null)))->toBeTrue()
            ->and($handler->canHandle(new Definition(substitute: 'SomeClass')))->toBeFalse();
    });

    it('resolves to Constructor mode with targetId', function () {
        $handler = new NullSubstitutionHandler();
        $definition = new Definition(substitute: null);

        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool
            {
                return false;
            }
            public function get(string $id)
            {
                return null;
            }
        };

        $resolution = $handler->resolve('TargetClass', $definition, $container);

        expect($resolution->mode)->toBe(SubstitutionMode::Constructor)
            ->and($resolution->targetId)->toBe('TargetClass');
    });
});
