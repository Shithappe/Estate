<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    title: String,
    des: String,
    booking_id: Number,
    target: String,
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const formData = ref({
    name: '',
    phone: '',
    messenger: '',
    email: '',
});

const messengers = ['WhatsApp', 'Telegram', 'Viber'];
const selectMessenger = (messenger) => {
    formData.value.messenger = messenger;
};

const resetForm = () => {
    formData.value = {
        name: '',
        phone: '',
        messenger: '',
        email: '',
    };
};

const closeModal = () => {
    emit('close');
    resetForm();
};

const submitForm = async () => {
    try {
        const response = await axios.post("/api/form_submissions", {
            'booking_id': props.booking_id || null,
            'target': props.target,
            'name': formData.value.name,
            'phone_number': formData.value.phone,
            'messenger': formData.value.messenger,
            'email': formData.value.email
        });
        console.log(response.data);
    } catch (error) {
        console.error(error);
    }
    closeModal();
};

</script>

<template>
    <Modal maxWidth="sm" :show="show" @close="closeModal">
        <template v-slot>
            <div class="p-6 bg-white rounded-lg">
                <h2 class="text-lg font-semibold">{{ title }}</h2>
                <span>{{ des }}</span>
                <form @submit.prevent="submitForm" class="mt-4">
                    <div class="mb-4">
                        <label for="name" class="block mb-1 text-sm font-medium text-gray-900">Name</label>
                        <input type="text" v-model="formData.name" id="name"
                            class="bg-slate-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="John" required />
                    </div>
                    <!-- <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                        <input type="tel" v-model="formData.phone" id="phone" class="mt-1 block w-full" required />
                    </div> -->

                    <div class="mb-4">
                        <label for="phone-input" class="block mb-1 text-sm font-medium text-gray-900">Phone
                            number:</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 top-0 flex items-center ps-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 19 18">
                                    <path
                                        d="M18 13.446a3.02 3.02 0 0 0-.946-1.985l-1.4-1.4a3.054 3.054 0 0 0-4.218 0l-.7.7a.983.983 0 0 1-1.39 0l-2.1-2.1a.983.983 0 0 1 0-1.389l.7-.7a2.98 2.98 0 0 0 0-4.217l-1.4-1.4a2.824 2.824 0 0 0-4.218 0c-3.619 3.619-3 8.229 1.752 12.979C6.785 16.639 9.45 18 11.912 18a7.175 7.175 0 0 0 5.139-2.325A2.9 2.9 0 0 0 18 13.446Z" />
                                </svg>
                            </div>
                            <input type="text" id="phone-input" aria-describedby="helper-text-explanation"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5"
                                v-model="formData.phone" placeholder="123-456-7890"
                                required />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="messenger" class="block mb-1 text-sm font-medium text-gray-700">Messenger for communication</label>
                        <Dropdown>
                            <template #trigger>
                                <button
                                    type="button"
                                    class="w-full text-left bg-white border border-gray-300 rounded-md shadow-sm px-4 py-2 text-gray-700">
                                    {{ formData.messenger || 'Choose a messenger' }}
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1 bg-white rounded-md shadow-xs">
                                    <a v-for="messenger in messengers" :key="messenger"
                                        @click.prevent="selectMessenger(messenger)"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ messenger }}
                                    </a>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                    <!-- <ul class="grid w-full gap-6 md:grid-cols-2">
                        <li>
                            <input type="radio" id="hosting-small" name="hosting" value="hosting-small" class="hidden peer" required />
                            <label for="hosting-small" class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">                           
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">0-50 MB</div>
                                </div>
                                <svg class="w-5 h-5 ms-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                </svg>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="hosting-big" name="hosting" value="hosting-big" class="hidden peer">
                            <label for="hosting-big" class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
                                <div class="block">
                                    <div class="w-full text-lg font-semibold">500-1000 MB</div>
                                </div>
                                <svg class="w-5 h-5 ms-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                </svg>
                            </label>
                        </li>
                    </ul> -->
                    <!-- <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" v-model="formData.email" id="email" class="mt-1 block w-full" required />
                    </div> -->
                    <div class="mb-6">
                        <label for="email" class="block mb-1 text-sm font-medium text-gray-900">Email address</label>
                        <input type="email" id="email"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            v-model="formData.email" placeholder="john.doe@company.com" required />
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Leave
                        a
                        request</button>
                </form>
            </div>
        </template>
    </Modal>
</template>