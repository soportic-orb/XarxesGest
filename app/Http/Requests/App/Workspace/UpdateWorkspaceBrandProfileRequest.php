<?php

declare(strict_types=1);

namespace App\Http\Requests\App\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceBrandProfileRequest extends FormRequest
{
    public const int FIELD_MAX_LENGTH = 1000;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'business' => ['nullable', 'string', 'max:'.self::FIELD_MAX_LENGTH],
            'audience' => ['nullable', 'string', 'max:'.self::FIELD_MAX_LENGTH],
            'tone' => ['nullable', 'string', 'max:'.self::FIELD_MAX_LENGTH],
            'topics' => ['nullable', 'string', 'max:'.self::FIELD_MAX_LENGTH],
            'references' => ['nullable', 'string', 'max:'.self::FIELD_MAX_LENGTH],
        ];
    }
}
