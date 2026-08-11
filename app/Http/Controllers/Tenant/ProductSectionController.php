<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ProductSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductSectionController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));

        $sections = ProductSection::query()
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.product_sections.index', [
            'title' => 'Seções de Produtos',
            'sections' => $sections,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('pages.tenant.product_sections.create', [
            'title' => 'Nova Seção',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateSection($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $section = ProductSection::query()->create($validated);

        return redirect()
            ->route('tenant.product-sections.show', $section)
            ->with('success', 'Seção criada com sucesso.');
    }

    public function show(Request $request, ProductSection $productSection): View
    {
        $this->ensureSectionBelongsToTenant($request, $productSection);

        return view('pages.tenant.product_sections.show', [
            'title' => 'Detalhes da Seção',
            'section' => $productSection,
        ]);
    }

    public function edit(Request $request, ProductSection $productSection): View
    {
        $this->ensureSectionBelongsToTenant($request, $productSection);

        return view('pages.tenant.product_sections.edit', [
            'title' => 'Editar Seção',
            'section' => $productSection,
        ]);
    }

    public function update(Request $request, ProductSection $productSection): RedirectResponse
    {
        $this->ensureSectionBelongsToTenant($request, $productSection);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateSection($request, $tenantId, $productSection);
        $productSection->update($validated);

        return redirect()
            ->route('tenant.product-sections.show', $productSection)
            ->with('success', 'Seção atualizada com sucesso.');
    }

    public function destroy(Request $request, ProductSection $productSection): RedirectResponse
    {
        $this->ensureSectionBelongsToTenant($request, $productSection);

        if ($productSection->categories()->exists() || $productSection->products()->exists()) {
            return back()->withErrors([
                'delete' => 'Não é possível excluir a seção enquanto houver categorias ou produtos vinculados.',
            ]);
        }

        $productSection->delete();

        return redirect()
            ->route('tenant.product-sections.index')
            ->with('success', 'Seção excluída com sucesso.');
    }

    private function validateSection(Request $request, int $tenantId, ?ProductSection $section = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_sections', 'slug')
                    ->ignore($section?->id)
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'description' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ]);
    }

    private function ensureSectionBelongsToTenant(Request $request, ProductSection $section): void
    {
        if ((int) $section->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
