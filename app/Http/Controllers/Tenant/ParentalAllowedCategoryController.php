<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ParentalControl;
use App\Models\ParentalControlAllowedCategory;
use App\Models\ProductCategory;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentalAllowedCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;

        $items = ParentalControlAllowedCategory::query()
            ->with(['parentalControl.student', 'category'])
            ->where('tenant_id', $tenantId)
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($studentId, fn ($query) => $query->whereHas('parentalControl', fn ($q) => $q->where('student_id', $studentId)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.parental_allowed_categories.index', [
            'title' => 'Categorias Permitidas',
            'items' => $items,
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'categories' => ProductCategory::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'studentId' => $studentId,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parental_allowed_categories.create', [
            'title' => 'Nova Categoria Permitida',
            'controls' => ParentalControl::query()
                ->with('student')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('id')
                ->get(),
            'categories' => ProductCategory::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateItem($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $item = ParentalControlAllowedCategory::query()->create($validated);

        return redirect()
            ->route('tenant.parental-allowed-categories.show', $item)
            ->with('success', 'Categoria permitida vinculada com sucesso.');
    }

    public function show(Request $request, ParentalControlAllowedCategory $parentalAllowedCategory): View
    {
        $this->ensureBelongsToTenant($request, $parentalAllowedCategory);
        $parentalAllowedCategory->load(['parentalControl.student', 'category']);

        return view('pages.tenant.parental_allowed_categories.show', [
            'title' => 'Detalhes da Categoria Permitida',
            'item' => $parentalAllowedCategory,
        ]);
    }

    public function edit(Request $request, ParentalControlAllowedCategory $parentalAllowedCategory): View
    {
        $this->ensureBelongsToTenant($request, $parentalAllowedCategory);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parental_allowed_categories.edit', [
            'title' => 'Editar Categoria Permitida',
            'item' => $parentalAllowedCategory,
            'controls' => ParentalControl::query()
                ->with('student')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('id')
                ->get(),
            'categories' => ProductCategory::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, ParentalControlAllowedCategory $parentalAllowedCategory): RedirectResponse
    {
        $this->ensureBelongsToTenant($request, $parentalAllowedCategory);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateItem($request, $tenantId, $parentalAllowedCategory);
        $parentalAllowedCategory->update($validated);

        return redirect()
            ->route('tenant.parental-allowed-categories.show', $parentalAllowedCategory)
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    private function validateItem(Request $request, int $tenantId, ?ParentalControlAllowedCategory $item = null): array
    {
        return $request->validate([
            'parental_control_id' => [
                'required',
                'integer',
                Rule::exists('parental_controls', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('product_categories', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('parental_control_allowed_categories', 'category_id')
                    ->ignore($item?->id)
                    ->where(fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('parental_control_id', $request->input('parental_control_id'))),
            ],
        ]);
    }

    private function ensureBelongsToTenant(Request $request, ParentalControlAllowedCategory $item): void
    {
        if ((int) $item->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
