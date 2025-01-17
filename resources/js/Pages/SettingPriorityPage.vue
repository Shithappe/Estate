<script setup>
import { ref, defineProps, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import Lucide from '@/Components/Lucide.vue';
import Modal from '@/Components/Modal.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import draggable from 'vuedraggable';
import { strToArray } from '@/Utils/strToArray.js';
import { checkImages } from '@/Utils/checkImages.js';
import { AgGridVue } from 'ag-grid-vue3';
import { AllCommunityModule, ModuleRegistry } from 'ag-grid-community';
import { MasterDetailModule } from 'ag-grid-enterprise'; 

// Явная регистрация модулей
ModuleRegistry.registerModules([AllCommunityModule, MasterDetailModule]);

const props = defineProps({
    priority: {
        type: Array,
        default: () => []
    },
});

const columnDefs = ref([
    {
        field: 'id',
        headerName: 'ID',
        width: 120,
        sortable: true,
        filter: true,
        cellRenderer: "agGroupCellRenderer"
    },
    {
        field: 'title',
        headerName: 'Title',
        flex: 1,
        filter: true
    },
    {
        field: 'priority',
        headerName: 'Priority',
        editable: true,
        width: 150,
        cellRenderer: (params) => {
            return params.value ?? '-';
        },
        cellEditor: 'agNumberCellEditor',
        sortable: true,
        filter: true
    },
    {
        field: 'forecast_price',
        headerName: 'Forecast Price',
        editable: true,
        width: 150,
        cellRenderer: (params) => {
            return params.value ?? '-';
        },
        cellEditor: 'agNumberCellEditor',
        sortable: true,
        filter: true
    },
    {
        headerName: 'Actions',
        width: 100,
        // cellRendererFramework: {
        //     template: `<Lucide icon="ColorPicker" class="w-5 h-5" />`,
        // },
        cellRenderer: (params) => {
            return `<button class="px-2 py-1 bg-slate-200 rounded">View</button>`;
            // return `<button class="m-2 p-1 border rounded"></button>`;
            // return `<Lucide class="w-5 h-5" icon="Images" />`;
        },
        cellStyle: { textAlign: 'center' },
        sortable: false,
        filter: false
    }
]);

const rowData = ref(props.priority);

const detailGridOptions = {
    columnDefs: [
        { field: "room_id", flex: 0.2 },
        { field: "room_type", flex: 1 },
        { 
            field: "estimated_price", 
            flex: 0.5, 
            editable: true,
            cellEditor: 'agNumberCellEditor', 
            cellRenderer: (params) => {
                return params.value ?? '-';
            },
        }
    ],
    headerHeight: 38,
    // Add this to ensure cell editing events are captured
    stopEditingWhenCellsLoseFocus: true,
    onCellEditingStopped: (params) => {
        // Log to verify the event is being triggered
        console.log('Cell editing stopped:', params);

        // Check if the edited column is estimated_price
        if (params.column.getColId() === 'estimated_price') {
            selectRoom.value = {
                room_id: params.data.room_id,
                estimated_price: params.data.estimated_price
            };
            
            // Call updateRoom
            updateRoom();
        }
    }
};

const detailCellRendererParams = {
    detailGridOptions: detailGridOptions,
    getDetailRowData: ({
        successCallback,
        data: { rooms },
    }) => successCallback(rooms),
};

const selectBooking = ref({
    id: null,
    priority: null,
    forecast_price: null
});

const selectRoom = ref({
    room_id: null,
    estimated_price: null
});

const onCellClicked = (event) => {
    if (event.column.getColId() === 'static_images') {
        openModal(event.data);
    }
};

const onCellEditingStopped = async (event) => {
    console.log(event.column.getColId());
    if (['priority', 'forecast_price'].includes(event.column.getColId())) {
        selectBooking.value = {
            id: event.data.id,
            priority: event.data.priority,
            forecast_price: event.data.forecast_price
        };
        await updateBooking();
    }
    if (event.column.getColId() === 'estimated_price') {
            selectRoom.value = {
                room_id: event.data.room_id,
                estimated_price: event.data.estimated_price
            };
            await updateRoom();
        }
};

// onMounted(() => {
    // console.log(props.priority);
// })


// Остальные методы остаются без изменений
const initializeImages = async (item) => {
    modalItem.value.images = await checkImages([...new Set([...strToArray(item.static_images, 500), ...strToArray(item.images, 500)])]);
};

const modalItem = ref(null);
const showImageReplace = ref(false);
const showAddBooking = ref(false);
const newBooking = ref('');
const isLoading = ref(false);
const bookingLink = ref(null);

const openModal = (item) => {
    showImageReplace.value = true;
    modalItem.value = item;
    initializeImages(item);
    modalItem.value.images = [...new Set([...strToArray(item.static_images, 500), ...strToArray(item.images, 500)])];
}

const closeModal = () => { 
    showAddBooking.value = false; newBooking.value = null; 
    showImageReplace.value = false; modalItem.value = null; 
    bookingLink.value = null;
    isLoading.value = false;
}

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

    closeModal();
}

