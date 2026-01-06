<?php

use Outboard\Di\Container;
use Outboard\Di\ExplicitResolver;
use Outboard\Di\ValueObjects\Definition;

describe('Resolver with regex and catch-all patterns', function () {
    it('matches ids with regex pattern', function () {
        $definitions = [
            '/^Service.*/' => new Definition(
                substitute: 'fallback',
            ),
            'fallback' => new Definition(
                substitute: fn() => (object) ['type' => 'service'],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        /** @var object{type: string} $result */
        $result = $container->get('ServiceFoo');

        expect($result->type)->toBe('service');
    });

    it('matches multiple ids with same regex', function () {
        $definitions = [
            '/Repository$/' => new Definition(
                substitute: 'repo-factory',
            ),
            'repo-factory' => new Definition(
                substitute: fn() => (object) ['type' => 'repository'],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        /** @var object{type: string} $userRepo */
        $userRepo = $container->get('UserRepository');
        /** @var object{type: string} $productRepo */
        $productRepo = $container->get('ProductRepository');

        expect($userRepo->type)->toBe('repository')
            ->and($productRepo->type)->toBe('repository');
    });

    it('prefers exact match over regex', function () {
        $definitions = [
            'SpecificService' => new Definition(
                substitute: fn() => (object) ['type' => 'specific'],
            ),
            '/Service$/' => new Definition(
                substitute: 'generic-factory',
            ),
            'generic-factory' => new Definition(
                substitute: fn() => (object) ['type' => 'generic'],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        /** @var object{type: string} $specific */
        $specific = $container->get('SpecificService');
        /** @var object{type: string} $generic */
        $generic = $container->get('OtherService');

        expect($specific->type)->toBe('specific')
            ->and($generic->type)->toBe('generic');
    });

    it('uses catch-all definition for classes', function () {
        $definitions = [
            '*' => new Definition(
                substitute: fn() => (object) ['type' => 'catch-all'],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        $result = $container->get(stdClass::class);

        expect($result->type)->toBe('catch-all');
    });

    it('catch-all definition works with substitute', function () {
        $definitions = [
            '*' => new Definition(
                substitute: 'fallback',
            ),
            'fallback' => new Definition(
                substitute: fn() => (object) ['type' => 'fallback'],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        /** @var object{type: string} $result */
        $result = $container->get('anything');

        expect($result->type)->toBe('fallback');
    });

    it('prefers specific definition over catch-all', function () {
        $definitions = [
            'specific' => new Definition(
                substitute: fn() => (object) ['type' => 'specific'],
            ),
            '*' => new Definition(
                substitute: fn() => (object) ['type' => 'catch-all'],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        /** @var object{type: string} $specific */
        $specific = $container->get('specific');
        $catchAll = $container->get(stdClass::class);

        expect($specific->type)->toBe('specific')
            ->and($catchAll->type)->toBe('catch-all');
    });

    it('regex pattern is case-sensitive by default', function () {
        $definitions = [
            '/^service/' => new Definition(
                substitute: fn() => (object) ['matched' => true],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        expect($container->has('serviceFoo'))->toBeTrue()
            ->and($container->has('ServiceFoo'))->toBeFalse();
    });

    it('regex pattern can use case-insensitive flag', function () {
        $definitions = [
            '/^service/i' => new Definition(
                substitute: fn() => (object) ['matched' => true],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        expect($container->has('serviceFoo'))->toBeTrue()
            ->and($container->has('ServiceFoo'))->toBeTrue();
    });

    it('catch-all only matches classes and interfaces when no substitute', function () {
        $definitions = [
            '*' => new Definition(
                // No substitute, so only matches existing classes
            ),
        ];
        $resolver = new ExplicitResolver($definitions);

        expect($resolver->has(stdClass::class))->toBeTrue()
            ->and($resolver->has('random-string-id'))->toBeFalse();
    });

    it('normalizes non-regex ids', function () {
        $definitions = [
            'MyService' => new Definition(
                substitute: fn() => (object) ['type' => 'service'],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);
        $container = new Container([$resolver]);

        // Should match because IDs are normalized to lowercase (unless regex)
        /** @var object{type: string} $result */
        $result = $container->get('myservice');

        expect($result->type)->toBe('service');
    });

    it('does not normalize regex patterns', function () {
        $definitions = [
            '/MyService/' => new Definition(
                substitute: 'matched-service',
            ),
            'matched-service' => new Definition(
                substitute: fn() => (object) ['matched' => true],
            ),
        ];
        $resolver = new ExplicitResolver($definitions);

        // Regex patterns should match case-sensitively
        expect($resolver->has('MyService'))->toBeTrue()
            ->and($resolver->has('myservice'))->toBeFalse();
    });
});
