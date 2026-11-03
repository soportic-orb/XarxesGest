<?php

declare(strict_types=1);

/**
 * TryPost 2.0 removed image generation and pinned it here; XarxesGest brings it
 * back for the composer's "Generate with AI" (Source::Ai, GenerateImage), so the
 * guard now pins how it is configured instead.
 */
test('images go through their own provider, openai unless the env says otherwise', function () {
    $config = require config_path('ai.php');

    expect($config)->toHaveKey('default_for_images')
        ->and(config('ai.providers.openai.models'))->toHaveKey('image');
});

test('the image provider is independent of the text provider', function () {
    config(['ai.default' => 'anthropic', 'ai.default_for_images' => 'openai']);

    expect(config('ai.default_for_images'))->toBe('openai')
        ->and(config('ai.default'))->toBe('anthropic');
});