const addingBooking = async () => {
    try {
        isLoading.value = true;
        const response = await axios.post('/api/add_booking', {
            link: newBooking.value
        });
        bookingLink.value = response.data.link;
    } catch (error) {
        console.error('Error:', error);
    } finally {
        isLoading.value = false;
    }
};

const updateBooking = async () => {
    try {
        const response = await axios.post('/api/update_booking', selectBooking.value);
        rowData.value = response.data;

        selectBooking.value = {
            id: null,
            priority: null,
            forecast_price: null
        };
    } catch (error) {
        console.error('Error:', error);
    }
};

const updateRoom = async () => {
    try {
        const response = await axios.post('/api/set_estimated_price_for_room', selectRoom.value);
        rowData.value = response.data;
        
        selectRoom.value = {
            room_id: null,
            estimated_price: null
        };
    } catch (error) {
        console.error('Error updating room:', error);
    }
};

// Методы для перетаскивания изображений остаются без изменений
const dragOptions = computed(() => ({
    animation: 200,
    group: "photos",
    disabled: false,
    ghostClass: "ghost"
}));

const drag = ref(false);
const dragItem = ref(null);

const handleDragStart = (evt) => {
    dragItem.value = evt.item;
};

const handleDragEnd = (evt) => {
    drag.value = false;
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

const findBooking = ref(null);
const findingBooking = async () => {
    try {
        const response = await axios.post(`/api/find_booking/`, {
            value: findBooking.value
        });
        rowData.value = response.data;
    } catch (error) {
        console.error('Error:', error);
    }
};
</script>

<template>
    <SimpleAppLayout title="EstateMarket">
        <div class="max-w-6xl mt-8 mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-x-6 mb-2">
                <span class="text-2xl font-semibold">Priority & Forecast price</span>
            </div>
            <div class="w-full flex gap-x-2 justify-between mb-2">
                <div class="flex gap-x-2 flex-grow">
                    <input type="text" class="w-full p-2 border border-gray-300 rounded-lg" v-model="findBooking" placeholder="Find by ID or Title..." />
                    <button class="px-4 py-2 border border-gray-300 rounded-lg" @click="findingBooking">Find</button>
                </div>
                <button class="px-4 py-2 border border-gray-300 rounded-lg" @click="showAddBooking = true">Add Booking</button>
            </div>
                <AgGridVue
                    style="height: 500px"
                    class="ag-theme-alpine"
                    :columnDefs="columnDefs"
                    :rowData="rowData"
                    :defaultColDef="{
                        resizable: true,
                        sortable: true,
                        filter: true
                    }"
                    :masterDetail="true"
                    :detailRowAutoHeight="true"
                    :detailCellRendererParams="detailCellRendererParams"
                    @cell-clicked="onCellClicked"
                    @cell-editing-stopped="onCellEditingStopped"
                />
        </div>

        <Modal maxWidth="2xl" :show="showImageReplace" @close="closeModal">
            <template v-slot>
                <div class="row">
                    <div class="col-6">
                        <draggable
                            class="list-group flex flex-row gap-x-1 overflow-x-auto whitespaceale-nowrap"
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

        <Modal maxWidth="sm" :show="showAddBooking" @close="closeModal">
            <template #default>
                <div class="p-6 bg-white rounded-lg relative">
                    <div class="absolute -top-2 -right-2 bg-gray-100 rounded-lg shadow cursor-pointer" @click="closeModal">
                        <Lucide class="text-gray-700 w-7 h-7" icon="X" />
                    </div>
                    <h2 class="text-lg font-semibold">Add Booking</h2>
                    <div class="mb-2">
                        <input type="text" v-model="newBooking"
                            class="bg-slate-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="Enter a link for new item" />
                        
                        <div v-if="!bookingLink" class="mt-2">
                            <button @click="addingBooking"
                                :disabled="isLoading"
                                class="w-full flex justify-center gap-1 p-2 text-md font-medium text-slate-100 bg-green-500 rounded-lg disabled:bg-green-300">
                                <span v-if="!isLoading">Add</span>
                                <span v-else>Processing...</span>
                            </button>
                        </div>
                        
                        <div v-else class="mt-2">
                            <a :href="bookingLink" target="_blank" 
                                class="block w-full text-center p-2 text-md font-medium text-blue-500 bg-blue-50 rounded-lg hover:bg-blue-100">
                                Open Booking Link
                            </a>
                        </div>

                        <p class="mt-3 text-sm text-slate-500">A few minutes after submitting, the addition will be complete</p>
                    </div>
                </div>
            </template>
        </Modal>
    </SimpleAppLayout>
</template>
