<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    suppliers: Array,
});

const form = useForm({
    supplier_name: '',
    description: '',
    total_expected_cost: '',
    paid_amount: 0,
    status: 'preorder',
    order_date: new Date().toISOString().split('T')[0],
    received_date: null,
});

const showSupplierSuggestions = ref(false);

const filteredSuppliers = computed(() => {
    if (!form.supplier_name) return props.suppliers;
    return props.suppliers.filter(s => s.toLowerCase().includes(form.supplier_name.toLowerCase()));
});

const selectSupplier = (name) => {
    form.supplier_name = name;
    showSupplierSuggestions.value = false;
};

const submit = () => {
    form.post(route('suppliers.store'));
};
</script>

<template>
    <Head title="Новый заказ поставщику" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4 px-4 sm:px-0 mb-6">
                    <Link :href="route('suppliers.index')" class="text-gray-500 hover:text-gray-700 transition-colors">← Назад</Link>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Новый заказ</h2>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Supplier Autocomplete -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700">Поставщик</label>
                            <input
                                type="text"
                                v-model="form.supplier_name"
                                @focus="showSupplierSuggestions = true"
                                @blur="setTimeout(() => showSupplierSuggestions = false, 200)"
                                class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Имя поставщика"
                                required
                            />
                            <div v-if="showSupplierSuggestions && filteredSuppliers.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <ul class="divide-y divide-gray-100">
                                    <li
                                        v-for="supplier in filteredSuppliers"
                                        :key="supplier"
                                        @click="selectSupplier(supplier)"
                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 text-gray-900"
                                    >
                                        {{ supplier }}
                                    </li>
                                </ul>
                            </div>
                            <div v-if="form.errors.supplier_name" class="mt-1 text-sm text-red-600">{{ form.errors.supplier_name }}</div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Описание заказа</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Например: 20 тонн бананов из Эквадора"
                            ></textarea>
                        </div>

                        <!-- Amounts -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Общая стоимость (TJS)</label>
                                <input type="number" step="0.01" v-model="form.total_expected_cost" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                                <div v-if="form.errors.total_expected_cost" class="mt-1 text-sm text-red-600">{{ form.errors.total_expected_cost }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Оплачено (TJS)</label>
                                <input type="number" step="0.01" v-model="form.paid_amount" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                                <div v-if="form.errors.paid_amount" class="mt-1 text-sm text-red-600">{{ form.errors.paid_amount }}</div>
                            </div>
                        </div>

                        <!-- Status & Date -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Статус</label>
                                <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="preorder">Предзаказ</option>
                                    <option value="received">Получено</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Дата заказа</label>
                                <input type="date" v-model="form.order_date" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                            </div>
                        </div>

                        <div v-if="form.status === 'received'">
                            <label class="block text-sm font-medium text-gray-700">Дата получения</label>
                            <input type="date" v-model="form.received_date" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
