<?php

use App\Models\ParentGuardian;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentWallet;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Services\AdultConsumerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function requesterSelfContext(): array
{
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);

    return compact('tenant', 'school');
}

it('registers a requester from invitation and opens the portal', function () {
    ['tenant' => $tenant, 'school' => $school] = requesterSelfContext();

    $invitation = TenantInvitation::factory()->create([
        'tenant_id' => $tenant->id,
        'type' => 'requester_registration',
        'active' => true,
        'max_uses' => 1,
        'expires_at' => now()->addDays(7),
    ]);

    $response = $this->post(route('invitations.accept.store', $invitation->token), [
        'name' => 'Ana Solicitante',
        'email' => 'ana.solicitante@teste.local',
        'password' => 'senha123',
        'password_confirmation' => 'senha123',
        'cpf' => '390.533.447-05',
        'phone' => '11999990000',
        'school_id' => $school->id,
    ]);

    $response->assertRedirect(route('requester.dashboard'));

    $user = User::query()->where('email', 'ana.solicitante@teste.local')->first();
    expect($user)->not->toBeNull()
        ->and($user->user_type)->toBe('requester');

    $student = Student::forPortalUser($user);
    expect($student)->not->toBeNull()
        ->and($student->profile_kind)->toBe(AdultConsumerService::PROFILE_ADULT)
        ->and($student->status)->toBe('active')
        ->and((int) $student->school_id)->toBe((int) $school->id);

    $this->actingAs($user)
        ->get(route('requester.menu'))
        ->assertOk();
});

it('lets a parent enable self ordering and place an order', function () {
    ['tenant' => $tenant, 'school' => $school] = requesterSelfContext();

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
    $child = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'status' => 'active',
    ]);
    StudentWallet::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $child->id,
        'balance' => 20,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $child->id,
    ]);

    $section = ProductSection::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'lanches',
    ]);
    $category = ProductCategory::factory()->create([
        'tenant_id' => $tenant->id,
        'section_id' => $section->id,
    ]);
    $product = Product::factory()->create([
        'tenant_id' => $tenant->id,
        'section_id' => $section->id,
        'category_id' => $category->id,
        'name' => 'Cafe',
        'price' => 8.5,
        'active' => true,
        'visible_in_app' => true,
        'stock_controlled' => false,
    ]);

    $this->actingAs($user)
        ->post(route('parent.self.enable'), ['school_id' => $school->id])
        ->assertRedirect(route('parent.self.menu'));

    $parent->refresh();
    expect($parent->self_student_id)->not->toBeNull();

    $self = Student::query()->find($parent->self_student_id);
    expect($self->profile_kind)->toBe('adult')
        ->and($self->name)->toBe($parent->name);

    $this->actingAs($user)
        ->post(route('parent.self.orders.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'payment_mode' => 'cash',
            'notes' => 'Pedido para mim',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('orders', [
        'tenant_id' => $tenant->id,
        'student_id' => $self->id,
        'parent_id' => $parent->id,
        'payment_mode' => 'cash',
    ]);
});
