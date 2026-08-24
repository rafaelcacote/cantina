<?php

use App\Models\Operator;
use App\Models\Order;
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
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function posContext(array $overrides = []): array
{
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);

    $operatorUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'operator',
    ]);

    Operator::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'user_id' => $operatorUser->id,
        'role' => 'cashier',
    ]);

    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'name' => 'Ana Silva',
        'enrollment_number' => 'MAT1001',
        'can_buy_on_tab' => true,
        'snack_access' => true,
        'convenience_access' => true,
        'personal_pin_hash' => app(PinService::class)->hash('1234'),
    ]);

    StudentWallet::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'balance' => 50,
        'allow_negative_balance' => false,
        'credit_limit' => 0,
    ]);

    StudentTab::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'active' => true,
        'current_balance' => 0,
    ]);

    $section = ProductSection::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'lanches',
    ]);
    $category = ProductCategory::factory()->create([
        'tenant_id' => $tenant->id,
        'section_id' => $section->id,
        'name' => 'Salgados',
    ]);
    $product = Product::factory()->create(array_merge([
        'tenant_id' => $tenant->id,
        'section_id' => $section->id,
        'category_id' => $category->id,
        'name' => 'Coxinha',
        'price' => 8.5,
        'active' => true,
        'stock_controlled' => true,
    ], $overrides['product'] ?? []));

    $stock = Stock::factory()->create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'quantity' => 30,
    ]);

    return compact('tenant', 'school', 'operatorUser', 'student', 'section', 'category', 'product', 'stock');
}

it('operador abre a tela do PDV', function () {
    $ctx = posContext();

    $this->actingAs($ctx['operatorUser'])
        ->get(route('operator.pos.index'))
        ->assertOk()
        ->assertSee('PDV')
        ->assertSee('Coxinha')
        ->assertSee('studentsSearchUrl')
        ->assertDontSee('http://localhost/operator/pos/students');
});

it('venda em dinheiro sem aluno confirma pedido e baixa estoque', function () {
    $ctx = posContext();

    $this->actingAs($ctx['operatorUser'])
        ->postJson(route('operator.pos.checkout'), [
            'school_id' => $ctx['school']->id,
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 2],
            ],
            'payment_mode' => 'cash',
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('order.status', 'confirmed');

    $order = Order::query()->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and($order->order_channel)->toBe('cashier')
        ->and($order->student_id)->toBeNull()
        ->and($order->payment_mode)->toBe('cash')
        ->and((float) $order->final_amount)->toBe(17.0)
        ->and($ctx['stock']->fresh()->quantity)->toBe(28)
        ->and(Payment::query()->where('order_id', $order->id)->where('payment_method', 'cash')->where('status', 'completed')->exists())->toBeTrue()
        ->and(StockMovement::query()->where('reference_type', 'order')->where('reference_id', $order->id)->exists())->toBeTrue();
});

it('ficha exige aluno e PIN e debita carteira', function () {
    $ctx = posContext();

    $this->actingAs($ctx['operatorUser'])
        ->postJson(route('operator.pos.checkout'), [
            'school_id' => $ctx['school']->id,
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'wallet',
            'student_id' => $ctx['student']->id,
            'student_pin' => '1234',
        ])
        ->assertOk()
        ->assertJsonPath('order.payment_mode', 'wallet');

    expect($ctx['student']->wallet->fresh()->balance)->toEqual(41.5)
        ->and($ctx['stock']->fresh()->quantity)->toBe(29);
});

it('conta exige PIN e lanca fiado', function () {
    $ctx = posContext();

    $this->actingAs($ctx['operatorUser'])
        ->postJson(route('operator.pos.checkout'), [
            'school_id' => $ctx['school']->id,
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'tab',
            'student_id' => $ctx['student']->id,
            'student_pin' => '1234',
        ])
        ->assertOk();

    $order = Order::query()->latest('id')->first();

    expect($order->payment_mode)->toBe('tab')
        ->and($order->student_id)->toBe($ctx['student']->id)
        ->and($order->tabEntries()->exists())->toBeTrue();
});

it('rejeita ficha sem aluno', function () {
    $ctx = posContext();

    $this->actingAs($ctx['operatorUser'])
        ->postJson(route('operator.pos.checkout'), [
            'school_id' => $ctx['school']->id,
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'wallet',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['student_id']);
});

it('rejeita PIN incorreto na ficha', function () {
    $ctx = posContext();

    $this->actingAs($ctx['operatorUser'])
        ->postJson(route('operator.pos.checkout'), [
            'school_id' => $ctx['school']->id,
            'items' => [
                ['product_id' => $ctx['product']->id, 'quantity' => 1],
            ],
            'payment_mode' => 'wallet',
            'student_id' => $ctx['student']->id,
            'student_pin' => '9999',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['student_pin']);

    expect($ctx['stock']->fresh()->quantity)->toBe(30)
        ->and(Order::query()->count())->toBe(0);
});

it('busca alunos por nome no PDV', function () {
    $ctx = posContext();

    $this->actingAs($ctx['operatorUser'])
        ->getJson(route('operator.pos.students', [
            'q' => 'Ana',
            'school_id' => $ctx['school']->id,
        ]))
        ->assertOk()
        ->assertJsonPath('students.0.name', 'Ana Silva')
        ->assertJsonPath('students.0.enrollment_number', 'MAT1001');
});

it('placeFromCashierPos rejeita estoque insuficiente', function () {
    $ctx = posContext();
    $ctx['stock']->update(['quantity' => 1]);

    expect(fn () => app(OrderService::class)->placeFromCashierPos(
        $ctx['operatorUser'],
        $ctx['school']->id,
        [['product_id' => $ctx['product']->id, 'quantity' => 5]],
        'cash',
    ))->toThrow(ValidationException::class);

    expect($ctx['stock']->fresh()->quantity)->toBe(1);
});
