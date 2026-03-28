<?php

use Outboard\Di\Support\RegexPatternMatcher;

describe('RegexPatternMatcher', static function () {
    it('detects valid regex patterns without warnings', function () {
        $matcher = new RegexPatternMatcher();

        expect($matcher->isPattern('/^service/i'))->toBeTrue()
            ->and($matcher->isPattern('service'))->toBeFalse();
    });

    it('matches regex patterns against subjects', function () {
        $matcher = new RegexPatternMatcher();

        expect($matcher->matches('/^service/i', 'ServiceFoo'))->toBeTrue()
            ->and($matcher->matches('/^service/', 'ServiceFoo'))->toBeFalse();
    });
});
