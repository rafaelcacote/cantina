<?php

namespace App\Http\Controllers\Tenant;

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

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $productId = $request->integer('product_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;
        $lowStock = $request->boolean('low_stock');

        $stocks = Stock::query()
            ->with(['product.section', 'product.category'])
            ->where('tenant_id', $tenantId)
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($sectionId, fn ($query) => $query->whereHas('product', fn ($q) => $q->where('section_id', $sectionId)))
            ->when($categoryId, fn ($query) => $query->whereHas('product', fn ($q) => $q->where('category_id', $categoryId)))
            ->when($lowStock, function ($query) {
                $query->whereHas('product', function ($productQuery) {
                    $productQuery->whereColumn('stocks.quantity', '<=', 'products.minimum_stock_alert');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.stocks.index', [
            'title' => 'Estoque',
            'stocks' => $stocks,
            'sections' => ProductSection::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'categories' => ProductCategory::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'section_id', 'name']),
            'products' => Product::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'section_id', 'category_id', 'name']),
            'sectionId' => $sectionId,
            'categoryId' => $categoryId,
            'productId' => $productId,
            'lowStock' => $lowStock,
        ]);
    }

    public function show(Request $request, Stock $stock): View
    {
        $this->ensureStockBelongsToTenant($request, $stock);
        $stock->load(['product.section', 'product.category']);

        $movements = StockMovement::query()
            ->with('creator')
            ->where('tenant_id', $stock->tenant_id)
            ->where('product_id', $stock->product_id)
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.tenant.stocks.show', [
            'title' => 'Detalhes do Estoque',
            'stock' => $stock,
            'movements' => $movements,
            'movementTypes' => $this->movementTypes(),
        ]);
    }

    public function edit(Request $request, Stock $stock): View
    {
        $this->ensureStockBelongsToTenant($request, $stock);
        $stock->load(['product.section', 'product.category']);

        return view('pages.tenant.stocks.edit', [
            'title' => 'Editar Estoque',
            'stock' => $stock,
            'movementTypes' => $this->movementTypes(),
        ]);
    }

    public function update(Request $request, Stock $stock): RedirectResponse
    {
        $this->ensureStockBelongsToTenant($request, $stock);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'reserved_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $stock->update($validated);

        return redirect()
            ->route('tenant.stocks.show', $stock)
            ->with('success', 'Estoque atualizado com sucesso.');
    }

    public function adjust(Request $request, Stock $stock): RedirectResponse
    {
        $this->ensureStockBelongsToTenant($request, $stock);

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

            StockMovement::query()->create([
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
            ->route('tenant.stocks.show', $stock)
            ->with('success', 'Ajuste de estoque realizado com sucesso.');
    }

    public function movements(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $productId = $request->integer('product_id') ?: null;
        $movementType = $request->string('movement_type')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $movements = StockMovement::query()
            ->with(['product.stock', 'creator'])
            ->where('tenant_id', $tenantId)
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($movementType, fn ($query) => $query->where('movement_type', $movementType))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.stocks.movements', [
            'title' => 'Movimentações de Estoque',
            'movements' => $movements,
            'products' => Product::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'movementTypes' => $this->movementTypes(),
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

    private function ensureStockBelongsToTenant(Request $request, Stock $stock): void
    {
        if ((int) $stock->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
