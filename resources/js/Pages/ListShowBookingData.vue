<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Lucide from '@/Components/Lucide.vue';
import CardBookingData from '@/Components/CardBookingData.vue';
import Dropdown from '@/Components/Dropdown.vue';
import RoomInfo from '@/Components/RoomInfo.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import moment from 'moment';

const props = defineProps({
    list: Object,
    auth: Object
});

const selectedOption = ref('Complexes');
const bookingData = ref(null);

// Вычисляемое свойство для получения массива id из props.list.hotels
const bookingIds = computed(() => {
    return props.list.hotels.map(hotel => hotel.id);
});

const dateRange = ref('');
const filterText = ref('');

// Функция для конвертации строки даты в объект Date
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

// Функция для выполнения запроса booking_data_rate
const fetchBookingData = async (startDate, endDate) => {
    try {
        const response = await axios.post("/api/booking_data_rate", {
            booking_id: null,
            booking_ids: bookingIds.value,
            checkin: startDate,
            checkout: endDate
        });
        console.log("API response:", response.data);
        bookingData.value = response.data;
    } catch (error) {
        console.error("Error in API call:", error);
    }
};

// Следим за изменениями dateRange и выполняем запрос
watch(dateRange, (newDateRange) => {
    const { startDate, endDate } = convertDateRange(newDateRange);

    if (startDate !== 'Invalid date' && endDate !== 'Invalid date') {
        // console.log("Sending request with dates:", startDate, endDate);
        fetchBookingData(startDate, endDate);
    } else {
        console.log("Invalid date range");
    }
});

onMounted(() => {
    // Устанавливаем начальный диапазон дат: от месяца назад до сегодня
    const endDate = moment();
    const startDate = moment().subtract(1, 'month');
    dateRange.value = `${startDate.format('DD MMM YYYY')} ~ ${endDate.format('DD MMM YYYY')}`;
});

const selectOption = (option) => { selectedOption.value = option }

const filteredBookingData = computed(() => {
    if (!filterText.value) {
        return bookingData.value;
    }

    const searchText = filterText.value.toLowerCase();

    // Фильтрация массива массивов
    return bookingData.value.map(rooms => 
        rooms.filter(room => room.room_type.toLowerCase().includes(searchText))
    ).filter(filteredRooms => filteredRooms.length > 0);
});

// Добавьте этот watch для отслеживания изменений filterText
watch(filterText, (newValue) => {
    console.log('Filter text changed:', newValue);
});
</script>

<template>
    <SimpleAppLayout title="List">

        <div class="mx-2 py-2 lg:py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <div class="flex justify-between items-center gap-x-2 mb-4">
                    <span class="text-2xl font-medium">{{ props.list.name }}</span>
                    <Dropdown align="right" width="48" :contentClasses="['py-1', 'bg-white']">
                        <template #trigger>
                            <button class="flex px-2 py-1 rounded-lg shadow hover:bg-gray-200">
                                <span>{{ selectedOption }}</span>
                                <Lucide class="w-5 h-5 mt-1" icon="ChevronDown" />
                            </button>
                        </template>
                        <template #content>
                            <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" @click="selectOption('Complexes')">Complexes</div>
                            <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" @click="selectOption('Units')">Units</div>
                            <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" @click="selectOption('Map')">Map</div>
                        </template>
                    </Dropdown>
                </div>

                <div v-if="selectedOption == 'Complexes'" class="my-8 flex flex-wrap">
                    <CardBookingData v-for="item in props.list.hotels" :key="item.id" :item="item" :auth="true" class="col-span-1" />
                </div>
                
                <div v-if="selectedOption == 'Units'">
                    <div class="mb-4 flex items-center space-x-4">
                        <DateRangePicker v-model="dateRange" class="flex-grow" />
                        <input 
                            v-model="filterText"
                            type="text"
                            placeholder="Filter"
                            class="px-4 py-2 border rounded-md"
                        />
                    </div>
                    
                    <div v-for="rooms in filteredBookingData" :key="rooms.booking_id">
                        <RoomInfo :rooms="rooms" />
                    </div>
                </div>

            </div>
        </div>
    </SimpleAppLayout>
</template>
