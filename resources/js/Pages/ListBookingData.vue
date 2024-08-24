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

const privacyMode = ref();

const showInput = ref(false);
const newListName = ref('');

const createNewList = async () => {
    try {
        const response = await axios.post("/api/create_list", {
            user_id: props.auth.user.id,
            name: newListName.value
        });
        showInput.value = false;
        lists.value.push(response.data.list);
    } catch (error) {
        console.error('Error creating list:', error);
    }
};

const editableList  = ref(null);
const showSetting = ref(false);
const openSetting = (list) => {
    console.log('openSetting', list);
    editableList.value = list;
    privacyMode.value = list.privacy_mode
    showSetting.value = true;
}
const closeModal = () => {
    showSetting.value = false;
    editableList.value = null;
    privacyMode.value = null;
}

const deleteList = async (list) => {
    try {
        await axios.delete(`/api/list/${list.id}`);
        lists.value = lists.value.filter(l => l.id !== list.id);
    } catch (error) {
        console.error(error);
    }
    closeModal();
}

const updateList = async (list) => {
    try {
        await axios.patch(`/api/list/${list.id}`, {
            user_id: props.auth.user.id,
            name: list.name,
            privacy_mode: privacyMode.value
        });
        list.privacy_mode = privacyMode.value;
    } catch (error) {
        console.error(error);
    }
    closeModal();
}

const setAccessLink = async (item) => {
    if (privacyMode.value === 'link') {
        try {
            await axios.patch(`/api/list/${item.id}`, {
                user_id: props.auth.user.id,
                privacy_mode: privacyMode.value
            });
        } catch (error) {
            console.error(error);
        }
    }
}

const copied = ref(false);
const copyLink = (click = false) => {
    const shareLink = `${window.location.origin}/list/share/${editableList.value.share_token}`;
    if (click) {
        navigator.clipboard.writeText(shareLink);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 1000);
    }
    return shareLink;
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

                <div class="flex gap-x-5 gap-y-4 mt-2 mb-4 flex-wrap">
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
                    <label for="rename" class="block mb-1 font-medium text-gray-900">Rename</label>
                    <input type="text" v-model="editableList.name" id="rename"
                        class="bg-slate-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    />
                </div>

                <div class="mb-4">
                    <label for="asses">Access</label>
                    <div id="asses" class="flex flex-col space-y-2">
                        <!-- Группа 1 -->
                        <label for="helper-radio-1" class="flex items-center cursor-pointer">
                            <input 
                                id="helper-radio-1" name="helper-radio"
                                type="radio" 
                                value="private"
                                v-model="privacyMode"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500" />
                            <div class="ms-2">
                                <span class="text-sm font-medium text-gray-900">List available only to you</span>
                                <p id="helper-radio-text-1" class="text-xs font-normal text-gray-500">No one else can view this list</p>
                            </div>
                        </label>
                        <!-- Группа 2 -->
                        <label for="helper-radio-2" class="flex items-center cursor-pointer">
                            <input 
                                id="helper-radio-2" name="helper-radio" 
                                type="radio"  
                                value="link"
                                v-model="privacyMode"
                                @change="setAccessLink(editableList)"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500" />
                            <div class="ms-2">
                                <span class="text-sm font-medium text-gray-900">List available to everyone via a link</span>
                                <p id="helper-radio-text-2" class="text-xs font-normal text-gray-500">Anyone with the link can view this list</p>
                            </div>
                        </label>
                        <!-- Группа 3 -->
                        <label for="helper-radio-3" class="flex items-center cursor-not-allowed text-gray-400">
                            <input 
                                id="helper-radio-3" 
                                name="helper-radio" 
                                type="radio" 
                                value="specific_users"
                                v-model="privacyMode"
                                disabled
                                class="w-4 h-4 text-gray-400 bg-gray-100 border-gray-300 opacity-50 cursor-not-allowed" />
                            <div class="ms-2">
                                <span class="text-sm font-medium">List available to specific users</span>
                                <p id="helper-radio-text-3" class="text-xs font-normal">Only selected users can view this list</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="w-full">
                    <div v-if="privacyMode === 'link'" class="relative">
                        <label for="npm-install-copy-text" class="sr-only">Label</label>
                        <input id="npm-install-copy-text" type="text" class="col-span-6 bg-gray-50 border border-gray-300 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-2.5 py-4" :value="copyLink()" disabled readonly>
                        <button @click="copyLink(true)" data-copy-to-clipboard-target="npm-install-copy-text" class="absolute end-2.5 top-1/2 -translate-y-1/2 text-gray-900 rounded-lg py-2 px-2.5 inline-flex items-center justify-center bg-white border-gray-200 border">
                            <span v-if="!copied" id="default-message" class="inline-flex items-center">
                                <svg class="w-3 h-3 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                                    <path d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                                </svg>
                                <span class="text-xs font-semibold">Copy</span>
                            </span>
                            <span v-else id="success-message" class="inline-flex items-center">
                                <svg class="w-3 h-3 text-blue-700" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                </svg>
                                <span class="text-xs font-semibold text-blue-700">Copied</span>   
                            </span>
                        </button>
                    </div>
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
