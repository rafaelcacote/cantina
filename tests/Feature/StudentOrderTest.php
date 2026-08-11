<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\PurchaseAuthorization;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\StudentWallet;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PinService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function studentOrderContext(): array
{
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'student',
    ]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'user_id' => $user->id,
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

    return compact('tenant', 'school', 'user', 'student', 'product');
}

it('aluno envia solicitacao de pedido a partir dos produtos cadastrados', function () {
    $ctx = studentOrderContext();

    $this->actingAs($ctx['user'])
        ->post(route('student.orders.store'), [
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
        ->and((float) $order->final_amount)->toBe(25.0)
        ->and($order->notes)->toBe('Sem cebola');

    expect($order->items)->toHaveCount(1)
        ->and($order->items->first()->quantity)->toBe(2);
});

it('aluno nao pede produto inativo ou oculto no app', function () {
    $ctx = studentOrderContext();
    $hidden = Product::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'section_id' => $ctx['product']->section_id,
        'category_id' => $ctx['product']->category_id,
        'price' => 8,
        'active' => true,
        'visible_in_app' => false,
        'stock_controlled' => false,
    ]);

    $this->actingAs($ctx['user'])
        ->from(route('student.orders.create'))
        ->post(route('student.orders.store'), [
            'items' => [
                ['product_id' => $hidden->id, 'quantity' => 1],
            ],
            'payment_mode' => 'wallet',
        ])
        ->assertRedirect(route('student.orders.create'))
        ->assertSessionHasErrors();

    expect(Order::query()->where('student_id', $ctx['student']->id)->count())->toBe(0);
});

it('aluno solicita fiado com PIN e a cantina confirma sem pedir de novo', function () {
    $ctx = studentOrderContext();
    $ctx['student']->update(['can_buy_on_tab' => true]);
    app(PinService::class)->assign($ctx['student'], '1234');
    StudentTab::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'current_balance' => 0,
        'active' => true,
    ]);

    $this->actingAs($ctx['user'])
        ->post(route('student.orders.store'), [
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'tab',
            'student_pin' => '1234',
        ])
        ->assertRedirect();

    $order = Order::query()->where('student_id', $ctx['student']->id)->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and($order->payment_mode)->toBe('tab')
        ->and($order->status)->toBe('pending')
        ->and(PurchaseAuthorization::query()->where('order_id', $order->id)->where('success', true)->exists())->toBeTrue();

    $admin = User::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'user_type' => 'tenant_admin',
    ]);

    $this->actingAs($admin)
        ->get(route('tenant.orders.show', $order))
        ->assertOk()
        ->assertSee('Pedido solicitado mediante inserção do PIN.')
        ->assertDontSee('Confirmar com PIN');

    $this->actingAs($admin)
        ->patch(route('tenant.orders.status.update', $order), [
            'status' => 'confirmed',
        ])
        ->assertRedirect(route('tenant.orders.show', $order));

    expect($order->fresh()->status)->toBe('confirmed');
});
