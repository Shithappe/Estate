<script setup>
import { ref, defineProps } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';

const props = defineProps({
    priority: Array,
});



const priority = ref(props.priority);

const selectItem = ref({
    id: null,
    show_priority: false,
    show_forecast_price: false,
    priority: null,
    forecast_price: null
});

const formData = ref({
    id: '',
    priority: '',
    msg: ''
});

const changePriority = (id, priority) => {
    selectItem.value.id = id;
    selectItem.value.priority = priority;
    selectItem.value.forecast_price = null;
    selectItem.value.show_priority = true;
    selectItem.value.show_forecast_price = false;
}

const changeForecastPrice = (id, forecast_price) => {
    selectItem.value.id = id;
    selectItem.value.priority = null;
    selectItem.value.forecast_price = forecast_price;
    selectItem.value.show_priority = false;
    selectItem.value.show_forecast_price = true;
}

const close = () => {
    selectItem.value = {
        id: null,
        show_priority: false,
        show_forecast_price: false,
        priority: null,
        forecast_price: null
    };
}

const submitForm = async () => {
    try {
        const response = await axios.post('/api/setting_priority', selectItem.value);
        priority.value = response.data;
        close();
    } catch (error) {
        console.error('Error:', error);
    }
};

</script>

<template>
    <SimpleAppLayout title="EstateMarket">

        <div  class="max-w-4xl mt-8 mx-auto sm:px-6 lg:px-8">

            <div class="flex gap-x-6 mb-2">
                <span class="text-2xl font-semibold">Priority & Forecast price</span>
            </div>

            <form class="flex gap-x-4 my-2" @submit.prevent="() => submitForm()">
                <input class="shadow rounded-lg" type="number" v-model="formData.id" placeholder="ID" required>
                <input class="shadow rounded-lg" type="number" v-model="formData.priority" placeholder="Priority" required>

                <button class="px-3 py-1 border shadow rounded-lg" type="submit">Add</button>
            </form>

            <table class="w-full border-collapse border border-gray-300 bg-white">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-300 px-4 py-2">ID</th>
                        <th class="border border-gray-300 px-4 py-2">Title</th>
                        <th class="border border-gray-300 px-4 py-2">Priority</th>
                        <th class="border border-gray-300 px-4 py-2 whitespace-nowrap">Forecast price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in priority" :key="item.id" class="hover:bg-slate-200">
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ item.id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ item.title }}</td>
                        <td class="w-48 relative border border-gray-300" @click="() => { changePriority(item.id, item.priority) }">
                            <div v-if="selectItem.show_priority && item.id === selectItem.id">
                                <div class="absolute bottom-1.5 left-32 flex gap-x-1">
                                    <button @click="submitForm" class="px-2.5 py-1 shadow-lg bg-white border rounded-lg">Save</button>
                                    <!-- <button @click="close" class="px-2.5 py-1 shadow-lg bg-white border rounded-lg">X</button> -->
                                </div>
                                <input class="h-10" type="number" v-model="selectItem.priority" placeholder="Priority">
                            </div>
                            <div class="px-4 py-2" v-else>{{ item.priority }}</div>
                        </td>
                        <td class="w-48 relative border border-gray-300" @click="changeForecastPrice(item.id, item.forecast_price)">
                            <div v-if="selectItem.show_forecast_price && item.id == selectItem.id">
                                <div class="absolute bottom-1.5 left-32 flex gap-x-1">
                                    <button @click="submitForm" class="px-2.5 py-1 shadow-lg bg-white border rounded-lg">Save</button>
                                    <!-- <button @click="close" class="px-2.5 py-1 shadow-lg bg-white border rounded-lg">X</button> -->
                                </div>
                                <input class="h-10" type="number" v-model="selectItem.forecast_price" placeholder="Forecast price">
                            </div>
                            <div class="px-4 py-2" v-else>{{ item.forecast_price }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SimpleAppLayout>
</template>

