<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\ParentalPreselectedOrder;
use App\Models\ParentalPreselectedOrderItem;
use App\Models\Product;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentalPreselectedOrderController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $schoolId = $request->integer('school_id') ?: null;
        $parentId = $request->integer('parent_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $status = $request->string('status')->toString();

        $orders = ParentalPreselectedOrder::query()
            ->with(['school', 'parent', 'student'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when($parentId, fn ($query) => $query->where('parent_id', $parentId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('order_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.parental_preselected_orders.index', [
            'title' => 'Pedidos Pré-definidos',
            'orders' => $orders,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'schools' => School::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'parents' => ParentGuardian::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'statuses' => $this->statuses(),
            'tenantId' => $tenantId,
            'schoolId' => $schoolId,
            'parentId' => $parentId,
            'studentId' => $studentId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.parental_preselected_orders.create', [
            'title' => 'Novo Pedido Pré-definido',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'schools' => School::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'parents' => ParentGuardian::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $order = ParentalPreselectedOrder::create($validated);

        return redirect()
            ->route('admin.parental-preselected-orders.show', $order)
            ->with('success', 'Pedido pré-definido criado com sucesso.');
    }

    public function show(ParentalPreselectedOrder $parentalPreselectedOrder): View
    {
        $parentalPreselectedOrder->load(['school', 'parent', 'student', 'items.product']);

        return view('pages.admin.parental_preselected_orders.show', [
            'title' => 'Detalhes do Pedido Pré-definido',
            'order' => $parentalPreselectedOrder,
            'tenantName' => DB::table('tenants')->where('id', $parentalPreselectedOrder->tenant_id)->value('name'),
            'statuses' => $this->statuses(),
            'products' => Product::query()
                ->select(['id', 'tenant_id', 'name'])
                ->where('tenant_id', $parentalPreselectedOrder->tenant_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(ParentalPreselectedOrder $parentalPreselectedOrder): View
    {
        return view('pages.admin.parental_preselected_orders.edit', [
            'title' => 'Editar Pedido Pré-definido',
            'order' => $parentalPreselectedOrder,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'schools' => School::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'parents' => ParentGuardian::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, ParentalPreselectedOrder $parentalPreselectedOrder): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $parentalPreselectedOrder->update($validated);

        return redirect()
            ->route('admin.parental-preselected-orders.show', $parentalPreselectedOrder)
            ->with('success', 'Pedido pré-definido atualizado com sucesso.');
    }

    public function addItem(Request $request, ParentalPreselectedOrder $parentalPreselectedOrder): RedirectResponse
    {
        $validated = $request->validate($this->itemRules($parentalPreselectedOrder));

        ParentalPreselectedOrderItem::create([
            'tenant_id' => $parentalPreselectedOrder->tenant_id,
            'parental_preselected_order_id' => $parentalPreselectedOrder->id,
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.parental-preselected-orders.show', $parentalPreselectedOrder)
            ->with('success', 'Item adicionado ao pedido pré-definido.');
    }

    public function updateItem(Request $request, ParentalPreselectedOrder $parentalPreselectedOrder, ParentalPreselectedOrderItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToOrder($parentalPreselectedOrder, $item);
        $validated = $request->validate($this->itemRules($parentalPreselectedOrder));

        $item->update([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.parental-preselected-orders.show', $parentalPreselectedOrder)
            ->with('success', 'Item do pedido pré-definido atualizado.');
    }

    public function removeItem(ParentalPreselectedOrder $parentalPreselectedOrder, ParentalPreselectedOrderItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToOrder($parentalPreselectedOrder, $item);
        $item->delete();

        return redirect()
            ->route('admin.parental-preselected-orders.show', $parentalPreselectedOrder)
            ->with('success', 'Item removido do pedido pré-definido.');
    }

    private function rules(): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
            'parent_id' => ['required', 'integer', Rule::exists('parents', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
            'order_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function itemRules(ParentalPreselectedOrder $order): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', $order->tenant_id))],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function ensureItemBelongsToOrder(ParentalPreselectedOrder $order, ParentalPreselectedOrderItem $item): void
    {
        if ((int) $item->parental_preselected_order_id !== (int) $order->id || (int) $item->tenant_id !== (int) $order->tenant_id) {
            abort(404);
        }
    }

    private function statuses(): array
    {
        return [
            'active' => 'Ativo',
            'paused' => 'Pausado',
            'cancelled' => 'Cancelado',
            'completed' => 'Concluído',
        ];
    }
}
