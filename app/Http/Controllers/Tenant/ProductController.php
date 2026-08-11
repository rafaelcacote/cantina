<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\Stock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const PRODUCT_TYPES = ['resale' => 'Revenda', 'production' => 'Produção'];

    private const SALE_TYPES = ['unit' => 'Unidade', 'weight' => 'Peso'];

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));
        $sectionId = $request->integer('section_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;

        $products = Product::query()
            ->with(['section', 'category'])
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($sectionId, fn ($query) => $query->where('section_id', $sectionId))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.products.index', [
            'title' => 'Produtos',
            'products' => $products,
            'sections' => ProductSection::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'categories' => ProductCategory::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'section_id', 'name']),
            'search' => $search,
            'sectionId' => $sectionId,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.products.create', [
            'title' => 'Novo Produto',
            'sections' => ProductSection::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'categories' => ProductCategory::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'section_id', 'name']),
            'productTypes' => self::PRODUCT_TYPES,
            'saleTypes' => self::SALE_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateProduct($request, $tenantId);
        $validated['tenant_id'] = $tenantId;
        $validated['image_url'] = $this->storeImage($request, $tenantId);
        unset($validated['image']);

        $product = Product::query()->create($validated);
        $this->ensureStockRecord($product);

        return redirect()
            ->route('tenant.products.show', $product)
            ->with('success', 'Produto criado com sucesso.');
    }

    public function show(Request $request, Product $product): View
    {
        $this->ensureProductBelongsToTenant($request, $product);
        $product->load(['section', 'category', 'stock']);

        return view('pages.tenant.products.show', [
            'title' => 'Detalhes do Produto',
            'product' => $product,
            'productTypes' => self::PRODUCT_TYPES,
            'saleTypes' => self::SALE_TYPES,
        ]);
    }

    public function edit(Request $request, Product $product): View
    {
        $this->ensureProductBelongsToTenant($request, $product);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.products.edit', [
            'title' => 'Editar Produto',
            'product' => $product,
            'sections' => ProductSection::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'categories' => ProductCategory::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'section_id', 'name']),
            'productTypes' => self::PRODUCT_TYPES,
            'saleTypes' => self::SALE_TYPES,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->ensureProductBelongsToTenant($request, $product);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateProduct($request, $tenantId);

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($product->image_url);
            $validated['image_url'] = $this->storeImage($request, $tenantId);
        }

        unset($validated['image']);

        $product->update($validated);
        $this->ensureStockRecord($product->fresh());

        return redirect()
            ->route('tenant.products.show', $product)
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->ensureProductBelongsToTenant($request, $product);

        if ($product->orderItems()->exists()) {
            return back()->withErrors([
                'delete' => 'Não é possível excluir o produto enquanto houver itens de pedido vinculados.',
            ]);
        }

        $product->delete();

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produto excluído com sucesso.');
    }

    private function validateProduct(Request $request, int $tenantId): array
    {
        return $request->validate([
            'section_id' => [
                'required',
                'integer',
                Rule::exists('product_sections', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('product_categories', 'id')->where(function ($query) use ($tenantId, $request) {
                    $query->where('tenant_id', $tenantId)
                        ->where('section_id', $request->input('section_id'));
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'product_type' => ['required', Rule::in(array_keys(self::PRODUCT_TYPES))],
            'sale_type' => ['required', Rule::in(array_keys(self::SALE_TYPES))],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
            'active' => ['required', 'boolean'],
            'visible_in_app' => ['required', 'boolean'],
            'allow_custom_request' => ['required', 'boolean'],
            'requires_preparation' => ['required', 'boolean'],
            'stock_controlled' => ['required', 'boolean'],
            'minimum_stock_alert' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function storeImage(Request $request, int $tenantId): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store("products/{$tenantId}", 'public');
    }

    private function deleteStoredImage(?string $imageUrl): void
    {
        if (! $imageUrl || str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($imageUrl);
    }

    private function ensureProductBelongsToTenant(Request $request, Product $product): void
    {
        if ((int) $product->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }

    private function ensureStockRecord(Product $product): void
    {
        if (! $product->stock_controlled || $product->stock) {
            return;
        }

        Stock::query()->create([
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);
    }
}
