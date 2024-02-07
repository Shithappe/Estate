<script setup>
import { ref } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Lucide from '@/Components/Lucide.vue';
import Pagination from '@/Components/Pagination.vue';
import PaginationPost from '@/Components/PaginationPost.vue';
import CardBookingData from '@/Components/CardBookingData.vue';
import SideBarFilters from '@/Components/SideBarFilters.vue';

const props = defineProps({
    data: Object,
    cities: Object,
    types: Object,
    facilities: Array
});

const searchInput = ref(null)
const load = ref(false);
const data = ref(props.data);

const showFilters = ref(false);
const useFilters = ref(false);

const selectedTitle = ref(null);

const updateData = (newData) => {
    console.log(newData);
    data.value = newData;
}

const applyFilters = async () => {
    useFilters.value = true;
    load.value = true;
    searchInput.value.blur();
    try {
        const response = await axios.post("/api/booking_data_filters", {
            'title': selectedTitle.value,
            'city': JSON.parse(localStorage.getItem('selectedCity')),
            'type': JSON.parse(localStorage.getItem('selectedTypes')),
            'facilities': JSON.parse(localStorage.getItem('selectedFacilities')),
            'price': JSON.parse(localStorage.getItem('selectedPrice')),
            'sort': JSON.parse(localStorage.getItem('selectedSort'))
        });
        data.value = response.data;
        load.value = false;
    } catch (error) {
        console.error(error);
    }
};

</script>

<template>
    <SimpleAppLayout title="Головна">

        <transition enter-active-class="transition ease-out duration-300" enter-from-class="-translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100" leave-active-class="transition ease-in duration-300"
            leave-from-class="translate-x-0 opacity-100" leave-to-class="-translate-x-full opacity-0">
            <SideBarFilters 
                v-if="showFilters" 
                :cities="props.cities" 
                :types="props.types" 
                :facilities="props.facilities" 
                @applyFilters="applyFilters" 
            />
        </transition>

        <div class="w-full px-4 py-6 mx-auto" :class="{ 'lg:w-4/5 lg:float-right lg:pl-24': showFilters, 'lg:px-24 lg:max-w-8xl': !showFilters }">

            <div class="flex ml-4"
                :class="{ 'xl:pl-4 2xl:pl-10': showFilters }">
                <button
                    class="px-2 py-0 rounded-lg shadow hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out text-md"
                    :class="{ 'shadow-lg text-slate-100 bg-black': showFilters }"
                    @click="() => { showFilters = !showFilters }">
                    <Lucide icon="Filter" />
                </button>

                <div class="relative w-full lg:max-w-4xl flex mx-4 transition duration-150 ease-in-out">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-2.5 pointer-events-none">
                        <Lucide icon="Search" />
                    </div>
                    <input 
                        ref="searchInput"
                        type="search" 
                        v-model="selectedTitle" 
                        @keyup.enter="applyFilters"
                        class="block w-full p-2 ps-10 text-md text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 transition duration-300 ease-in-out"
                        placeholder="Komaneka at Bisma..." required>
                    <button  @click="applyFilters"
                        class="text-white absolute end-px inset-y-px bg-blue-700 focus:outline-none font-medium rounded-lg text-md px-4 py-2">Search</button>
                </div>

            </div>

            <!-- <div 
                class="my-8 flex flex-col lg:grid lg:gap-1" 
                :class="{ 'lg:grid-cols-3': showFilters, 'lg:grid-cols-4': !showFilters, 'opacity-50': load }">
                <CardBookingData v-for="item in data.data" :key="item.id" :item="item" class="col-span-1" />
            </div> -->
            <div 
                class="my-8 flex flex flex-wrap" 
                :class="{ 'xl:pl-4 2xl:pl-10': showFilters, '': !showFilters, 'opacity-50': load }">
                <CardBookingData v-for="item in data.data" :key="item.id" :item="item" class="col-span-1" />
            </div>

            <PaginationPost v-if="useFilters" class="mt-6" :links="data.links" :updateData="updateData" />
            <Pagination v-else class="mt-6" :links="data.links" />
        </div>
    </SimpleAppLayout>
</template>

