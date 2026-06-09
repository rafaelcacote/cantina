<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Product;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DailyMenuController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));
        $schoolId = $request->integer('school_id') ?: null;
        $menuDate = $request->string('menu_date')->toString();

        $dailyMenus = DailyMenu::query()
            ->with('school')
            ->where('tenant_id', $tenantId)
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when($menuDate, fn ($query) => $query->whereDate('menu_date', $menuDate))
            ->orderByDesc('menu_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.daily_menus.index', [
            'title' => 'Cardápios',
            'dailyMenus' => $dailyMenus,
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'search' => $search,
            'schoolId' => $schoolId,
            'menuDate' => $menuDate,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.daily_menus.create', [
            'title' => 'Novo Cardápio',
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateDailyMenu($request, $tenantId);
        $validated['tenant_id'] = $tenantId;
        $validated['active'] = $request->boolean('active');

        $dailyMenu = DailyMenu::query()->create($validated);

        return redirect()
            ->route('tenant.daily-menus.show', $dailyMenu)
            ->with('success', 'Cardápio criado com sucesso.');
    }

    public function show(Request $request, DailyMenu $dailyMenu): View
    {
        $this->ensureDailyMenuBelongsToTenant($request, $dailyMenu);
        $dailyMenu->load(['school', 'items.product']);

        return view('pages.tenant.daily_menus.show', [
            'title' => 'Detalhes do Cardápio',
            'dailyMenu' => $dailyMenu,
            'products' => Product::query()
                ->where('tenant_id', $dailyMenu->tenant_id)
                ->orderBy('name')
                ->get(['id', 'name', 'price', 'active']),
        ]);
    }

    public function edit(Request $request, DailyMenu $dailyMenu): View
    {
        $this->ensureDailyMenuBelongsToTenant($request, $dailyMenu);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.daily_menus.edit', [
            'title' => 'Editar Cardápio',
            'dailyMenu' => $dailyMenu,
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $this->ensureDailyMenuBelongsToTenant($request, $dailyMenu);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateDailyMenu($request, $tenantId, $dailyMenu);
        $validated['active'] = $request->boolean('active');

        $dailyMenu->update($validated);

        return redirect()
            ->route('tenant.daily-menus.show', $dailyMenu)
            ->with('success', 'Cardápio atualizado com sucesso.');
    }

    public function addItem(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $this->ensureDailyMenuBelongsToTenant($request, $dailyMenu);

        $validated = $request->validate($this->itemRules($dailyMenu));
        $validated['active'] = $request->boolean('active');
        $validated['tenant_id'] = $dailyMenu->tenant_id;
        $validated['daily_menu_id'] = $dailyMenu->id;

        DailyMenuItem::query()->create($validated);

        return redirect()
            ->route('tenant.daily-menus.show', $dailyMenu)
            ->with('success', 'Item adicionado ao cardápio com sucesso.');
    }

    public function updateItem(Request $request, DailyMenu $dailyMenu, DailyMenuItem $item): RedirectResponse
    {
        $this->ensureDailyMenuBelongsToTenant($request, $dailyMenu);
        $this->ensureItemBelongsToMenu($dailyMenu, $item);

        $validated = $request->validate($this->itemRules($dailyMenu, $item));
        $validated['active'] = $request->boolean('active');
        $validated['tenant_id'] = $dailyMenu->tenant_id;
        $validated['daily_menu_id'] = $dailyMenu->id;

        $item->update($validated);

        return redirect()
            ->route('tenant.daily-menus.show', $dailyMenu)
            ->with('success', 'Item do cardápio atualizado com sucesso.');
    }

    public function removeItem(Request $request, DailyMenu $dailyMenu, DailyMenuItem $item): RedirectResponse
    {
        $this->ensureDailyMenuBelongsToTenant($request, $dailyMenu);
        $this->ensureItemBelongsToMenu($dailyMenu, $item);

        $item->delete();

        return redirect()
            ->route('tenant.daily-menus.show', $dailyMenu)
            ->with('success', 'Item removido do cardápio com sucesso.');
    }

    private function validateDailyMenu(Request $request, int $tenantId, ?DailyMenu $dailyMenu = null): array
    {
        return $request->validate([
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'menu_date' => [
                'required',
                'date',
                Rule::unique('daily_menus', 'menu_date')
                    ->ignore($dailyMenu?->id)
                    ->where(function ($query) use ($tenantId, $request) {
                        $query->where('tenant_id', $tenantId)
                            ->where('school_id', $request->input('school_id'));
                    }),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);
    }

    private function itemRules(DailyMenu $dailyMenu, ?DailyMenuItem $item = null): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', $dailyMenu->tenant_id)),
                Rule::unique('daily_menu_items', 'product_id')
                    ->ignore($item?->id)
                    ->where(function ($query) use ($dailyMenu) {
                        $query->where('tenant_id', $dailyMenu->tenant_id)
                            ->where('daily_menu_id', $dailyMenu->id);
                    }),
            ],
            'planned_quantity' => ['nullable', 'integer', 'min:0'],
            'available_quantity' => ['nullable', 'integer', 'min:0'],
            'price_override' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    private function ensureDailyMenuBelongsToTenant(Request $request, DailyMenu $dailyMenu): void
    {
        if ((int) $dailyMenu->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }

    private function ensureItemBelongsToMenu(DailyMenu $dailyMenu, DailyMenuItem $item): void
    {
        if ((int) $item->daily_menu_id !== (int) $dailyMenu->id || (int) $item->tenant_id !== (int) $dailyMenu->tenant_id) {
            abort(404);
        }
    }
}
