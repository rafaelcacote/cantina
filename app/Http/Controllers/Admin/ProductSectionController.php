<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductSectionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $tenantId = $request->integer('tenant_id') ?: null;

        $sections = ProductSection::query()
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($builder) use ($searchTerm) {
                    $builder->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('slug', 'like', "%{$searchTerm}%");
                });
            })
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.product_sections.index', [
            'title' => 'Seções de Produtos',
            'sections' => $sections,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'search' => $search,
            'tenantId' => $tenantId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.product_sections.create', [
            'title' => 'Nova Seção',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        ProductSection::create($this->payload($request, $validated));

        return redirect()
            ->route('admin.product-sections.index')
            ->with('success', 'Seção criada com sucesso.');
    }

    public function show(ProductSection $productSection): View
    {
        return view('pages.admin.product_sections.show', [
            'title' => 'Detalhes da Seção',
            'section' => $productSection,
            'tenantName' => DB::table('tenants')->where('id', $productSection->tenant_id)->value('name'),
        ]);
    }

    public function edit(ProductSection $productSection): View
    {
        return view('pages.admin.product_sections.edit', [
            'title' => 'Editar Seção',
            'section' => $productSection,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ProductSection $productSection): RedirectResponse
    {
        $validated = $request->validate($this->rules($productSection));
        $productSection->update($this->payload($request, $validated));

        return redirect()
            ->route('admin.product-sections.show', $productSection)
            ->with('success', 'Seção atualizada com sucesso.');
    }

    private function rules(?ProductSection $section = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_sections', 'slug')
                    ->ignore($section?->id)
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
