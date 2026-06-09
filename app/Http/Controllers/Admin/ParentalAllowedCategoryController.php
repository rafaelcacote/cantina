<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentalControl;
use App\Models\ParentalControlAllowedCategory;
use App\Models\ProductCategory;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentalAllowedCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;

        $items = ParentalControlAllowedCategory::query()
            ->with(['parentalControl.student', 'category'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($studentId, fn ($query) => $query->whereHas('parentalControl', fn ($controlQuery) => $controlQuery->where('student_id', $studentId)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.parental_allowed_categories.index', [
            'title' => 'Categorias Permitidas',
            'items' => $items,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'categories' => ProductCategory::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.parental_allowed_categories.create', [
            'title' => 'Nova Categoria Permitida',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'controls' => ParentalControl::query()->with('student')->select(['id', 'tenant_id', 'student_id'])->orderByDesc('id')->get(),
            'categories' => ProductCategory::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $item = ParentalControlAllowedCategory::create($validated);

        return redirect()
            ->route('admin.parental-allowed-categories.show', $item)
            ->with('success', 'Categoria permitida vinculada com sucesso.');
    }

    public function show(ParentalControlAllowedCategory $parentalAllowedCategory): View
    {
        $parentalAllowedCategory->load(['parentalControl.student', 'category']);

        return view('pages.admin.parental_allowed_categories.show', [
            'title' => 'Detalhes da Categoria Permitida',
            'item' => $parentalAllowedCategory,
            'tenantName' => DB::table('tenants')->where('id', $parentalAllowedCategory->tenant_id)->value('name'),
        ]);
    }

    public function edit(ParentalControlAllowedCategory $parentalAllowedCategory): View
    {
        return view('pages.admin.parental_allowed_categories.edit', [
            'title' => 'Editar Categoria Permitida',
            'item' => $parentalAllowedCategory,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'controls' => ParentalControl::query()->with('student')->select(['id', 'tenant_id', 'student_id'])->orderByDesc('id')->get(),
            'categories' => ProductCategory::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ParentalControlAllowedCategory $parentalAllowedCategory): RedirectResponse
    {
        $validated = $request->validate($this->rules($parentalAllowedCategory));
        $parentalAllowedCategory->update($validated);

        return redirect()
            ->route('admin.parental-allowed-categories.show', $parentalAllowedCategory)
            ->with('success', 'Vínculo de categoria permitida atualizado com sucesso.');
    }

    private function rules(?ParentalControlAllowedCategory $item = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'parental_control_id' => [
                'required',
                'integer',
                Rule::exists('parental_controls', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('product_categories', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
                Rule::unique('parental_control_allowed_categories', 'category_id')
                    ->ignore($item?->id)
                    ->where(fn ($query) => $query
                        ->where('tenant_id', request('tenant_id'))
                        ->where('parental_control_id', request('parental_control_id'))),
            ],
        ];
    }
}
