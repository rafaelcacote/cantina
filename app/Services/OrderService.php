<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Student;
use App\Models\StudentWallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /** Statuses that commit stock and financial side effects. */
    public const COMMITTED_STATUSES = ['confirmed', 'preparing', 'ready', 'delivered'];

    public function __construct(
        private readonly StockService $stockService,
        private readonly WalletService $walletService,
        private readonly TabService $tabService,
        private readonly PinService $pinService,
        private readonly ParentalControlService $parentalControlService,
    ) {}

    public function transitionStatus(Order $order, string $newStatus, ?User $actor = null, ?string $studentPin = null): Order
    {
        $oldStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return $order;
        }

        $wasCommitted = $this->isCommitted($oldStatus);
        $willBeCommitted = $this->isCommitted($newStatus);

        $order->loadMissing(['items.product.section', 'student']);

        // Validações (incl. PIN) fora da transação para auditar falhas sem rollback.
        if (! $wasCommitted && $willBeCommitted) {
            $this->validateBeforeCommit($order, $studentPin, $actor);
        }

        return DB::transaction(function () use ($order, $newStatus, $actor, $wasCommitted, $willBeCommitted) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->load(['items.product.section', 'student']);

            if (! $wasCommitted && $willBeCommitted) {
                $this->stockService->debitForOrder($order, $actor);
                $this->applyPaymentEffects($order, $actor);
            }

            if ($wasCommitted && ($newStatus === 'cancelled' || ! $willBeCommitted)) {
                $this->reverseSideEffects($order, $actor);
            }

            $order->update(['status' => $newStatus]);

            return $order->fresh(['items.product', 'student']);
        });
    }

    public function isCommitted(string $status): bool
    {
        return in_array($status, self::COMMITTED_STATUSES, true);
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $requestedItems
     */
    public function placeFromStudentApp(
        Student $student,
        User $actor,
        array $requestedItems,
        string $paymentMode,
        ?string $notes = null,
        ?string $studentPin = null,
    ): Order {
        return $this->placeFromAppPortal(
            $student,
            $actor,
            $requestedItems,
            $paymentMode,
            $notes,
            $studentPin,
            null,
        );
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $requestedItems
     */
    public function placeFromParentApp(
        ParentGuardian $parent,
        Student $student,
        User $actor,
        array $requestedItems,
        string $paymentMode,
        ?string $notes = null,
    ): Order {
        if ((int) $parent->tenant_id !== (int) $student->tenant_id) {
            throw ValidationException::withMessages([
                'items' => 'Aluno não encontrado.',
            ]);
        }

        if ($student->status !== 'active') {
            throw ValidationException::withMessages([
                'items' => 'A cantina ainda precisa confirmar o cadastro deste aluno.',
            ]);
        }

        return $this->placeFromAppPortal(
            $student,
            $actor,
            $requestedItems,
            $paymentMode,
            $notes,
            null,
            $parent,
        );
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $requestedItems
     */
    private function placeFromAppPortal(
        Student $student,
        User $actor,
        array $requestedItems,
        string $paymentMode,
        ?string $notes,
        ?string $studentPin,
        ?ParentGuardian $parent,
    ): Order {
        if (! $student->school_id) {
            throw ValidationException::withMessages([
                'items' => 'Aluno sem escola vinculada.',
            ]);
        }

        $allowedModes = ['wallet', 'cash'];
        if ($student->can_buy_on_tab) {
            $allowedModes[] = 'tab';
        }

        if (! in_array($paymentMode, $allowedModes, true)) {
            throw ValidationException::withMessages([
                'payment_mode' => 'Esta forma de pagamento não está disponível para você.',
            ]);
        }

        $productIds = collect($requestedItems)->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->all();

        $products = Product::query()
            ->with(['section', 'stock'])
            ->where('tenant_id', $student->tenant_id)
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach (array_values($requestedItems) as $index => $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($productId < 1 || $quantity < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}" => 'Item inválido.',
                ]);
            }

            $product = $products->get($productId);

            if (! $product || ! $product->active || ! $product->visible_in_app) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Este produto não está disponível para pedido.',
                ]);
            }

            $slug = $product->section?->slug;
            if ($slug === 'conveniencia' && ! $student->convenience_access) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Você não tem acesso à seção Conveniência.',
                ]);
            }
            if ($slug === 'lanches' && ! $student->snack_access) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Você não tem acesso à seção Lanches.',
                ]);
            }

            if ($product->stock_controlled) {
                $available = (int) ($product->stock?->quantity ?? 0);
                if ($quantity > $available) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "Quantidade indisponível para {$product->name}.",
                    ]);
                }
            }

            $unitPrice = (float) $product->price;

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $quantity, 2),
            ];
        }

        if ($lines === []) {
            throw ValidationException::withMessages([
                'items' => 'Selecione ao menos um item.',
            ]);
        }

        return DB::transaction(function () use ($student, $actor, $lines, $paymentMode, $notes, $studentPin, $parent) {
            $total = round(array_sum(array_column($lines, 'total_price')), 2);

            $order = Order::query()->create([
                'tenant_id' => $student->tenant_id,
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'parent_id' => $parent?->id,
                'placed_by_user_id' => $actor->id,
                'order_channel' => 'app',
                'order_type' => 'immediate',
                'status' => 'pending',
                'payment_mode' => $paymentMode,
                'total_amount' => $total,
                'discount_amount' => 0,
                'final_amount' => $total,
                'notes' => $notes,
            ]);

            foreach ($lines as $line) {
                OrderItem::query()->create([
                    'tenant_id' => $student->tenant_id,
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'item_name_snapshot' => $line['product']->name,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'total_price' => $line['total_price'],
                    'item_status' => 'pending',
                ]);
            }

            $order->load(['items.product.section', 'student']);
            $this->parentalControlService->assertPurchaseAllowed($order);

            if ($paymentMode === 'wallet') {
                $this->assertWalletReady($order);
            }

            if ($paymentMode === 'tab') {
                $this->tabService->assertAllowedForOrder($order);

                if ($parent) {
                    $this->pinService->recordAuthorization($order, null, true, null, $actor, 'app', 'parent');
                } else {
                    $this->pinService->assertValidForTabOrder($order, $studentPin, $actor, 'app');
                }
            }

            return $order->fresh(['items.product', 'student']);
        });
    }

    /**
     * Venda rápida no PDV do caixa: cria o pedido com itens e confirma na hora
     * (baixa estoque + efeitos financeiros).
     *
     * - cash / pix / card: aluno opcional (venda de balcão)
     * - wallet (ficha) / tab (conta): aluno + PIN obrigatórios
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $requestedItems
     */
    public function placeFromCashierPos(
        User $actor,
        int $schoolId,
        array $requestedItems,
        string $paymentMode,
        ?int $studentId = null,
        ?string $studentPin = null,
        ?string $notes = null,
    ): Order {
        $tenantId = (int) $actor->tenant_id;

        if (! in_array($paymentMode, ['cash', 'pix', 'card', 'wallet', 'tab'], true)) {
            throw ValidationException::withMessages([
                'payment_mode' => 'Forma de pagamento inválida.',
            ]);
        }

        $needsStudent = in_array($paymentMode, ['wallet', 'tab'], true);

        if ($needsStudent && ! $studentId) {
            throw ValidationException::withMessages([
                'student_id' => 'Selecione o aluno para pagar com ficha ou conta.',
            ]);
        }

        $student = null;
        if ($studentId) {
            $student = Student::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($studentId)
                ->first();

            if (! $student) {
                throw ValidationException::withMessages([
                    'student_id' => 'Aluno não encontrado.',
                ]);
            }

            if ((int) $student->school_id !== $schoolId) {
                throw ValidationException::withMessages([
                    'student_id' => 'O aluno não pertence à escola desta venda.',
                ]);
            }
        }

        if ($needsStudent && $student) {
            if ($studentPin === null || trim($studentPin) === '') {
                throw ValidationException::withMessages([
                    'student_pin' => 'Informe o PIN do aluno.',
                ]);
            }

            // Ficha (carteira): PIN validado aqui. Conta (fiado): PinService no commit.
            if ($paymentMode === 'wallet' && ! $this->pinService->verify($student, trim($studentPin))) {
                throw ValidationException::withMessages([
                    'student_pin' => 'PIN do aluno incorreto.',
                ]);
            }
        }

        $productIds = collect($requestedItems)->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->all();

        $products = Product::query()
            ->with(['section', 'stock'])
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach (array_values($requestedItems) as $index => $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($productId < 1 || $quantity < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}" => 'Item inválido.',
                ]);
            }

            $product = $products->get($productId);

            if (! $product || ! $product->active) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Produto indisponível.',
                ]);
            }

            if ($student) {
                $slug = $product->section?->slug;
                if ($slug === 'conveniencia' && ! $student->convenience_access) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'Aluno sem acesso à seção Conveniência.',
                    ]);
                }
                if ($slug === 'lanches' && ! $student->snack_access) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'Aluno sem acesso à seção Lanches.',
                    ]);
                }
            }

            if ($product->stock_controlled) {
                $available = (int) ($product->stock?->quantity ?? 0);
                if ($quantity > $available) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "Estoque insuficiente para {$product->name}.",
                    ]);
                }
            }

            $unitPrice = (float) $product->price;

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $quantity, 2),
            ];
        }

        if ($lines === []) {
            throw ValidationException::withMessages([
                'items' => 'Adicione ao menos um produto.',
            ]);
        }

        return DB::transaction(function () use ($actor, $tenantId, $schoolId, $student, $lines, $paymentMode, $notes, $studentPin) {
            $total = round(array_sum(array_column($lines, 'total_price')), 2);

            $order = Order::query()->create([
                'tenant_id' => $tenantId,
                'school_id' => $schoolId,
                'student_id' => $student?->id,
                'parent_id' => null,
                'placed_by_user_id' => $actor->id,
                'order_channel' => 'cashier',
                'order_type' => 'immediate',
                'status' => 'pending',
                'payment_mode' => $paymentMode,
                'total_amount' => $total,
                'discount_amount' => 0,
                'final_amount' => $total,
                'notes' => $notes,
            ]);

            foreach ($lines as $line) {
                OrderItem::query()->create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'item_name_snapshot' => $line['product']->name,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'total_price' => $line['total_price'],
                    'item_status' => 'pending',
                ]);
            }

            return $this->transitionStatus($order, 'confirmed', $actor, $studentPin);
        });
    }

    private function validateBeforeCommit(Order $order, ?string $studentPin, ?User $actor): void
    {
        if ($order->items->isEmpty()) {
            throw ValidationException::withMessages([
                'status' => 'Não é possível confirmar um pedido sem itens.',
            ]);
        }

        $this->parentalControlService->assertPurchaseAllowed($order);
        $this->stockService->assertAvailableForOrder($order);

        match ($order->payment_mode) {
            'wallet' => $this->assertWalletReady($order),
            'tab' => $this->assertTabReady($order, $studentPin, $actor),
            'cash', 'pix', 'card' => $this->assertImmediatePaymentReady($order),
            default => null,
        };
    }

    private function assertWalletReady(Order $order): void
    {
        if (! $order->student_id) {
            throw ValidationException::withMessages([
                'status' => 'Pedido com pagamento por carteira precisa de um aluno vinculado.',
            ]);
        }

        $wallet = StudentWallet::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('student_id', $order->student_id)
            ->first();

        if (! $wallet) {
            throw ValidationException::withMessages([
                'status' => 'Aluno não possui carteira cadastrada.',
            ]);
        }

        $amount = (float) $order->final_amount;
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'status' => 'Valor final do pedido inválido para débito na carteira.',
            ]);
        }

        $after = round((float) $wallet->balance - $amount, 2);

        if ($after < 0 && ! $wallet->allow_negative_balance) {
            $creditLimit = (float) $wallet->credit_limit;
            if ($after < -$creditLimit) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Saldo insuficiente na carteira (saldo: R$ %s, pedido: R$ %s).',
                        number_format((float) $wallet->balance, 2, ',', '.'),
                        number_format($amount, 2, ',', '.')
                    ),
                ]);
            }
        }
    }

    private function assertTabReady(Order $order, ?string $studentPin, ?User $actor): void
    {
        $this->tabService->assertAllowedForOrder($order);

        if ($this->pinService->orderAlreadyAuthorizedByPin($order)) {
            return;
        }

        $this->pinService->assertValidForTabOrder($order, $studentPin, $actor);
    }

    private function assertImmediatePaymentReady(Order $order): void
    {
        if ((float) $order->final_amount <= 0) {
            throw ValidationException::withMessages([
                'status' => 'Valor final do pedido inválido para registrar pagamento.',
            ]);
        }
    }

    private function applyPaymentEffects(Order $order, ?User $actor): void
    {
        match ($order->payment_mode) {
            'wallet' => $this->walletService->debitForOrder($order, $actor),
            'tab' => $this->chargeTab($order, $actor),
            'cash', 'pix', 'card' => $this->createImmediatePayment($order, $order->payment_mode, $actor),
            default => null,
        };
    }

    private function chargeTab(Order $order, ?User $actor): void
    {
        $entry = $this->tabService->chargeForOrder($order, true, $actor);
        $this->pinService->recordAuthorization($order, $entry, true, null, $actor);
    }

    private function createImmediatePayment(Order $order, string $method, ?User $actor): void
    {
        $exists = Payment::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('order_id', $order->id)
            ->where('status', 'completed')
            ->exists();

        if ($exists) {
            return;
        }

        Payment::query()->create([
            'tenant_id' => $order->tenant_id,
            'student_id' => $order->student_id,
            'parent_id' => $order->parent_id,
            'order_id' => $order->id,
            'tab_entry_id' => null,
            'amount' => $order->final_amount,
            'payment_method' => $method,
            'reference' => "order:{$order->id}",
            'status' => 'completed',
            'paid_at' => now(),
            'created_by' => $actor?->id,
        ]);
    }

    private function reverseSideEffects(Order $order, ?User $actor): void
    {
        $this->stockService->restoreForOrder($order, $actor);
        $this->walletService->refundForOrder($order, $actor);
        $this->tabService->cancelForOrder($order);
        $this->cancelPaymentsForOrder($order);
    }

    private function cancelPaymentsForOrder(Order $order): void
    {
        Payment::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('order_id', $order->id)
            ->where('status', 'completed')
            ->whereIn('payment_method', ['cash', 'pix', 'card'])
            ->update(['status' => 'cancelled']);
    }
}
