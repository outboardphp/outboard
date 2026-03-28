<?php

use Outboard\Di\InstanceCache;
use Outboard\Di\Exception\NotFoundException;

describe('InstanceCache', function () {
    it('starts with empty caches', function () {
        $cache = new InstanceCache();

        expect($cache->hasShared('anything'))->toBeFalse()
            ->and($cache->hasFactory('anything'))->toBeFalse()
            ->and($cache->count())->toBe(0);
    });

    it('stores and retrieves shared instances', function () {
        $cache = new InstanceCache();
        $instance = new stdClass();
        $instance->value = 'test';

        $cache->setShared('service', $instance);

        expect($cache->hasShared('service'))->toBeTrue()
            ->and($cache->getShared('service'))->toBe($instance)
            ->and($cache->getShared('service')->value)->toBe('test');
    });

    it('stores and retrieves factories', function () {
        $cache = new InstanceCache();
        $factory = fn() => (object)['value' => 'from factory'];

        $cache->setFactory('service', $factory);

        expect($cache->hasFactory('service'))->toBeTrue()
            ->and($cache->getFactory('service'))->toBe($factory)
            ->and($cache->getFactory('service')()->value)->toBe('from factory');
    });

    it('returns same shared instance on multiple gets', function () {
        $cache = new InstanceCache();
        $instance = new stdClass();

        $cache->setShared('service', $instance);

        $first = $cache->getShared('service');
        $second = $cache->getShared('service');

        expect($first)->toBe($second)
            ->and($first)->toBe($instance);
    });

    it('throws when getting non-existent shared instance', function () {
        $cache = new InstanceCache();

        expect(fn() => $cache->getShared('nonexistent'))
            ->toThrow(NotFoundException::class, "Shared instance 'nonexistent' not found in cache");
    });

    it('throws when getting non-existent factory', function () {
        $cache = new InstanceCache();

        expect(fn() => $cache->getFactory('nonexistent'))
            ->toThrow(NotFoundException::class, "Factory for 'nonexistent' not found in cache");
    });

    it('clears specific service from both caches', function () {
        $cache = new InstanceCache();

        $cache->setShared('service1', new stdClass());
        $cache->setFactory('service2', fn() => new stdClass());

        expect($cache->hasShared('service1'))->toBeTrue()
            ->and($cache->hasFactory('service2'))->toBeTrue();

        $cache->clear('service1');

        expect($cache->hasShared('service1'))->toBeFalse()
            ->and($cache->hasFactory('service2'))->toBeTrue();
    });

    it('clears all cached items', function () {
        $cache = new InstanceCache();

        $cache->setShared('service1', new stdClass());
        $cache->setShared('service2', new stdClass());
        $cache->setFactory('service3', fn() => new stdClass());

        expect($cache->count())->toBe(3);

        $cache->clearAll();

        expect($cache->count())->toBe(0)
            ->and($cache->hasShared('service1'))->toBeFalse()
            ->and($cache->hasShared('service2'))->toBeFalse()
            ->and($cache->hasFactory('service3'))->toBeFalse();
    });

    it('returns list of shared instance IDs', function () {
        $cache = new InstanceCache();

        $cache->setShared('service1', new stdClass());
        $cache->setShared('service2', new stdClass());

        $ids = $cache->getSharedIds();

        expect($ids)->toContain('service1')
            ->and($ids)->toContain('service2')
            ->and(count($ids))->toBe(2);
    });

    it('returns list of factory IDs', function () {
        $cache = new InstanceCache();

        $cache->setFactory('factory1', fn() => new stdClass());
        $cache->setFactory('factory2', fn() => new stdClass());

        $ids = $cache->getFactoryIds();

        expect($ids)->toContain('factory1')
            ->and($ids)->toContain('factory2')
            ->and(count($ids))->toBe(2);
    });

    it('counts total cached items', function () {
        $cache = new InstanceCache();

        expect($cache->count())->toBe(0);

        $cache->setShared('service1', new stdClass());
        expect($cache->count())->toBe(1);

        $cache->setShared('service2', new stdClass());
        expect($cache->count())->toBe(2);

        $cache->setFactory('factory1', fn() => new stdClass());
        expect($cache->count())->toBe(3);
    });

    it('handles overwriting shared instances', function () {
        $cache = new InstanceCache();

        $first = (object)['value' => 1];
        $second = (object)['value' => 2];

        $cache->setShared('service', $first);
        expect($cache->getShared('service')->value)->toBe(1);

        $cache->setShared('service', $second);
        expect($cache->getShared('service')->value)->toBe(2);
    });

    it('handles overwriting factories', function () {
        $cache = new InstanceCache();

        $cache->setFactory('service', fn() => (object)['value' => 1]);
        expect($cache->getFactory('service')()->value)->toBe(1);

        $cache->setFactory('service', fn() => (object)['value' => 2]);
        expect($cache->getFactory('service')()->value)->toBe(2);
    });

    it('distinguishes between shared and factory for same ID', function () {
        $cache = new InstanceCache();

        // You can theoretically have both (though Container wouldn't do this)
        $cache->setShared('service', (object)['type' => 'shared']);
        $cache->setFactory('service', fn() => (object)['type' => 'factory']);

        expect($cache->hasShared('service'))->toBeTrue()
            ->and($cache->hasFactory('service'))->toBeTrue()
            ->and($cache->getShared('service')->type)->toBe('shared')
            ->and($cache->getFactory('service')()->type)->toBe('factory');
    });

    it('clear removes from both shared and factory caches', function () {
        $cache = new InstanceCache();

        $cache->setShared('service', new stdClass());
        $cache->setFactory('service', fn() => new stdClass());

        expect($cache->count())->toBe(2);

        $cache->clear('service');

        expect($cache->count())->toBe(0)
            ->and($cache->hasShared('service'))->toBeFalse()
            ->and($cache->hasFactory('service'))->toBeFalse();
    });
});

