<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

watch(search, debounce(function (value) {
    router.get(route('sales.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'TJS' }).format(amount);
};
</script>

<template>
    <Head title="Продажи" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6 px-4 sm:px-0">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Продажи</h2>
                    <Link
                        :href="route('sales.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 transition ease-in-out duration-150"
                    >
                        <PlusIcon class="h-4 w-4 mr-2" />
                        <span class="hidden sm:inline">Новая продажа</span>
                        <span class="sm:hidden">Добавить</span>
                    </Link>
                </div>

                <!-- Search/Filter -->
                <div class="px-4 sm:px-0 mb-6">
                    <div class="relative max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Поиск по клиенту..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl shadow-sm leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                        />
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ sale.client_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ new Date(sale.sale_date).toLocaleDateString('ru-RU') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-900 font-medium">{{ formatCurrency(sale.total_amount) }}</div>
                                        <div class="text-xs text-gray-500">Оплачено: {{ formatCurrency(sale.paid_amount) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="sale.status === 'completed'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Завершен</span>
                                        <span v-else-if="sale.status === 'debt'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Долг</span>
                                        <span v-else-if="sale.status === 'swap'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Бартер</span>
                                    </td>
                                </tr>
                                <tr v-if="sales.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">Продажи не найдены</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
