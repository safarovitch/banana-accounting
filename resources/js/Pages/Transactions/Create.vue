<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    type: 'expense',
    amount: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0],
    receipt: null,
});

const fileInputRef = ref(null);

const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            form.reset();
            if (fileInputRef.value) fileInputRef.value.value = null;
        },
    });
};
</script>

<template>
    <Head title="Новая запись" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4 px-4 sm:px-0 mb-6">
                    <Link :href="route('transactions.index')" class="text-gray-500 hover:text-gray-700 transition-colors">← Назад</Link>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Новая запись</h2>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Type Toggle -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Тип операции</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    @click="form.type = 'income'"
                                    :class="[
                                        form.type === 'income'
                                            ? 'bg-emerald-600 text-white ring-2 ring-emerald-600 ring-offset-2'
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                        'py-3 px-4 rounded-xl text-sm font-semibold transition-all duration-150'
                                    ]"
                                >
                                    ↑ Доход
                                </button>
                                <button
                                    type="button"
                                    @click="form.type = 'expense'"
                                    :class="[
                                        form.type === 'expense'
                                            ? 'bg-red-500 text-white ring-2 ring-red-500 ring-offset-2'
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                        'py-3 px-4 rounded-xl text-sm font-semibold transition-all duration-150'
                                    ]"
                                >
                                    ↓ Расход
                                </button>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Сумма (TJS)</label>
                            <input
                                type="number"
                                step="0.01"
                                v-model="form.amount"
                                class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="0.00"
                                required
                            />
                            <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Описание</label>
                            <input
                                type="text"
                                v-model="form.description"
                                class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Описание операции"
                                required
                            />
                            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Дата</label>
                            <input
                                type="date"
                                v-model="form.transaction_date"
                                class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                required
                            />
                            <div v-if="form.errors.transaction_date" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_date }}</div>
                        </div>

                        <!-- Receipt Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Фото чека (опционально)</label>
                            <input
                                type="file"
                                @input="form.receipt = $event.target.files[0]"
                                ref="fileInputRef"
                                accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors"
                            />
                            <div v-if="form.errors.receipt" class="mt-1 text-sm text-red-600">{{ form.errors.receipt }}</div>
                        </div>

                        <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-2">
                            {{ form.progress.percentage }}%
                        </progress>

                        <div class="pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                :class="[
                                    form.type === 'income' ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' : 'bg-red-500 hover:bg-red-600 focus:ring-red-400',
                                    'w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition-colors'
                                ]"
                            >
                                {{ form.type === 'income' ? 'Добавить доход' : 'Добавить расход' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
