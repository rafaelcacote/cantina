<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentalControl;
use App\Models\ParentalControlBlockedProduct;
use App\Models\Product;
use App\Models\Student;
use App\Services\TabService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ChildControlController extends Controller
{
    use ResolvesParentProfile;

    public function __construct(
        private readonly TabService $tabs,
    ) {}

    public function show(Request $request, Student $student): Response
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $link->load(['student.school', 'student.wallet']);

        $control = ParentalControl::query()
            ->with('blockedProducts')
            ->where('tenant_id', $parent->tenant_id)
            ->where('student_id', $student->id)
            ->first();

        $blockedIds = $control
            ? $control->blockedProducts->pluck('product_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $products = Product::query()
            ->with(['category', 'section'])
            ->where('tenant_id', $parent->tenant_id)
            ->where('active', true)
            ->where('visible_in_app', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'category' => $product->category?->name,
                'section' => $product->section?->name,
                'blocked' => in_array((int) $product->id, $blockedIds, true),
            ])
            ->values()
            ->all();

        return Inertia::render('Parent/ChildControls', [
            'child' => $this->presentChild($link),
            'control' => [
                'enabled' => (bool) ($control?->enabled ?? false),
                'daily_spending_limit' => $control?->daily_spending_limit !== null
                    ? (float) $control->daily_spending_limit
                    : null,
                'weekly_spending_limit' => $control?->weekly_spending_limit !== null
                    ? (float) $control->weekly_spending_limit
                    : null,
                'allow_tab_usage' => (bool) ($control?->allow_tab_usage ?? $student->can_buy_on_tab),
                'allow_wallet_usage' => (bool) ($control?->allow_wallet_usage ?? true),
                'allow_convenience_access' => (bool) ($control?->allow_convenience_access ?? $student->convenience_access),
                'allow_snack_access' => (bool) ($control?->allow_snack_access ?? $student->snack_access),
            ],
            'products' => $products,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $parent = $this->parentFor($request);
        $this->ensureOwnsStudent($parent, $student);

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'daily_spending_limit' => ['nullable', 'numeric', 'min:0'],
            'weekly_spending_limit' => ['nullable', 'numeric', 'min:0'],
            'allow_tab_usage' => ['required', 'boolean'],
            'allow_wallet_usage' => ['required', 'boolean'],
            'allow_convenience_access' => ['required', 'boolean'],
            'allow_snack_access' => ['required', 'boolean'],
            'blocked_product_ids' => ['nullable', 'array'],
            'blocked_product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $parent->tenant_id)
                    ->where('active', true)
                    ->where('visible_in_app', true)),
            ],
        ]);

        $enabled = (bool) $validated['enabled'];
        $allowTab = (bool) $validated['allow_tab_usage'];
        $blockedIds = collect($validated['blocked_product_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($parent, $student, $validated, $enabled, $allowTab, $blockedIds) {
            $control = ParentalControl::query()->updateOrCreate(
                [
                    'tenant_id' => $parent->tenant_id,
                    'student_id' => $student->id,
                ],
                [
                    'enabled' => $enabled,
                    'control_mode' => $enabled ? 'blocklist' : 'none',
                    'daily_spending_limit' => $validated['daily_spending_limit'] ?? null,
                    'weekly_spending_limit' => $validated['weekly_spending_limit'] ?? null,
                    'allow_tab_usage' => $allowTab,
                    'allow_wallet_usage' => (bool) $validated['allow_wallet_usage'],
                    'allow_convenience_access' => (bool) $validated['allow_convenience_access'],
                    'allow_snack_access' => (bool) $validated['allow_snack_access'],
                ],
            );

            $student->update([
                'can_buy_on_tab' => $allowTab,
                'convenience_access' => (bool) $validated['allow_convenience_access'],
                'snack_access' => (bool) $validated['allow_snack_access'],
            ]);

            if ($allowTab) {
                $this->tabs->ensureForStudent($student);
            }

            ParentalControlBlockedProduct::query()
                ->where('tenant_id', $parent->tenant_id)
                ->where('parental_control_id', $control->id)
                ->delete();

            foreach ($blockedIds as $productId) {
                ParentalControlBlockedProduct::query()->create([
                    'tenant_id' => $parent->tenant_id,
                    'parental_control_id' => $control->id,
                    'product_id' => $productId,
                ]);
            }
        });

        return redirect()
            ->route('parent.children.controls', $student)
            ->with('success', 'Controle parental atualizado.');
    }
}
