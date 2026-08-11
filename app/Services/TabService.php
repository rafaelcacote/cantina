<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\TabEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TabService
{
    public function ensureForStudent(Student $student): StudentTab
    {
        $tab = StudentTab::query()->firstOrCreate(
            [
                'tenant_id' => $student->tenant_id,
                'student_id' => $student->id,
            ],
            [
                'current_balance' => 0,
                'billing_cycle_type' => 'monthly',
                'active' => true,
            ],
        );

        if (! $tab->active) {
            $tab->update(['active' => true]);
        }

        return $tab;
    }

    public function hasChargedForOrder(Order $order): bool
    {
        return TabEntry::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('order_id', $order->id)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    public function assertAllowedForOrder(Order $order): void
    {
        $order->loadMissing(['student', 'items.product.section']);

        if (! $order->student_id || ! $order->student) {
            throw ValidationException::withMessages([
                'status' => 'Pedido no fiado precisa de um aluno vinculado.',
            ]);
        }

        if (! $order->student->can_buy_on_tab) {
            throw ValidationException::withMessages([
                'status' => 'Este aluno não está autorizado a comprar no fiado.',
            ]);
        }

        $tab = StudentTab::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('student_id', $order->student_id)
            ->where('active', true)
            ->first();

        if (! $tab) {
            throw ValidationException::withMessages([
                'status' => 'Aluno não possui conta de fiado ativa.',
            ]);
        }

        if ($order->items->isEmpty()) {
            throw ValidationException::withMessages([
                'status' => 'Pedido sem itens não pode ser lançado no fiado.',
            ]);
        }

        foreach ($order->items as $item) {
            $slug = $item->product?->section?->slug;
            if ($slug !== 'lanches') {
                $name = $item->item_name_snapshot ?: ($item->product?->name ?? 'item');
                throw ValidationException::withMessages([
                    'status' => "Fiado só é permitido para produtos da seção Lanches. \"{$name}\" não se qualifica.",
                ]);
            }
        }
    }

    public function chargeForOrder(Order $order, bool $authorizedByPin, ?User $actor = null): TabEntry
    {
        if ($this->hasChargedForOrder($order)) {
            return TabEntry::query()
                ->where('tenant_id', $order->tenant_id)
                ->where('order_id', $order->id)
                ->where('status', '!=', 'cancelled')
                ->firstOrFail();
        }

        $this->assertAllowedForOrder($order);

        $amount = (float) $order->final_amount;
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'status' => 'Valor final do pedido inválido para lançamento no fiado.',
            ]);
        }

        return DB::transaction(function () use ($order, $authorizedByPin, $actor, $amount) {
            $tab = StudentTab::query()
                ->where('tenant_id', $order->tenant_id)
                ->where('student_id', $order->student_id)
                ->where('active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $entry = TabEntry::query()->create([
                'tenant_id' => $order->tenant_id,
                'student_tab_id' => $tab->id,
                'student_id' => $order->student_id,
                'order_id' => $order->id,
                'amount' => $amount,
                'description' => "Lançamento do pedido #{$order->id}",
                'entry_date' => now()->toDateString(),
                'status' => 'open',
                'authorized_by_pin' => $authorizedByPin,
                'authorization_method' => $authorizedByPin ? 'pin' : 'manual',
                'authorized_at' => $authorizedByPin ? now() : null,
                'created_by' => $actor?->id,
            ]);

            $tab->recalculateBalance();

            return $entry;
        });
    }

    public function cancelForOrder(Order $order): void
    {
        $entry = TabEntry::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('order_id', $order->id)
            ->where('status', 'open')
            ->first();

        if (! $entry) {
            return;
        }

        DB::transaction(function () use ($entry) {
            $entry->update(['status' => 'cancelled']);
            $entry->studentTab?->recalculateBalance();
        });
    }

    /**
     * @return array{key: string, label: string, start: string, end: string, prev: string, next: string}
     */
    public function resolveMonth(?string $month): array
    {
        $parsed = is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month)
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $labels = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        return [
            'key' => $parsed->format('Y-m'),
            'label' => ($labels[(int) $parsed->month] ?? $parsed->format('F')).' '.$parsed->year,
            'start' => $parsed->toDateString(),
            'end' => $parsed->copy()->endOfMonth()->toDateString(),
            'prev' => $parsed->copy()->subMonthNoOverflow()->format('Y-m'),
            'next' => $parsed->copy()->addMonthNoOverflow()->format('Y-m'),
        ];
    }

    /**
     * @param  Collection<int, TabEntry>  $entries
     * @return array{charged: float, open: float, paid: float, count: int}
     */
    public function summarizeEntries(Collection $entries): array
    {
        $active = $entries->where('status', '!=', 'cancelled');

        return [
            'charged' => round((float) $active->sum(fn (TabEntry $entry) => (float) $entry->amount), 2),
            'open' => round((float) $active->where('status', 'open')->sum(fn (TabEntry $entry) => (float) $entry->amount), 2),
            'paid' => round((float) $active->where('status', 'paid')->sum(fn (TabEntry $entry) => (float) $entry->amount), 2),
            'count' => $active->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentEntry(TabEntry $entry): array
    {
        $items = $entry->relationLoaded('order') && $entry->order?->relationLoaded('items')
            ? $entry->order->items
            : collect();

        return [
            'id' => $entry->id,
            'amount' => (float) $entry->amount,
            'status' => $entry->status,
            'description' => $entry->description,
            'entry_date' => $entry->entry_date?->format('d/m'),
            'entry_date_full' => $entry->entry_date?->format('d/m/Y'),
            'order_id' => $entry->order_id,
            'student_id' => $entry->student_id,
            'student_name' => $entry->relationLoaded('student') ? $entry->student?->name : null,
            'item_count' => $items->count(),
            'preview' => $items->take(2)->pluck('item_name_snapshot')->filter()->implode(', ')
                ?: ($entry->description ?: 'Lançamento no fiado'),
        ];
    }
}
