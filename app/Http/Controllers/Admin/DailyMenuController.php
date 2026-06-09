<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Product;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DailyMenuController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $tenantId = $request->integer('tenant_id') ?: null;
        $schoolId = $request->integer('school_id') ?: null;
        $menuDate = $request->string('menu_date')->toString();

        $dailyMenus = DailyMenu::query()
            ->with('school')
            ->when($search, fn ($query, $searchTerm) => $query->where('title', 'like', "%{$searchTerm}%"))
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when($menuDate, fn ($query) => $query->whereDate('menu_date', $menuDate))
            ->orderByDesc('menu_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.daily_menus.index', [
            'title' => 'Cardápios',
            'dailyMenus' => $dailyMenus,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'schools' => School::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'search' => $search,
            'tenantId' => $tenantId,
            'schoolId' => $schoolId,
            'menuDate' => $menuDate,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.daily_menus.create', [
            'title' => 'Novo Cardápio',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'schools' => School::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['active'] = $request->boolean('active');

        $dailyMenu = DailyMenu::create($validated);

        return redirect()
            ->route('admin.daily-menus.show', $dailyMenu)
            ->with('success', 'Cardápio criado com sucesso.');
    }

    public function show(DailyMenu $dailyMenu): View
    {
        $dailyMenu->load(['school', 'items.product']);

        return view('pages.admin.daily_menus.show', [
            'title' => 'Detalhes do Cardápio',
            'dailyMenu' => $dailyMenu,
            'tenantName' => DB::table('tenants')->where('id', $dailyMenu->tenant_id)->value('name'),
            'products' => Product::query()
                ->select(['id', 'tenant_id', 'name', 'price', 'active'])
                ->where('tenant_id', $dailyMenu->tenant_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(DailyMenu $dailyMenu): View
    {
        return view('pages.admin.daily_menus.edit', [
            'title' => 'Editar Cardápio',
            'dailyMenu' => $dailyMenu,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'schools' => School::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $validated = $request->validate($this->rules($dailyMenu));
        $validated['active'] = $request->boolean('active');

        $dailyMenu->update($validated);

        return redirect()
            ->route('admin.daily-menus.show', $dailyMenu)
            ->with('success', 'Cardápio atualizado com sucesso.');
    }

    public function addItem(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        $validated = $request->validate($this->itemRules($dailyMenu));
        $validated['active'] = $request->boolean('active');
        $validated['tenant_id'] = $dailyMenu->tenant_id;
        $validated['daily_menu_id'] = $dailyMenu->id;

        DailyMenuItem::create($validated);

        return redirect()
            ->route('admin.daily-menus.show', $dailyMenu)
            ->with('success', 'Item adicionado ao cardápio com sucesso.');
    }

    public function updateItem(Request $request, DailyMenu $dailyMenu, DailyMenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($dailyMenu, $item);

        $validated = $request->validate($this->itemRules($dailyMenu, $item));
        $validated['active'] = $request->boolean('active');
        $validated['tenant_id'] = $dailyMenu->tenant_id;
        $validated['daily_menu_id'] = $dailyMenu->id;

        $item->update($validated);

        return redirect()
            ->route('admin.daily-menus.show', $dailyMenu)
            ->with('success', 'Item do cardápio atualizado com sucesso.');
    }

    public function removeItem(DailyMenu $dailyMenu, DailyMenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($dailyMenu, $item);
        $item->delete();

        return redirect()
            ->route('admin.daily-menus.show', $dailyMenu)
            ->with('success', 'Item removido do cardápio com sucesso.');
    }

    private function rules(?DailyMenu $dailyMenu = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(function ($query) {
                    $query->where('tenant_id', request('tenant_id'));
                }),
            ],
            'menu_date' => [
                'required',
                'date',
                Rule::unique('daily_menus', 'menu_date')
                    ->ignore($dailyMenu?->id)
                    ->where(function ($query) {
                        $query->where('tenant_id', request('tenant_id'))
                            ->where('school_id', request('school_id'));
                    }),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    private function itemRules(DailyMenu $dailyMenu, ?DailyMenuItem $item = null): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) use ($dailyMenu) {
                    $query->where('tenant_id', $dailyMenu->tenant_id);
                }),
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

    private function ensureItemBelongsToMenu(DailyMenu $dailyMenu, DailyMenuItem $item): void
    {
        if ((int) $item->daily_menu_id !== (int) $dailyMenu->id || (int) $item->tenant_id !== (int) $dailyMenu->tenant_id) {
            abort(404);
        }
    }
}
