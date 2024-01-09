<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
    item: Object,
});


const modeBtn = ref('Show');
const facilities = ref(props.item.facilities.slice(0, 4));
const showAllFacilities = ref(false);

const images = props.item.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));

let count_rooms = 0, occupancy_rate = 0;
props.item.rooms.forEach(room => {
    count_rooms += Number(room.max_available);
    occupancy_rate += room.occupancy_rate;
});
occupancy_rate = Math.round(occupancy_rate / props.item.rooms.length); 

const showFacilities = () => {
    showAllFacilities.value = !showAllFacilities.value;
    if (showAllFacilities.value) {
        facilities.value = props.item.facilities;
        modeBtn.value = 'Hide';
    }
    else {
        facilities.value = props.item.facilities.slice(0, 5);
        modeBtn.value = 'Show';
    }
}
</script>

<template>
    <div class="m-4 lg:m-0 flex flex-col lg:grid lg:grid-cols-4 lg:grid-rows-1 bg-gray-100 shadow rounded-md">

        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
            <slide v-for="image in images" :key="image" class="w-full rounded-lg overflow-hidden" :class="{'h-36': !showAllFacilities, 'h-48': showAllFacilities}">
                <img class="object-cover w-full rounded-lg" :src="image" alt="">
            </slide>

            <template #addons>
                <navigation />
            </template>
        </carousel>


        <div class="col-span-3  ml-6 mr-4 pt-2 pb-2">
            <div class="flex flex-col lg:flex-row relative">
                <div class="text-xl font-semibold hover:text-blue-800">
                    <Link :href="'booking_data/' + item.id">{{ item.title }}</Link>
                </div>
                <div class="lg:mx-2 mt-1 flex">
                    <Lucide v-for="star in item.star" class="w-5 h-5 fill-black" icon="Star" />
                </div>
                <div>
                    <a :href="item.link" target="_blank" class="absolute top-1 right-0" rel="noopener noreferrer">
                        <Lucide class="w-5 h-5 cursor-pointer" icon="ExternalLink" />
                    </a>
                </div>
            </div>

            <div class="text-md mb-1">{{ item.city }}</div>

            <div class="flex flex-wrap gap-2">
                <span class="px-2 py-1 rounded-lg shadow" v-for="facility in facilities"
                    :key="facility">
                    {{ facility }}
                </span>
                <button v-if="item.facilities.length > 5" class="px-2 py-1 opacity-70 hover:opacity-100 underline underline-offset-1 leading-5 transition duration-300 ease-in-out text-md"
                            @click="showFacilities">{{ modeBtn }} more
                </button>
            </div>

            <div class="mr-8 mt-5 flex flex-wrap font-medium justify-between">
                <div>Type: {{ item.type }}</div>
                <div>Rooms types: {{ item.rooms.length }}</div>
                <div>Count rooms: {{ count_rooms }}</div>
                <div v-if="item.rooms[0]">Rate {{ occupancy_rate }}%</div>
            </div>
        </div>
    </div>
</template>
