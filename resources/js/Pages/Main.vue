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
                    <select v-model="selectedCity" @change="filterCity" class="mx-2">
                        <option :value="null" selected disabled hidden>City</option>
                        <option v-for="city in cities" :value="city">{{ city }}</option>
                    </select>

                    <input type="number" v-model="selectedPrice[0]" placeholder="min">
                    <input type="number" v-model="selectedPrice[1]" placeholder="max">
                    <button class="ml-2" @click="applyQuery">Applay</button>
                </div>

                <ul class="flex-col gap-8">
                    <li v-for="book in books.data" :key="book.id" class="my-8">
                        <CardItem :item="book" />
                    </li>
                </ul>

                <Pagination class="mt-6" :links="books.links" />
            </div>
        </div>
    </SimpleAppLayout>
</template>
