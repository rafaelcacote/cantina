<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('exibe o formulário de alterar senha', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'tenant_admin',
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->get(route('password.edit'))
        ->assertOk()
        ->assertSee('Alterar senha');
});

it('altera a senha com dados válidos', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'manager',
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'nova-senha',
            'password_confirmation' => 'nova-senha',
        ])
        ->assertRedirect(route('password.edit'))
        ->assertSessionHas('success');

    expect(Hash::check('nova-senha', $user->fresh()->password))->toBeTrue();
});

it('rejeita senha atual incorreta', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'manager',
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->from(route('password.edit'))
        ->put(route('password.update'), [
            'current_password' => 'errada',
            'password' => 'nova-senha',
            'password_confirmation' => 'nova-senha',
        ])
        ->assertRedirect(route('password.edit'))
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
