<?php

use Outboard\Di\Container;
use Outboard\Di\ExplicitResolver;
use Outboard\Di\AutowiringResolver;
use Outboard\Di\ValueObjects\Definition;

describe('Container::call()', function () {
    it('calls a closure with no parameters', function () {
        $container = new Container([new ExplicitResolver()]);
        $result = $container->call(fn() => 'hello');

        expect($result)->toBe('hello');
    });

    it('calls a closure with explicit positional params', function () {
        $container = new Container([new ExplicitResolver()]);
        $result = $container->call(
            fn($a, $b) => $a + $b,
            [10, 20],
        );

        expect($result)->toBe(30);
    });

    it('calls a closure with explicit named params', function () {
        $container = new Container([new ExplicitResolver()]);
        $result = $container->call(
            fn($a, $b) => $a . $b,
            ['b' => 'world', 'a' => 'hello'],
        );

        expect($result)->toBe('helloworld');
    });

    it('resolves type-hinted params from container', function () {
        $definitions = [
            stdClass::class => new Definition(
                substitute: fn() => (object) ['value' => 42],
            ),
        ];
        $container = new Container([new ExplicitResolver($definitions)]);

        $result = $container->call(fn(stdClass $obj) => $obj->value);

        expect($result)->toBe(42);
    });

    it('mixes explicit and container-resolved params', function () {
        $definitions = [
            stdClass::class => new Definition(
                substitute: fn() => (object) ['value' => 100],
            ),
        ];
        $container = new Container([new ExplicitResolver($definitions)]);

        $result = $container->call(
            fn($multiplier, stdClass $obj) => $obj->value * $multiplier,
            [2],
        );

        expect($result)->toBe(200);
    });

    it('uses named param over container resolution', function () {
        $definitions = [
            stdClass::class => new Definition(
                substitute: fn() => (object) ['value' => 'from container'],
            ),
        ];
        $container = new Container([new ExplicitResolver($definitions)]);

        $override = (object) ['value' => 'explicit'];
        $result = $container->call(
            fn(stdClass $obj) => $obj->value,
            ['obj' => $override],
        );

        expect($result)->toBe('explicit');
    });

    it('throws exception when required param cannot be resolved', function () {
        $container = new Container([new ExplicitResolver()]);

        expect(fn() => $container->call(fn($required) => $required))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'must be manually supplied');
    });

    it('uses default value for optional params that cannot be resolved', function () {
        $container = new Container([new ExplicitResolver()]);

        $result = $container->call(fn($optional = 'default') => $optional ?? 'was null');

        // When a param cannot be resolved and is optional, it's set to the default value
        expect($result)->toBe('default');
    });

    it('resolves first available class from union type', function () {
        $definitions = [
            ConcreteClass::class => new Definition(
                substitute: fn() => new ConcreteClass('resolved'),
            ),
        ];
        $container = new Container([new ExplicitResolver($definitions)]);

        $result = $container->call(function (NonExistentInterface|ConcreteClass $param) {
            return $param->value;
        });

        expect($result)->toBe('resolved');
    });

    it('throws when no class in union type can be resolved and param is required', function () {
        $container = new Container([new ExplicitResolver()]);

        expect(fn() => $container->call(
            fn(NonExistentInterface|AnotherNonExistent $param) => $param,
        ))->toThrow(\Outboard\Di\Exception\ContainerException::class, 'Unable to resolve');
    });

    it('uses null when no class in union type can be resolved and param is optional', function () {
        $container = new Container([new ExplicitResolver()]);

        $result = $container->call(
            fn(NonExistentInterface|AnotherNonExistent|null $param = null) => $param ?? 'was null',
        );

        expect($result)->toBe('was null');
    });

    it('can call array callable', function () {
        $definitions = [
            CallableClass::class => new Definition(),
        ];
        $container = new Container([new ExplicitResolver($definitions)]);

        $instance = $container->get(CallableClass::class);
        $result = $container->call([$instance, 'method'], ['value' => 'test']);

        expect($result)->toBe('test');
    });

    it('can call invokable object', function () {
        $container = new Container([new ExplicitResolver()]);
        $invokable = new InvokableClass();

        $result = $container->call($invokable, ['message' => 'hello']);

        expect($result)->toBe('hello');
    });

    it('resolves nested dependencies in call', function () {
        $definitions = [
            stdClass::class => new Definition(shared: true, substitute: fn() => new stdClass()),
            CallableClass::class => new Definition(),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        $obj1 = $container->get(stdClass::class);
        $callable = $container->get(CallableClass::class);

        // The shared stdClass should be the same instance
        expect($callable->dependency)->toBe($obj1);
    });
});

interface NonExistentInterface {}
interface AnotherNonExistent {}

class ConcreteClass
{
    public function __construct(
        public string $value,
    ) {}
}

class CallableClass
{
    public function __construct(
        public ?stdClass $dependency = null,
    ) {}

    public function method(string $value): string
    {
        return $value;
    }
}

class InvokableClass
{
    public function __invoke(string $message): string
    {
        return $message;
    }
}
