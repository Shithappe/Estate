<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
    item: Object,
});

const images = ref(props.item.images.unshift(props.item.main_image));
</script>

<template>
    <div class="flex flex-col lg:flex-row mx-4 gap-x-8 bg-gray-100 shadow rounded-md">
        <div class="lg:w-full rounded-lg">
            <carousel id="gallery" :items-to-show="1" :wrap-around="false">
                <slide v-for="image in item.images" :key="image">
                    <img class="object-cover w-full h-full rounded-lg" :src="image" alt="">
                </slide>

                <template #addons>
                    <navigation />
                    <div class="lg:hidden">
                        <pagination />
                    </div>
                </template>
            </carousel>
        </div>


        <div class="relative w-full lg:w-96 mx-6 py-6">
            <div class="text-xl font-semibold">{{ item.builder_name }}</div>
            <div>{{ item.city }}, {{ item.street }}</div>

            <div class="mt-1 mb-4 flex gap-x-1">
                <Lucide v-for="star in item.rate" class="w-6 h-6 fill-black" icon="Star" />
            </div>

            <div class="my-1 text-md">{{ item.title }}</div>
            <div class="whitespace-pre-line pr-4">{{ item.description }}</div>

            <div class="mt-4 mb-6 grid grid-cols-2 gap-y-2 font-medium justify-between">
                <div class="flex items-center gap-2">
                    <Lucide class="w-5 h-5" icon="BedDouble" /> {{ item.room_count }}
                </div>
                <div class="flex items-center gap-2">
                    <Lucide class="w-5 h-5" icon="Layers2" /> {{ item.floor }}
                </div>
                <div class="flex items-center gap-2">
                    <Lucide class="w-5 h-5" icon="Scan" /> {{ item.square }}m²
                </div>
                <div class="flex items-center gap-2">
                    <Lucide class="w-5 h-5" icon="BoxSelect" /> {{ item.price_per_meter }} per m²
                </div>
            </div>
            <div class="text-2xl font-bold mb-12">{{ item.price }} $</div>


            <Link :href="'/estate/' + item.id" class="absolute pr-12 lg:pr-0 bottom-4 w-full">
                <button class="w-full p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">See Details</button>
            </Link>
        </div>
    </div>
</template>
