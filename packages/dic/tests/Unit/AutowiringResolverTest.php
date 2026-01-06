<?php

use Outboard\Di\AutowiringResolver;
use Outboard\Di\Container;
use Outboard\Di\ValueObjects\Definition;

describe('AutowiringResolver', function () {
    it('has() returns true for existing classes', function () {
        $resolver = new AutowiringResolver();

        expect($resolver->has(stdClass::class))->toBeTrue();
    });

    it('has() returns false for non-existent classes', function () {
        $resolver = new AutowiringResolver();

        expect($resolver->has('NonExistentClass'))->toBeFalse();
    });

    it('autowires a simple class constructor', function () {
        $definitions = [];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        $result = $container->get(SimpleAutowiredClass::class);

        expect($result)->toBeInstanceOf(SimpleAutowiredClass::class)
            ->and($result->dependency)->toBeInstanceOf(stdClass::class);
    });

    it('autowires nested dependencies', function () {
        $resolver = new AutowiringResolver();
        $container = new Container([$resolver]);

        $result = $container->get(NestedDependency::class);

        expect($result)->toBeInstanceOf(NestedDependency::class)
            ->and($result->simple)->toBeInstanceOf(SimpleAutowiredClass::class)
            ->and($result->simple->dependency)->toBeInstanceOf(stdClass::class);
    });

    it('mixes autowiring with explicit params by position', function () {
        $definitions = [
            MixedParamsClass::class => new Definition(
                withParams: ['explicit value'],
            ),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        $result = $container->get(MixedParamsClass::class);

        expect($result)->toBeInstanceOf(MixedParamsClass::class)
            ->and($result->name)->toBe('explicit value')
            ->and($result->dependency)->toBeInstanceOf(stdClass::class);
    });

    it('mixes autowiring with explicit params by name', function () {
        $definitions = [
            MixedParamsClass::class => new Definition(
                withParams: ['name' => 'named value'],
            ),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        $result = $container->get(MixedParamsClass::class);

        expect($result->name)->toBe('named value')
            ->and($result->dependency)->toBeInstanceOf(stdClass::class);
    });

    it('autowires callables with type hints', function () {
        $definitions = [
            'callable' => new Definition(
                substitute: fn(stdClass $obj) => $obj,
            ),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        $result = $container->get('callable');

        expect($result)->toBeInstanceOf(stdClass::class);
    });

    it('throws exception for union type parameters', function () {
        $definitions = [
            'callable' => new Definition(
                substitute: fn(stdClass|array $param) => $param,
            ),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        expect(fn() => $container->get('callable'))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'union or intersect type');
    });

    it('throws exception for builtin type without default', function () {
        $definitions = [
            BuiltinTypeClass::class => new Definition(),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        expect(fn() => $container->get(BuiltinTypeClass::class))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'without class type hint');
    });

    it('uses default value for builtin types', function () {
        $resolver = new AutowiringResolver();
        $container = new Container([$resolver]);

        $result = $container->get(DefaultValueClass::class);

        expect($result)->toBeInstanceOf(DefaultValueClass::class)
            ->and($result->dependency)->toBeInstanceOf(stdClass::class)
            ->and($result->name)->toBe('default');
    });

    it('resolves params that reference container ids', function () {
        $definitions = [
            'my_object' => new Definition(
                substitute: fn() => (object)['value' => 42],
            ),
            WithContainerRef::class => new Definition(
                withParams: ['obj' => 'my_object'],
            ),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        $result = $container->get(WithContainerRef::class);

        expect($result->obj->value)->toBe(42);
    });

    it('handles classes with no constructor', function () {
        $resolver = new AutowiringResolver();
        $container = new Container([$resolver]);

        $result = $container->get(stdClass::class);

        expect($result)->toBeInstanceOf(stdClass::class);
    });

    it('autowires closure parameters', function () {
        $definitions = [
            'closure' => new Definition(
                substitute: function (SimpleAutowiredClass $obj) {
                    return $obj->dependency;
                },
            ),
        ];
        $resolver = new AutowiringResolver($definitions);
        $container = new Container([$resolver]);

        $result = $container->get('closure');

        expect($result)->toBeInstanceOf(stdClass::class);
    });
});

class SimpleAutowiredClass
{
    public function __construct(
        public stdClass $dependency,
    ) {}
}

class NestedDependency
{
    public function __construct(
        public SimpleAutowiredClass $simple,
    ) {}
}

class MixedParamsClass
{
    public function __construct(
        public string $name,
        public stdClass $dependency,
    ) {}
}

class BuiltinTypeClass
{
    public function __construct(
        public string $name,
    ) {}
}

class DefaultValueClass
{
    public function __construct(
        public stdClass $dependency,
        public string $name = 'default',
    ) {}
}

class WithContainerRef
{
    public function __construct(
        public object $obj,
    ) {}
}

