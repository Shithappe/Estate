<script setup>
import { ref, defineProps } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';

const props = defineProps({
    priority: Object,
});

const priority = ref(props.priority);

const showAction = ref({
    id: null,
    show: false,
    priority: null
});

const actions = (id, priority) => {
    showAction.value.id = id;
    showAction.value.priority = priority;
    showAction.value.show = true;
}

const formData = ref({
    id: '',
    priority: '',
    msg: ''
});

const submitForm = async (msg, def = false) => {
    console.log(msg);
    console.log(formData.value);
    if (def) {
        formData.value.id = showAction.value.id;
        formData.value.priority = showAction.value.priority;
    }
    formData.value.msg = msg;
    try {
        const response = await axios.post('/api/setting_priority', formData.value);
        priority.value = response.data;
        console.log(response.data);

        formData.value.id = '';
        formData.value.priority = '';
        showAction.value.id = null;
        showAction.value.priority = null;
        showAction.value.show = false;
    } catch (error) {
        console.error('Error:', error);
    }
};

</script>

<template>
    <SimpleAppLayout title="Головна">

        <div  class="max-w-4xl mt-8 mx-auto sm:px-6 lg:px-8">

            <div class="flex gap-x-6 mb-2">
                <span class="text-2xl font-semibold">Priority</span>
            </div>

            <form class="flex gap-x-4 my-2" @submit.prevent="() => submitForm('edit')">
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
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in priority" :key="item.id" class="hover:bg-slate-200"
                        @click="() => { actions(item.id, item.priority) }">
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ item.id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ item.title }}</td>
                        <td class="w-48 relative border border-gray-300">
                            <div v-if="showAction.show && item.id == showAction.id">
                                <div class="absolute bottom-14 left-6 flex gap-x-2">
                                    <button @click="() => submitForm('edit', true)" class="px-2.5 py-1.5 shadow-lg bg-white border rounded-lg">Edit</button>
                                    <button @click="() => submitForm('delete', true)" class="px-2.5 py-1.5 shadow-lg bg-white border rounded-lg">Delete</button>
                                </div>
                                <input class="h-10" type="number" v-model="showAction.priority" placeholder="Priority">
                            </div>
                            <div class="px-4 py-2" v-else>{{ item.priority }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SimpleAppLayout>
</template>

