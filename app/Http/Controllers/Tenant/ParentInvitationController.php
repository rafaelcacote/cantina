<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ParentInvitationController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $invitations = TenantInvitation::query()
            ->with('creator')
            ->where('tenant_id', $tenantId)
            ->where('type', 'parent_registration')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.tenant.parent_invitations.index', [
            'title' => 'Convites de responsáveis',
            'invitations' => $invitations,
        ]);
    }

    public function create(): View
    {
        return view('pages.tenant.parent_invitations.create', [
            'title' => 'Novo convite de responsável',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ]);

        $invitation = TenantInvitation::query()->create([
            'tenant_id' => $request->user()->tenant_id,
            'token' => Str::random(48),
            'type' => 'parent_registration',
            'expires_at' => $validated['expires_at'] ?? now()->addDays(30),
            'max_uses' => $validated['max_uses'] ?? null,
            'used_count' => 0,
            'active' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('tenant.parent-invitations.show', $invitation)
            ->with('success', 'Convite gerado. Copie o link e envie ao responsável.');
    }

    public function show(Request $request, TenantInvitation $parentInvitation): View
    {
        $this->ensureInvitationBelongsToTenant($request, $parentInvitation);
        $parentInvitation->load('creator');

        $acceptUrl = $parentInvitation->acceptUrl();
        $tenantName = $request->user()->tenant?->name ?? 'a cantina';
        $shareText = "Olá! Cadastre-se na {$tenantName} e registre seus filhos neste link:\n{$acceptUrl}";

        return view('pages.tenant.parent_invitations.show', [
            'title' => 'Convite de responsável',
            'invitation' => $parentInvitation,
            'acceptUrl' => $acceptUrl,
            'whatsappUrl' => 'https://wa.me/?text='.rawurlencode($shareText),
            'mailtoUrl' => 'mailto:?subject='.rawurlencode("Convite — {$tenantName}").'&body='.rawurlencode($shareText),
        ]);
    }

    public function toggle(Request $request, TenantInvitation $parentInvitation): RedirectResponse
    {
        $this->ensureInvitationBelongsToTenant($request, $parentInvitation);

        $parentInvitation->update([
            'active' => ! $parentInvitation->active,
        ]);

        $message = $parentInvitation->active
            ? 'Convite reativado.'
            : 'Convite desativado. O link deixa de funcionar.';

        return back()->with('success', $message);
    }

    private function ensureInvitationBelongsToTenant(Request $request, TenantInvitation $invitation): void
    {
        if (
            (int) $invitation->tenant_id !== (int) $request->user()->tenant_id
            || $invitation->type !== 'parent_registration'
        ) {
            abort(404);
        }
    }
}
