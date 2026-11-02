<?php

declare(strict_types=1);

use App\Ai\Agents\IdeaGenerator;
use App\Enums\User\Locale;
use Laravel\Ai\Gateway\TextGenerationOptions;

test('temperature is omitted when the text provider is anthropic', function () {
    config(['ai.default' => 'anthropic']);

    $options = TextGenerationOptions::forAgent(new IdeaGenerator(Locale::Catalan, 'Torns', 'RRHH'));

    expect($options->temperature)->toBeNull();
});

test('temperature is kept for other text providers', function () {
    config(['ai.default' => 'openai']);

    $options = TextGenerationOptions::forAgent(new IdeaGenerator(Locale::Catalan, 'Torns', 'RRHH'));

    expect($options->temperature)->toBe(0.8);
});
