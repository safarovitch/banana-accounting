<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { PaperClipIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    order: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'TJS' }).format(amount);
};

const remainingDebt = computed(() => {
    return Math.max(0, props.order.total_expected_cost - props.order.paid_amount);
});

const paidPercentage = computed(() => {
    if (!props.order.total_expected_cost || props.order.total_expected_cost === 0) return 0;
    return Math.min(100, Math.round((props.order.paid_amount / props.order.total_expected_cost) * 100));
});

const form = useForm({
    amount: remainingDebt.value || 0,
    payment_date: new Date().toISOString().split('T')[0],
    receipt: null,
});

const fileInputRef = ref(null);

const addPayment = () => {
    form.post(route('supplier-payments.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('amount', 'receipt');
            form.amount = remainingDebt.value;
            if (fileInputRef.value) fileInputRef.value.value = null;
        },
    });
};

const toggleStatus = () => {
    const newStatus = props.order.status === 'preorder' ? 'received' : 'preorder';
    router.put(route('suppliers.update', props.order.id), {
        status: newStatus,
        received_date: newStatus === 'received' ? new Date().toISOString().split('T')[0] : null,
    });
};
</script>

<template>
    <Head :title="`Заказ #${order.id} - ${order.supplier_name}`" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <!-- Back Link & Header -->
                <div class="flex items-center space-x-4 px-4 sm:px-0 mb-6">
                    <Link :href="route('suppliers.index')" class="text-gray-500 hover:text-gray-700 transition-colors">← К списку</Link>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Заказ #{{ order.id }}</h2>
                </div>

                <!-- Order Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ order.supplier_name }}</h3>
                            <p class="text-gray-500 text-sm">Заказан {{ new Date(order.order_date).toLocaleDateString('ru-RU') }}</p>
                            <p v-if="order.received_date" class="text-emerald-600 text-sm">Получен {{ new Date(order.received_date).toLocaleDateString('ru-RU') }}</p>
                        </div>
                        <button
                            @click="toggleStatus"
                            :class="[
                                order.status === 'preorder'
                                    ? 'bg-amber-100 text-amber-800 hover:bg-amber-200'
                                    : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200',
                                'px-4 py-2 text-sm font-semibold rounded-full flex items-center gap-1.5 transition-colors mt-2 sm:mt-0'
                            ]"
                        >
                            <ClockIcon v-if="order.status === 'preorder'" class="h-4 w-4" />
                            <CheckCircleIcon v-else class="h-4 w-4" />
                            {{ order.status === 'preorder' ? 'Предзаказ' : 'Получено' }}
                        </button>
                    </div>

                    <p v-if="order.description" class="text-gray-600 mb-6 text-sm bg-gray-50 p-4 rounded-xl border border-gray-100">
                        {{ order.description }}
                    </p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-t border-b border-gray-100 mb-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Сумма заказа</p>
                            <p class="font-semibold text-lg text-gray-900">{{ formatCurrency(order.total_expected_cost) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Оплачено</p>
                            <p class="font-semibold text-lg text-emerald-600">{{ formatCurrency(order.paid_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Долг</p>
                            <p class="font-semibold text-lg text-red-600">{{ formatCurrency(remainingDebt) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Прогресс</p>
                            <p class="font-semibold text-lg text-blue-600">{{ paidPercentage }}%</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div
                            :class="[
                                paidPercentage >= 100 ? 'bg-emerald-500' : 'bg-blue-500',
                                'h-3 rounded-full transition-all duration-700'
                            ]"
                            :style="{ width: paidPercentage + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Payments Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Payments List -->
                    <div class="md:col-span-2 space-y-4">
                        <h3 class="text-lg font-bold text-gray-900 px-4 sm:px-0 mb-2">История платежей</h3>
                        <div v-if="order.payments.length === 0" class="text-gray-500 text-sm px-4 sm:px-0">Платежи пока не добавлены.</div>
                        <div v-for="payment in order.payments" :key="payment.id" class="bg-white p-4 rounded-2xl shadow-sm flex items-center justify-between border border-gray-100 hover:shadow-md transition-shadow">
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
                    <div v-if="remainingDebt > 0">
                        <h3 class="text-lg font-bold text-gray-900 px-4 sm:px-0 mb-4">Добавить оплату</h3>
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
                                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 mt-2 transition-colors"
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
