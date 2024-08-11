<script setup>
import { ref, defineProps, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Modal from '@/Components/Modal.vue';
import draggable from 'vuedraggable';
import { strToArray } from '@/Utils/strToArray.js';
import { checkImages } from '@/Utils/checkImages.js';

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

const updateImages = async (item) => {
    modalItem.value.images = await checkImages([...new Set([...strToArray(item.static_images, 500), ...strToArray(item.images, 500)])]);
};

const modalItem = ref(null);
const show = ref(false);
const openModal = (item) => {
    show.value = true;
    modalItem.value = item;
    updateImages(item);
    modalItem.value.images = [...new Set([...strToArray(item.static_images, 500), ...strToArray(item.images, 500)])];
}

const closeModal = () => { show.value = false; modalItem.value = null; }

const saveImages = async () => {
    try {
        const response = await axios.post("/api/change_images_order/", {
            id: modalItem.value.id,
            static_images: String(modalItem.value.images)
        });
        console.log(response.data);
    } catch (error) {
        console.error(error);
    }

    show.value = false;
    modalItem.value = null;
}

watch(() => selectItem.value.priority, (newVal) => {
    selectItem.value.show_priority = newVal !== null && newVal !== undefined;
});

watch(() => selectItem.value.forecast_price, (newVal) => {
    selectItem.value.show_forecast_price = newVal !== null && newVal !== undefined;
});

const submitForm = async () => {
    console.log(selectItem.value);
    try {
        const response = await axios.post('/api/update_booking', selectItem.value);
        priority.value = response.data;
        
        selectItem.value = {
            id: null,
            show_priority: false,
            show_forecast_price: false,
            priority: null,
            forecast_price: null
        };
    } catch (error) {
        console.error('Error:', error);
    }
};

const dragOptions = computed(() => {
    return {
        animation: 200,
        group: "photos",
        disabled: false,
        ghostClass: "ghost"
      };
});

const drag = ref(false);
const dragItem = ref(null);

const handleDragStart = (evt) => {
    dragItem.value = evt.item;
};

const handleDragEnd = (evt) => {
    drag.value = false;
    // Проверка, если элемент был перетаскиваем в зону удаления
    if (dragItem.value) {
        const isOverDeleteArea = evt.from === evt.from.parentElement;
        if (isOverDeleteArea) {
            removePhoto();
        }
        dragItem.value = null;
    }
};

const removePhoto = () => {
    if (dragItem.value) {
        const src = dragItem.value.querySelector('img').src;
        modalItem.value.images = modalItem.value.images.filter(
            (item) => item !== src
        );
        dragItem.value = null;
    }
};

</script>

<template>
    <SimpleAppLayout title="EstateMarket">

        <div  class="max-w-6xl mt-8 mx-auto sm:px-6 lg:px-8">

            <div class="flex gap-x-6 mb-2">
                <span class="text-2xl font-semibold">Priority & Forecast price</span>
            </div>

            <form class="flex gap-x-4 my-2" @submit.prevent="() => submitForm()">
                <input class="shadow rounded-lg" type="number" v-model="selectItem.id" placeholder="ID" required>
                <input class="shadow rounded-lg" type="number" v-model="selectItem.priority" placeholder="Priority">
                <input class="shadow rounded-lg" type="number" v-model="selectItem.forecast_price" placeholder="Forecast price">

                <button class="px-3 py-1 border shadow rounded-lg" type="submit">Add</button>
            </form>

            <table class="w-full border-collapse border border-gray-300 bg-white">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-300 px-4 py-2">ID</th>
                        <th class="border border-gray-300 px-4 py-2">Title</th>
                        <th class="border border-gray-300 px-4 py-2">Priority</th>
                        <th class="border border-gray-300 px-4 py-2 whitespace-nowrap">Forecast price</th>
                        <th class="border border-gray-300 px-4 py-2">Static images</th>
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
                                </div>
                                <input class="h-10" type="number" v-model="selectItem.priority" placeholder="Priority">
                            </div>
                            <div class="px-4 py-2" v-else>{{ item.priority }}</div>
                        </td>
                        <td class="w-48 relative border border-gray-300" @click="changeForecastPrice(item.id, item.forecast_price)">
                            <div v-if="selectItem.show_forecast_price && item.id == selectItem.id">
                                <div class="absolute bottom-1.5 left-32 flex gap-x-1">
                                    <button @click="submitForm" class="px-2.5 py-1 shadow-lg bg-white border rounded-lg">Save</button>
                                </div>
                                <input class="h-10" type="number" v-model="selectItem.forecast_price" placeholder="Forecast price">
                            </div>
                            <div class="px-4 py-2" v-else>{{ item.forecast_price }}</div>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center hover:cursor-pointer hover:bg-slate-300 hover:shadow-lg"
                            @click="openModal(item)"
                        >
                            view
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal maxWidth="2xl" :show="show" @close="closeModal">
            <template v-slot>
                <div class="row">

                    <div class="col-6">
                    <draggable
                        class="list-group flex flex-row gap-x-1 overflow-x-auto whitespace-nowrap"
                        v-model="modalItem.images"
                        tag="div"
                        v-bind="dragOptions"
                        @start="handleDragStart"
                        @end="handleDragEnd"
                        item-key="index"
                    >
                        <template #item="{ element, index }">
                        <div class="list-group-item flex-shrink-0" :key="index">
                            <img class="w-32 h-32 object-cover rounded-lg" :src="element" alt="">
                        </div>
                        </template>
                    </draggable>
                    
                    <div 
                        class="delete-area mt-4 p-4 bg-red-200 border border-2 border-red-600 border-dashed rounded-lg text-center"
                        @drop.prevent="removePhoto"
                        @dragover.prevent
                    >
                        Drag here to delete
                    </div>
                    </div>

                    <div class="flex gap-x-2 my-4 float-end">
                        <button class="w-24 flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg"
                                @click="saveImages">
                            Save
                        </button>
                        <button class="w-24 flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg"
                                @click="closeModal">
                            Cancel
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </SimpleAppLayout>
</template>
