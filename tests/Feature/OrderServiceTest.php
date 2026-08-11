<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\School;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\StudentWallet;
use App\Models\TabEntry;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\OrderService;
use App\Services\PinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function makeOrderContext(array $overrides = []): array
{
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'can_buy_on_tab' => true,
        'snack_access' => true,
        'convenience_access' => true,
        'personal_pin_hash' => app(PinService::class)->hash('1234'),
    ]);

    $section = ProductSection::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Lanches',
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
        'price' => 10,
        'stock_controlled' => true,
        'minimum_stock_alert' => 5,
    ]);
    $stock = Stock::factory()->create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'quantity' => 20,
    ]);

    $order = Order::factory()->create(array_merge([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'student_id' => $student->id,
        'status' => 'pending',
        'payment_mode' => 'wallet',
        'total_amount' => 10,
        'discount_amount' => 0,
        'final_amount' => 10,
    ], $overrides));

    OrderItem::factory()->create([
        'tenant_id' => $tenant->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'item_name_snapshot' => $product->name,
        'unit_price' => 10,
        'quantity' => 1,
        'total_price' => 10,
    ]);

    return compact('tenant', 'school', 'student', 'section', 'product', 'stock', 'order');
}

it('debita estoque e carteira ao confirmar pedido', function () {
    $ctx = makeOrderContext(['payment_mode' => 'wallet']);
    StudentWallet::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'balance' => 50,
        'allow_negative_balance' => false,
        'credit_limit' => 0,
    ]);

    app(OrderService::class)->transitionStatus($ctx['order'], 'confirmed');

    expect($ctx['stock']->fresh()->quantity)->toBe(19)
        ->and($ctx['order']->fresh()->status)->toBe('confirmed')
        ->and(WalletTransaction::query()->where('reference_id', $ctx['order']->id)->where('transaction_type', 'debit')->exists())->toBeTrue()
        ->and($ctx['student']->wallet->fresh()->balance)->toEqual(40.0)
        ->and(StockMovement::query()->where('reference_type', 'order')->where('reference_id', $ctx['order']->id)->exists())->toBeTrue();
});

it('rejeita confirmação com estoque insuficiente', function () {
    $ctx = makeOrderContext();
    $ctx['stock']->update(['quantity' => 0]);
    StudentWallet::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'balance' => 50,
    ]);

    expect(fn () => app(OrderService::class)->transitionStatus($ctx['order'], 'confirmed'))
        ->toThrow(ValidationException::class);

    expect($ctx['order']->fresh()->status)->toBe('pending');
});

it('lança fiado com PIN e só permite seção lanches', function () {
    $ctx = makeOrderContext(['payment_mode' => 'tab']);
    StudentTab::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'current_balance' => 0,
        'active' => true,
    ]);

    app(OrderService::class)->transitionStatus($ctx['order'], 'confirmed', null, '1234');

    $entry = TabEntry::query()->where('order_id', $ctx['order']->id)->first();

    expect($entry)->not->toBeNull()
        ->and($entry->authorized_by_pin)->toBeTrue()
        ->and((float) $entry->studentTab->fresh()->current_balance)->toEqual(10.0);
});

it('confirma fiado do app sem pedir PIN de novo', function () {
    $ctx = makeOrderContext([
        'payment_mode' => 'tab',
        'order_channel' => 'app',
    ]);

    $user = User::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'user_type' => 'student',
    ]);
    $ctx['student']->update(['user_id' => $user->id]);
    $ctx['order']->update(['placed_by_user_id' => $user->id]);

    StudentTab::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'current_balance' => 0,
        'active' => true,
    ]);

    app(OrderService::class)->transitionStatus($ctx['order']->fresh(), 'confirmed');

    expect($ctx['order']->fresh()->status)->toBe('confirmed')
        ->and(TabEntry::query()->where('order_id', $ctx['order']->id)->exists())->toBeTrue();
});

it('ainda exige PIN ao confirmar fiado criado no painel', function () {
    $ctx = makeOrderContext([
        'payment_mode' => 'tab',
        'order_channel' => 'cashier',
    ]);
    StudentTab::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'current_balance' => 0,
        'active' => true,
    ]);

    expect(fn () => app(OrderService::class)->transitionStatus($ctx['order'], 'confirmed'))
        ->toThrow(ValidationException::class);

    expect($ctx['order']->fresh()->status)->toBe('pending');
});

it('cria pagamento ao confirmar pedido em pix', function () {
    $ctx = makeOrderContext(['payment_mode' => 'pix']);

    app(OrderService::class)->transitionStatus($ctx['order'], 'confirmed');

    expect(Payment::query()->where('order_id', $ctx['order']->id)->where('status', 'completed')->exists())->toBeTrue()
        ->and($ctx['stock']->fresh()->quantity)->toBe(19);
});

it('estorna estoque e carteira ao cancelar pedido confirmado', function () {
    $ctx = makeOrderContext(['payment_mode' => 'wallet']);
    StudentWallet::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'student_id' => $ctx['student']->id,
        'balance' => 50,
        'allow_negative_balance' => false,
        'credit_limit' => 0,
    ]);

    $service = app(OrderService::class);
    $service->transitionStatus($ctx['order'], 'confirmed');
    $service->transitionStatus($ctx['order']->fresh(), 'cancelled');

    expect($ctx['stock']->fresh()->quantity)->toBe(20)
        ->and((float) $ctx['student']->wallet->fresh()->balance)->toEqual(50.0)
        ->and($ctx['order']->fresh()->status)->toBe('cancelled');
});
