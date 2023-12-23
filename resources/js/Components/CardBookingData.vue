<script setup>
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


let avgPercentage = 0;
let avgPercentageCount = 0;

for (let key in props.item.averageOccupancyPercentage) {
    if (props.item.averageOccupancyPercentage.hasOwnProperty(key)) {
        avgPercentage += parseInt(props.item.averageOccupancyPercentage[key]);
        avgPercentageCount++;
    }
}

if (avgPercentageCount !== 0) {
    avgPercentage = Math.round(avgPercentage / avgPercentageCount);
} else {
    avgPercentage = 0;
}

</script>

<template>
    <div class="m-4 lg:m-0 flex flex-col lg:grid lg:grid-cols-4 lg:grid-rows-1  bg-gray-100 shadow rounded-md">

        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
            <slide v-for="image in images" :key="image" class="w-full h-36 rounded-lg overflow-hidden">
                <img class="object-cover w-full rounded-lg" :src="image" alt="">
            </slide>

            <template #addons>
                <navigation />
            </template>
        </carousel>


        <div class="col-span-3 lg:h-36 ml-6 mr-4 pt-2 pb-2">
                <div class="flex flex-col lg:flex-row relative">
                    <div class="text-xl font-semibold hover:text-blue-800">
                        <Link :href="'booking_data/'+item.id">{{ item.title }}</Link>
                    </div>
                    <div class="lg:mx-2 mt-1 mb-4 flex">
                        <Lucide v-for="star in item.star" class="w-5 h-5 fill-black" icon="Star" />
                        <!-- <div class="">{{ item.star }}</div> -->
                    </div>
                    <div>
                        <a :href="item.link" target="_blank" class="absolute top-1 right-0" rel="noopener noreferrer">
                            <Lucide class="w-5 h-5 cursor-pointer" icon="ExternalLink" />
                        </a>
                    </div>
                </div>

                <div class="text-md">{{ item.city }}</div>

                <div class="mr-8 mt-5 hidden md:flex font-medium justify-between" v-if="item.rooms[0]">
                    <div>Rooms types: {{ item.rooms.length }}</div>
                    <div>Count rooms: {{ totalRooms }}</div>
                    <div v-if="avg_room_price">Avg price {{ avg_room_price.toFixed(2) }}$</div>
                    <div>Rate {{ avgPercentage }}%</div>
                </div>
                <div v-else class="hidden md:block text-center mt-4 text-lg">No rooms data</div>

        </div>
    </div>
</template>
