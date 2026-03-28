<?php

use Outboard\Di\Support\DefinitionIdNormalizer;

describe('DefinitionIdNormalizer', static function () {
    it('normalizes non-regex ids', function () {
        $normalizer = new DefinitionIdNormalizer();

        $normalized = $normalizer->normalizeDefinitionId('\\My\\Service');

        expect($normalized)->toBe('my\\service');
    });

    it('preserves regex patterns and catch-all ids', function () {
        $normalizer = new DefinitionIdNormalizer();

        $regex = $normalizer->normalizeDefinitionId('/^Service.*/');
        $catchAll = $normalizer->normalizeDefinitionId('*');

        expect($regex)->toBe('/^Service.*/')
            ->and($catchAll)->toBe('*');
    });

    it('detects regex patterns through the matcher collaborator', function () {
        $normalizer = new DefinitionIdNormalizer();

        expect($normalizer->isRegexPattern('/^service/i'))->toBeTrue()
            ->and($normalizer->isRegexPattern('not-a-regex'))->toBeFalse();
    });
});
