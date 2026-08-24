@extends('layouts.pos')

@section('content')
<div
    class="flex h-dvh flex-col"
    x-data="posApp(@js([
        'products' => $products,
        'categories' => $categories,
        'schools' => $schools,
        'activeSchoolId' => $activeSchoolId,
        'schoolLocked' => $schoolLocked,
        'checkoutUrl' => $checkoutUrl,
        'studentsSearchUrl' => $studentsSearchUrl,
        'csrfToken' => $csrfToken,
    ]))"
    x-cloak
>
    {{-- Top bar --}}
    <header class="flex shrink-0 items-center gap-3 border-b border-slate-200 bg-white px-4 py-3">
        <a href="{{ route('operator.dashboard') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
            Sair do PDV
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="truncate text-lg font-semibold tracking-tight">PDV · Cantina</h1>
            <p class="truncate text-xs text-slate-500" x-text="schoolLabel"></p>
        </div>
        <template x-if="!schoolLocked && schools.length > 1">
            <select
                x-model.number="schoolId"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium"
            >
                <template x-for="school in schools" :key="school.id">
                    <option :value="school.id" x-text="school.name"></option>
                </template>
            </select>
        </template>
        <div
            x-show="flash"
            x-transition
            class="rounded-xl px-3 py-2 text-sm font-semibold"
            :class="flashOk ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
            x-text="flash"
        ></div>
    </header>

    <div class="flex min-h-0 flex-1 flex-col lg:flex-row">
        {{-- Products --}}
        <main class="flex min-h-0 min-w-0 flex-1 flex-col">
            <div class="flex shrink-0 gap-2 overflow-x-auto pos-scroll border-b border-slate-200 bg-white px-3 py-2">
                <button
                    type="button"
                    @click="categoryId = null"
                    class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold"
                    :class="categoryId === null ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'"
                >Todos</button>
                <template x-for="cat in categories" :key="cat.id">
                    <button
                        type="button"
                        @click="categoryId = cat.id"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold"
                        :class="categoryId === cat.id ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'"
                        x-text="cat.name"
                    ></button>
                </template>
            </div>

            <div class="pos-scroll min-h-0 flex-1 overflow-y-auto p-3">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button
                            type="button"
                            @click="addProduct(product)"
                            class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-sm active:scale-[0.98] active:bg-amber-50"
                            :disabled="product.stock_controlled && product.stock_qty !== null && product.stock_qty < 1"
                            :class="product.stock_controlled && product.stock_qty !== null && product.stock_qty < 1 ? 'opacity-40' : ''"
                        >
                            <div class="relative aspect-[4/3] w-full bg-slate-100">
                                <template x-if="product.image_url">
                                    <img
                                        :src="product.image_url"
                                        :alt="product.name"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                </template>
                                <template x-if="!product.image_url">
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                        <span
                                            class="text-3xl font-bold uppercase text-slate-400"
                                            x-text="(product.name || '?').charAt(0)"
                                        ></span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-1 flex-col justify-between gap-2 p-3">
                                <span class="line-clamp-2 text-sm font-semibold leading-snug sm:text-base" x-text="product.name"></span>
                                <span class="flex items-end justify-between gap-2">
                                    <span class="text-lg font-bold tabular-nums text-emerald-700" x-text="money(product.price)"></span>
                                    <span
                                        class="text-[11px] font-medium text-slate-400"
                                        x-show="product.stock_controlled"
                                        x-text="'Est. ' + (product.stock_qty ?? 0)"
                                    ></span>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
                <p x-show="filteredProducts.length === 0" class="py-16 text-center text-slate-500">Nenhum produto nesta categoria.</p>
            </div>
        </main>

        {{-- Cart --}}
        <aside class="flex h-[42vh] shrink-0 flex-col border-t border-slate-200 bg-white lg:h-auto lg:w-[380px] lg:border-l lg:border-t-0">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-base font-semibold">Carrinho</h2>
                <button
                    type="button"
                    @click="clearCart()"
                    class="text-sm font-medium text-rose-600"
                    x-show="cart.length"
                >Limpar</button>
            </div>

            <div class="pos-scroll min-h-0 flex-1 overflow-y-auto px-3 py-2">
                <template x-if="cart.length === 0">
                    <p class="py-8 text-center text-sm text-slate-400">Toque nos produtos para adicionar</p>
                </template>
                <template x-for="line in cart" :key="line.product_id">
                    <div class="mb-2 flex items-center gap-2 rounded-xl bg-slate-50 p-2">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold" x-text="line.name"></p>
                            <p class="text-xs tabular-nums text-slate-500" x-text="money(line.unit_price) + ' un.'"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="changeQty(line.product_id, -1)" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-xl font-bold shadow-sm">−</button>
                            <span class="w-8 text-center text-base font-bold tabular-nums" x-text="line.quantity"></span>
                            <button type="button" @click="changeQty(line.product_id, 1)" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-xl font-bold shadow-sm">+</button>
                        </div>
                        <p class="w-16 text-right text-sm font-bold tabular-nums" x-text="money(line.unit_price * line.quantity)"></p>
                    </div>
                </template>
            </div>

            <div class="shrink-0 space-y-3 border-t border-slate-200 p-4">
                <div class="flex items-baseline justify-between">
                    <span class="text-sm text-slate-500">Total</span>
                    <span class="text-2xl font-bold tabular-nums" x-text="money(total)"></span>
                </div>

                {{-- Student (optional until account/ficha) --}}
                <div class="rounded-xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Aluno</p>
                            <p class="truncate text-sm font-semibold" x-text="student ? student.name : 'Venda sem aluno'"></p>
                            <p class="truncate text-xs text-slate-500" x-show="student" x-text="studentMeta"></p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <button type="button" @click="openStudentModal()" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white">
                                <span x-text="student ? 'Trocar' : 'Vincular'"></span>
                            </button>
                            <button type="button" x-show="student" @click="clearStudent()" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600">Limpar</button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        @click="pay('cash')"
                        :disabled="!canCheckout || paying"
                        class="rounded-2xl bg-emerald-600 px-2 py-4 text-sm font-bold text-white disabled:opacity-40 active:bg-emerald-700"
                    >Dinheiro</button>
                    <button
                        type="button"
                        @click="pay('wallet')"
                        :disabled="!canCheckout || paying"
                        class="rounded-2xl bg-sky-600 px-2 py-4 text-sm font-bold text-white disabled:opacity-40 active:bg-sky-700"
                    >Ficha</button>
                    <button
                        type="button"
                        @click="pay('tab')"
                        :disabled="!canCheckout || paying"
                        class="rounded-2xl bg-amber-500 px-2 py-4 text-sm font-bold text-white disabled:opacity-40 active:bg-amber-600"
                    >Conta</button>
                </div>
                <p class="text-center text-[11px] text-slate-400">Ficha e Conta exigem aluno + PIN</p>
            </div>
        </aside>
    </div>

    {{-- Student search modal --}}
    <div
        x-show="studentModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4"
        @keydown.escape.window="studentModal = false"
    >
        <div
            @click.outside="studentModal = false"
            class="flex max-h-[85dvh] w-full max-w-lg flex-col rounded-t-3xl bg-white sm:rounded-3xl"
        >
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h3 class="text-base font-semibold">Buscar aluno</h3>
                <button type="button" @click="studentModal = false" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-500">Fechar</button>
            </div>
            <div class="p-4">
                <input
                    type="search"
                    x-model="studentQuery"
                    @input.debounce.300ms="searchStudents()"
                    placeholder="Nome ou matrícula…"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base outline-none focus:border-slate-400"
                    autocomplete="off"
                >
            </div>
            <div class="pos-scroll min-h-0 flex-1 overflow-y-auto px-4 pb-4">
                <template x-for="s in studentResults" :key="s.id">
                    <button
                        type="button"
                        @click="selectStudent(s)"
                        class="mb-2 flex w-full items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-left active:bg-amber-50"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-semibold" x-text="s.name"></p>
                            <p class="truncate text-xs text-slate-500" x-text="[s.enrollment_number, s.grade, s.classroom].filter(Boolean).join(' · ')"></p>
                        </div>
                        <div class="shrink-0 text-right text-xs">
                            <p class="font-semibold tabular-nums text-emerald-700" x-text="'R$ ' + Number(s.wallet_balance).toFixed(2).replace('.', ',')"></p>
                            <p class="text-slate-400" x-text="s.can_buy_on_tab ? 'Conta ok' : 'Sem conta'"></p>
                        </div>
                    </button>
                </template>
                <p x-show="studentQuery.length >= 2 && studentResults.length === 0 && !searchingStudents" class="py-6 text-center text-sm text-slate-400">Nenhum aluno encontrado</p>
            </div>
        </div>
    </div>

    {{-- PIN modal --}}
    <div
        x-show="pinModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center sm:p-4"
    >
        <div class="w-full max-w-sm rounded-t-3xl bg-white p-5 sm:rounded-3xl" @click.outside="closePinModal()">
            <h3 class="text-lg font-semibold" x-text="pendingMode === 'wallet' ? 'PIN da ficha' : 'PIN da conta'"></h3>
            <p class="mt-1 text-sm text-slate-500" x-text="student ? student.name : ''"></p>
            <input
                type="password"
                inputmode="numeric"
                maxlength="12"
                x-model="studentPin"
                x-ref="pinInput"
                class="mt-4 w-full rounded-2xl border border-slate-200 px-4 py-4 text-center text-2xl tracking-[0.4em] outline-none focus:border-slate-400"
                placeholder="••••"
                @keydown.enter="confirmPinPay()"
            >
            <p x-show="pinError" class="mt-2 text-center text-sm font-medium text-rose-600" x-text="pinError"></p>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" @click="closePinModal()" class="rounded-2xl border border-slate-200 py-3 text-sm font-semibold">Cancelar</button>
                <button type="button" @click="confirmPinPay()" :disabled="paying || !studentPin" class="rounded-2xl bg-slate-900 py-3 text-sm font-semibold text-white disabled:opacity-40">Confirmar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posApp', (config) => ({
        products: config.products || [],
        categories: config.categories || [],
        schools: config.schools || [],
        schoolId: config.activeSchoolId,
        schoolLocked: !!config.schoolLocked,
        checkoutUrl: config.checkoutUrl,
        studentsSearchUrl: config.studentsSearchUrl,
        csrfToken: config.csrfToken,

        categoryId: null,
        cart: [],
        student: null,
        studentModal: false,
        studentQuery: '',
        studentResults: [],
        searchingStudents: false,
        pinModal: false,
        studentPin: '',
        pinError: '',
        pendingMode: null,
        paying: false,
        flash: '',
        flashOk: true,
        flashTimer: null,

        get schoolLabel() {
            const school = this.schools.find((s) => s.id === this.schoolId);
            return school ? school.name : '';
        },

        get filteredProducts() {
            if (this.categoryId === null) return this.products;
            return this.products.filter((p) => p.category_id === this.categoryId);
        },

        get total() {
            return this.cart.reduce((sum, line) => sum + line.unit_price * line.quantity, 0);
        },

        get canCheckout() {
            return this.cart.length > 0 && !this.paying;
        },

        get studentMeta() {
            if (!this.student) return '';
            const parts = [
                this.student.enrollment_number,
                'Ficha R$ ' + Number(this.student.wallet_balance || 0).toFixed(2).replace('.', ','),
            ];
            return parts.filter(Boolean).join(' · ');
        },

        money(value) {
            return 'R$ ' + Number(value || 0).toFixed(2).replace('.', ',');
        },

        addProduct(product) {
            if (product.stock_controlled && product.stock_qty !== null && product.stock_qty < 1) {
                this.showFlash('Sem estoque: ' + product.name, false);
                return;
            }
            const existing = this.cart.find((l) => l.product_id === product.id);
            if (existing) {
                if (product.stock_controlled && product.stock_qty !== null && existing.quantity >= product.stock_qty) {
                    this.showFlash('Estoque insuficiente', false);
                    return;
                }
                existing.quantity += 1;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    unit_price: product.price,
                    quantity: 1,
                    stock_controlled: product.stock_controlled,
                    stock_qty: product.stock_qty,
                });
            }
        },

        changeQty(productId, delta) {
            const line = this.cart.find((l) => l.product_id === productId);
            if (!line) return;
            const next = line.quantity + delta;
            if (next < 1) {
                this.cart = this.cart.filter((l) => l.product_id !== productId);
                return;
            }
            if (line.stock_controlled && line.stock_qty !== null && next > line.stock_qty) {
                this.showFlash('Estoque insuficiente', false);
                return;
            }
            line.quantity = next;
        },

        clearCart() {
            this.cart = [];
        },

        openStudentModal() {
            this.studentModal = true;
            this.studentQuery = '';
            this.studentResults = [];
        },

        clearStudent() {
            this.student = null;
        },

        selectStudent(student) {
            this.student = student;
            this.studentModal = false;
            if (this.pendingMode === 'wallet' || this.pendingMode === 'tab') {
                if (this.pendingMode === 'tab' && !student.can_buy_on_tab) {
                    this.showFlash('Este aluno não pode comprar na conta', false);
                    this.pendingMode = null;
                    return;
                }
                this.studentPin = '';
                this.pinError = '';
                this.pinModal = true;
                this.$nextTick(() => this.$refs.pinInput?.focus());
            }
        },

        async searchStudents() {
            const q = this.studentQuery.trim();
            if (q.length < 2) {
                this.studentResults = [];
                return;
            }
            this.searchingStudents = true;
            try {
                const url = new URL(this.studentsSearchUrl, window.location.origin);
                url.searchParams.set('q', q);
                url.searchParams.set('school_id', String(this.schoolId));
                const res = await fetch(url.toString(), {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) {
                    this.studentResults = [];
                    return;
                }
                const data = await res.json();
                this.studentResults = data.students || [];
            } catch (e) {
                this.studentResults = [];
            } finally {
                this.searchingStudents = false;
            }
        },

        pay(mode) {
            if (!this.canCheckout) return;

            if (mode === 'cash') {
                this.submitSale(mode, null);
                return;
            }

            if (!this.student) {
                this.pendingMode = mode;
                this.openStudentModal();
                this.showFlash('Selecione o aluno para ' + (mode === 'wallet' ? 'ficha' : 'conta'), false);
                return;
            }

            if (mode === 'tab' && !this.student.can_buy_on_tab) {
                this.showFlash('Este aluno não pode comprar na conta', false);
                return;
            }

            this.pendingMode = mode;
            this.studentPin = '';
            this.pinError = '';
            this.pinModal = true;
            this.$nextTick(() => this.$refs.pinInput?.focus());
        },

        closePinModal() {
            this.pinModal = false;
            this.pendingMode = null;
            this.studentPin = '';
            this.pinError = '';
        },

        confirmPinPay() {
            if (!this.pendingMode || !this.studentPin) return;
            this.submitSale(this.pendingMode, this.studentPin);
        },

        async submitSale(mode, pin) {
            this.paying = true;
            this.pinError = '';
            try {
                const res = await fetch(this.checkoutUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        school_id: this.schoolId,
                        items: this.cart.map((l) => ({
                            product_id: l.product_id,
                            quantity: l.quantity,
                        })),
                        payment_mode: mode,
                        student_id: this.student ? this.student.id : null,
                        student_pin: pin,
                    }),
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    const msg = this.firstError(data) || 'Não foi possível concluir a venda.';
                    if (this.pinModal) {
                        this.pinError = msg;
                    } else {
                        this.showFlash(msg, false);
                    }
                    return;
                }

                this.applyLocalStock(data.order);
                this.closePinModal();
                this.clearCart();
                if (mode !== 'cash') {
                    this.clearStudent();
                }
                this.showFlash('Venda #' + data.order.id + ' · ' + this.money(data.order.final_amount), true);
            } catch (e) {
                this.showFlash('Falha de conexão. Tente de novo.', false);
            } finally {
                this.paying = false;
            }
        },

        applyLocalStock(order) {
            // Atualiza estoque local para refletir a venda na grade.
            this.cart.forEach((line) => {
                const product = this.products.find((p) => p.id === line.product_id);
                if (product && product.stock_controlled && product.stock_qty !== null) {
                    product.stock_qty = Math.max(0, product.stock_qty - line.quantity);
                }
            });
        },

        firstError(data) {
            if (!data || !data.errors) return data?.message || null;
            const key = Object.keys(data.errors)[0];
            return key ? data.errors[key][0] : null;
        },

        showFlash(message, ok) {
            this.flash = message;
            this.flashOk = ok;
            clearTimeout(this.flashTimer);
            this.flashTimer = setTimeout(() => { this.flash = ''; }, 3500);
        },
    }));
});
</script>
@endpush
