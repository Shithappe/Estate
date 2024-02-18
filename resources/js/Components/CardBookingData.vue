<script setup>
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
    item: Object,
});

const images = props.item.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));

// console.log(props.item.rooms);

let count_rooms = 0, occupancy_rate = 0;
// props.item.rooms.forEach(room => {
//     console.log(room.occupancy_rate);
//     // count_rooms += Number(room.max_available);
//     // occupancy_rate += Number(room.occupancy_rate);
// });
// occupancy_rate = Math.round(occupancy_rate / props.item.rooms.length);

</script>

<template>
    <div class="m-4 w-72 min-w-64 max-96 flex flex-col bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 hover:bg-gray-200 transition duration-300 ease-in-out"
        :class="{'bg-green-200 hover:bg-green-300': props.item.selected}">

        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
            <slide v-for="image in images" :key="image" class="w-full h-36 rounded-lg overflow-hidden">
                <img class="object-cover w-full rounded-lg" :src="image" alt="">
            </slide>

            <template #addons>
                <navigation />
            </template>
        </carousel>


        <div class="relative col-span-3 h-72 mx-3 pt-2 pb-2">
            <div class="flex flex-col relative">
                <div class="text-xl font-semibold hover:text-blue-800">
                    <Link :href="'booking_data/' + item.id">{{ item.title }}</Link>
                </div>
                <div class="mt-1 flex">
                    <Lucide v-for="star in item.star" class="w-5 h-5 fill-black" icon="Star" />
                </div>
            </div>

            <div class="flex items-center text-md mb-1">
                <!-- <Lucide class="w-4 h-4" icon="MapPin" /> -->
                {{ item.city }}
            </div>

            <div class="mt-4 mb-6 flex justify-between gap-y-2 px-2 font-medium">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Hotel" /> {{ item.type }}
                    </div>
                    <div class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Bed" /> {{ count_rooms }}
                    </div>
                    <div class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Tags" /> {{ item.rooms.length }}
                    </div>
                    
                </div>

                <div class="flex flex-col">
                    <div v-if="item.price" class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="DollarSign" /> {{ item.price }}
                    </div>
                    
                    <div v-if="item.score" class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Star" /> {{ item.score }}
                    </div>

                    <div v-if="item.rooms[0]" class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Zap" /> {{ occupancy_rate }}%
                    </div>
                </div>
            </div>

            <Link :href="'booking_data/' + item.id" class="absolute bottom-3 w-full">
                <button class="w-full p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">See Details</button>
            </Link>
        </div>
    </div>
</template>
