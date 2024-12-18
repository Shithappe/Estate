<script setup>
import { ref, defineProps, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Modal from '@/Components/Modal.vue';
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
        width: 100,
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
    }
]);

const rowData = ref(props.priority);

const detailCellRendererParams = {
  detailGridOptions: {
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
  },
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
const show = ref(false);
const openModal = (item) => {
    show.value = true;
    modalItem.value = item;
    initializeImages(item);
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
</script>

<template>
    <SimpleAppLayout title="EstateMarket">
        <div class="max-w-6xl mt-8 mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-x-6 mb-2">
                <span class="text-2xl font-semibold">Priority & Forecast price</span>
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

        <Modal maxWidth="2xl" :show="show" @close="closeModal">
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
    </SimpleAppLayout>
</template>
