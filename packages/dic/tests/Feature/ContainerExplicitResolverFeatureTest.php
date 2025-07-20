<?php

declare(strict_types=1);

use Outboard\Di\Container;
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

it('stores tags on the definition', function () {
    $definitions = [
        'tagged' => new Definition(
            substitute: fn() => new stdClass(),
            tags: ['service', 'important'],
        ),
    ];
    expect($definitions['tagged']->tags)->toContain('service')
        ->and($definitions['tagged']->tags)->toContain('important');
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
