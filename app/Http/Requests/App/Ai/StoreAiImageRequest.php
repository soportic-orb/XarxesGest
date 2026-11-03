<?php

declare(strict_types=1);

namespace App\Http\Requests\App\Ai;

use App\Ai\Agents\ImagePromptWriter;
use App\Enums\Media\ImageAspect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:3', 'max:'.ImagePromptWriter::MAX_LENGTH],
            'aspect' => ['required', Rule::enum(ImageAspect::class)],
        ];
    }
}
