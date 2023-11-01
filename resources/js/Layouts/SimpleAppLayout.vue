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
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

    </div>
</template>
