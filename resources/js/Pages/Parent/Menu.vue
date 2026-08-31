<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import ConfirmSheet from '@/Components/portal/ConfirmSheet.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { computed, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { formatMoney } from '@/composables/useFormat';

const props = defineProps({
    dateLabel: { type: String, required: true },
    menuTitle: { type: String, default: 'Produtos da cantina' },
    items: { type: Array, default: () => [] },
    child: { type: Object, required: true },
    checkoutHref: { type: String, required: true },
    cartKey: { type: String, required: true },
});

const qty = reactive({});
const query = ref('');
const reviewOpen = ref(false);
const selectedCategory = ref('all');

const formatDate = computed(() => props.dateLabel);

const categories = computed(() => {
    const seen = new Map();

    props.items.forEach((item) => {
        const id = item.category_id ?? 'uncategorized';
        const name = item.category || 'Outros';

        if (!seen.has(id)) {
            seen.set(id, { id, name, count: 0 });
        }

        seen.get(id).count += 1;
    });

    return [...seen.values()].sort((a, b) => a.name.localeCompare(b.name, 'pt-BR'));
});

const searchedItems = computed(() => {
    const term = query.value.trim().toLowerCase();

    if (!term) {
        return props.items;
    }

    return props.items.filter((item) =>
        [item.name, item.description, item.category].filter(Boolean).join(' ').toLowerCase().includes(term),
    );
});

const filteredItems = computed(() => {
    if (selectedCategory.value === 'all') {
        return searchedItems.value;
    }

    return searchedItems.value.filter((item) => {
        const id = item.category_id ?? 'uncategorized';

        return String(id) === String(selectedCategory.value);
    });
});

const groupedItems = computed(() => {
    const groups = new Map();

    filteredItems.value.forEach((item) => {
        const id = item.category_id ?? 'uncategorized';
        const name = item.category || 'Outros';

        if (!groups.has(id)) {
            groups.set(id, { id, name, items: [] });
        }

        groups.get(id).items.push(item);
    });

    return [...groups.values()];
});

const maxFor = (item) => (item.unlimited ? 20 : Math.max(0, Number(item.available || 0)));
const quantityOf = (item) => Number(qty[item.product_id] || 0);

const setQuantity = (item, next) => {
    qty[item.product_id] = Math.min(maxFor(item), Math.max(0, next));
};

const cartItems = computed(() =>
    props.items
        .filter((item) => quantityOf(item) > 0)
        .map((item) => ({
            product_id: item.product_id,
            name: item.name,
            price: item.price,
            quantity: quantityOf(item),
            image: item.image,
        })),
);

const cartCount = computed(() => cartItems.value.reduce((sum, item) => sum + item.quantity, 0));
const cartTotal = computed(() => cartItems.value.reduce((sum, item) => sum + item.price * item.quantity, 0));

const goToCheckout = () => {
    if (!cartItems.value.length) {
        return;
    }

    sessionStorage.setItem(props.cartKey, JSON.stringify(cartItems.value));
    reviewOpen.value = false;
    router.visit(props.checkoutHref);
};
</script>

