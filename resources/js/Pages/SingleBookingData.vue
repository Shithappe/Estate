<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

import moment from 'moment';
import Lucide from '@/Components/Lucide.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from "leaflet";
import "leaflet.markercluster";
import markerIcon from "@/assets/pin.png";



const props = defineProps({
    booking: Object,
    rooms: Object,
    facilities: Object
});

const book = props.booking[0];
const images = book.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));

let rooms = ref(props.rooms);

const selectedDate = ref('all');

const today = moment().format('YYYY-MM-DD');
const weekAgo = moment().subtract(7, 'days').format('YYYY-MM-DD');
const mounthAgo = moment().subtract(1, 'months').format('YYYY-MM-DD');
const yearAgo = moment().subtract(1, 'years').format('YYYY-MM-DD');

const selectedDated = async () => {

    let checkin = null;
    let checkout = today;

    switch (selectedDate.value) {
        case 'week':
            checkin = weekAgo;
            break;
        case 'month':
            checkin = mounthAgo;
            break;
        case 'year':
            checkin = yearAgo;
            break;

        default:
            checkin = null;
            checkout = null;
            break;
    }

    try {
        const response = await axios.post("/api/booking_data_rate", {
            'booking_id': book.id,
            'checkin': checkin,
            'checkout': checkout
        });
        console.log(response.data);
        rooms.value = response.data;
    } catch (error) {
        console.error(error);
    }
}

let map = null;
const location = book.location.split(',')

onMounted(() => {
    selectedDated();


    map = L.map("mapContainer").setView(location, 15);
    L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
        attribution:
            '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    const customIcon = L.icon({
        iconUrl: markerIcon, // Путь к вашему изображению маркера
        iconSize: [50, 50], // Размер изображения маркера
        iconAnchor: [25, 48], // Якорь иконки
    });


    // Создание кластеризатора маркеров
    const markerCluster = L.markerClusterGroup();

    const markers = [L.marker(location, { icon: customIcon })];

    markerCluster.addLayers(markers);
    map.addLayer(markerCluster);
})

</script>

<template>
    <SimpleAppLayout title="Головна">
        <template #header>
            <h2 class="flex gap-x-2 font-semibold text-xl text-gray-800 leading-tight">
                <Link href="/">
                <Lucide icon="ArrowLeft" />
                </Link>
                <div>{{ book.title }}</div>
            </h2>
        </template>

        <div class="mx-2 py-2 lg:py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="">
                    <carousel id="gallery" :items-to-show="1" :wrap-around="false">
                        <slide v-for="image in images" :key="image">
                            <img class="object-cover rounded-lg" :src="image" alt="">
                        </slide>

                        <template #addons>
                            <navigation />
                            <pagination />
                        </template>
                    </carousel>

                    <div class="mt-6">
                        <div class="flex gap-x-2 items-center text-2xl font-semibold">
                            <div>{{ book.title }}</div>

                            <a :href="book.link" target="_blank" rel="noopener noreferrer">
                                <Lucide class="w-5 h-5 cursor-pointer" icon="ExternalLink" />
                            </a>
                        </div>



                        <div class="flex gap-2 mt-2 mb-4">
                            <Lucide class="w-5 h-5" icon="MapPin" /> {{ book.city }}
                        </div>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-2 py-1 rounded-lg shadow" v-for="facility in facilities" :key="facility">
                                {{ facility }}
                            </span>
                        </div>


                        <div>{{ book.description }}</div>

                        <div class="flex flex-col md:flex-row mt-8 gap-x-4">
                            <div class="w-full sm:w-full md:w-1/3 flex flex-col gap-y-4 items-center">

                                <div class="w-full flex gap-x-2">
                                    <select v-model="selectedDate" @change="selectedDated"
                                        class="w-full py-2 border-0 text-gray-500 rounded-lg shadow focus:shadow-lg focus:outline-none focus:ring focus:border-blue-300 appearance-none leading-5 transition duration-150 ease-in-out">
                                        <option value="all" selected>All Dates</option>
                                        <option value="year">Last Year</option>
                                        <option value="month">Last Month</option>
                                        <option value="week">Last Week</option>
                                    </select>

                                    <button class="p-2 rounded-lg shadow hover:border-blue-300">
                                        <Lucide icon="CalendarDays" />
                                    </button>

                                    <!-- <Calendar v-model="selectedDate" :inline="true"></Calendar>
                                    <p>Выбранная дата: {{ selectedDate }}</p> -->
                                </div>

                                <div class="w-full">
                                    <div v-for="(percentage, roomType) in rooms" :key="roomType">
                                        <div class="mt-2 p-4 rounded-lg shadow text-center">
                                            {{ roomType }} - {{ percentage }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="mapContainer" style="width: 100%; height: 500px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>

