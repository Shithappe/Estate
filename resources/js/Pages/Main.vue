<script setup>
import { Link } from '@inertiajs/vue3';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    books: Object,
});
</script>

<template>
    <SimpleAppLayout title="Головна">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Головна
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="w-4/5">

                    <ul>
                        <li v-for="book in books.data" :key="book.id" class="m-2 p-2 shadow rounded-lg">
                            <Link :href="'/estate/' + book.id">
                                <div class="text-lg">{{ book.title }}</div>


                                <div class="flex">
                                    <a :href="book.url" target="_blank" rel="noopener noreferrer" class="text-slate-500">
                                        {{ book.street }}
                                    </a>

                                    <div class="mx-2 px-1 text-sm text-slate-600 bg-slate-300 rounded-lg">{{ book.area }}</div>
                                </div>
                            </Link>
                        </li>
                    </ul>

                    <Pagination class="mt-6" :links="books.links" />
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>
