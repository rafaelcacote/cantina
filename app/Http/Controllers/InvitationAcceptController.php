<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\TenantInvitationController;
use App\Models\Operator;
use App\Models\School;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Rules\ValidCpf;
use App\Rules\ValidPhone;
use App\Services\AdultConsumerService;
use App\Services\ParentRegistrationService;
use App\Support\Cpf;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class InvitationAcceptController extends Controller
{
    public function __construct(
        private readonly ParentRegistrationService $parentRegistration,
        private readonly AdultConsumerService $adultConsumers,
    ) {}

    public function show(string $token): View|Response
    {
        $invitation = $this->findInvitation($token);

        if ($invitation->type === 'parent_registration') {
            return $this->showParentInvitation($invitation);
        }

        if ($invitation->type === 'requester_registration') {
            return $this->showRequesterInvitation($invitation);
        }

        $this->ensureUsable($invitation);

        return view('pages.auth.accept-invitation', [
            'title' => 'Aceitar convite',
            'invitation' => $invitation,
            'types' => TenantInvitationController::TYPES,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->findInvitation($token);

        if ($invitation->type === 'parent_registration') {
            return $this->storeParentInvitation($request, $invitation);
        }

        if ($invitation->type === 'requester_registration') {
            return $this->storeRequesterInvitation($request, $invitation);
        }

        $this->ensureUsable($invitation);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = DB::transaction(function () use ($invitation, $validated) {
            $userType = match ($invitation->type) {
                'tenant_admin' => 'tenant_admin',
                'operator' => 'operator',
                default => throw ValidationException::withMessages([
                    'email' => 'Tipo de convite não suportado.',
                ]),
            };

            $user = User::query()->create([
                'tenant_id' => $invitation->tenant_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'user_type' => $userType,
            ]);

            if ($invitation->type === 'operator') {
                Operator::query()->create([
                    'tenant_id' => $invitation->tenant_id,
                    'school_id' => null,
                    'user_id' => $user->id,
                    'role' => 'operator',
                ]);
            }

            $invitation->markUsed();

            return $user;
        });

        Auth::login($user);

        $route = match ($user->user_type) {
            'tenant_admin', 'manager' => 'tenant.dashboard',
            'operator' => 'operator.dashboard',
            default => 'dashboard',
        };

        return redirect()
            ->route($route)
            ->with('success', 'Conta criada com sucesso a partir do convite.');
    }

    private function showParentInvitation(TenantInvitation $invitation): Response
    {
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

        $schools = School::query()
            ->where('tenant_id', $invitation->tenant_id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Invite/Accept', [
            'token' => $invitation->token,
            'tenant' => [
                'name' => $tenant?->name ?? 'Cantina',
                'logo_url' => $tenant?->logoSrc(),
            ],
            'schools' => $schools,
            'relationshipTypes' => ParentRegistrationService::RELATIONSHIP_TYPES,
            'shifts' => ParentRegistrationService::SHIFTS,
            'expiresAt' => $invitation->expires_at?->format('d/m/Y'),
        ]);
    }

    private function storeParentInvitation(Request $request, TenantInvitation $invitation): RedirectResponse
    {
        $this->ensureUsable($invitation);

        $request->merge([
            'cpf' => Cpf::digits($request->input('cpf')),
            'phone' => Phone::digits($request->input('phone')) ?: null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', new ValidPhone],
            'cpf' => [
                'required',
                'digits:11',
                new ValidCpf,
                Rule::unique('users', 'cpf'),
                Rule::unique('parents', 'cpf'),
            ],
            'children' => ['required', 'array', 'min:1'],
            'children.*.name' => ['required', 'string', 'max:255'],
            'children.*.school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query->where('tenant_id', $invitation->tenant_id)),
            ],
            'children.*.birth_date' => ['nullable', 'date', 'before:today'],
            'children.*.grade' => ['nullable', 'string', 'max:50'],
            'children.*.classroom' => ['nullable', 'string', 'max:50'],
            'children.*.shift' => ['nullable', 'string', Rule::in(ParentRegistrationService::SHIFTS)],
            'children.*.relationship_type' => ['nullable', 'string', Rule::in(ParentRegistrationService::RELATIONSHIP_TYPES)],
        ], [
            'cpf.required' => 'Informe o CPF.',
            'cpf.digits' => 'O CPF deve ter 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado. Entre com sua conta ou fale com a cantina.',
        ]);

        $user = $this->parentRegistration->registerFromInvitation(
            $invitation,
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => Phone::format($validated['phone'] ?? null),
                'cpf' => $validated['cpf'],
            ],
            $validated['children'],
        );

        Auth::login($user);

        return redirect()
            ->route('parent.dashboard')
            ->with('success', 'Conta criada. Seus filhos já estão vinculados e aguardam a confirmação da cantina.');
    }

    private function showRequesterInvitation(TenantInvitation $invitation): Response
    {
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

        $schools = School::query()
            ->where('tenant_id', $invitation->tenant_id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Invite/AcceptRequester', [
            'token' => $invitation->token,
            'tenant' => [
                'name' => $tenant?->name ?? 'Cantina',
                'logo_url' => $tenant?->logoSrc(),
            ],
            'schools' => $schools,
            'expiresAt' => $invitation->expires_at?->format('d/m/Y'),
        ]);
    }

    private function storeRequesterInvitation(Request $request, TenantInvitation $invitation): RedirectResponse
    {
        $this->ensureUsable($invitation);

        $request->merge([
            'cpf' => Cpf::digits($request->input('cpf')),
            'phone' => Phone::digits($request->input('phone')) ?: null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', new ValidPhone],
            'cpf' => [
                'required',
                'digits:11',
                new ValidCpf,
                Rule::unique('users', 'cpf'),
            ],
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $invitation->tenant_id)
                    ->where('active', true)),
            ],
        ], [
            'cpf.required' => 'Informe o CPF.',
            'cpf.digits' => 'O CPF deve ter 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado. Entre com sua conta ou fale com a cantina.',
        ]);

        $user = $this->adultConsumers->registerRequesterFromInvitation($invitation, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => Phone::format($validated['phone'] ?? null),
            'cpf' => $validated['cpf'],
            'school_id' => (int) $validated['school_id'],
        ]);

        Auth::login($user);

        return redirect()
            ->route('requester.dashboard')
            ->with('success', 'Conta criada. Você já pode pedir na cantina.');
    }

    private function findInvitation(string $token): TenantInvitation
    {
        return TenantInvitation::query()
            ->with('tenant')
            ->where('token', $token)
            ->firstOrFail();
    }

    private function ensureUsable(TenantInvitation $invitation): void
    {
        if (! $invitation->isUsable()) {
            abort(410, $invitation->unusableReason());
        }
    }
}
