<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PurchaseAuthorization;
use App\Models\Student;
use App\Models\TabEntry;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PinService
{
    public function hash(string $pin): string
    {
        return Hash::make($pin);
    }

    public function assign(Student $student, string $pin): void
    {
        $pin = trim($pin);

        $student->forceFill([
            'personal_pin' => $pin,
            'personal_pin_hash' => $this->hash($pin),
        ])->save();
    }

    public function applyToPayload(array $payload, ?string $pin): array
    {
        if (! is_string($pin) || trim($pin) === '') {
            unset($payload['personal_pin']);

            return $payload;
        }

        $pin = trim($pin);
        $payload['personal_pin'] = $pin;
        $payload['personal_pin_hash'] = $this->hash($pin);

        return $payload;
    }

    public function reveal(Student $student): ?string
    {
        $plain = $student->personal_pin;
        if (is_string($plain) && $plain !== '') {
            return $plain;
        }

        $stored = $student->personal_pin_hash;
        if ($stored && ! $this->isHashed($stored)) {
            return (string) $stored;
        }

        return null;
    }

    public function hasPin(Student $student): bool
    {
        return filled($student->personal_pin) || filled($student->personal_pin_hash);
    }

    public function isHashed(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return str_starts_with($value, '$2y$')
            || str_starts_with($value, '$2a$')
            || str_starts_with($value, '$argon');
    }

    public function verify(Student $student, string $pin): bool
    {
        $plain = $student->personal_pin;
        if (is_string($plain) && $plain !== '') {
            return hash_equals($plain, $pin);
        }

        $stored = $student->personal_pin_hash;
        if ($stored === null || $stored === '') {
            return false;
        }

        if ($this->isHashed($stored)) {
            return Hash::check($pin, $stored);
        }

        // Compatibilidade com PINs legados em texto puro.
        return hash_equals((string) $stored, $pin);
    }

    public function assertValidForTabOrder(Order $order, ?string $pin, ?User $actor = null, string $deviceType = 'web'): void
    {
        if ($this->hasSuccessfulPinAuthorization($order)) {
            return;
        }

        $order->loadMissing('student');

        if (! $order->student) {
            throw ValidationException::withMessages([
                'student_pin' => 'Pedido no fiado precisa de um aluno com PIN cadastrado.',
            ]);
        }

        if ($pin === null || trim($pin) === '') {
            $this->recordAuthorization($order, null, false, 'PIN não informado', $actor, $deviceType);

            throw ValidationException::withMessages([
                'student_pin' => 'Informe o PIN do aluno para confirmar compra no fiado.',
            ]);
        }

        $ok = $this->verify($order->student, $pin);

        if (! $ok) {
            $this->recordAuthorization($order, null, false, 'PIN inválido', $actor, $deviceType);

            throw ValidationException::withMessages([
                'student_pin' => 'PIN do aluno inválido.',
            ]);
        }

        $this->recordAuthorization($order, null, true, null, $actor, $deviceType);
    }

    public function hasSuccessfulPinAuthorization(Order $order): bool
    {
        return $order->purchaseAuthorizations()
            ->where('success', true)
            ->where('auth_method', 'pin')
            ->exists();
    }

    public function orderAlreadyAuthorizedByPin(Order $order): bool
    {
        if ($order->payment_mode !== 'tab') {
            return false;
        }

        if ($this->hasSuccessfulPinAuthorization($order)) {
            return true;
        }

        $order->loadMissing('student');

        // Pedidos do app já exigem PIN no checkout; cobre solicitações anteriores à gravação da autorização.
        return $order->order_channel === 'app'
            && $order->student
            && $order->student->user_id
            && (int) $order->placed_by_user_id === (int) $order->student->user_id;
    }

    public function recordAuthorization(
        Order $order,
        ?TabEntry $tabEntry,
        bool $success,
        ?string $failureReason = null,
        ?User $actor = null,
        string $deviceType = 'web',
    ): PurchaseAuthorization {
        return PurchaseAuthorization::query()->create([
            'tenant_id' => $order->tenant_id,
            'school_id' => $order->school_id,
            'student_id' => $order->student_id,
            'order_id' => $order->id,
            'tab_entry_id' => $tabEntry?->id,
            'authorization_type' => 'tab_confirmation',
            'auth_method' => 'pin',
            'success' => $success,
            'failure_reason' => $failureReason,
            'device_type' => $deviceType,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_by' => $actor?->id,
        ]);
    }
}
