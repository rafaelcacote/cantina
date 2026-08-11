<?php

namespace App\Http\Controllers;

use App\Models\StudentInvitation;
use App\Services\StudentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentInviteAcceptController extends Controller
{
    public function __construct(private readonly StudentAccessService $access) {}

    public function show(string $token): Response
    {
        $invitation = $this->findInvitation($token);
        $tenant = $invitation->tenant;

        if (! $invitation->isUsable()) {
            return Inertia::render('Invite/Unavailable', [
                'tenant' => [
                    'name' => $tenant?->name ?? 'Cantina',
                    'logo_url' => $tenant?->logoSrc(),
                ],
                'reason' => $invitation->unusableReason(),
            ]);
        }

        return Inertia::render('Invite/StudentAccept', [
            'token' => $invitation->token,
            'tenant' => [
                'name' => $tenant?->name ?? 'Cantina',
                'logo_url' => $tenant?->logoSrc(),
            ],
            'studentName' => $invitation->student?->name ?? 'Aluno',
            'expiresAt' => $invitation->expires_at?->format('d/m/Y'),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->findInvitation($token);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $this->access->accept($invitation, $validated);
        Auth::login($user);

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Acesso criado. Agora você já pode pedir na cantina.');
    }

    private function findInvitation(string $token): StudentInvitation
    {
        return StudentInvitation::query()
            ->with(['tenant', 'student'])
            ->where('token', $token)
            ->firstOrFail();
    }
}
