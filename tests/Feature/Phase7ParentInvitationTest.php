<?php

use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentWallet;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tenant admin gera convite de responsável', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'tenant_admin',
    ]);

    $this->actingAs($admin)
        ->post(route('tenant.parent-invitations.store'), [
            'expires_at' => now()->addDays(15)->format('Y-m-d\TH:i'),
            'max_uses' => 10,
        ])
        ->assertRedirect();

    $invitation = TenantInvitation::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', 'parent_registration')
        ->first();

    expect($invitation)->not->toBeNull()
        ->and($invitation->max_uses)->toBe(10)
        ->and($invitation->active)->toBeTrue();
});

it('responsável cria conta e cadastra filhos pelo convite', function () {
    $tenant = Tenant::factory()->create(['name' => 'Cantina Demo']);
    $school = School::factory()->create([
        'tenant_id' => $tenant->id,
        'active' => true,
    ]);
    $invitation = TenantInvitation::factory()->create([
        'tenant_id' => $tenant->id,
        'type' => 'parent_registration',
        'token' => 'parent-invite-token',
        'active' => true,
        'max_uses' => 5,
        'used_count' => 0,
        'expires_at' => now()->addWeek(),
    ]);

    $this->get(route('invitations.accept', $invitation->token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Invite/Accept')
            ->where('tenant.name', 'Cantina Demo')
            ->has('schools', 1));

    $this->post(route('invitations.accept.store', $invitation->token), [
        'name' => 'Ana Responsavel',
        'email' => 'ana.mae@teste.local',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '11999990000',
        'cpf' => '390.533.447-05',
        'children' => [
            [
                'name' => 'Lucas Silva',
                'school_id' => $school->id,
                'birth_date' => '2015-03-10',
                'grade' => '4o Ano',
                'classroom' => 'B',
                'shift' => 'Manhã',
                'relationship_type' => 'Mãe',
            ],
            [
                'name' => 'Clara Silva',
                'school_id' => $school->id,
                'relationship_type' => 'Mãe',
            ],
        ],
    ])->assertRedirect(route('parent.dashboard'));

    $user = User::query()->where('email', 'ana.mae@teste.local')->first();
    $parent = ParentGuardian::query()->where('user_id', $user->id)->first();

    expect($user)->not->toBeNull()
        ->and($user->user_type)->toBe('parent')
        ->and($user->tenant_id)->toBe($tenant->id)
        ->and($parent)->not->toBeNull()
        ->and($user->cpf)->toBe('39053344705')
        ->and($parent->cpf)->toBe('39053344705')
        ->and($user->phone)->toBe('(11) 99999-0000')
        ->and($parent->phone)->toBe('(11) 99999-0000')
        ->and(Student::query()->where('tenant_id', $tenant->id)->count())->toBe(2)
        ->and(StudentParent::query()->where('parent_id', $parent->id)->count())->toBe(2)
        ->and(Student::query()->where('name', 'Lucas Silva')->value('status'))->toBe('pending')
        ->and(StudentWallet::query()->where('tenant_id', $tenant->id)->count())->toBe(2)
        ->and($invitation->fresh()->used_count)->toBe(1)
        ->and(auth()->id())->toBe($user->id);
});

it('rejeita CPF inválido no convite do responsável', function () {
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
    $invitation = TenantInvitation::factory()->create([
        'tenant_id' => $tenant->id,
        'type' => 'parent_registration',
        'token' => 'invalid-cpf-invite',
        'active' => true,
        'expires_at' => now()->addWeek(),
    ]);

    $this->from(route('invitations.accept', $invitation->token))
        ->post(route('invitations.accept.store', $invitation->token), [
            'name' => 'Ana Responsavel',
            'email' => 'ana.invalida@teste.local',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cpf' => '123.456.789-01',
            'children' => [
                ['name' => 'Lucas Silva', 'school_id' => $school->id],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('cpf');
});

it('rejeita CPF já cadastrado no convite do responsável', function () {
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
    User::factory()->create([
        'tenant_id' => $tenant->id,
        'cpf' => '39053344705',
    ]);
    $invitation = TenantInvitation::factory()->create([
        'tenant_id' => $tenant->id,
        'type' => 'parent_registration',
        'token' => 'duplicate-cpf-invite',
        'active' => true,
        'expires_at' => now()->addWeek(),
    ]);

    $this->from(route('invitations.accept', $invitation->token))
        ->post(route('invitations.accept.store', $invitation->token), [
            'name' => 'Outra Mae',
            'email' => 'outra.mae@teste.local',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cpf' => '39053344705',
            'children' => [
                ['name' => 'Clara Silva', 'school_id' => $school->id],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('cpf');
});

it('bloqueia convite de responsável expirado', function () {
    $tenant = Tenant::factory()->create();
    $invitation = TenantInvitation::factory()->create([
        'tenant_id' => $tenant->id,
        'type' => 'parent_registration',
        'token' => 'expired-parent-invite',
        'active' => true,
        'expires_at' => now()->subDay(),
    ]);

    $this->get(route('invitations.accept', $invitation->token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Invite/Unavailable'));
});

it('parent autenticado lista filhos e pedidos no portal', function () {
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'parent',
        'name' => 'Maria Responsavel',
    ]);
    $parent = ParentGuardian::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'name' => $user->name,
    ]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'name' => 'Joao Aluno',
    ]);
    StudentWallet::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'balance' => 20,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user)
        ->get(route('parent.children.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/Children')
            ->has('children', 1)
            ->where('children.0.name', 'Joao Aluno'));

    $this->actingAs($user)
        ->get(route('parent.children.show', $student))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/ChildShow')
            ->where('child.name', 'Joao Aluno'));

    $this->actingAs($user)
        ->get(route('parent.orders.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Parent/Orders'));

    $this->actingAs($user)
        ->get(route('parent.account'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Parent/Account'));
});
