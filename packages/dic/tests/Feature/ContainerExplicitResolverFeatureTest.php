<?php

declare(strict_types=1);

use Outboard\Di\Container;
use Outboard\Di\ContainerFactory;
use Outboard\Di\ExplicitResolver;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\Enums\Scope;

it('returns the same instance for singleton scope', function () {
    $definitions = [
        'foo' => new Definition(singleton: true, substitute: fn() => new stdClass()),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $a = $container->get('foo');
    $b = $container->get('foo');
    expect($a)->toBe($b);
});

it('returns different instances for prototype scope', function () {
    $definitions = [
        'bar' => new Definition(singleton: false, substitute: fn() => (object) ['prop1' => 'val']),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $a = $container->get('bar');
    $b = $container->get('bar');
    expect($a)->not()->toBe($b);
});

it('passes withParams to the factory', function () {
    $definitions = [
        'baz' => new Definition(
            substitute: fn($x, $y) => [$x, $y],
            withParams: [1, 2],
        ),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $result = $container->get('baz');
    expect($result)->toBe([1, 2]);
});

it('decorates the instance using a call that returns', function () {
    $definitions = [
        'qux' => new Definition(
            substitute: fn() => new stdClass(),
            call: fn($obj) => (object) ['decorated' => true],
        ),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $result = $container->get('qux');
    // @phpstan-ignore-next-line
    expect($result->decorated)->toBeTrue();
});

it('handles property cascade correctly', function () {
    $definitions = [
        'combo' => new Definition(
            singleton: Scope::Singleton,
            substitute: fn($x) => (object) ['x' => $x],
            withParams: [42],
            call: fn($obj) => (object) ['x' => $obj->x, 'decorated' => true],
        ),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $result1 = $container->get('combo');
    $result2 = $container->get('combo');
    expect($result1)->toBe($result2)
        ->and($result1->x)->toBe(42)
        ->and($result1->decorated)->toBeTrue();
});

it('instantiates a real class id', function () {
    $definitions = [
        TestClass::class => new Definition(
            withParams: ['Test Name', 123],
        ),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $result = $container->get(TestClass::class);
    expect($result)->toBeInstanceOf(TestClass::class)
        ->and($result->name)->toBe('Test Name')
        ->and($result->value)->toBe(123);
});

it('instantiates a real class id with a substitute class id', function () {
    $definitions = [
        TestClass::class => new Definition(
            strict: true,
            substitute: AnotherTestClass::class,
            withParams: ['Test Name', 123],
        ),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $result = $container->get(TestClass::class);
    expect($result)->toBeInstanceOf(AnotherTestClass::class);
});

it('instantiates a real class id with a substitute arbitrary id', function () {
    $defProv = new class implements \Outboard\Di\Contracts\DefinitionProvider {
        public function getDefinitions(): array
        {
            return [
                TestClass::class => new Definition(
                    strict: true, // Required here to avoid circular reference
                    substitute: 'another',
                ),
                'another' => new Definition(
                    substitute: AnotherTestClass::class,
                    withParams: ['Test Name', 123],
                ),
            ];
        }
    };
    $container = new ContainerFactory($defProv, [ExplicitResolver::class])();
    $result = $container->get(TestClass::class);
    expect($result)->toBeInstanceOf(AnotherTestClass::class);
});

it('detects circular dependencies', function () {
    $defProv = new class implements \Outboard\Di\Contracts\DefinitionProvider {
        public function getDefinitions(): array
        {
            return [
                TestClass::class => new Definition(
                    substitute: 'another',
                ),
                'another' => new Definition(
                    substitute: AnotherTestClass::class,
                    withParams: ['Test Name', 123],
                ),
            ];
        }
    };
    expect(fn() => new ContainerFactory($defProv, [ExplicitResolver::class])())
        ->toThrow(Outboard\Di\Exception\ContainerException::class);
});


it('honors definition inheritance', function () {
    $definitions = [
        TestClass::class => new Definition(
            withParams: ['Test Name', 123],
        ),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    $result = $container->get(AnotherTestClass::class);
    expect($result)->toBeInstanceOf(AnotherTestClass::class)
        ->and($result->name)->toBe('Test Name')
        ->and($result->value)->toBe(123);
});

it('honors definition non-inheritance', function () {
    $definitions = [
        TestClass::class => new Definition(
            strict: true,
            withParams: ['Test Name', 123],
        ),
    ];
    $container = new Container([
        new ExplicitResolver($definitions),
    ]);
    expect(fn() => $container->get(AnotherTestClass::class))
        ->toThrow(Outboard\Di\Exception\NotFoundException::class);
});

class TestClass
{
    public function __construct(
        public string $name,
        public int $value,
    ) {}
}

class AnotherTestClass extends TestClass {}
