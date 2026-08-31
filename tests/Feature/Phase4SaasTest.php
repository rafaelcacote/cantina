<?php

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('permite super admin listar e criar planos', function () {
    $admin = User::factory()->create([
        'user_type' => 'super_admin',
        'tenant_id' => null,
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.plans.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->post(route('admin.plans.store'), [
            'name' => 'Plano Pro',
            'slug' => 'pro',
            'price' => 199.90,
            'billing_cycle' => 'monthly',
            'max_students' => 1000,
            'max_users' => 50,
            'features_text' => "Suporte\nRelatórios",
            'active' => '1',
        ])
        ->assertRedirect(route('admin.plans.index'));

    expect(Plan::query()->where('slug', 'pro')->exists())->toBeTrue();
});

it('permite super admin criar tenant_admin com tenant vinculado', function () {
    $admin = User::factory()->create([
        'user_type' => 'super_admin',
        'tenant_id' => null,
        'password' => Hash::make('password'),
    ]);
    $tenant = Tenant::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Gestor Cantina',
            'email' => 'gestor@teste.local',
            'phone' => '92999999999',
            'user_type' => 'tenant_admin',
            'tenant_id' => $tenant->id,
            'password' => 'password',
            'active' => '1',
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::query()->where('email', 'gestor@teste.local')->first();

    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($tenant->id)
        ->and($user->user_type)->toBe('tenant_admin');

    $this->actingAs($user)
        ->get(route('tenant.dashboard'))
        ->assertOk();
});

it('aceita convite de tenant_admin e cria usuário', function () {
    $tenant = Tenant::factory()->create();
    $invitation = TenantInvitation::factory()->create([
        'tenant_id' => $tenant->id,
        'type' => 'tenant_admin',
        'token' => 'test-invite-token-phase4',
        'active' => true,
        'max_uses' => 1,
        'used_count' => 0,
        'expires_at' => now()->addDay(),
    ]);

    $this->get(route('invitations.accept', $invitation->token))->assertOk();

    $this->post(route('invitations.accept.store', $invitation->token), [
        'name' => 'Novo Admin',
        'email' => 'novoadmin@teste.local',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('tenant.dashboard'));

    expect(User::query()->where('email', 'novoadmin@teste.local')->where('user_type', 'tenant_admin')->exists())->toBeTrue()
        ->and($invitation->fresh()->used_count)->toBe(1)
        ->and($invitation->fresh()->active)->toBeFalse();
});
