<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function hasDebitedForOrder(Order $order): bool
    {
        return StockMovement::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('movement_type', 'out')
            ->exists();
    }

    public function assertAvailableForOrder(Order $order): void
    {
        $order->loadMissing(['items.product.stock']);

        $errors = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product || ! $product->stock_controlled) {
                continue;
            }

            $stock = $product->stock;
            $available = $stock ? (int) $stock->quantity : 0;
            $needed = (int) $item->quantity;

            if ($available < $needed) {
                $errors[] = "Estoque insuficiente para \"{$item->item_name_snapshot}\" (disponível: {$available}, necessário: {$needed}).";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'status' => implode(' ', $errors),
            ]);
        }
    }

    public function debitForOrder(Order $order, ?User $actor = null): void
    {
        if ($this->hasDebitedForOrder($order)) {
            return;
        }

        $this->assertAvailableForOrder($order);

        $order->loadMissing(['items.product.stock']);

        DB::transaction(function () use ($order, $actor) {
            foreach ($order->items as $item) {
                $product = $item->product;
                if (! $product || ! $product->stock_controlled) {
                    continue;
                }

                $stock = Stock::query()
                    ->where('tenant_id', $order->tenant_id)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw ValidationException::withMessages([
                        'status' => "Produto \"{$item->item_name_snapshot}\" controla estoque, mas não possui registro de estoque.",
                    ]);
                }

                $previous = (int) $stock->quantity;
                $qty = (int) $item->quantity;
                $new = $previous - $qty;

                if ($new < 0) {
                    throw ValidationException::withMessages([
                        'status' => "Estoque insuficiente para \"{$item->item_name_snapshot}\" (disponível: {$previous}, necessário: {$qty}).",
                    ]);
                }

                $stock->update(['quantity' => $new]);

                StockMovement::query()->create([
                    'tenant_id' => $order->tenant_id,
                    'product_id' => $product->id,
                    'movement_type' => 'out',
                    'quantity' => $qty,
                    'previous_quantity' => $previous,
                    'new_quantity' => $new,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'description' => "Baixa automática do pedido #{$order->id}",
                    'created_by' => $actor?->id,
                ]);

                $this->notifyIfLowStock($product->fresh(), $stock->fresh(), $actor);
            }
        });
    }

    public function restoreForOrder(Order $order, ?User $actor = null): void
    {
        if (! $this->hasDebitedForOrder($order)) {
            return;
        }

        $order->loadMissing(['items.product']);

        DB::transaction(function () use ($order, $actor) {
            $outMovements = StockMovement::query()
                ->where('tenant_id', $order->tenant_id)
                ->where('reference_type', 'order')
                ->where('reference_id', $order->id)
                ->where('movement_type', 'out')
                ->get();

            foreach ($outMovements as $movement) {
                $alreadyRestored = StockMovement::query()
                    ->where('tenant_id', $order->tenant_id)
                    ->where('reference_type', 'order_cancel')
                    ->where('reference_id', $order->id)
                    ->where('product_id', $movement->product_id)
                    ->where('movement_type', 'in')
                    ->exists();

                if ($alreadyRestored) {
                    continue;
                }

                $stock = Stock::query()
                    ->where('tenant_id', $order->tenant_id)
                    ->where('product_id', $movement->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    continue;
                }

                $previous = (int) $stock->quantity;
                $qty = (int) $movement->quantity;
                $new = $previous + $qty;

                $stock->update(['quantity' => $new]);

                StockMovement::query()->create([
                    'tenant_id' => $order->tenant_id,
                    'product_id' => $movement->product_id,
                    'movement_type' => 'in',
                    'quantity' => $qty,
                    'previous_quantity' => $previous,
                    'new_quantity' => $new,
                    'reference_type' => 'order_cancel',
                    'reference_id' => $order->id,
                    'description' => "Estorno de estoque do pedido #{$order->id}",
                    'created_by' => $actor?->id,
                ]);
            }
        });
    }

    private function notifyIfLowStock(Product $product, Stock $stock, ?User $actor): void
    {
        if ((int) $stock->quantity > (int) $product->minimum_stock_alert) {
            return;
        }

        $alreadyNotified = Notification::query()
            ->where('tenant_id', $product->tenant_id)
            ->where('notification_type', 'low_stock')
            ->whereNull('read_at')
            ->where('payload->product_id', $product->id)
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        Notification::query()->create([
            'tenant_id' => $product->tenant_id,
            'user_id' => $actor?->id,
            'student_id' => null,
            'notification_type' => 'low_stock',
            'title' => 'Estoque baixo',
            'message' => "O produto \"{$product->name}\" está com estoque {$stock->quantity} (mínimo: {$product->minimum_stock_alert}).",
            'payload' => [
                'product_id' => $product->id,
                'stock_id' => $stock->id,
                'quantity' => (int) $stock->quantity,
                'minimum_stock_alert' => (int) $product->minimum_stock_alert,
            ],
            'read_at' => null,
        ]);
    }
}
