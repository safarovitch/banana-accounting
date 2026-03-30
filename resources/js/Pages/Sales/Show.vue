<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { PaperClipIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    sale: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'TJS' }).format(amount);
};

const remainingDebt = computed(() => {
    return Math.max(0, props.sale.total_amount - props.sale.paid_amount);
});

const form = useForm({
    amount: remainingDebt.value || 0,
    payment_date: new Date().toISOString().split('T')[0],
    receipt: null,
});

const fileInputRef = ref(null);

const addPayment = () => {
    form.post(route('sale-payments.store', props.sale.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('amount', 'receipt');
            form.amount = remainingDebt.value; // reset to new remaining
            if (fileInputRef.value) fileInputRef.value.value = null;
        },
    });
};
</script>

<template>
    <Head :title="`Продажа #${sale.id} - ${sale.client_name}`" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Back Link & Header -->
                <div class="flex items-center space-x-4 px-4 sm:px-0 mb-6">
                    <Link :href="route('sales.index')" class="text-gray-500 hover:text-gray-700">← К списку</Link>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Продажа #{{ sale.id }}</h2>
                </div>

                <!-- Sale Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ sale.client_name }}</h3>
                            <p class="text-gray-500 text-sm">От {{ new Date(sale.sale_date).toLocaleDateString('ru-RU') }}</p>
                        </div>
                        <div class="mt-2 sm:mt-0">
                            <span v-if="sale.status === 'completed'" class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Завершен</span>
                            <span v-else-if="sale.status === 'debt'" class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Динамика (Долг)</span>
                            <span v-else-if="sale.status === 'swap'" class="px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">Бартер</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-t border-b border-gray-100 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Сумма продажи</p>
                            <p class="font-semibold text-lg text-gray-900">{{ formatCurrency(sale.total_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Оплачено</p>
                            <p class="font-semibold text-lg text-green-600">{{ formatCurrency(sale.paid_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Долг</p>
                            <p class="font-semibold text-lg text-red-600">{{ formatCurrency(remainingDebt) }}</p>
                        </div>
                    </div>

                    <div v-if="sale.swap_description" class="bg-purple-50 p-4 rounded-xl border border-purple-100">
                        <p class="text-sm font-semibold text-purple-800 mb-1">Детали бартера:</p>
                        <p class="text-purple-700 text-sm">{{ sale.swap_description }}</p>
                    </div>
                </div>

                <!-- Payments Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Payments List -->
                    <div class="md:col-span-2 space-y-4">
                        <h3 class="text-lg font-bold text-gray-900 px-4 sm:px-0 mb-2">История платежей</h3>
                        <div v-if="sale.payments.length === 0" class="text-gray-500 text-sm px-4 sm:px-0">Платежи пока не добавлены.</div>
                        <div v-for="payment in sale.payments" :key="payment.id" class="bg-white p-4 rounded-2xl shadow-sm flex items-center justify-between border border-gray-100 hover:shadow-md transition-shadow">
                            <div>
                                <div class="font-bold text-gray-900 text-lg">{{ formatCurrency(payment.amount) }}</div>
                                <div class="text-xs text-gray-500">{{ new Date(payment.payment_date).toLocaleDateString('ru-RU') }}</div>
                            </div>
                            <div v-if="payment.receipt_url">
                                <a :href="payment.receipt_url" target="_blank" class="flex items-center text-sm text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg transition-colors">
                                    <PaperClipIcon class="h-4 w-4 mr-1" />
                                    <span>Чек</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Add Payment Form -->
                    <div v-if="sale.status !== 'completed'">
                        <h3 class="text-lg font-bold text-gray-900 px-4 sm:px-0 mb-4">Детали оплаты</h3>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                            <form @submit.prevent="addPayment" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Сумма (TJS)</label>
                                    <input type="number" step="0.01" v-model="form.amount" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                                    <div v-if="form.errors.amount" class="mt-1 text-xs text-red-600">{{ form.errors.amount }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Дата</label>
                                    <input type="date" v-model="form.payment_date" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                                    <div v-if="form.errors.payment_date" class="mt-1 text-xs text-red-600">{{ form.errors.payment_date }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Фото чека (опционально)</label>
                                    <input 
                                        type="file" 
                                        @input="form.receipt = $event.target.files[0]" 
                                        ref="fileInputRef"
                                        accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors"
                                    />
                                    <div v-if="form.errors.receipt" class="mt-1 text-xs text-red-600">{{ form.errors.receipt }}</div>
                                </div>
                                
                                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-2">
                                    {{ form.progress.percentage }}%
                                </progress>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 mt-2"
                                >
                                    Сохранить платеж
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
