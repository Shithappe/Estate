<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import Lucide from '@/Components/Lucide.vue';
import Modal from '@/Components/Modal.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';


const props = defineProps({
    lists: Array,
    auth: Object
});

const lists = ref([...props.lists]);

const showInput = ref(false);
const newListName = ref('');

const createNewList = async () => {
    try {
        await axios.post("/api/create_list", {
            user_id: props.auth.user.id,
            name: newListName.value
        });
        location.reload(); // Перезагрузка страницы при успешном создании списка
    } catch (error) {
        console.error('Error creating list:', error);
    }
};

const editableList  = ref(null);
const showSetting = ref(false);
const openSetting = (list) => {
    console.log('openSetting');
    editableList.value = list;
    showSetting.value = true;
}
const closeModal = () => {
    showSetting.value = false;
    editableList.value = null;
}

const deleteList = async (list) => {
    try {
        const response = await axios.delete(`/api/list/${list.id}`);
        lists.value = lists.value.filter(l => l.id !== list.id);
    } catch (error) {
        console.error(error);
    }
    closeModal();
}

const updateList = async (list) => {
    console.log(list.name);
    
    try {
        const response = await axios.patch(`/api/list/${list.id}`, {
            user_id: props.auth.user.id,
            name: list.name
        });
        console.log(response.data);
    } catch (error) {
        console.error(error);
    }
    closeModal();
}

onMounted(() => {
    console.log(props.lists);

});
</script>

<template>
    <SimpleAppLayout title="List">

        <div class="mx-2 py-2 lg:py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <div class="flex justify-between items-center gap-x-2 mb-4">
                    <span class="text-2xl font-medium">My Lists</span>
                    <div>
                        <button @click="showInput = !showInput" class="flex text-lg text-slate-800">{{ showInput ? '&#x2715;' : 'New list' }}</button>

                        <div v-if="showInput" class="absolute right-56 mb-4 p-2 z-10 bg-slate-200 rounded-md shadow-lg">
                            <input v-model="newListName" placeholder="New List Name" class="w-full p-2 border rounded-md mb-2" />
                            <button @click="createNewList" class="w-full p-2 bg-blue-500 text-white rounded-md">Create List</button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-x-6 gap-y-4 mt-2 mb-4">
                    <div v-for="list in lists" :key="list.id">
                        <Link :href="'list/' + list.id">
                            <div class="relative min-w-64 block justify-between shadow rounded-xl p-4 bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out">
                                <Lucide class="w-5 h-5 absolute top-2 right-2 opacity-70 hover:opacity-100 transition duration-100 ease-in-out" icon="Settings" @click.prevent.stop="openSetting(list)" />
                                <div class="text-4xl font-extralight">{{ list.hotels.length }}</div>
                                <div class="text-lg">{{ list.name }}</div>
                            </div>
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <Modal maxWidth="sm" :show="showSetting" @close="closeModal">
        <template v-slot>
            <div class="p-6 bg-white rounded-lg">
                <h2 class="text-lg font-semibold mb-4">Setting List</h2>

                <div class="mb-4">
                        <label for="name" class="block mb-1 text-sm font-medium text-gray-900">Rename</label>
                        <input type="text" v-model="editableList.name" id="name"
                            class="bg-slate-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        />
                    </div>

                <div class="flex justify-between items-center mt-6">
                    <button @click="deleteList(editableList)" class="w-12 flex justify-center gap-1 px-8 py-2 text-md font-medium hover:text-slate-100 hover:bg-red-600 rounded-lg transition duration-300 ease-in-out">Delete</button>
                    <button @click="updateList(editableList)" class="w-12 flex justify-center gap-1 px-8 py-2 text-md font-medium hover:text-slate-100 hover:bg-slate-900 rounded-lg transition duration-300 ease-in-out">Save</button>
                </div>
                
            </div>
        </template>
    </Modal>
    
    </SimpleAppLayout>
</template>
