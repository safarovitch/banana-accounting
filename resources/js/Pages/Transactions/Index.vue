<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    transactions: Array,
});
</script>

<template>
    <Head title="Финансы" />

    <AuthenticatedLayout>
        <div class="pb-12 pt-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-6 px-4 sm:px-0">Финансы</h2>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                    <div class="p-6 text-gray-900">Модуль финансов в разработке...</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
