<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\WalletTopup;
use App\Services\WalletTopupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletTopupController extends Controller
{
    use ResolvesParentProfile;

    public function __construct(private readonly WalletTopupService $topups) {}

    public function create(Request $request, Student $student): Response|RedirectResponse
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $link->load(['student.school', 'student.wallet']);

        $pixKey = trim((string) $request->user()->tenant?->pix);

        if ($pixKey === '') {
            return redirect()
                ->route('parent.children.show', $student)
                ->with('error', 'A cantina ainda não cadastrou a chave Pix para recargas.');
        }

        return Inertia::render('Parent/TopupCreate', [
            'child' => $this->presentChild($link),
            'pixKey' => $pixKey,
        ]);
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        $parent = $this->parentFor($request);
        $this->ensureOwnsStudent($parent, $student);
        $parent->load('tenant');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:1000'],
        ], [
            'amount.required' => 'Informe o valor da recarga.',
            'amount.min' => 'O valor mínimo é R$ 1,00.',
            'amount.max' => 'O valor máximo é R$ 1.000,00.',
        ]);

        $topup = $this->topups->create($parent, $student, (float) $validated['amount']);

        return redirect()
            ->route('parent.topups.show', $topup)
            ->with('success', 'Solicitação criada. Copie a chave Pix e envie o comprovante depois do pagamento.');
    }

    public function show(Request $request, WalletTopup $walletTopup): Response
    {
        $parent = $this->parentFor($request);
        $this->ensureOwnsTopup($parent, $walletTopup);
        $walletTopup->load(['student.school', 'tenant']);

        return Inertia::render('Parent/TopupShow', [
            'topup' => $this->presentTopup($walletTopup),
        ]);
    }

    public function receipt(Request $request, WalletTopup $walletTopup): RedirectResponse
    {
        $parent = $this->parentFor($request);
        $this->ensureOwnsTopup($parent, $walletTopup);

        $validated = $request->validate([
            'receipt' => ['required', 'image', 'max:5120'],
        ], [
            'receipt.required' => 'Envie a foto do comprovante.',
            'receipt.image' => 'O comprovante precisa ser uma imagem.',
            'receipt.max' => 'A imagem pode ter no máximo 5 MB.',
        ]);

        $this->topups->attachReceipt($walletTopup, $validated['receipt']);

        return redirect()
            ->route('parent.topups.show', $walletTopup)
            ->with('success', 'Comprovante enviado. A cantina vai conferir e creditar a carteira.');
    }

    private function ensureOwnsTopup(ParentGuardian $parent, WalletTopup $topup): void
    {
        if (
            (int) $topup->tenant_id !== (int) $parent->tenant_id
            || (int) $topup->parent_id !== (int) $parent->id
        ) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function presentTopup(WalletTopup $topup): array
    {
        return [
            'id' => $topup->id,
            'code' => $topup->code,
            'amount' => (float) $topup->amount,
            'pix_key' => $topup->pix_key,
            'status' => $topup->status,
            'receipt_url' => $topup->receiptSrc(),
            'rejection_reason' => $topup->rejection_reason,
            'can_upload' => $topup->canUploadReceipt(),
            'whatsapp_url' => $topup->whatsappUrl(),
            'created_at' => $topup->created_at?->format('d/m/Y H:i'),
            'child' => [
                'id' => $topup->student?->id,
                'name' => $topup->student?->name ?? 'Aluno',
                'school' => $topup->student?->school?->name,
            ],
        ];
    }
}
