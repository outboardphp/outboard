<?php

use Outboard\Di\Matching\DefinitionMatcher;
use Outboard\Di\Matching\ExactMatchStrategy;
use Outboard\Di\Matching\RegexMatchStrategy;
use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\Support\RegexPatternMatcher;
use Outboard\Di\Tests\Fixtures\DefinitionMatcherBaseService;
use Outboard\Di\Tests\Fixtures\DefinitionMatcherChildService;
use Outboard\Di\ValueObject\Definition;

describe('DefinitionMatcher', static function () {
    it('matches exact ids case-insensitively', function () {
        $matcher = new DefinitionMatcher();
        $definitions = [
            'myservice' => new Definition(substitute: 'fallback'),
        ];

        $match = $matcher->match('MyService', $definitions);

        expect($match)->not->toBeNull()
            ->and($match?->definitionId)->toBe('myservice');
    });

    it('preserves per-definition precedence for subclass and regex matching', function () {
        $matcher = new DefinitionMatcher();
        $definitions = [
            '/Service$/' => new Definition(substitute: 'regex-hit'),
            DefinitionMatcherBaseService::class => new Definition(
                strict: false,
                substitute: 'subclass-hit',
            ),
        ];

        $match = $matcher->match(DefinitionMatcherChildService::class, $definitions);

        expect($match)->not->toBeNull()
            ->and($match?->definitionId)->toBe('/Service$/');
    });

    it('uses catch-all only for class or interface ids when substitute is missing', function () {
        $matcher = new DefinitionMatcher();
        $definitions = [
            '*' => new Definition(),
        ];

        $classMatch = $matcher->match(stdClass::class, $definitions);
        $stringMatch = $matcher->match('anything', $definitions);

        expect($classMatch)->not->toBeNull()
            ->and($classMatch?->definitionId)->toBe('*')
            ->and($stringMatch)->toBeNull();
    });

    it('uses catch-all for any id when substitute is set', function () {
        $matcher = new DefinitionMatcher();
        $definitions = [
            '*' => new Definition(substitute: 'fallback'),
        ];

        $match = $matcher->match('anything', $definitions);

        expect($match)->not->toBeNull()
            ->and($match?->definitionId)->toBe('*');
    });

    it('accepts an explicitly shared regex matcher without leaking it through the normalizer', function () {
        $regexPatternMatcher = new class extends RegexPatternMatcher {
            public function isPattern(string $pattern): bool
            {
                return $pattern === 'custom-pattern';
            }

            public function matches(string $pattern, string $subject): bool
            {
                return $pattern === 'custom-pattern' && $subject === 'TargetService';
            }
        };

        $matcher = new DefinitionMatcher(
            exactMatch: new ExactMatchStrategy(new DefinitionIdNormalizer($regexPatternMatcher)),
            regexMatch: new RegexMatchStrategy($regexPatternMatcher),
        );

        $match = $matcher->match('TargetService', [
            'custom-pattern' => new Definition(substitute: 'hit'),
        ]);

        expect($match)->not->toBeNull()
            ->and($match?->definitionId)->toBe('custom-pattern');
    });
});
