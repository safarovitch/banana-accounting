<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    TruckIcon,
    ClockIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    orders: Object,
    filters: Object,
    summary: Object,
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

const applyFilters = debounce(() => {
    router.get(route('suppliers.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, replace: true });
}, 300);

watch(search, applyFilters);
watch(statusFilter, applyFilters);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'TJS' }).format(amount);
};

const paidPercentage = (order) => {
    if (!order.total_expected_cost || order.total_expected_cost === 0) return 0;
    return Math.min(100, Math.round((order.paid_amount / order.total_expected_cost) * 100));
};
</script>

<template>
    <Head title="Поставщики" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Page Header -->
                <div class="flex justify-between items-center mb-6 px-4 sm:px-0">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Поставщики</h2>
                    <Link
                        :href="route('suppliers.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 transition ease-in-out duration-150"
                    >
                        <PlusIcon class="h-4 w-4 mr-2" />
                        <span class="hidden sm:inline">Новый заказ</span>
                        <span class="sm:hidden">Добавить</span>
                    </Link>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 px-4 sm:px-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 h-12 w-12 bg-blue-50 rounded-xl flex items-center justify-center">
                            <TruckIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Всего заказов</p>
                            <p class="text-lg font-bold text-gray-800">{{ summary.totalOrders }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 h-12 w-12 bg-amber-50 rounded-xl flex items-center justify-center">
                            <ClockIcon class="h-6 w-6 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ожидают поставки</p>
                            <p class="text-lg font-bold text-amber-600">{{ summary.preorderCount }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 h-12 w-12 bg-red-50 rounded-xl flex items-center justify-center">
                            <span class="text-red-500 font-bold text-lg">₸</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Общий долг</p>
                            <p class="text-lg font-bold text-red-500">{{ formatCurrency(summary.totalDebt) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-3 px-4 sm:px-0 mb-6">
                    <div class="relative flex-1 max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Поиск по поставщику..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl shadow-sm leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="statusFilter = ''"
                            :class="[!statusFilter ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50', 'px-4 py-2 rounded-xl text-sm font-medium transition-colors']"
                        >
                            Все
                        </button>
                        <button
                            @click="statusFilter = 'preorder'"
                            :class="[statusFilter === 'preorder' ? 'bg-amber-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50', 'px-4 py-2 rounded-xl text-sm font-medium transition-colors']"
                        >
                            Предзаказ
                        </button>
                        <button
                            @click="statusFilter = 'received'"
                            :class="[statusFilter === 'received' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50', 'px-4 py-2 rounded-xl text-sm font-medium transition-colors']"
                        >
                            Получено
                        </button>
                    </div>
                </div>

                <!-- Supplier Order Cards -->
                <div class="space-y-3 px-4 sm:px-0">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        @click="router.get(route('suppliers.show', order.id))"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow cursor-pointer group"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">{{ order.supplier_name }}</h3>
                                <p class="text-sm text-gray-500 mt-0.5">{{ new Date(order.order_date).toLocaleDateString('ru-RU') }}</p>
                            </div>
                            <span v-if="order.status === 'preorder'" class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 flex items-center gap-1">
                                <ClockIcon class="h-3.5 w-3.5" />
                                Предзаказ
                            </span>
                            <span v-else class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                <CheckCircleIcon class="h-3.5 w-3.5" />
                                Получено
                            </span>
                        </div>

                        <p v-if="order.description" class="text-sm text-gray-600 mb-3 line-clamp-2">{{ order.description }}</p>

                        <!-- Progress Bar -->
                        <div class="mt-3">
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="text-gray-500">Оплачено: {{ formatCurrency(order.paid_amount) }}</span>
                                <span class="font-medium text-gray-700">{{ formatCurrency(order.total_expected_cost) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div
                                    :class="[
                                        paidPercentage(order) >= 100 ? 'bg-emerald-500' : 'bg-blue-500',
                                        'h-2.5 rounded-full transition-all duration-500'
                                    ]"
                                    :style="{ width: paidPercentage(order) + '%' }"
                                ></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 text-right">{{ paidPercentage(order) }}%</p>
                        </div>
                    </div>

                    <div v-if="orders.data.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                        Заказы не найдены
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links && orders.links.length > 3" class="mt-6 px-4 sm:px-0 flex justify-center">
                    <nav class="inline-flex -space-x-px rounded-xl overflow-hidden shadow-sm border border-gray-200">
                        <Link
                            v-for="link in orders.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50',
                                !link.url ? 'opacity-50 cursor-not-allowed' : '',
                                'px-3 py-2 text-sm border-r border-gray-200 last:border-r-0'
                            ]"
                        />
                    </nav>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
