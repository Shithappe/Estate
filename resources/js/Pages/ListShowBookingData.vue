<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AddToListModal from '@/Components/AddToListModal.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Lucide from '@/Components/Lucide.vue';
import CardBookingData from '@/Components/CardBookingData.vue';
import Dropdown from '@/Components/Dropdown.vue';
import RoomInfo from '@/Components/RoomInfo.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import moment from 'moment';

const props = defineProps({
    is_share: {
        type: Boolean,
        default: false
    },
    lists: Object,
    list: Object,
    auth: Object
});

const selectedOption = ref(props.list.type === 'complex' ? 'Complexes' : 'Units');
const bookingData = ref(null);

// Вычисляемое свойство для получения массива id из props.list.hotels
const itemIds = computed(() => {
    if (props.list.type === 'complex') return props.list.items.map(items => items.id);
    if (props.list.type === 'unit') return props.list.items.map(items => items.room_id);
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
            booking_ids: props.list.type === 'complex' ? itemIds.value : null,
            rooms_ids: props.list.type === 'unit' ? itemIds.value : null,
            checkin: startDate,
            checkout: endDate
        });

        if (props.list.type === 'unit') {
            // Распаковываем вложенные массивы
            const flattenedData = response.data.flat();

            // Группируем элементы по полю booking_title
            const groupedByBookingTitle = flattenedData.reduce((acc, item) => {
                // Если массив для текущего booking_title не существует, создаем его
                if (!acc[item.booking_title]) {
                    acc[item.booking_title] = [];
                }
                // Добавляем элемент в соответствующий массив
                acc[item.booking_title].push(item);
                return acc;
            }, {});

            // Преобразуем объект в массив массивов
            const groupedArray = Object.values(groupedByBookingTitle);
            bookingData.value = groupedArray;
        } else {
            bookingData.value = response.data;
        }
        
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

    // console.log(props);
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

const copied = ref(false);
const copyLink = (click = false) => {
    setAccessLink(props.list);

    const shareLink = `${window.location.origin}/list/share/${props.list.share_token}`;
    if (click) {
        navigator.clipboard.writeText(shareLink);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 1000);
    }
    return shareLink;
}

const setAccessLink = async (item) => {
    try {
        await axios.patch(`/api/list/${item.id}`, {
            user_id: props.auth.user.id,
            privacy_mode: "link"
        });
    } catch (error) {
        console.error(error);
    }
}

const removeItemFromList = (listType, itemId) => {
    // console.log(listType, itemId);

    if (listType === 'unit') {
        bookingData.value = bookingData.value.map(group =>
            group.filter(room => room.room_id !== itemId)
        ).filter(group => group.length > 0);
    }

    if (listType === 'complex') {
        props.list.items = props.list.items.filter(item => item.id !== itemId);
    }
};


const searchText = ref(''); // Текст, введенный пользователем

const applyFilters = () => {
  // Устанавливаем filterText в значение searchText при нажатии кнопки
  filterText.value = searchText.value.trim();
};

const filteredItems = computed(() => {
  if (!filterText.value) {
    return props.list.items;
  }

  const searchText = filterText.value.toLowerCase();
  return props.list.items.filter(item => item.title.toLowerCase().includes(searchText));
});

const showAddToListModal = ref(false);
const openAddToListModal = () => { showAddToListModal.value = true };
const closeAddToListModal = () => { showAddToListModal.value = false; };

</script>

<template>
    <SimpleAppLayout title="List">

        <div class="mx-2 py-2 lg:py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <div class="flex justify-between items-center gap-x-2 mb-4">
                    <span class="text-2xl font-medium">{{ props.list.name }}</span>
                    <div class="flex gap-x-2">
                        <button v-if="!is_share" @click="copyLink(true)" data-copy-to-clipboard-target="npm-install-copy-text" class="text-gray-900 rounded-lg py-2 px-2.5 inline-flex items-center justify-center bg-white border-gray-200 border">
                            <span v-if="!copied" id="default-message" class="inline-flex items-center">
                                <svg class="w-3 h-3 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                                    <path d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                                </svg>
                                <span class="text-xs font-semibold">Copy Link</span>
                            </span>
                            <span v-else id="success-message" class="inline-flex items-center">
                                <svg class="w-3 h-3 text-blue-700" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                </svg>
                                <span class="text-xs font-semibold text-blue-700">Copied</span>   
                            </span>
                        </button>

                        <button v-else-if="is_share && props.auth.user?.id" class="text-gray-900 rounded-lg py-2 px-2.5 flex gap-x-2 items-center justify-center bg-white border-gray-200 border">
                            <Lucide class="w-5 h-5" icon="Save" />
                            <span @click="openAddToListModal" class="text-sm font-semibold">Save list</span>
                        </button>


                        <Dropdown align="right" width="48" :contentClasses="['py-1', 'bg-white']">
                            <template #trigger>
                                <button class="flex px-2 py-1 rounded-lg shadow hover:bg-gray-200">
                                    <span>{{ selectedOption }}</span>
                                    <Lucide class="w-5 h-5 mt-1" icon="ChevronDown" />
                                </button>
                            </template>
                            <template #content>
                                <div v-if="props.list.type === 'complex'" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" @click="selectOption('Complexes')">Complexes</div>
                                <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" @click="selectOption('Units')">Units</div>
                                <div class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" @click="selectOption('Map')">Map</div>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <div v-if="selectedOption == 'Complexes'" class="my-8 flex-col gap-y-4">
                    <div class="flex justify-center mx-auto mb-4">
                    <div class="history relative w-full lg:max-w-4xl flex mx-4 transition duration-150 ease-in-out">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-2.5 pointer-events-none">
                        <Lucide icon="Search" />
                        </div>
                        <input ref="searchInput" type="search" v-model="searchText" @focus="showHistory = true"
                            class="block w-full p-2 ps-10 text-md text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 transition duration-300 ease-in-out"
                            placeholder="The Slow..." required>
                        <button @click="applyFilters"
                                class="text-white absolute end-px inset-y-px bg-blue-700 focus:outline-none font-medium rounded-lg text-md px-4 py-2">Search</button>
                    </div>
                    </div>

                    <div class="flex flex-wrap">
                    <CardBookingData
                        class="col-span-1"
                        v-for="item in filteredItems" :key="item.id" 
                        :listId="props.list.id"
                        :item="item"
                        :auth="auth"
                        :lists="lists"
                        @removeItem="removeItemFromList"
                    />
                    </div>
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
                        <RoomInfo
                            :rooms="rooms"
                            :listId="props.list.id"
                            :listType="props.list.type"
                            @removeItem="removeItemFromList"
                        />
                    </div>
                </div>

            </div>
        </div>

        <AddToListModal
            v-if="props.lists && showAddToListModal"
            :lists="props.lists.complex"
            :itemId="props.list.items.map(item => item.id)"
            type='complex'
            :auth="props.auth"
            :show="showAddToListModal"
            @close="closeAddToListModal"
        />
    </SimpleAppLayout>
</template>
