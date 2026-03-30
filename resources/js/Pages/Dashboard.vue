<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ShoppingCartIcon,
    CurrencyDollarIcon,
    UserGroupIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ScaleIcon,
    ReceiptPercentIcon,
    TruckIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: Object,
    recentSales: Array,
    recentTransactions: Array,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'TJS' }).format(amount);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU');
};

const getStatusClass = (status) => {
    switch (status) {
        case 'completed': return 'bg-emerald-100 text-emerald-800';
        case 'debt': return 'bg-red-100 text-red-800';
        case 'swap': return 'bg-purple-100 text-purple-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'completed': return 'Завершено';
        case 'debt': return 'Долг';
        case 'swap': return 'Бартер';
        default: return status;
    }
};
</script>

<template>
    <Head title="Панель управления" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="px-4 sm:px-0 mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Обзор бизнеса</h2>
                    <p class="mt-1 text-sm text-gray-500">Краткая сводка ваших финансовых операций и недавней активности.</p>
                </div>

                <!-- Primary Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 px-4 sm:px-0">
                    <!-- Total Sales -->
                    <div class="relative overflow-hidden bg-white px-4 pt-5 pb-12 shadow-sm border border-gray-100 rounded-2xl hover:shadow-md transition-shadow sm:px-6 sm:pt-6">
                        <dt>
                            <div class="absolute rounded-xl bg-blue-500 p-3">
                                <ShoppingCartIcon class="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <p class="ml-16 truncate text-sm font-medium text-gray-500 uppercase tracking-wider">Общие Продажи</p>
                        </dt>
                        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalSales) }}</p>
                        </dd>
                    </div>

                    <!-- Client Debt (Receivables) -->
                    <div class="relative overflow-hidden bg-white px-4 pt-5 pb-12 shadow-sm border border-gray-100 rounded-2xl hover:shadow-md transition-shadow sm:px-6 sm:pt-6">
                        <dt>
                            <div class="absolute rounded-xl bg-red-500 p-3">
                                <ReceiptPercentIcon class="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <p class="ml-16 truncate text-sm font-medium text-gray-500 uppercase tracking-wider">Долги клиентов</p>
                        </dt>
                        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                            <p class="text-2xl font-bold text-red-600">{{ formatCurrency(stats.totalDebt) }}</p>
                        </dd>
                    </div>

                    <!-- Net Balance -->
                    <div class="relative overflow-hidden bg-white px-4 pt-5 pb-12 shadow-sm border border-gray-100 rounded-2xl hover:shadow-md transition-shadow sm:px-6 sm:pt-6">
                        <dt>
                            <div class="absolute rounded-xl bg-emerald-500 p-3">
                                <ScaleIcon class="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <p class="ml-16 truncate text-sm font-medium text-gray-500 uppercase tracking-wider">Чистый баланс</p>
                        </dt>
                        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                            <p :class="[stats.balance >= 0 ? 'text-emerald-600' : 'text-red-600', 'text-2xl font-bold']">
                                {{ formatCurrency(stats.balance) }}
                            </p>
                        </dd>
                    </div>

                    <!-- Supplier Debt (Payables) -->
                    <div class="relative overflow-hidden bg-white px-4 pt-5 pb-12 shadow-sm border border-gray-100 rounded-2xl hover:shadow-md transition-shadow sm:px-6 sm:pt-6">
                        <dt>
                            <div class="absolute rounded-xl bg-amber-500 p-3">
                                <TruckIcon class="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <p class="ml-16 truncate text-sm font-medium text-gray-500 uppercase tracking-wider">Долг поставщикам</p>
                        </dt>
                        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                            <p class="text-2xl font-bold text-amber-600">{{ formatCurrency(stats.supplierDebt) }}</p>
                        </dd>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-4 sm:px-0">
                    <!-- Recent Sales -->
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Недавние продажи</h3>
                            <Link :href="route('sales.index')" class="text-sm font-semibold text-blue-600 hover:text-blue-500">Посмотреть все</Link>
                        </div>
                        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="sale in recentSales" :key="sale.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                                    <Link :href="route('sales.show', sale.id)" class="flex items-center justify-between">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900">{{ sale.client_name }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ formatDate(sale.sale_date) }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-gray-900">{{ formatCurrency(sale.total_amount) }}</p>
                                            <span :class="[getStatusClass(sale.status), 'mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium']">
                                                {{ getStatusLabel(sale.status) }}
                                            </span>
                                        </div>
                                    </Link>
                                </li>
                                <li v-if="recentSales.length === 0" class="px-6 py-12 text-center text-gray-500 text-sm">Продаж пока нет.</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Recent Transactions -->
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Потоки финансов</h3>
                            <Link :href="route('transactions.index')" class="text-sm font-semibold text-blue-600 hover:text-blue-500">Посмотреть все</Link>
                        </div>
                        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="tx in recentTransactions" :key="tx.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center min-w-0">
                                            <div :class="[tx.type === 'income' ? 'bg-emerald-50' : 'bg-red-50', 'flex-shrink-0 h-10 w-10 rounded-xl flex items-center justify-center mr-4']">
                                                <ArrowTrendingUpIcon v-if="tx.type === 'income'" class="h-5 w-5 text-emerald-600" />
                                                <ArrowTrendingDownIcon v-else class="h-5 w-5 text-red-500" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ tx.description }}</p>
                                                <p class="mt-1 text-xs text-gray-500">{{ formatDate(tx.transaction_date) }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p :class="[tx.type === 'income' ? 'text-emerald-600' : 'text-red-500', 'text-sm font-bold']">
                                                {{ tx.type === 'income' ? '+' : '−' }}{{ formatCurrency(tx.amount) }}
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li v-if="recentTransactions.length === 0" class="px-6 py-12 text-center text-gray-500 text-sm">Транзакций пока нет.</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
