<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    HomeIcon,
    CurrencyDollarIcon,
    ShoppingCartIcon,
    IdentificationIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline';

const navigation = [
    { name: 'Главная', href: route('dashboard'), icon: HomeIcon, current: route().current('dashboard') },
    { name: 'Продажи', href: route('sales.index'), icon: ShoppingCartIcon, current: route().current('sales.*') },
    { name: 'Финансы', href: route('transactions.index'), icon: CurrencyDollarIcon, current: route().current('transactions.*') },
    { name: 'Поставщики', href: route('suppliers.index'), icon: IdentificationIcon, current: route().current('suppliers.*') },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col md:flex-row pb-16 md:pb-0">
        <!-- Desktop Sidebar -->
        <nav class="hidden md:flex w-64 flex-col bg-white border-r border-gray-200 fixed inset-y-0 pt-5 pb-4">
            <div class="flex items-center px-6 mb-8">
                <Link :href="route('dashboard')">
                    <ApplicationLogo class="h-8 w-auto text-blue-600" />
                </Link>
                <div class="ml-3 font-bold text-xl text-gray-800">Bananan</div>
            </div>
            
            <div class="flex-1 flex flex-col overflow-y-auto px-4 space-y-2">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[item.current ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center px-3 py-2 text-sm font-medium rounded-xl transition-colors']"
                >
                    <component :is="item.icon" :class="[item.current ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500', 'flex-shrink-0 -ml-1 mr-3 h-6 w-6']" aria-hidden="true" />
                    <span class="truncate">{{ item.name }}</span>
                </Link>
            </div>
            
            <div class="mt-auto border-t border-gray-200 px-4 pt-4">
                <Link :href="route('profile.edit')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <UserCircleIcon class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-500" />
                    <span>Профиль</span>
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="w-full mt-1 group flex items-center justify-start px-3 py-2 text-sm font-medium rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <ArrowRightOnRectangleIcon class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-500" />
                    <span>Выход</span>
                </Link>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 md:pl-64 flex flex-col min-h-screen">
            <!-- Header for Mobile -->
            <header class="md:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                <div class="flex items-center">
                    <ApplicationLogo class="h-7 w-auto text-blue-600" />
                    <span class="ml-2 font-bold text-lg text-gray-800">Bananan</span>
                </div>
                <Link :href="route('profile.edit')">
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                        {{ usePage().props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                </Link>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 flex justify-between px-2 pb-[env(safe-area-inset-bottom)] pt-2 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] z-20">
            <Link
                v-for="item in navigation"
                :key="item.name"
                :href="item.href"
                :class="[item.current ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900', 'flex flex-col items-center justify-center w-full py-1 pb-2 transition-colors']"
            >
                <component :is="item.icon" :class="[item.current ? 'mb-1 stroke-2' : '', 'h-6 w-6 mb-1']" aria-hidden="true" />
                <span class="text-[10px] uppercase font-medium tracking-wider">{{ item.name }}</span>
            </Link>
        </nav>
    </div>
</template>
