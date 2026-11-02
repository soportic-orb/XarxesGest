<?php

declare(strict_types=1);

namespace App\Ai\Concerns;

/**
 * Current Claude models (Opus 5.x, Sonnet 5.x, Fable) reject any `temperature`
 * value with a 400, and laravel/ai forwards it verbatim to the Messages API.
 * Agents return their preferred temperature through this helper, which omits
 * it when the text provider is Anthropic. laravel/ai resolves a `temperature()`
 * method before the #[Temperature] attribute, and null means "not sent".
 */
trait ResolvesTemperature
{
    protected function temperatureFor(float $value): ?float
    {
        return config('ai.default') === 'anthropic' ? null : $value;
    }
}
