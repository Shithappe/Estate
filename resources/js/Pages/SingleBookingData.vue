<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import moment from 'moment';
import Lucide from '@/Components/Lucide.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

import { GoogleMap, Marker } from "vue3-google-map";


const props = defineProps({
    booking: Object,
    rooms: Object,
});

function structureCoordinates(inputString) {
    const coordinates = inputString.split(',');
    const result = [];

    for (let i = 0; i < coordinates.length; i += 2) {
        const latitude = parseFloat(coordinates[i].slice(0, 6));
        const longitude = parseFloat(coordinates[i + 1].slice(0, 6));

        if (!isNaN(latitude) && !isNaN(longitude)) {
            result.push({ lat: longitude, lng: latitude });
        }
    }

    return result;
}

function findCenter(coordinates) {
    if (coordinates.length === 0) {
        return null;
    }

    let sumLat = 0;
    let sumLng = 0;

    for (let i = 0; i < coordinates.length; i++) {
        sumLat += coordinates[i].lat;
        sumLng += coordinates[i].lng;
    }

    const avgLat = sumLat / coordinates.length;
    const avgLng = sumLng / coordinates.length;

    return { lat: avgLat, lng: avgLng };
}

const book = props.booking[0]
const images = book.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));

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
    } catch (error) {
        console.error(error);
    }
}

// const center = { lat: 40.689247, lng: -74.044502 };
// const center = { lat: -8.523275, lng: 115.245701 };
const coordinates = structureCoordinates(book.coordinates);
const center = findCenter(coordinates);


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
                        <div class="text-2xl font-bold">{{ book.title }}</div>

                        <div class="flex gap-2 mt-2 mb-4">
                            <Lucide class="w-5 h-5" icon="MapPin" /> {{ book.city }}
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
                                        <!-- <datepicker v-model="selectedDateRange" :config="datepickerConfig"></datepicker> -->
                                    </button>
                                </div>

                                <div class="w-full">
                                    <div v-for="(percentage, roomType) in rooms" :key="roomType">
                                        <div class="mt-2 p-4 rounded-lg shadow text-center">
                                            {{ roomType }} - {{ percentage }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="w-full sm:w-full md:w-2/3 h-96 bg-gray-400"></div> -->
                            <GoogleMap api-key="AIzaSyCMenSGPO8e2i7EFsO456VZkMh30a310YE" style="width: 100%; height: 500px"
                                :center="center" :zoom="13">
                                <!-- <Marker :options="{ position: coordinates[0] }" /> -->
                                <Marker v-for="(coord, index) in coordinates" :key="index" :options="{ position: coord }" />
                            </GoogleMap>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>

