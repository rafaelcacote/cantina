<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));
        $sectionId = $request->integer('section_id') ?: null;

        $categories = ProductCategory::query()
            ->with('section')
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($sectionId, fn ($query) => $query->where('section_id', $sectionId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.product_categories.index', [
            'title' => 'Categorias de Produtos',
            'categories' => $categories,
            'sections' => ProductSection::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'search' => $search,
            'sectionId' => $sectionId,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.product_categories.create', [
            'title' => 'Nova Categoria',
            'sections' => ProductSection::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateCategory($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $category = ProductCategory::query()->create($validated);

        return redirect()
            ->route('tenant.product-categories.show', $category)
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function show(Request $request, ProductCategory $productCategory): View
    {
        $this->ensureCategoryBelongsToTenant($request, $productCategory);
        $productCategory->load('section');

        return view('pages.tenant.product_categories.show', [
            'title' => 'Detalhes da Categoria',
            'category' => $productCategory,
        ]);
    }

    public function edit(Request $request, ProductCategory $productCategory): View
    {
        $this->ensureCategoryBelongsToTenant($request, $productCategory);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.product_categories.edit', [
            'title' => 'Editar Categoria',
            'category' => $productCategory,
            'sections' => ProductSection::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->ensureCategoryBelongsToTenant($request, $productCategory);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateCategory($request, $tenantId, $productCategory);
        $productCategory->update($validated);

        return redirect()
            ->route('tenant.product-categories.show', $productCategory)
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->ensureCategoryBelongsToTenant($request, $productCategory);

        if ($productCategory->products()->exists()) {
            return back()->withErrors([
                'delete' => 'Não é possível excluir a categoria enquanto houver produtos vinculados.',
            ]);
        }

        $productCategory->delete();

        return redirect()
            ->route('tenant.product-categories.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }

    private function validateCategory(Request $request, int $tenantId, ?ProductCategory $category = null): array
    {
        return $request->validate([
            'section_id' => [
                'required',
                'integer',
                Rule::exists('product_sections', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_categories', 'slug')
                    ->ignore($category?->id)
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'description' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ]);
    }

    private function ensureCategoryBelongsToTenant(Request $request, ProductCategory $category): void
    {
        if ((int) $category->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
