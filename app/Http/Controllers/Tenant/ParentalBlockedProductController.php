<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ParentalControl;
use App\Models\ParentalControlBlockedProduct;
use App\Models\Product;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentalBlockedProductController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $productId = $request->integer('product_id') ?: null;

        $items = ParentalControlBlockedProduct::query()
            ->with(['parentalControl.student', 'product'])
            ->where('tenant_id', $tenantId)
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($studentId, fn ($query) => $query->whereHas('parentalControl', fn ($q) => $q->where('student_id', $studentId)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.parental_blocked_products.index', [
            'title' => 'Produtos Bloqueados',
            'items' => $items,
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'studentId' => $studentId,
            'productId' => $productId,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parental_blocked_products.create', [
            'title' => 'Novo Produto Bloqueado',
            'controls' => ParentalControl::query()
                ->with('student')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('id')
                ->get(),
            'products' => Product::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateItem($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $item = ParentalControlBlockedProduct::query()->create($validated);

        return redirect()
            ->route('tenant.parental-blocked-products.show', $item)
            ->with('success', 'Produto bloqueado vinculado com sucesso.');
    }

    public function show(Request $request, ParentalControlBlockedProduct $parentalBlockedProduct): View
    {
        $this->ensureBelongsToTenant($request, $parentalBlockedProduct);
        $parentalBlockedProduct->load(['parentalControl.student', 'product']);

        return view('pages.tenant.parental_blocked_products.show', [
            'title' => 'Detalhes do Produto Bloqueado',
            'item' => $parentalBlockedProduct,
        ]);
    }

    public function edit(Request $request, ParentalControlBlockedProduct $parentalBlockedProduct): View
    {
        $this->ensureBelongsToTenant($request, $parentalBlockedProduct);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parental_blocked_products.edit', [
            'title' => 'Editar Produto Bloqueado',
            'item' => $parentalBlockedProduct,
            'controls' => ParentalControl::query()
                ->with('student')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('id')
                ->get(),
            'products' => Product::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, ParentalControlBlockedProduct $parentalBlockedProduct): RedirectResponse
    {
        $this->ensureBelongsToTenant($request, $parentalBlockedProduct);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateItem($request, $tenantId, $parentalBlockedProduct);
        $parentalBlockedProduct->update($validated);

        return redirect()
            ->route('tenant.parental-blocked-products.show', $parentalBlockedProduct)
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    private function validateItem(Request $request, int $tenantId, ?ParentalControlBlockedProduct $item = null): array
    {
        return $request->validate([
            'parental_control_id' => [
                'required',
                'integer',
                Rule::exists('parental_controls', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('parental_control_blocked_products', 'product_id')
                    ->ignore($item?->id)
                    ->where(fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('parental_control_id', $request->input('parental_control_id'))),
            ],
        ]);
    }

    private function ensureBelongsToTenant(Request $request, ParentalControlBlockedProduct $item): void
    {
        if ((int) $item->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
