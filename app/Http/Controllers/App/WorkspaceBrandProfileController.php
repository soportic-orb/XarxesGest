<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Requests\App\Workspace\UpdateWorkspaceBrandProfileRequest;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;

/**
 * What the brand of the current workspace does, for whom and how it speaks.
 * The AI features (trend proposals, idea generation) read it.
 */
class WorkspaceBrandProfileController extends Controller
{
    public function update(UpdateWorkspaceBrandProfileRequest $request): RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace;

        $this->authorize('update', $workspace);

        $profile = collect(Workspace::BRAND_PROFILE_FIELDS)
            ->mapWithKeys(fn (string $field): array => [$field => trim((string) $request->validated($field))])
            ->filter(fn (string $value): bool => $value !== '')
            ->all();

        $workspace->update(['brand_profile' => $profile === [] ? null : $profile]);

        return back()->with('flash.success', __('settings.flash.brand_profile_updated'));
    }
}
