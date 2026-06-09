<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $tenantId = $request->integer('tenant_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;

        $categories = ProductCategory::query()
            ->with('section')
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($builder) use ($searchTerm) {
                    $builder->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('slug', 'like', "%{$searchTerm}%");
                });
            })
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($sectionId, fn ($query) => $query->where('section_id', $sectionId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.product_categories.index', [
            'title' => 'Categorias de Produtos',
            'categories' => $categories,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'sections' => ProductSection::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'search' => $search,
            'tenantId' => $tenantId,
            'sectionId' => $sectionId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.product_categories.create', [
            'title' => 'Nova Categoria',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'sections' => ProductSection::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        ProductCategory::create($this->payload($request, $validated));

        return redirect()
            ->route('admin.product-categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function show(ProductCategory $productCategory): View
    {
        $productCategory->load('section');

        return view('pages.admin.product_categories.show', [
            'title' => 'Detalhes da Categoria',
            'category' => $productCategory,
            'tenantName' => DB::table('tenants')->where('id', $productCategory->tenant_id)->value('name'),
        ]);
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('pages.admin.product_categories.edit', [
            'title' => 'Editar Categoria',
            'category' => $productCategory,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'sections' => ProductSection::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $validated = $request->validate($this->rules($productCategory));
        $productCategory->update($this->payload($request, $validated));

        return redirect()
            ->route('admin.product-categories.show', $productCategory)
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    private function rules(?ProductCategory $category = null): array
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_categories', 'slug')
                    ->ignore($category?->id)
                    ->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
            ],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    private function payload(Request $request, array $validated): array
    {
        $validated['active'] = $request->boolean('active');

        return $validated;
    }
}
