<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandlesTenantLogo
{
    protected function storeTenantLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        return $request->file('logo')->store('tenants/logos', 'public');
    }

    protected function deleteStoredTenantLogo(?string $logoUrl): void
    {
        if (! $logoUrl || str_starts_with($logoUrl, 'http://') || str_starts_with($logoUrl, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($logoUrl);
    }
}
