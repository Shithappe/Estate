<script setup>
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';

defineProps({
    item: Object,
});
</script>

<template>
    <div class="w-full h-96 flex gap-8 bg-gray-100 rounded-md">
        <div class="w-2/3 h-min-96 bg-cover bg-center rounded-lg shadow-lg" :style="'background-image: url(' + item.main_image + ');'"></div>


        <div class="relative w-96 mx-6 py-6">
            <div class="text-2xl font-bold">{{ item.price_per_meter * item.square }} $</div>
            <div class="text-xl font-semibold">{{ item.builder_name }}</div>
            <div class="mb-4">{{ item.city }}, {{ item.street }}</div>

            <div>{{ item.title }}</div>
            <div>{{ item.description }}</div>
            
            <div class="mt-4 grid grid-cols-2 gap-y-2 font-medium justify-between">
                <div class="flex items-center gap-2"><Lucide class="w-5 h-5" icon="BedDouble" /> {{ item.room_count }}</div>
                <div class="flex items-center gap-2"><Lucide class="w-5 h-5" icon="Layers2" /> {{ item.floor }}</div>
                <div class="flex items-center gap-2"><Lucide class="w-5 h-5" icon="Scan" /> {{ item.square }}m²</div>
                <div class="flex items-center gap-2"><Lucide class="w-5 h-5" icon="BoxSelect" /> {{ item.price_per_meter }} per m²</div>
            </div>

            <Link :href="'/estate/' + item.id">
                <button class="absolute bottom-6 w-full p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">See Details</button>
            </Link>
        </div>
    </div>
</template>
