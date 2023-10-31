<script setup>
import { ref } from 'vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import CardItem from '@/Components/CardItem.vue';

const props = defineProps({
    books: Object,
    cities: Object,
});

const urlParams = new URLSearchParams(window.location.search);
const selectedCity = ref(urlParams.get("city"));
const selectedPrice = ref([urlParams.get("minPrice"), urlParams.get("maxPrice")]);
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
    addQuery('minPrice', selectedPrice.value[0]);
    addQuery('maxPrice', selectedPrice.value[1]);

    window.location.href = (`/${query.value}`)
}

</script>

<template>
    <SimpleAppLayout title="Головна">

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex">
                    <select v-model="selectedCity" @change="filterCity"
                        class=" w-72 ml-2 p-2 border-0 text-gray-600 rounded-lg shadow focus:shadow-lg focus:outline-none focus:ring focus:border-blue-300 appearance-none leading-5 transition duration-150 ease-in-out">
                        <option :value="null" selected disabled hidden>City</option>
                        <option v-for="city in cities" :value="city" class="p-2">{{ city }}</option>
                    </select>

                    <div class="mx-2 flex relative rounded-lg text-gray-600">
                        <input type="number" v-model="selectedPrice[0]" placeholder="Min" min="0"
                            class="border-0 shadow rounded-l-lg py-2 px-4 pl-8 pr-8 focus:outline-none focus:z-10 focus:ring focus:border-blue-300 block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />

                        <input type="number" v-model="selectedPrice[1]" placeholder="Max" min="0"
                            class="border-0 shadow rounded-r-lg py-2 px-4 pl-8 pr-8 focus:outline-none focus:ring focus:border-blue-300 block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                    </div>


                    <button
                        class="ml-2 px-4 py-2 rounded-lg shadow hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out text-md"
                        @click="applyQuery">Applay</button>
                </div>

                <ul class="flex-col gap-8">
                    <li v-for="book in books.data" :key="book.id" class="my-8">
                        <CardItem :item="book" />
                    </li>
                </ul>

                <Pagination class="mt-6" :links="books.links" />
            </div>
        </div>
    </SimpleAppLayout></template>
