<?php

use App\Models\Operator;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('redireciona manager para tenant dashboard no login', function () {
    $tenant = Tenant::factory()->create();
    $manager = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'manager',
        'email' => 'manager@test.local',
        'password' => Hash::make('password'),
    ]);

    $this->post(route('signin.store'), [
        'email' => $manager->email,
        'password' => 'password',
    ])->assertRedirect(route('tenant.dashboard'));
});

it('redireciona operator para operator dashboard no login', function () {
    $tenant = Tenant::factory()->create();
    $operator = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'operator',
        'email' => 'operator@test.local',
        'password' => Hash::make('password'),
    ]);

    Operator::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $operator->id,
        'school_id' => null,
        'role' => 'operator',
    ]);

    $this->post(route('signin.store'), [
        'email' => $operator->email,
        'password' => 'password',
    ])->assertRedirect(route('operator.dashboard'));
});

it('permite manager acessar painel tenant', function () {
    $tenant = Tenant::factory()->create();
    $manager = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'manager',
    ]);

    $this->actingAs($manager)
        ->get(route('tenant.dashboard'))
        ->assertOk();
});

it('bloqueia operator do painel tenant', function () {
    $tenant = Tenant::factory()->create();
    $operator = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'operator',
    ]);

    $this->actingAs($operator)
        ->get(route('tenant.dashboard'))
        ->assertForbidden();
});

it('operador só vê alunos da escola vinculada', function () {
    $tenant = Tenant::factory()->create();
    $schoolA = School::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Escola A']);
    $schoolB = School::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Escola B']);

    $operator = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'operator',
    ]);

    Operator::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $operator->id,
        'school_id' => $schoolA->id,
        'role' => 'operator',
    ]);

    $studentA = Student::factory()->create(['tenant_id' => $tenant->id, 'school_id' => $schoolA->id, 'name' => 'Aluno A']);
    $studentB = Student::factory()->create(['tenant_id' => $tenant->id, 'school_id' => $schoolB->id, 'name' => 'Aluno B']);

    $this->actingAs($operator)
        ->get(route('operator.students.show', $studentA))
        ->assertOk();

    $this->actingAs($operator)
        ->get(route('operator.students.show', $studentB))
        ->assertNotFound();
});

it('mostra os dados do tenant na pagina de perfil', function () {
    $tenant = Tenant::factory()->create([
        'name' => 'Cantina Central',
        'email' => 'cantina@teste.local',
        'pix' => '12345678901',
    ]);
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'tenant_admin',
    ]);

    $this->actingAs($admin)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('Cantina Central')
        ->assertSee('cantina@teste.local')
        ->assertSee('12345678901');
});

it('operador acessa dashboard e pedidos', function () {
    $tenant = Tenant::factory()->create();
    $operator = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'operator',
    ]);

    Operator::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->get(route('operator.dashboard'))
        ->assertOk();

    $this->actingAs($operator)
        ->get(route('operator.orders.index'))
        ->assertOk();
});
