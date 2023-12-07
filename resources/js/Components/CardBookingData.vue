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
    <div class="grid grid-cols-4 grid-rows-1 mx-4 bg-gray-100 shadow rounded-md">


        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
            <slide v-for="image in images" :key="image" class="w-full h-36 rounded-lg overflow-hidden">
                <img class="object-cover w-full rounded-lg" :src="image" alt="">
            </slide>

            <template #addons>
                <navigation />
            </template>
        </carousel>


        <div class="col-span-3 h-36 ml-6 mr-4 pt-2">
                <div class="flex relative">
                    <div class="text-xl font-semibold">{{ item.title }}</div>
                    <div class="mx-2 mt-1 mb-4 flex gap-x-1">
                        <Lucide v-for="star in item.star" class="w-5 h-5 fill-black" icon="Star" />
                        <div class="">{{ item.star }}</div>
                    </div>
                    <a :href="item.link" target="_blank" class="absolute top-1 right-0" rel="noopener noreferrer">
                        <Lucide v-for="star in item.star" class="w-5 h-5 cursor-pointer" icon="ExternalLink" />
                    </a>

                </div>
                <div class="text-md">{{ item.city }}</div>




                <div class="w-3/4 mt-5 flex font-medium justify-between" v-if="item.rooms[0]">
                    <div>Rooms types: {{ item.rooms.length }}</div>
                    <div>Count rooms: {{ totalRooms }}</div>
                    <div v-if="avg_room_price">Avg price {{ avg_room_price.toFixed(2) }}$</div>
                </div>
                <div v-else class="text-center mt-4 text-lg">No rooms data</div>

                <!-- <a :href="item.Link" target="_blank" class="cursor-pointer text-lg font-semibold hover:text-blue-600">Link</a> -->
                
                <!-- <Link :href="item.link" class="w-full pr-12 lg:pr-0 bottom-4">
                <button class="w-full p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Link</button>
                </Link> -->
        </div>

        <!-- <div class="h-min grid mx-6 mt-6 overflow-y-auto">
            <div v-if="item.rooms[0]" v-for="room in item.rooms" class="flex flex-row">
                <div>{{ room.title }}</div><br>
            </div>
            <div v-else class="mx-auto mt-18 text-lg">No rooms data</div>
        </div> -->


    </div>
</template>
