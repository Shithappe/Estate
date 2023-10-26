<script setup>
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import CardItem from '@/Components/CardItem.vue';

defineProps({
    books: Object,
});
</script>

<template>
    <SimpleAppLayout title="Головна">
        <!-- <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Головна
            </h2>
        </template> -->

        
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div>
                    filters
                </div>

                <ul class="flex-col gap-8">
                    <li v-for="book in books.data" :key="book.id" class="my-8">
                        <CardItem :item="book" />
                    </li>
                </ul>

                <Pagination class="mt-6" :links="books.links" />
            </div>
        </div>
    </SimpleAppLayout>
</template>
