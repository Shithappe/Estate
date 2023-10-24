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

                <div class="w-4/5">
                    <div>{{ item.title }}</div>
                    <div>{{ item.street }}</div>
                    <div>{{ item.area }}</div>
                    
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>
