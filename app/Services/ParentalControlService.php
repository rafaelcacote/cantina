<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ParentalControl;
use App\Models\Product;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParentalControlService
{
    public function enabledControl(?Student $student): ?ParentalControl
    {
        if (! $student) {
            return null;
        }

        return ParentalControl::query()
            ->with(['blockedProducts', 'allowedCategories'])
            ->where('tenant_id', $student->tenant_id)
            ->where('student_id', $student->id)
            ->where('enabled', true)
            ->first();
    }

    public function studentCanSeeProduct(?Student $student, Product $product, ?ParentalControl $control = null): bool
    {
        $control ??= $this->enabledControl($student);
        $slug = $product->section?->slug;

        if ($control) {
            if ($slug === 'conveniencia' && ! $control->allow_convenience_access) {
                return false;
            }
            if ($slug === 'lanches' && ! $control->allow_snack_access) {
                return false;
            }
            if ($control->blockedProducts->contains('product_id', $product->id)) {
                return false;
            }
            if (in_array($control->control_mode, ['allowlist', 'mixed'], true)) {
                $allowedCategoryIds = $control->allowedCategories->pluck('category_id')->all();
                if ($allowedCategoryIds !== [] && ! in_array($product->category_id, $allowedCategoryIds, true)) {
                    return false;
                }
            }

            return true;
        }

        if ($slug === 'conveniencia' && $student && ! $student->convenience_access) {
            return false;
        }
        if ($slug === 'lanches' && $student && ! $student->snack_access) {
            return false;
        }

        return true;
    }

    public function assertPurchaseAllowed(Order $order): void
    {
        if (! $order->student_id) {
            return;
        }

        $order->loadMissing(['items.product.section', 'student']);

        $control = ParentalControl::query()
            ->with(['allowedCategories', 'blockedProducts'])
            ->where('tenant_id', $order->tenant_id)
            ->where('student_id', $order->student_id)
            ->where('enabled', true)
            ->first();

        if (! $control) {
            $this->assertStudentAccessFlags($order);

            return;
        }

        if ($order->payment_mode === 'tab' && ! $control->allow_tab_usage) {
            throw ValidationException::withMessages([
                'status' => 'Controle parental bloqueia uso de fiado para este aluno.',
            ]);
        }

        if ($order->payment_mode === 'wallet' && ! $control->allow_wallet_usage) {
            throw ValidationException::withMessages([
                'status' => 'Controle parental bloqueia uso de carteira para este aluno.',
            ]);
        }

        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $slug = $product->section?->slug;
            if ($slug === 'conveniencia' && ! $control->allow_convenience_access) {
                throw ValidationException::withMessages([
                    'status' => "Controle parental bloqueia conveniência. Item: {$item->item_name_snapshot}.",
                ]);
            }
            if ($slug === 'lanches' && ! $control->allow_snack_access) {
                throw ValidationException::withMessages([
                    'status' => "Controle parental bloqueia lanches. Item: {$item->item_name_snapshot}.",
                ]);
            }

            $blockedIds = $control->blockedProducts->pluck('product_id')->all();
            if (in_array($product->id, $blockedIds, true)) {
                throw ValidationException::withMessages([
                    'status' => "Produto \"{$item->item_name_snapshot}\" está bloqueado pelo controle parental.",
                ]);
            }

            if (in_array($control->control_mode, ['allowlist', 'mixed'], true)) {
                $allowedCategoryIds = $control->allowedCategories->pluck('category_id')->all();
                if ($allowedCategoryIds !== [] && ! in_array($product->category_id, $allowedCategoryIds, true)) {
                    throw ValidationException::withMessages([
                        'status' => "Categoria do produto \"{$item->item_name_snapshot}\" não é permitida pelo controle parental.",
                    ]);
                }
            }
        }

        $this->assertSpendingLimits($order, $control);
    }

    private function assertStudentAccessFlags(Order $order): void
    {
        $student = $order->student;
        if (! $student) {
            return;
        }

        foreach ($order->items as $item) {
            $slug = $item->product?->section?->slug;
            if ($slug === 'conveniencia' && ! $student->convenience_access) {
                throw ValidationException::withMessages([
                    'status' => 'Aluno sem acesso à seção Conveniência.',
                ]);
            }
            if ($slug === 'lanches' && ! $student->snack_access) {
                throw ValidationException::withMessages([
                    'status' => 'Aluno sem acesso à seção Lanches.',
                ]);
            }
        }
    }

    private function assertSpendingLimits(Order $order, ParentalControl $control): void
    {
        $amount = (float) $order->final_amount;

        if ($control->daily_spending_limit !== null) {
            $spentToday = (float) DB::table('orders')
                ->where('tenant_id', $order->tenant_id)
                ->where('student_id', $order->student_id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivered'])
                ->whereDate('created_at', now()->toDateString())
                ->sum('final_amount');

            if (($spentToday + $amount) > (float) $control->daily_spending_limit) {
                throw ValidationException::withMessages([
                    'status' => 'Pedido ultrapassa o limite diário do controle parental.',
                ]);
            }
        }

        if ($control->weekly_spending_limit !== null) {
            $spentWeek = (float) DB::table('orders')
                ->where('tenant_id', $order->tenant_id)
                ->where('student_id', $order->student_id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivered'])
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('final_amount');

            if (($spentWeek + $amount) > (float) $control->weekly_spending_limit) {
                throw ValidationException::withMessages([
                    'status' => 'Pedido ultrapassa o limite semanal do controle parental.',
                ]);
            }
        }
    }
}
