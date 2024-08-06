<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';


const props = defineProps({
    lists: Array,
    auth: Object
});

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
                        <button @click="showInput = !showInput" class="flex text-lg text-slate-800">New list</button>

                        <div v-if="showInput" class="absolute right-56 mb-4">
                            <input v-model="newListName" placeholder="New List Name" class="w-full p-2 border rounded-md mb-2" />
                            <button @click="createNewList" class="w-full p-2 bg-blue-500 text-white rounded-md">Create List</button>
                        </div>
                    </div>
                </div>

                

                
                <div class="flex gap-x-8 gap-y-4 mt-2 mb-4">
                    <div v-for="list in props.lists" :key="list.id">
                        <Link :href="'list/' + list.id">
                            <div class="block justify-between shadow rounded-xl p-4 bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out">
                                <div class="text-4xl font-extralight">{{ list.hotels.length }}</div>
                                <div class="text-lg">{{ list.name }}</div>
                            </div>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </SimpleAppLayout>
</template>
