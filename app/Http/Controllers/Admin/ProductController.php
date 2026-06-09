<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $tenantId = $request->integer('tenant_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;

        $products = Product::query()
            ->with(['section', 'category'])
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($builder) use ($searchTerm) {
                    $builder->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('sku', 'like', "%{$searchTerm}%");
                });
            })
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($sectionId, fn ($query) => $query->where('section_id', $sectionId))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.products.index', [
            'title' => 'Produtos',
            'products' => $products,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'sections' => ProductSection::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'categories' => ProductCategory::query()->select(['id', 'tenant_id', 'section_id', 'name'])->orderBy('name')->get(),
            'search' => $search,
            'tenantId' => $tenantId,
            'sectionId' => $sectionId,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.products.create', [
            'title' => 'Novo Produto',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'sections' => ProductSection::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'categories' => ProductCategory::query()->select(['id', 'tenant_id', 'section_id', 'name'])->orderBy('name')->get(),
            'productTypes' => ['resale' => 'Revenda', 'production' => 'Produção'],
            'saleTypes' => ['unit' => 'Unidade', 'weight' => 'Peso'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Product::create($this->payload($request, $validated));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produto criado com sucesso.');
    }

    public function show(Product $product): View
    {
        $product->load(['section', 'category']);

        return view('pages.admin.products.show', [
            'title' => 'Detalhes do Produto',
            'product' => $product,
            'tenantName' => DB::table('tenants')->where('id', $product->tenant_id)->value('name'),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('pages.admin.products.edit', [
            'title' => 'Editar Produto',
            'product' => $product,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'sections' => ProductSection::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'categories' => ProductCategory::query()->select(['id', 'tenant_id', 'section_id', 'name'])->orderBy('name')->get(),
            'productTypes' => ['resale' => 'Revenda', 'production' => 'Produção'],
            'saleTypes' => ['unit' => 'Unidade', 'weight' => 'Peso'],
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate($this->rules($product));

        $product->update($this->payload($request, $validated));

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Produto atualizado com sucesso.');
    }

    private function rules(?Product $product = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'section_id' => [
                'required',
                'integer',
                Rule::exists('product_sections', 'id')->where(function ($query) {
                    $query->where('tenant_id', request('tenant_id'));
                }),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('product_categories', 'id')->where(function ($query) {
                    $query->where('tenant_id', request('tenant_id'))
                        ->where('section_id', request('section_id'));
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'product_type' => ['required', Rule::in(['resale', 'production'])],
            'sale_type' => ['required', Rule::in(['unit', 'weight'])],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'active' => ['nullable', 'boolean'],
            'visible_in_app' => ['nullable', 'boolean'],
            'allow_custom_request' => ['nullable', 'boolean'],
            'requires_preparation' => ['nullable', 'boolean'],
            'stock_controlled' => ['nullable', 'boolean'],
            'minimum_stock_alert' => ['required', 'integer', 'min:0'],
        ];
    }

    private function payload(Request $request, array $validated): array
    {
        $validated['active'] = $request->boolean('active');
        $validated['visible_in_app'] = $request->boolean('visible_in_app');
        $validated['allow_custom_request'] = $request->boolean('allow_custom_request');
        $validated['requires_preparation'] = $request->boolean('requires_preparation');
        $validated['stock_controlled'] = $request->boolean('stock_controlled');

        return $validated;
    }
}
