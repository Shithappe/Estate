<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});


const logout = () => {
    // мб снести localstorage
    router.post(route('logout'));
};


</script>

<template>
    <div>

        <Head :title="title" />

        <nav class="text-slate-200 bg-slate-800 border-b border-gray-100">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link href="/">
                    <div class="hidden lg:block flex items-center justify-between">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <p class="mx-2 text-lg">Estate market</p>
                        </div>


                    </div>
                    </Link>

                    <div class="flex gap-x-4 text-sm lg:text-md">
                        <a href="/booking_data-map"
                            class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                            Map
                        </a>
                        <a href="/estate"
                            class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                            Estate
                        </a>
                        <a href="https://offers.estatemarket.io/" target="_blank"
                            class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                            Offer Analysis
                        </a>
                        <Link v-if="!$page.props.auth.user" :href="route('login')"
                            class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                            Log in
                        </Link>
                        <!-- <div v-else class="p-2 hover:text-black hover:bg-slate-200 rounded-md transition duration-300 ease-in-out"> -->
                        <div v-else>
                            <!-- awd -->
                            <Dropdown width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center p-2 hover:text-black hover:bg-slate-200 focus:text-black focus:bg-slate-200 rounded-md transition duration-300 ease-in-out">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>


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
