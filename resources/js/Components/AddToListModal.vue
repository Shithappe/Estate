<script setup>
import { ref } from 'vue';
import Lucide from '@/Components/Lucide.vue';
import Modal from '@/Components/Modal.vue';
import Dropdown from '@/Components/Dropdown.vue';
import axios from 'axios';

const props = defineProps({
    lists: {
        type: Array,
        required: true,
    },
    itemId: {
        type: [String, Number, Array],
        required: true,
    },
    type: String,
    show: {
        type: Boolean,
        default: false,
    },
    auth: {
        type: Object,
        required: true,
    },
});

const lists = ref([...props.lists]);

const emit = defineEmits(['close', 'updateLists']);

const selectedListItem = ref('');
const newItem = ref('');

const showInput = ref(false);

const selectItem = (item) => {
    selectedListItem.value = item;
};

const resetForm = () => {
    selectedListItem.value = '';
    newItem.value = '';
    showInput.value = false;
};

const closeModal = () => {
    emit('close');
    resetForm();
};

const submitForm = async () => {
    if (newItem.value.trim() !== '') {
        // Создание нового списка
        try {
            const response = await axios.post("/api/create_list", {
                user_id: props.auth.user.id,
                name: newItem.value,
                type: props.type,
                item_id: props.itemId,
            });
            // Обновление списка в родительском компоненте
            emit('updateLists', response.data.list);
            lists.value.push(response.data.list);
            newItem.value = '';
            showInput.value = false;
            closeModal();
        } catch (error) {
            console.error(error);
        }
    } else if (selectedListItem.value) {
        // Добавление элемента в существующий список
        try {
            const response = await axios.post("/api/add_to_list/", {
                list_id: selectedListItem.value.id,
                booking_id: props.itemId,
            });
            closeModal();
        } catch (error) {
            if (error.response.status == 400) closeModal();
            console.error('already in list', error);
        }
    }
};
</script>

<template>
    <Modal maxWidth="sm" :show="show" @close="closeModal">
        <template #default>
            <div class="p-6 bg-white rounded-lg relative">
                <div class="absolute -top-2 -right-2 bg-gray-100 rounded-lg shadow cursor-pointer" @click="closeModal">
                    <Lucide class="text-gray-700 w-7 h-7" icon="X" />
                </div>
                <h2 class="text-lg font-semibold">Add to list</h2>
                <form @submit.prevent="submitForm" class="mt-4">
                    <!-- Добавление нового списка -->
                    <div class="mb-2">
                        <label for="new-item" class="block mb-1 text-sm font-medium text-gray-900">New list</label>
                        <input type="text" v-model="newItem" id="new-item"
                            class="bg-slate-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="Enter a name for the new list" />
                        <button type="submit"
                            class="mt-2 w-full flex justify-center gap-1 p-2 text-md font-medium text-slate-100 bg-blue-500 rounded-lg">
                            Create & Add
                        </button>
                    </div>

                    <div class="flex items-center mb-2">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-4 text-gray-500">or</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>

                    <div class="mb-4">
                        <label for="existing-items" class="block mb-1 text-sm font-medium text-gray-900">Select an existing list</label>
                        <Dropdown>
                            <template #trigger>
                                <button
                                    type="button"
                                    class="w-full text-left bg-white border border-gray-300 rounded-md shadow-sm px-4 py-2 text-gray-700">
                                    {{ selectedListItem?.name || 'Select a list' }}
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1 bg-white rounded-md shadow-xs">
                                    <a v-for="list in lists" :key="list.id"
                                        @click.prevent="selectItem(list)"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ list.name }}
                                    </a>
                                </div>
                            </template>
                        </Dropdown>
                        <button type="submit"
                            class="mt-2 w-full flex justify-center gap-1 p-2 text-md font-medium text-slate-100 bg-green-500 rounded-lg">
                            Add to selected list
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </Modal>
</template>
