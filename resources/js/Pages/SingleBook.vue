<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';


const props = defineProps({
    item: Object,
});

const images = ref(props.item.images.unshift(props.item.main_image));
</script>

<template>
    <SimpleAppLayout title="EstateMarket">
        <template #header>
            <h2 class="flex gap-x-2 font-semibold text-xl text-gray-800 leading-tight">
                <Link href="/">
                    <Lucide icon="ArrowLeft" />
                </Link>
                <div>{{ item.title }}</div>
            </h2>
        </template>

        <div class="mx-2 py-2 lg:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="">
                    <carousel id="gallery" :items-to-show="1" :wrap-around="false">
                        <slide v-for="image in item.images" :key="image">
                            <img class="object-cover rounded-lg" :src="image" alt="">
                        </slide>

                        <template #addons>
                            <navigation />
                            <pagination />
                        </template>
                    </carousel>

                    <div class="mt-6">
                        <div class="text-2xl font-bold">{{ item.title }}</div>
                        <div class="text-xl font-semibold">{{ item.builder_name }}</div>

                        <div class="text-2xl font-bold">{{ item.price }} $</div>
                        <div class="flex gap-2 mt-2 mb-4">
                            <Lucide class="w-5 h-5" icon="MapPin" /> {{ item.city }}, {{ item.street }}
                        </div>
                        <div class="mt-1 mb-4 flex gap-x-1">
                            <Lucide v-for="star in item.rate" class="w-6 h-6 fill-black" icon="Star" />
                        </div>

                        <div>{{ item.description }}</div>

                        <div class="mt-4 mb-16 grid grid-cols-2 gap-y-2 font-medium justify-between">
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
                    </div>
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>
