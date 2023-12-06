<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
    item: Object,
});

const images = props.item.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));

let totalPrices = 0;
props.item.rooms.forEach(room => {
    totalPrices += parseFloat(room.prices);
});

let avg_room_price = totalPrices / props.item.rooms.length

let totalRooms = 0;
props.item.rooms.forEach(room => {
    totalRooms += parseFloat(room.max_available_rooms);
});

</script>

<template>
    <div class="grid grid-cols-3 grid-rows-1 mx-4 bg-gray-100 shadow rounded-md">


        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
            <slide v-for="image in images" :key="image">
                <img class=" rounded-lg" :src="image" alt="">
            </slide>

            <template #addons>
                <navigation />
            </template>
        </carousel>


        <div class="mx-6 py-6">
                <div class="flex">
                    <div class="text-xl font-semibold">{{ item.title }}</div>
                    <div class="mx-2 mt-1 mb-4 flex gap-x-1">
                        <Lucide v-for="star in item.star" class="w-5 h-5 fill-black" icon="Star" />
                        <div class="">{{ item.star }}</div>
                    </div>
                </div>
                <div class="text-md">{{ item.city }}</div>




                <div class="mt-4 mb-16 grid grid-cols-2 gap-y-2 font-medium justify-between">
                    <div>Rooms types: {{ item.rooms.length }}</div>
                    <div>Count rooms: {{ totalRooms }}</div>
                    <div v-if="avg_room_price">Avg price {{ avg_room_price.toFixed(2) }}$</div>
                </div>

                <Link :href="item.link" class="w-full pr-12 lg:pr-0 bottom-4">
                <button class="w-full p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Link</button>
                </Link>
        </div>

        <div class="h-min grid mx-6 mt-6 overflow-y-auto">
            <div v-if="item.rooms[0]" v-for="room in item.rooms" class="flex flex-row">
                <div>{{ room.title }}</div><br>
            </div>
            <div v-else class="mx-auto mt-18 text-lg">No rooms data</div>
        </div>


    </div>
</template>
