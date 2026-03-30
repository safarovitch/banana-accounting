<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ScaleIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    transactions: Object,
    filters: Object,
    summary: Object,
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');

const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

const applyFilters = debounce(() => {
    router.get(route('transactions.index'), {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
    }, { preserveState: true, replace: true });
}, 300);

watch(search, applyFilters);
watch(typeFilter, applyFilters);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'TJS' }).format(amount);
};

const confirmDelete = (id) => {
    if (confirm('Удалить эту запись?')) {
        router.delete(route('transactions.destroy', id));
    }
};
</script>

<template>
    <Head title="Финансы" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Page Header -->
                <div class="flex justify-between items-center mb-6 px-4 sm:px-0">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Финансы</h2>
                    <Link
                        :href="route('transactions.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 transition ease-in-out duration-150"
                    >
                        <PlusIcon class="h-4 w-4 mr-2" />
                        <span class="hidden sm:inline">Новая запись</span>
                        <span class="sm:hidden">Добавить</span>
                    </Link>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 px-4 sm:px-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 h-12 w-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                            <ArrowTrendingUpIcon class="h-6 w-6 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Доход</p>
                            <p class="text-lg font-bold text-emerald-600">{{ formatCurrency(summary.totalIncome) }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 h-12 w-12 bg-red-50 rounded-xl flex items-center justify-center">
                            <ArrowTrendingDownIcon class="h-6 w-6 text-red-500" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Расход</p>
                            <p class="text-lg font-bold text-red-500">{{ formatCurrency(summary.totalExpense) }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 h-12 w-12 bg-blue-50 rounded-xl flex items-center justify-center">
                            <ScaleIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Баланс</p>
                            <p :class="['text-lg font-bold', summary.balance >= 0 ? 'text-blue-600' : 'text-red-600']">{{ formatCurrency(summary.balance) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="flex flex-col sm:flex-row gap-3 px-4 sm:px-0 mb-6">
                    <div class="relative flex-1 max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Поиск по описанию..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl shadow-sm leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="typeFilter = ''"
                            :class="[!typeFilter ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50', 'px-4 py-2 rounded-xl text-sm font-medium transition-colors']"
                        >
                            Все
                        </button>
                        <button
                            @click="typeFilter = 'income'"
                            :class="[typeFilter === 'income' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50', 'px-4 py-2 rounded-xl text-sm font-medium transition-colors']"
                        >
                            Доходы
                        </button>
                        <button
                            @click="typeFilter = 'expense'"
                            :class="[typeFilter === 'expense' ? 'bg-red-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50', 'px-4 py-2 rounded-xl text-sm font-medium transition-colors']"
                        >
                            Расходы
                        </button>
                    </div>
                </div>

                <!-- Transactions List (Card-based for mobile) -->
                <div class="space-y-3 px-4 sm:px-0">
                    <div
                        v-for="tx in transactions.data"
                        :key="tx.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 flex items-start justify-between hover:shadow-md transition-shadow group"
                    >
                        <div class="flex items-start space-x-4 min-w-0">
                            <div :class="[
                                'flex-shrink-0 h-10 w-10 rounded-xl flex items-center justify-center mt-0.5',
                                tx.type === 'income' ? 'bg-emerald-50' : 'bg-red-50'
                            ]">
                                <ArrowTrendingUpIcon v-if="tx.type === 'income'" class="h-5 w-5 text-emerald-600" />
                                <ArrowTrendingDownIcon v-else class="h-5 w-5 text-red-500" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ tx.description }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">{{ new Date(tx.transaction_date).toLocaleDateString('ru-RU') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                            <span :class="['text-lg font-bold whitespace-nowrap', tx.type === 'income' ? 'text-emerald-600' : 'text-red-500']">
                                {{ tx.type === 'income' ? '+' : '−' }}{{ formatCurrency(tx.amount) }}
                            </span>
                            <button
                                @click.stop="confirmDelete(tx.id)"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 p-1 rounded-lg transition-all"
                                title="Удалить"
                            >
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div v-if="transactions.data.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                        Записи не найдены
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.links && transactions.links.length > 3" class="mt-6 px-4 sm:px-0 flex justify-center">
                    <nav class="inline-flex -space-x-px rounded-xl overflow-hidden shadow-sm border border-gray-200">
                        <Link
                            v-for="link in transactions.links"
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
