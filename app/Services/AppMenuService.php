<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Student;

class AppMenuService
{
    public function __construct(
        private readonly ParentalControlService $parentalControlService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function catalogForStudent(?Student $student, int $tenantId): array
    {
        $control = $this->parentalControlService->enabledControl($student);

        return Product::query()
            ->with(['section', 'category', 'stock'])
            ->where('tenant_id', $tenantId)
            ->where('active', true)
            ->where('visible_in_app', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (Product $product) => $this->parentalControlService->studentCanSeeProduct($student, $product, $control))
            ->map(function (Product $product) {
                $stockControlled = (bool) $product->stock_controlled;
                $available = $stockControlled ? (int) ($product->stock?->quantity ?? 0) : null;

                return [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->imageSrc(),
                    'available' => $available,
                    'unlimited' => ! $stockControlled,
                    'can_order' => ! $stockControlled || $available > 0,
                    'price' => (float) $product->price,
                    'category_id' => $product->category_id,
                    'category' => $product->category?->name,
                ];
            })
            ->sortBy([
                fn (array $item) => mb_strtolower($item['category'] ?? 'zzz'),
                fn (array $item) => mb_strtolower($item['name']),
            ])
            ->values()
            ->all();
    }
}
