<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentalControl;
use App\Models\ParentalControlBlockedProduct;
use App\Models\Product;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentalBlockedProductController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $productId = $request->integer('product_id') ?: null;

        $items = ParentalControlBlockedProduct::query()
            ->with(['parentalControl.student', 'product'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($studentId, fn ($query) => $query->whereHas('parentalControl', fn ($controlQuery) => $controlQuery->where('student_id', $studentId)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.parental_blocked_products.index', [
            'title' => 'Produtos Bloqueados',
            'items' => $items,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'products' => Product::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
            'productId' => $productId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.parental_blocked_products.create', [
            'title' => 'Novo Produto Bloqueado',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'controls' => ParentalControl::query()->with('student')->select(['id', 'tenant_id', 'student_id'])->orderByDesc('id')->get(),
            'products' => Product::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $item = ParentalControlBlockedProduct::create($validated);

        return redirect()
            ->route('admin.parental-blocked-products.show', $item)
            ->with('success', 'Produto bloqueado vinculado com sucesso.');
    }

    public function show(ParentalControlBlockedProduct $parentalBlockedProduct): View
    {
        $parentalBlockedProduct->load(['parentalControl.student', 'product']);

        return view('pages.admin.parental_blocked_products.show', [
            'title' => 'Detalhes do Produto Bloqueado',
            'item' => $parentalBlockedProduct,
            'tenantName' => DB::table('tenants')->where('id', $parentalBlockedProduct->tenant_id)->value('name'),
        ]);
    }

    public function edit(ParentalControlBlockedProduct $parentalBlockedProduct): View
    {
        return view('pages.admin.parental_blocked_products.edit', [
            'title' => 'Editar Produto Bloqueado',
            'item' => $parentalBlockedProduct,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'controls' => ParentalControl::query()->with('student')->select(['id', 'tenant_id', 'student_id'])->orderByDesc('id')->get(),
            'products' => Product::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ParentalControlBlockedProduct $parentalBlockedProduct): RedirectResponse
    {
        $validated = $request->validate($this->rules($parentalBlockedProduct));
        $parentalBlockedProduct->update($validated);

        return redirect()
            ->route('admin.parental-blocked-products.show', $parentalBlockedProduct)
            ->with('success', 'Vínculo de produto bloqueado atualizado com sucesso.');
    }

    private function rules(?ParentalControlBlockedProduct $item = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'parental_control_id' => [
                'required',
                'integer',
                Rule::exists('parental_controls', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
            ],
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
                Rule::unique('parental_control_blocked_products', 'product_id')
                    ->ignore($item?->id)
                    ->where(fn ($query) => $query
                        ->where('tenant_id', request('tenant_id'))
                        ->where('parental_control_id', request('parental_control_id'))),
            ],
        ];
    }
}