<template>
    <Head :title="`Cardápio · ${child.name}`" />

    <MobileShell role="parent">
        <section class="space-y-6" :class="cartCount ? 'pb-28' : ''">
            <div>
                <BackLink :href="`/parent/children/${child.id}`" :label="child.name" />
                <div class="mt-5 flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/45">
                    <span class="flex size-6 items-center justify-center rounded-full bg-zest/70 text-ink">
                        <i class="pi pi-sparkles text-[9px]" />
                    </span>
                    Disponível hoje · {{ formatDate }}
                </div>
                <h2 class="font-display mt-3 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Pedir para {{ child.name }}
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-soft/60">
                    {{ menuTitle }}. O pedido entra na cantina em nome do seu filho.
                </p>
            </div>

            <div v-if="items.length" class="space-y-3">
                <div class="rounded-[1.2rem] bg-white/60 p-1 shadow-[0_8px_24px_rgba(20,36,31,0.05)] backdrop-blur">
                    <IconField>
                        <InputIcon class="pi pi-search !text-ink-soft/35" />
                        <InputText
                            v-model="query"
                            placeholder="O que você quer comer?"
                            class="w-full !border-0 !bg-transparent !shadow-none"
                        />
                    </IconField>
                </div>

                <div
                    v-if="categories.length > 1"
                    class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    <button
                        type="button"
                        class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="selectedCategory === 'all'
                            ? 'bg-ink text-zest shadow-[0_8px_18px_rgba(20,36,31,0.16)]'
                            : 'bg-white/75 text-ink-soft/70 ring-1 ring-line'"
                        @click="selectedCategory = 'all'"
                    >
                        Todos
                        <span class="ml-1 text-[10px] opacity-70">{{ items.length }}</span>
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        type="button"
                        class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="String(selectedCategory) === String(category.id)
                            ? 'bg-ink text-zest shadow-[0_8px_18px_rgba(20,36,31,0.16)]'
                            : 'bg-white/75 text-ink-soft/70 ring-1 ring-line'"
                        @click="selectedCategory = category.id"
                    >
                        {{ category.name }}
                        <span class="ml-1 text-[10px] opacity-70">{{ category.count }}</span>
                    </button>
                </div>
            </div>

            <div v-if="groupedItems.length" class="space-y-6">
                <section
                    v-for="group in groupedItems"
                    :key="group.id"
                    class="space-y-3"
                >
                    <div class="flex items-end justify-between gap-3 px-0.5">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">
                                Categoria
                            </p>
                            <h3 class="font-display mt-0.5 text-xl font-semibold leading-none text-ink">
                                {{ group.name }}
                            </h3>
                        </div>
                        <span class="rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-semibold text-ink-soft/50 ring-1 ring-line">
                            {{ group.items.length }}
                        </span>
                    </div>

                    <article
                        v-for="item in group.items"
                        :key="item.id"
                        class="portal-card overflow-hidden rounded-[1.5rem] transition"
                        :class="quantityOf(item) ? 'border-leaf/20 shadow-[0_12px_28px_rgba(61,122,95,0.08)]' : ''"
                    >
                        <div class="flex gap-3.5 p-3">
                            <div class="relative size-[5.75rem] shrink-0 overflow-hidden rounded-[1.2rem] bg-mist">
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    :alt="item.name"
                                    class="size-full object-cover"
                                >
                                <div
                                    v-else
                                    class="flex size-full items-center justify-center text-ink-soft/30"
                                >
                                    <i class="pi pi-image text-xl" />
                                </div>
                                <span
                                    v-if="quantityOf(item)"
                                    class="absolute right-1.5 top-1.5 flex size-6 items-center justify-center rounded-full bg-ink text-[10px] font-bold text-zest shadow-md"
                                >
                                    {{ quantityOf(item) }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1 py-0.5">
                                <h3 class="truncate font-display text-[1.05rem] font-semibold text-ink">{{ item.name }}</h3>
                                <p v-if="item.description" class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-ink-soft/55">
                                    {{ item.description }}
                                </p>

                                <div class="mt-2.5 flex items-end justify-between gap-2">
                                    <div>
                                        <p class="font-display text-lg font-semibold leading-none text-leaf-deep">
                                            {{ formatMoney(item.price) }}
                                        </p>
                                        <p v-if="!item.unlimited && item.can_order" class="mt-0.5 text-[11px] text-ink-soft/45">
                                            {{ item.available }} disponíveis
                                        </p>
                                    </div>

                                    <div v-if="item.can_order" class="flex items-center gap-1.5">
                                        <Button
                                            v-if="quantityOf(item) > 0"
                                            icon="pi pi-minus"
                                            rounded
                                            text
                                            severity="secondary"
                                            size="small"
                                            aria-label="Diminuir"
                                            @click="setQuantity(item, quantityOf(item) - 1)"
                                        />
                                        <span
                                            v-if="quantityOf(item) > 0"
                                            class="min-w-5 text-center text-sm font-semibold text-ink"
                                        >
                                            {{ quantityOf(item) }}
                                        </span>
                                        <Button
                                            icon="pi pi-plus"
                                            rounded
                                            size="small"
                                            aria-label="Adicionar"
                                            :disabled="quantityOf(item) >= maxFor(item)"
                                            @click="setQuantity(item, quantityOf(item) + 1)"
                                        />
                                    </div>
                                    <span v-else class="rounded-full bg-mist px-2.5 py-1 text-[10px] font-semibold text-ink-soft/45">
                                        Indisponível
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>
            </div>

            <div
                v-else
                class="portal-card rounded-[1.4rem] px-5 py-10 text-center"
            >
                <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-zest/70 text-ink">
                    <i class="pi pi-inbox text-xl" />
                </span>
                <p class="font-display mt-4 text-lg font-semibold text-ink">
                    {{ query ? 'Nada encontrado' : 'Nenhum produto disponível' }}
                </p>
                <p class="mx-auto mt-2 max-w-[30ch] text-sm text-ink-soft/60">
                    {{ query
                        ? 'Tente outro nome ou limpe a busca.'
                        : 'Assim que a cantina cadastrar produtos, eles aparecem aqui.' }}
                </p>
            </div>
        </section>

        <div
            v-if="cartCount"
            class="fixed inset-x-0 bottom-[calc(5.75rem+env(safe-area-inset-bottom))] z-30 px-4"
        >
            <button
                type="button"
                class="mx-auto flex w-full max-w-lg items-center justify-between rounded-[1.45rem] border border-white/10 bg-ink px-5 py-3.5 text-left text-foam shadow-[0_18px_42px_rgba(20,36,31,0.3)] transition active:scale-[0.99]"
                @click="reviewOpen = true"
            >
                <span>
                    <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-zest/85">
                        {{ cartCount }} {{ cartCount === 1 ? 'item' : 'itens' }}
                    </span>
                    <span class="font-display text-lg font-semibold">Ver sacola</span>
                </span>
                <span class="flex items-center gap-3">
                    <span class="font-display text-lg font-semibold text-zest">{{ formatMoney(cartTotal) }}</span>
                    <span class="flex size-8 items-center justify-center rounded-full bg-zest text-ink">
                        <i class="pi pi-arrow-right text-[11px]" />
                    </span>
                </span>
            </button>
        </div>

        <ConfirmSheet
            v-model:visible="reviewOpen"
            title="Revisar pedido"
            message="Confira os itens antes de ir para o pagamento."
            confirm-label="Continuar"
            icon="pi pi-shopping-bag"
            @confirm="goToCheckout"
        >
            <ul class="mt-4 divide-y divide-line rounded-2xl bg-mist/70 px-3">
                <li
                    v-for="item in cartItems"
                    :key="item.product_id"
                    class="flex items-center justify-between gap-3 py-2.5 text-sm"
                >
                    <span class="min-w-0 truncate text-ink">
                        {{ item.quantity }}× {{ item.name }}
                    </span>
                    <span class="shrink-0 font-semibold text-ink">
                        {{ formatMoney(item.price * item.quantity) }}
                    </span>
                </li>
            </ul>
            <div class="mt-3 flex items-center justify-between px-1">
                <span class="text-sm text-ink-soft/60">Total</span>
                <span class="font-display text-lg font-semibold text-ink">{{ formatMoney(cartTotal) }}</span>
            </div>
        </ConfirmSheet>
    </MobileShell>
</template>
