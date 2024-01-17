<script setup>
import { ref, watch, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

import moment from 'moment';
import Lucide from '@/Components/Lucide.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

import VueTailwindDatePicker from "vue-tailwind-datepicker";

import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from "leaflet";
import "leaflet.markercluster";
import markerIcon from "@/assets/pin.png";


const props = defineProps({
    booking: Object,
    facilities: Object
});


const book = props.booking[0];
const rooms = ref(null);
const images = book.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));

const dateValue = ref("");
const formatter = ref({
    date: 'DD MMM YYYY',
    month: 'MMM',
});
function dDate(date) {
    return date > new Date()
}

watch(dateValue, (newValue) => {
    let { startDate, endDate } = convertDateRange(newValue);

    if (startDate == 'Invalid date' || endDate == 'Invalid date') {
        startDate = null;
        endDate = null;
    }
    selectedDated(startDate, endDate)
});

function convertDateRange(dateString) {
    const [startDateStr, endDateStr] = dateString.split(' ~ ');
    let dayFormat = 'D';

    // const startDate = moment(startDateStr, 'DD MMM YYYY').format('YYYY-MM-D');
    // const endDate = moment(endDateStr, 'DD MMM YYYY').format('YYYY-MM-D');
    const startMoment = moment(startDateStr, 'DD MMM YYYY');
    const endMoment = moment(endDateStr, 'DD MMM YYYY');

    if (startMoment.date() > 1 || endMoment.date() > 1) {
        dayFormat = 'DD';
    }

    const startDate = startMoment.format(`YYYY-MM-${dayFormat}`);
    const endDate = endMoment.format(`YYYY-MM-${dayFormat}`);

    console.log(startDate, endDate);
    return { startDate, endDate };
}


const selectedDated = async (checkin, checkout) => {
    try {
        const response = await axios.post("/api/booking_data_rate", {
            'booking_id': book.id,
            'checkin': checkin,
            'checkout': checkout
        });
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
});

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


                        <div class="mb-4">{{ book.description }}</div>

                        <VueTailwindDatePicker v-model="dateValue" :formatter="formatter" :disable-date="dDate"
                            @change="() => { console.log(dateValue); }" />


                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-4 my-4">
                            <div v-for="room in rooms" :key="room">
                                <div
                                    class="shadow rounded-lg p-4 bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out">
                                    <div class="text-2xl">{{ room.occupancy ? room.occupancy + '%' : 'No Data' }}</div>
                                    <div>{{ room.room_type }}</div>
                                </div>
                            </div>
                        </div>

                        <div id="mapContainer" style="z-index: 0; width: 100%; height: 500px"></div>

                    </div>
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>

