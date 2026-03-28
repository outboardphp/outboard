<?php

use Outboard\Di\Resolver;
use Outboard\Di\Support\ClassExistsImplicitResolvablePolicy;

describe('ImplicitResolvablePolicy', static function () {
    it('allows implicit class resolution when class-exists policy is used', function () {
        $resolver = new Resolver(implicitResolvablePolicy: new ClassExistsImplicitResolvablePolicy());

        expect($resolver->has(stdClass::class))->toBeTrue();
    });

    it('does not implicitly resolve missing classes', function () {
        $resolver = new Resolver(implicitResolvablePolicy: new ClassExistsImplicitResolvablePolicy());

        expect($resolver->has('Definitely\\Missing\\Class'))->toBeFalse();
    });
});
