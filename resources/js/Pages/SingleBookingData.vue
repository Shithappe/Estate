<script setup>
import { ref, watch, onMounted } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';

import moment from 'moment';
import Lucide from '@/Components/Lucide.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import Map from '@/Components/Map.vue';
import RoomInfo from '@/Components/RoomInfo.vue';
import AddToListModal from '@/Components/AddToListModal.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

import FormSubmissions from '@/Components/FormSubmissions.vue';
import { strToArray } from '@/Utils/strToArray.js';
import { checkImages } from '@/Utils/checkImages.js';
import { computed } from 'vue';



const props = defineProps({
    booking: Object,
    lists: Object,
    facilities: Object,
    auth: Object
});
const today = new Date();
const tomorrow = new Date(today);
tomorrow.setDate(today.getDate() + 1);

const showModal = ref(false);
const showModal1 = ref(false);
const openModal = () => { showModal.value = true; };
const openModal1 = () => { showModal1.value = true; };
const closeModal = () => { showModal.value = false; showModal1.value = false; };

const showAddToListModal = ref(false);
const closeAddToListModal = () => { showAddToListModal.value = false; };

const openAddToListModal = () => {
    showAddToListModal.value = true;
};

const book = props.booking[0];
const rooms = ref(null);
const dateRange = ref('');

const filteredImages = ref([]);

const initializeImages = async () => {
    filteredImages.value = await checkImages([...new Set([...strToArray(book.static_images, 1024), ...strToArray(book.images, 1024)])]);
};


function convertDateRange(dateString) {
    const [startDateStr, endDateStr] = dateString.split(' ~ ');
    let dayFormat = 'D';

    const startMoment = moment(startDateStr, 'DD MMM YYYY');
    const endMoment = moment(endDateStr, 'DD MMM YYYY');

    if (startMoment.date() > 1 || endMoment.date() > 1) {
        dayFormat = 'DD';
    }

    const startDate = startMoment.format(`YYYY-MM-${dayFormat}`);
    const endDate = endMoment.format(`YYYY-MM-${dayFormat}`);

    return { startDate, endDate };
}

const selectedDated = async (checkin, checkout) => {
    try {
        const response = await axios.post("/api/booking_data_rate", {
            'booking_id': book.id,
            'checkin': checkin,
            'checkout': checkout
        });
        // console.log("API response:", response.data); // Лог для отладки
        rooms.value = response.data;
    } catch (error) {
        console.error("Error in API call:", error);
    }
}

watch(dateRange, (newDateRange) => {
    const { startDate, endDate } = convertDateRange(newDateRange);

    if (startDate !== 'Invalid date' && endDate !== 'Invalid date') {
        selectedDated(startDate, endDate);
    } else {
        console.log("Invalid date range"); // Лог для отладки
    }
});

const allData = ref(null);
const getAll = async () => {
    try {
        const response = await axios.get("/get_all/" + book.id);
        allData.value = response.data;
    } catch (error) {
        console.error(error);
    }
}

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

const cutDescription = (text) => {
    // Разделяем текст на абзацы
    const paragraphs = text.split("\n");
    return paragraphs[0];

    // Разбиваем первый абзац на предложения
    const sentences = paragraphs[0].match(/[^.!?]+[.!?]+/g) || [];

    // Возвращаем первые три предложения, если их 5, или весь первый абзац
    // return sentences.length >= 5 ? sentences.slice(0, 3).join(" ") : firstParagraph;
};

const showFullText = ref(false);

const formattedDescription = computed(() => {
    return wrapParagraphs(showFullText.value ? book.description : cutDescription(book.description));
});

const mapLocations = ref([]);

onMounted(() => {
    initializeImages();
    
    // Подготавливаем данные для карты
    mapLocations.value = [{
        location: book.location.split(',').map(Number)
    }];
});

</script>

<template>
    <SimpleAppLayout :title="book.title + ' -'" :image="filteredImages[0]">
        <template #header>
            <h2 class="flex gap-x-2 font-semibold text-xl text-gray-800 leading-tight">
                <Link href="/"><Lucide icon="ArrowLeft" /></Link>
                <div>{{ book.title }}</div>
            </h2>
        </template>

        <div class="mx-2 py-2 lg:py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div>
                    <carousel id="gallery" :items-to-show="1" :wrap-around="false">
                        <slide v-for="image in filteredImages" :key="image" class="">
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

                        <div class="flex gap-x-2 mb-2">
                            <button @click="openModal" class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Buy object</button>
                            <button @click="openModal1" class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Get a consultation</button>
                            <button @click="openAddToListModal" class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Add to list</button>
                        </div>
                        <FormSubmissions :booking_id="book.id" target="buy" title="Buy investment property in Bali with passive income" des="" :show="showModal" @close="closeModal" />
                        <FormSubmissions :booking_id="book.id" target="get_consultation" title="Get advice on buying investment property in Bali with passive income" des="" :show="showModal1" @close="closeModal" />

                        <AddToListModal
                            :lists="props.lists.complex"
                            :itemId="book.id"
                            type='complex'
                            :auth="auth"
                            :show="showAddToListModal"
                            @close="closeAddToListModal"
                            @updateLists="newList => {
                                emit('updateLists', newList);
                            }"
                        />

                        <div class="flex flex-wrap my-4">
                            <div class="flex flex-col gap-y-2" v-html="formattedDescription"></div>
                            
                            <div v-if="!showFullText" @click="showFullText = true" class="cursor-pointer text-blue-500">Show more</div>
                            <div v-else @click="showFullText = false" class="cursor-pointer text-blue-500">Show less</div>
                        </div>
                        <!-- <div class="flex flex-col gap-y-2 mb-4" v-html="wrapParagraphs(book.description)"></div> -->

                        <DateRangePicker v-model="dateRange" />

                        <!-- <p class="mt-1 text-red-500 text-xs">The service shows the occupancy of the object, which is carried out through Booking.com service. Direct rentals are not taken into account here.</p> -->
                        <!-- <p class="mt-1 text-red-500 text-xs">Technical works are in progress, temporary absence of results is possible.</p> -->
                        <p class="mt-1 text-md">Data is displayed according to the hotel's occupancy on <a class="underline" :href="`${book.link}?checkin=${formatDateForLink(today)}&checkout=${formatDateForLink(tomorrow)}`" target="_blank" rel="noopener noreferrer">Booking.com</a></p>

                        <RoomInfo class="mt-2" v-if="rooms" :rooms="rooms" :lists="lists" :auth="auth" />

                        <Map :locations="mapLocations" />
                        <!-- <div id="mapContainer" style="z-index: 0; width: 100%; height: 500px"></div> -->

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
                                        <th>Price</th>
                                        <th>Checkin</th>
                                        <th>Checkout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="book in allData.rooms_2_day" :key="book.id">
                                        <td>{{ book.room_type }}</td>
                                        <td>{{ book.available_rooms }}</td>
                                        <td>{{ book.price }}</td>
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