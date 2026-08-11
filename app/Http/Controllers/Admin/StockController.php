<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $searchProduct = $request->integer('product_id') ?: null;
        $tenantId = $request->integer('tenant_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;
        $lowStock = $request->boolean('low_stock');

        $stocks = Stock::query()
            ->with(['product.section', 'product.category'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($searchProduct, fn ($query) => $query->where('product_id', $searchProduct))
            ->when($sectionId, fn ($query) => $query->whereHas('product', fn ($query) => $query->where('section_id', $sectionId)))
            ->when($categoryId, fn ($query) => $query->whereHas('product', fn ($query) => $query->where('category_id', $categoryId)))
            ->when($lowStock, function ($query) {
                $query->whereHas('product', function ($productQuery) {
                    $productQuery->whereColumn('stocks.quantity', '<=', 'products.minimum_stock_alert');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.stocks.index', [
            'title' => 'Estoque',
            'stocks' => $stocks,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'sections' => ProductSection::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'categories' => ProductCategory::query()->select(['id', 'tenant_id', 'section_id', 'name'])->orderBy('name')->get(),
            'products' => Product::query()->select(['id', 'tenant_id', 'section_id', 'category_id', 'name'])->orderBy('name')->get(),
            'tenantId' => $tenantId,
            'sectionId' => $sectionId,
            'categoryId' => $categoryId,
            'productId' => $searchProduct,
            'lowStock' => $lowStock,
        ]);
    }

    public function show(Stock $stock): View
    {
        $this->ensureStockBelongsToProductTenant($stock);
        $stock->load(['product.section', 'product.category']);

        $movements = StockMovement::query()
            ->with('creator')
            ->where('tenant_id', $stock->tenant_id)
            ->where('product_id', $stock->product_id)
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.admin.stocks.show', [
            'title' => 'Detalhes do Estoque',
            'stock' => $stock,
            'movements' => $movements,
            'tenantName' => DB::table('tenants')->where('id', $stock->tenant_id)->value('name'),
            'movementTypes' => $this->movementTypes(),
        ]);
    }

    public function edit(Stock $stock): View
    {
        $this->ensureStockBelongsToProductTenant($stock);
        $stock->load(['product.section', 'product.category']);

        return view('pages.admin.stocks.edit', [
            'title' => 'Editar Estoque',
            'stock' => $stock,
            'tenantName' => DB::table('tenants')->where('id', $stock->tenant_id)->value('name'),
            'movementTypes' => $this->movementTypes(),
        ]);
    }

    public function update(Request $request, Stock $stock): RedirectResponse
    {
        $this->ensureStockBelongsToProductTenant($stock);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'reserved_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $previousQuantity = (int) $stock->quantity;
        $newQuantity = (int) $validated['quantity'];

        DB::transaction(function () use ($stock, $request, $validated, $previousQuantity, $newQuantity) {
            $stock->update($validated);

            if ($previousQuantity !== $newQuantity) {
                StockMovement::create([
                    'tenant_id' => $stock->tenant_id,
                    'product_id' => $stock->product_id,
                    'movement_type' => 'adjustment',
                    'quantity' => $newQuantity,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $newQuantity,
                    'description' => 'Edição direta do estoque',
                    'created_by' => $request->user()->id,
                    'reference_type' => 'direct_edit',
                    'reference_id' => $stock->id,
                ]);
            }
        });

        return redirect()
            ->route('admin.stocks.show', $stock)
            ->with('success', 'Estoque atualizado com sucesso.');
    }

    public function adjust(Request $request, Stock $stock): RedirectResponse
    {
        $this->ensureStockBelongsToProductTenant($stock);

        $validated = $request->validate([
            'movement_type' => ['required', Rule::in(array_keys($this->movementTypes()))],
            'quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $previousQuantity = (int) $stock->quantity;
        $newQuantity = $this->calculateNewQuantity(
            $validated['movement_type'],
            $previousQuantity,
            (int) $validated['quantity']
        );

        if ($newQuantity < 0) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => 'A quantidade não pode ficar negativa após o ajuste.']);
        }

        DB::transaction(function () use ($stock, $request, $validated, $previousQuantity, $newQuantity) {
            $stock->update(['quantity' => $newQuantity]);

            StockMovement::create([
                'tenant_id' => $stock->tenant_id,
                'product_id' => $stock->product_id,
                'movement_type' => $validated['movement_type'],
                'quantity' => (int) $validated['quantity'],
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'description' => $validated['description'] ?? null,
                'created_by' => $request->user()->id,
                'reference_type' => 'manual_adjustment',
            ]);
        });

        return redirect()
            ->route('admin.stocks.show', $stock)
            ->with('success', 'Ajuste de estoque realizado com sucesso.');
    }

    public function movements(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $productId = $request->integer('product_id') ?: null;
        $movementType = $request->string('movement_type')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $movements = StockMovement::query()
            ->with(['product.stock', 'creator'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($movementType, fn ($query) => $query->where('movement_type', $movementType))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.stocks.movements', [
            'title' => 'Movimentações de Estoque',
            'movements' => $movements,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'products' => Product::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'movementTypes' => $this->movementTypes(),
            'tenantId' => $tenantId,
            'productId' => $productId,
            'movementType' => $movementType,
            'from' => $from,
            'to' => $to,
        ]);
    }

    private function movementTypes(): array
    {
        return [
            'in' => 'Entrada',
            'out' => 'Saída',
            'adjustment' => 'Ajuste',
            'loss' => 'Perda',
            'production' => 'Produção',
        ];
    }

    private function calculateNewQuantity(string $movementType, int $previousQuantity, int $quantity): int
    {
        return match ($movementType) {
            'in', 'production' => $previousQuantity + $quantity,
            'out', 'loss' => $previousQuantity - $quantity,
            'adjustment' => $quantity,
            default => $previousQuantity,
        };
    }

    private function ensureStockBelongsToProductTenant(Stock $stock): void
    {
        $productTenantId = Product::query()
            ->withoutGlobalScope('tenant')
            ->whereKey($stock->product_id)
            ->value('tenant_id');

        if (! $productTenantId || (int) $productTenantId !== (int) $stock->tenant_id) {
            throw new HttpException(422, 'Estoque e produto devem pertencer ao mesmo tenant.');
        }
    }
}
