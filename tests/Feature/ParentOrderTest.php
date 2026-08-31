<?php

use App\Models\Order;
use App\Models\ParentGuardian;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\PurchaseAuthorization;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentTab;
use App\Models\StudentWallet;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PinService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function parentOrderContext(): array
{
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
        'status' => 'active',
        'snack_access' => true,
        'can_buy_on_tab' => false,
    ]);
    StudentWallet::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'balance' => 50,
        'allow_negative_balance' => false,
        'credit_limit' => 0,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $student->id,
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
        'name' => 'Sanduiche',
        'price' => 12.5,
        'active' => true,
        'visible_in_app' => true,
        'stock_controlled' => false,
    ]);

    return compact('tenant', 'school', 'user', 'parent', 'student', 'product');
}

it('responsavel ve o cardapio do filho', function () {
    $ctx = parentOrderContext();

    $this->actingAs($ctx['user'])
        ->get(route('parent.children.menu', $ctx['student']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/Menu')
            ->where('child.id', $ctx['student']->id)
            ->has('items', 1)
            ->where('items.0.name', 'Sanduiche'));
});

it('responsavel solicita pedido em nome do filho', function () {
    $ctx = parentOrderContext();

    $this->actingAs($ctx['user'])
        ->post(route('parent.children.orders.store', $ctx['student']), [
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 2],
            ],
            'payment_mode' => 'wallet',
            'notes' => 'Sem cebola',
        ])
        ->assertRedirect();

    $order = Order::query()->where('student_id', $ctx['student']->id)->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('pending')
        ->and($order->order_channel)->toBe('app')
        ->and((int) $order->parent_id)->toBe((int) $ctx['parent']->id)
        ->and((int) $order->placed_by_user_id)->toBe((int) $ctx['user']->id)
        ->and((float) $order->final_amount)->toBe(25.0)
        ->and($order->notes)->toBe('Sem cebola');
});

it('responsavel nao pede para aluno que nao e dele', function () {
    $ctx = parentOrderContext();
    $other = Student::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'school_id' => $ctx['school']->id,
        'status' => 'active',
        'snack_access' => true,
    ]);

    $this->actingAs($ctx['user'])
        ->post(route('parent.children.orders.store', $other), [
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'wallet',
        ])
        ->assertNotFound();

    expect(Order::query()->where('student_id', $other->id)->count())->toBe(0);
});

it('responsavel nao pede para filho ainda pendente', function () {
    $ctx = parentOrderContext();
    $ctx['student']->update(['status' => 'pending']);

    $this->actingAs($ctx['user'])
        ->from(route('parent.children.orders.create', $ctx['student']))
        ->post(route('parent.children.orders.store', $ctx['student']), [
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'wallet',
        ])
        ->assertForbidden();

    expect(Order::query()->where('student_id', $ctx['student']->id)->count())->toBe(0);
});

it('responsavel solicita fiado sem PIN e a cantina confirma sem pedir de novo', function () {
    $ctx = parentOrderContext();
    $ctx['student']->update(['can_buy_on_tab' => true]);
    app(PinService::class)->assign($ctx['student'], '1234');
    StudentTab::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'current_balance' => 0,
        'active' => true,
    ]);

    $this->actingAs($ctx['user'])
        ->post(route('parent.children.orders.store', $ctx['student']), [
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'tab',
        ])
        ->assertRedirect();

    $order = Order::query()->where('student_id', $ctx['student']->id)->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and($order->payment_mode)->toBe('tab')
        ->and($order->status)->toBe('pending')
        ->and((int) $order->parent_id)->toBe((int) $ctx['parent']->id)
        ->and(PurchaseAuthorization::query()->where('order_id', $order->id)->where('success', true)->where('auth_method', 'parent')->exists())->toBeTrue();

    $admin = User::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'user_type' => 'tenant_admin',
    ]);

    $this->actingAs($admin)
        ->get(route('tenant.orders.show', $order))
        ->assertOk()
        ->assertSee('Pedido solicitado pelo responsável.')
        ->assertDontSee('Confirmar com PIN');

    $this->actingAs($admin)
        ->patch(route('tenant.orders.status.update', $order), [
            'status' => 'confirmed',
        ])
        ->assertRedirect(route('tenant.orders.show', $order));

    expect($order->fresh()->status)->toBe('confirmed');
});

it('responsavel com um filho vai direto ao cardapio ao criar pedido', function () {
    $ctx = parentOrderContext();

    $this->actingAs($ctx['user'])
        ->get(route('parent.orders.create'))
        ->assertRedirect(route('parent.children.menu', $ctx['student']));
});
