<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesTenantLogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use HandlesTenantLogo;

    public function show(Request $request): View
    {
        $user = $request->user()->loadMissing('tenant');

        return view('pages.profile', [
            'title' => 'Perfil',
            'tenant' => $user->tenant,
            'canEditTenant' => $user->user_type === 'tenant_admin' && $user->tenant !== null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->user_type !== 'tenant_admin') {
            abort(403, 'Apenas o administrador da cantina pode editar estes dados.');
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            abort(403, 'Usuário sem tenant vinculado.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pix' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'pix' => $validated['pix'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            $this->deleteStoredTenantLogo($tenant->logo_url);
            $data['logo_url'] = $this->storeTenantLogo($request);
        }

        $tenant->update($data);

        return redirect()
            ->route('profile')
            ->with('success', 'Dados da cantina atualizados com sucesso.');
    }
}
