<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';

defineProps({
    title: String,
});


</script>

<template>
    <div>

        <Head :title="title" />

        <nav class="text-slate-200 bg-slate-800 border-b border-gray-100">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="hidden lg:block flex items-center justify-between">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <!-- <ApplicationMark class="block h-9 w-auto" /> -->
                            <p class="mx-2 text-lg">Estate market</p>
                        </div>


                    </div>

                    <div class="flex gap-x-4 text-sm lg:text-md">
                        <a href="/booking_data-map"
                            class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                            Map
                        </a>
                        <a href="/estate"
                            class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                            Estate
                        </a>
                        <a href="http://65.21.157.152/" target="_blank"
                            class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                            Offer Analysis
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white shadow">
            <div class=" mx-auto flex justify-between items-center py-3 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

    </div></template>
