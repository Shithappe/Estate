<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';

import Lucide from '@/Components/Lucide.vue';


defineProps({
    title: String,
});


</script>

<template>
    <div>

        <Head :title="title" />

        <!-- max-w-7xl -->

        <div class="min-h-screen bg-gray-100">
            <div class="w-16 h-full fixed flex-col shadow-md">
                <div
                    class="py-5 flex justify-center aling-center cursor-pointer hover:bg-red-500 hover:text-white hover:shadow transition ease-in-out duration-150">
                    <Lucide class="w-6 h-6" icon="AlignJustify" />
                </div>
                <div
                    class="py-5 flex justify-center aling-center cursor-pointer hover:bg-red-500 hover:text-white hover:shadow transition ease-in-out duration-150">
                    <Lucide class="w-6 h-6" icon="Gauge" />
                </div>
                <div
                    class="py-5 flex justify-center aling-center cursor-pointer hover:bg-red-500 hover:text-white hover:shadow transition ease-in-out duration-150">
                    <Lucide class="w-6 h-6" icon="Folders" />
                </div>
                <div
                    class="py-5 flex justify-center aling-center cursor-pointer hover:bg-red-500 hover:text-white hover:shadow transition ease-in-out duration-150">
                    <Lucide class="w-6 h-6" icon="ShoppingCart" />
                </div>
                <div
                    class="py-5 flex justify-center aling-center cursor-pointer hover:bg-red-500 hover:text-white hover:shadow transition ease-in-out duration-150">
                    <Lucide class="w-6 h-6" icon="Undo2" />
                </div>
            </div>
            <div class="pl-16">
                <nav class="text-slate-200 bg-slate-800 border-b border-gray-100">
                    <!-- Primary Navigation Menu -->
                    <div class="mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <!-- Logo -->
                                <div class="shrink-0 flex items-center">
                                    <ApplicationMark class="block h-9 w-auto" />
                                    <p class="mx-2">Estate market</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class=" mx-auto flex justify-between items-center py-3 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                        <!-- <button class="flex items-center px-2 py-1 border shadow rounded-lg">
                            <Lucide class="w-4 h-4 mr-1" icon="ShoppingCart" />
                            <Link href="/cart">Кошик</Link>
                        </button> -->
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>

            </div>
        </div>
    </div>
</template>
