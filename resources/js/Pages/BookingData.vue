<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import CardBookingData from '@/Components/CardBookingData.vue';

import Multiselect from 'vue-multiselect'

const props = defineProps({
    data: Object,
    cities: Object,
    types: Object,
});

const urlParams = new URLSearchParams(window.location.search);
const selectedCity = ref(urlParams.get("city"));
const selectedTitle = ref(urlParams.get("title"));
const selectedType = ref(urlParams.get("type"));
const query = ref('');


const addQuery = (name, value) => {
    if (value) {
        if (!query.value) {
            query.value = `?${name}=${value}`
        }
        else {
            query.value = query.value + `&${name}=${value}`
        }
    }
}

const applyQuery = () => {

    addQuery('city', selectedCity.value);
    addQuery('title', selectedTitle.value);
    addQuery('type', selectedType.value);
    // addQuery('minPrice', selectedPrice.value[0]);
    // addQuery('maxPrice', selectedPrice.value[1]);

    window.location.href = (`/booking_data${query.value}`)
}

const selected = ref([])

const updateValuePrimitive = (value) => {
    selected.value.push(value)
}

</script>

<template>
    <SimpleAppLayout title="Головна">

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row justify-between px-4 lg:px-0">
                <div class="flex flex-col lg:flex-row gap-y-3 items-stretch">
                    <!-- <Multiselect
                        :options="cities"
                        :selected="selected.value"
                        :multiple="false"
                        :searchable="false"
                        :close-on-select="false"
                        :show-labels="false"
                        @update="updateValuePrimitive"
                        placeholder="Select one"
                        label="name"
                    /> -->

                    <select v-model="selectedCity" @change="filterCity"
                        class="w-full lg:w-72 mr-0 lg:mr-2 border-0 text-gray-500 rounded-lg shadow focus:shadow-lg focus:outline-none focus:ring focus:border-blue-300 appearance-none leading-5 transition duration-150 ease-in-out">
                        <option :value="null" selected disabled hidden>City</option>
                        <option v-for="city in cities" :value="city">{{ city }}</option>
                    </select>

                    <select v-model="selectedType"
                        class="w-full lg:w-72 mr-0 lg:mr-2 border-0 text-gray-500 rounded-lg shadow focus:shadow-lg focus:outline-none focus:ring focus:border-blue-300 appearance-none leading-5 transition duration-150 ease-in-out">
                        <option :value="null" selected disabled hidden>Type</option>
                        <option v-for="type in types" :value="type">{{ type }}</option>
                    </select>

                    <div class="flex flex-col sm:flex-row gap-y-3 relative rounded-lg text-gray-600">
                        <div class="sm:w-full flex">
                            <input type="text" v-model="selectedTitle" placeholder="Title"
                                class="mr-0 lg:mr-2 border-0 shadow rounded-lg w-full lg:w-72 focus:outline-none focus:z-10 focus:ring focus:border-blue-300 block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                        </div>

                        <button
                            class="px-4 py-2 rounded-lg shadow hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out text-md"
                            @click="applyQuery">Apply
                        </button>
                    </div>
                </div>

                <Link href="/booking_data-map">
                <button
                    class="mt-4 lg:mt-0 px-4 py-2 rounded-lg shadow hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out text-md">View
                    on Map
                </button>
                </Link>
            </div>

            <ul class="flex-col">
                <li v-for="item in data.data" :key="item.id" class="my-8">
                    <CardBookingData :item="item" />
                </li>
            </ul>

            <Pagination class="mt-6" :links="data.links" />
        </div>
    </SimpleAppLayout>
</template>
