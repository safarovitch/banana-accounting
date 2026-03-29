<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    clients: Array,
});

const form = useForm({
    client_name: '',
    total_amount: '',
    paid_amount: 0,
    status: 'completed',
    swap_description: '',
    sale_date: new Date().toISOString().split('T')[0],
});

const showClientSuggestions = ref(false);

const filteredClients = computed(() => {
    if (!form.client_name) return props.clients;
    return props.clients.filter(c => c.toLowerCase().includes(form.client_name.toLowerCase()));
});

const selectClient = (name) => {
    form.client_name = name;
    showClientSuggestions.value = false;
};

const submit = () => {
    form.post(route('sales.store'));
};
</script>

<template>
    <Head title="Новая продажа" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4 px-4 sm:px-0 mb-6">
                    <Link :href="route('sales.index')" class="text-gray-500 hover:text-gray-700">← Назад</Link>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Новая продажа</h2>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Client Autocomplete -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700">Клиент</label>
                            <input 
                                type="text" 
                                v-model="form.client_name" 
                                @focus="showClientSuggestions = true"
                                @blur="setTimeout(() => showClientSuggestions = false, 200)"
                                class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Начните вводить имя клиента"
                                required
                            />
                            <div v-if="showClientSuggestions && filteredClients.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <ul class="divide-y divide-gray-100">
                                    <li 
                                        v-for="client in filteredClients" 
                                        :key="client"
                                        @click="selectClient(client)"
                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 text-gray-900"
                                    >
                                        {{ client }}
                                    </li>
                                </ul>
                            </div>
                            <div v-if="form.errors.client_name" class="mt-1 text-sm text-red-600">{{ form.errors.client_name }}</div>
                        </div>

                        <!-- Amounts -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Сумма продажи (TJS)</label>
                                <input type="number" step="0.01" v-model="form.total_amount" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                                <div v-if="form.errors.total_amount" class="mt-1 text-sm text-red-600">{{ form.errors.total_amount }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Оплачено (TJS)</label>
                                <input type="number" step="0.01" v-model="form.paid_amount" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                                <div v-if="form.errors.paid_amount" class="mt-1 text-sm text-red-600">{{ form.errors.paid_amount }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Статус оплаты</label>
                                <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="completed">Оплачено полностью</option>
                                    <option value="debt">Долг / В рассрочку</option>
                                    <option value="swap">Бартер (Машина, Дом и т.д.)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Дата продажи</label>
                                <input type="date" v-model="form.sale_date" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                            </div>
                        </div>

                        <div v-if="form.status === 'swap'">
                            <label class="block text-sm font-medium text-gray-700">Описание бартера (например, Toyota Corolla)</label>
                            <textarea v-model="form.swap_description" rows="3" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                            <div v-if="form.errors.swap_description" class="mt-1 text-sm text-red-600">{{ form.errors.swap_description }}</div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
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
