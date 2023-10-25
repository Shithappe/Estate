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

                <ul class="flex gap-8">
                    <li v-for="book in books.data" :key="book.id" class="w-min m-2 shadow rounded-lg">
                        <Link :href="'/estate/' + book.id">
                        <div class="w-72 h-96 parent relative bg-cover bg-center rounded-lg shadow-lg"
                            :style="'background-image: url(' + book.main_image + ');'">


                                <div class="child absolute w-72 p-4 pt-28 text-white rounded-lg bottom-0 w-max bg-gradient-to-t from-black to-transparent">

                                    <div class="text-lg text-slate-200">{{ book.title }}</div>
                                    
                                    <div class="flex text-slate-500">
                                            {{ book.city }}, {{ book.street }}
                                        <!-- <div class="mx-2 px-1 text-sm text-slate-600 bg-slate-300 rounded-lg">{{ book.district }}</div> -->
                                    </div>

                                </div>
                            </div>
                        </Link>
                    </li>
                </ul>

                <Pagination class="mt-6" :links="books.links" />
            </div>
        </div>
    </SimpleAppLayout>
</template>
