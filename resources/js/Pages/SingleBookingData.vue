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
// import LineChart from '@/Components/LineChart.vue';


const props = defineProps({
    booking: Object,
    facilities: Object
});
const today = new Date();
const tomorrow = new Date(today);
tomorrow.setDate(today.getDate() + 1);

// Получаем дату месяц назад
const lastMonth = new Date();
lastMonth.setMonth(today.getMonth() - 1);

// Функция для форматирования даты в формат DD MMM YYYY
const formatDate = (date) => {
  const day = String(date.getDate()).padStart(2, '0');
  const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();
  return `${day} ${month} ${year}`;
};

// Форматируем даты
const startDateStr = formatDate(lastMonth);
const endDateStr = formatDate(today);

// Формируем строку в требуемом формате
const dateRange = `${startDateStr} ~ ${endDateStr}`;


const book = props.booking[0];
const rooms = ref(null);

const images = book.images.replaceAll('max500', 'max1024').slice(1, -1).split(', ').map(item => item.slice(1, -1));

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
    console.log(dateString);
    const [startDateStr, endDateStr] = dateString.split(' ~ ');
    let dayFormat = 'D';

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
    console.log(checkin, checkout);
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

const allData = ref(null);
const getAll = async () => {
    try {
        const response = await axios.get("/get_all/" + book.id);
        allData.value = response.data;
    } catch (error) {
        console.error(error);
    }
}

let map = null;
const location = book.location.split(',')


function formatDateForLink(date) {
    const year = date.getFullYear();
    let month = date.getMonth() + 1;
    if (month < 10) month = '0' + month;
    let day = date.getDate();
    if (day < 10) day = '0' + day;
    return `${year}-${month}-${day}`;
}

function wrapParagraphs(text) {
    const paragraphs = text.split(/\n\n/); // Разбиваем текст на абзацы
    const wrappedParagraphs = paragraphs.map(paragraph => `<p>${paragraph.trim()}</p>`); // Оборачиваем каждый абзац в тег <p>
    return wrappedParagraphs.join('\n'); // Объединяем абзацы с новыми тегами <p>
}

onMounted(() => {
    dateValue.value = dateRange;
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
    <SimpleAppLayout :title="book.title + ' -'">
        <template #header>
            <h2 class="flex gap-x-2 font-semibold text-xl text-gray-800 leading-tight">
                <Link href="/">
                <Lucide icon="ArrowLeft" />
                </Link>
                <div>{{ book.title }}</div>
            </h2>
        </template>

        <div class="mx-2 py-2 lg:py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div>
                    <carousel id="gallery" :items-to-show="1" :wrap-around="false">
                        <slide v-for="image in images" :key="image" class="">
                            <img class="w-full max-h-128 object-cover rounded-lg" :src="image" alt="">
                        </slide>

                        <template #addons>
                            <navigation />
                            <pagination />
                        </template>
                    </carousel>

                    <div class="mt-6 relative">
                        <div class="sm:block lg:absolute z-10 -top-36 left-1.5 rounded-lg lg:px-4 lg:pt-2 lg:backdrop-filter lg:backdrop-blur-md lg:bg-gray-200 lg:bg-opacity-30">
                            <div class="flex gap-x-2 items-center text-3xl font-semibold">
                                <div>{{ book.title }}</div>

                                <a :href="`${book.link}?checkin=${formatDateForLink(today)}&checkout=${formatDateForLink(tomorrow)}`" target="_blank" rel="noopener noreferrer">
                                    <Lucide class="w-5 h-5 cursor-pointer" icon="ExternalLink" />
                                </a>
                            </div>

                            <div class="flex gap-2 mt-2 mb-4">
                                <Lucide class="w-5 h-5" icon="MapPin" /> {{ book.city }}
                            </div>
                        </div>


                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-2 py-1 rounded-lg shadow" v-for="facility in facilities" :key="facility">
                                {{ facility }}
                            </span>
                        </div>


                        <div class="flex flex-col gap-y-2 mb-4" v-html="wrapParagraphs(book.description)"></div>

                        <VueTailwindDatePicker v-model="dateValue" :formatter="formatter" :disable-date="dDate"
                            @change="() => { console.log(dateValue); }" />

                        <!-- <LineChart /> -->

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-4 my-4">
                            <div v-for="room in rooms" :key="room">
                                <div
                                    class="flex justify-between shadow rounded-lg p-4 bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out">
                                    <div>
                                        <div class="text-2xl">{{ room.occupancy > 0 ? room.occupancy + '%' : 'N/A' }}
                                        </div>
                                        <div>{{ room.room_type }}</div>
                                    </div>
                                    <div class="flex flex-col justify-between items-end">
                                        <div v-if="room.price" class="text-xl">${{ room.price }}</div>
                                        <!-- <div v-if="room.hasOwnProperty('active') && room.active == false" class="text-sm">{{ room.active }}*not on the site</div> -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="mapContainer" style="z-index: 0; width: 100%; height: 500px"></div>

                        <button @click="getAll">Get all data</button>
                        <div v-if="allData" class="flex flex-col gap-y-2">
                            <table v-if="allData?.booking_data">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Link</th>
                                        <th>Address</th>
                                        <th>City</th>
                                        <th>Price</th>
                                        <th>Created At</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Итерация по данным -->
                                    <tr v-for="book in allData.booking_data" :key="book.id">
                                        <td>{{ book.id }}</td>
                                        <td>{{ book.title }}</td>
                                        <td>{{ book.description }}</td>
                                        <td><a :href="book.link" target="_blank">Link</a></td>
                                        <td>{{ book.address }}</td>
                                        <td>{{ book.city }}</td>
                                        <td>{{ book.price }}</td>
                                        <td>{{ book.created_at }}</td>
                                        <td>{{ book.location }}</td>
                                        <td>{{ book.type }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table v-if="allData?.rooms">
                                <thead>
                                    <tr>
                                        <th>Room type</th>
                                        <th>Max available</th>
                                        <th>Active</th>
                                        <th>Price</th>
                                        <th>Occupancy</th>
                                        <th>Updated at</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="book in allData.rooms" :key="book.id">
                                        <td>{{ book.room_type }}</td>
                                        <td>{{ book.max_available }}</td>
                                        <td>{{ book.active }}</td>
                                        <td>{{ book.price }}</td>
                                        <td>{{ book.occupancy }}</td>
                                        <td>{{ book.updated_at }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table v-if="allData?.rooms_2_day">
                                <thead>
                                    <tr>
                                        <th>Room type</th>
                                        <th>Available rooms</th>
                                        <th>Checkin</th>
                                        <th>Checkout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="book in allData.rooms_2_day" :key="book.id">
                                        <td>{{ book.room_type }}</td>
                                        <td>{{ book.available_rooms }}</td>
                                        <td>{{ book.checkin }}</td>
                                        <td>{{ book.checkout }}</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>

<style scoped>
table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}

br {
    height: 10px;
}

.break-lines {
        white-space: pre-line;
}
</style>