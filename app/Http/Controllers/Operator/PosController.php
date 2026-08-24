<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\School;
use App\Models\Student;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $schoolId = $user->scopedSchoolId();

        $schools = School::query()
            ->where('tenant_id', $tenantId)
            ->when($schoolId, fn ($q) => $q->whereKey($schoolId))
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($schools->isEmpty()) {
            abort(403, 'Nenhuma escola disponível para o operador.');
        }

        $activeSchoolId = $schoolId ?: (int) $schools->first()->id;

        $products = Product::query()
            ->with(['category:id,name', 'section:id,name,slug', 'stock:id,product_id,quantity'])
            ->where('tenant_id', $tenantId)
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'image_url' => $product->imageSrc(),
                'category_id' => $product->category_id,
                'section_name' => $product->section?->name,
                'stock_controlled' => (bool) $product->stock_controlled,
                'stock_qty' => $product->stock_controlled
                    ? (int) ($product->stock?->quantity ?? 0)
                    : null,
            ]);

        $categories = ProductCategory::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $products->pluck('category_id')->filter()->unique())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.operator.pos.index', [
            'title' => 'PDV',
            'schools' => $schools,
            'activeSchoolId' => $activeSchoolId,
            'schoolLocked' => (bool) $schoolId,
            'products' => $products,
            'categories' => $categories,
            // Relative paths: absolute APP_URL (e.g. http://localhost) breaks fetch when
            // the browser is on another host/port (e.g. 127.0.0.1:8000).
            'checkoutUrl' => route('operator.pos.checkout', absolute: false),
            'studentsSearchUrl' => route('operator.pos.students', absolute: false),
            'csrfToken' => csrf_token(),
        ]);
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $scopedSchoolId = $user->scopedSchoolId();
        $q = trim((string) $request->get('q', ''));
        $schoolId = (int) $request->get('school_id', $scopedSchoolId ?? 0);

        if ($scopedSchoolId && $schoolId !== $scopedSchoolId) {
            return response()->json(['students' => []]);
        }

        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json(['students' => []]);
        }

        $students = Student::query()
            ->with('wallet:id,student_id,balance')
            ->where('tenant_id', $tenantId)
            ->when($schoolId > 0, fn ($query) => $query->where('school_id', $schoolId))
            ->when($scopedSchoolId, fn ($query) => $query->where('school_id', $scopedSchoolId))
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('enrollment_number', 'like', "%{$q}%");
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(12)
            ->get(['id', 'name', 'enrollment_number', 'classroom', 'grade', 'can_buy_on_tab']);

        return response()->json([
            'students' => $students->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->name,
                'enrollment_number' => $student->enrollment_number,
                'classroom' => $student->classroom,
                'grade' => $student->grade,
                'can_buy_on_tab' => (bool) $student->can_buy_on_tab,
                'wallet_balance' => (float) ($student->wallet?->balance ?? 0),
            ]),
        ]);
    }

    public function checkout(Request $request, OrderService $orderService): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $scopedSchoolId = $user->scopedSchoolId();

        $validated = $request->validate([
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'payment_mode' => ['required', Rule::in(['cash', 'pix', 'card', 'wallet', 'tab'])],
            'student_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'student_pin' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $schoolId = (int) $validated['school_id'];

        if ($scopedSchoolId && $schoolId !== $scopedSchoolId) {
            throw ValidationException::withMessages([
                'school_id' => 'Você só pode vender na sua escola.',
            ]);
        }

        $order = $orderService->placeFromCashierPos(
            $user,
            $schoolId,
            $validated['items'],
            $validated['payment_mode'],
            isset($validated['student_id']) ? (int) $validated['student_id'] : null,
            $validated['student_pin'] ?? null,
            $validated['notes'] ?? null,
        );

        return response()->json([
            'ok' => true,
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'payment_mode' => $order->payment_mode,
                'final_amount' => (float) $order->final_amount,
                'student_id' => $order->student_id,
                'student_name' => $order->student?->name,
                'items_count' => $order->items->count(),
            ],
        ]);
    }
}
