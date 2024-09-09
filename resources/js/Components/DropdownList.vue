<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    title: String,
    description: String,
    listItems: Array,
    selectedItem: String,
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit']);

const selectedListItem = ref(props.selectedItem || '');
const newItem = ref('');

const resetForm = () => {
    selectedListItem.value = '';
    newItem.value = '';
};

const closeModal = () => {
    emit('close');
    resetForm();
};

const submitForm = async () => {
    if (newItem.value) {
        emit('submit', newItem.value);
    } else {
        emit('submit', selectedListItem.value);
    }
    closeModal();
};

const selectItem = (item) => {
    selectedListItem.value = item;
};
</script>

<template>
    <Modal maxWidth="sm" :show="show" @close="closeModal">
        <template v-slot>
            <div class="p-6 bg-white rounded-lg">
                <h2 class="text-lg font-semibold">{{ title }}</h2>
                <p>{{ description }}</p>
                <form @submit.prevent="submitForm" class="mt-4">
                    <div class="mb-4">
                        <label for="new-item" class="block mb-1 text-sm font-medium text-gray-900">Add New Item</label>
                        <input type="text" v-model="newItem" id="new-item"
                            class="bg-slate-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="Enter new item" />
                    </div>

                    <div class="mb-4">
                        <label for="existing-items" class="block mb-1 text-sm font-medium text-gray-900">Select from Existing Items</label>
                        <Dropdown>
                            <template #trigger>
                                <button
                                    type="button"
                                    class="w-full text-left bg-white border border-gray-300 rounded-md shadow-sm px-4 py-2 text-gray-700">
                                    {{ selectedListItem || 'Choose an item' }}
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1 bg-white rounded-md shadow-xs">
                                    <a v-for="item in listItems" :key="item"
                                        @click.prevent="selectItem(item)"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ item }}
                                    </a>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">
                        Submit
                    </button>
                </form>
            </div>
        </template>
    </Modal>
</template>
