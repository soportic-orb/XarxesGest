<?php

declare(strict_types=1);

namespace App\Http\Requests\App\Ai;

use App\Enums\Media\ImageAspect;
use App\Rules\PostContentFitsMaxLength;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiImagePromptRequest extends FormRequest
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
            'content' => ['required', 'string', new PostContentFitsMaxLength],
            'aspect' => ['required', Rule::enum(ImageAspect::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => __('posts.composer.ai_image.errors.content_required'),
        ];
    }
}
