<script setup>
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';

defineProps({
    item: Object,
});
</script>

<template>
    <SimpleAppLayout title="Головна">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <div>{{ item.title }}</div>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex gap-8">
                    <img class="rounded-md" :src="item.main_image" alt="">
                    <div class="h-min w-96 p-4 bg-slate-300 rounded-md">
                        <div class="text-xl font-bold">{{ item.builder_name }}</div>
                        <div class="mb-4">{{ item.city }}, {{ item.street }}</div>

                        <div class="">{{ item.price_per_meter }} per m²</div>
                        <div>{{ item.title }}</div>
                        <div>{{ item.street }}</div>
                        <div>{{ item.area }}</div>
                    </div>
                </div>
                <div>{{ item }}</div>
            </div>
        </div>
    </SimpleAppLayout>
</template>
