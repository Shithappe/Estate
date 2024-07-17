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
    <Modal maxWidth="xl" :show="show" @close="closeModal">
        <template v-slot>
            <div class="p-6 bg-white rounded-lg">
                <h2 class="text-lg font-semibold">{{ title }}</h2>
                <span>{{ des }}</span>
                <form @submit.prevent="submitForm" class="mt-4">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" v-model="formData.name" id="name" class="mt-1 block w-full" required />
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                        <input type="tel" v-model="formData.phone" id="phone" class="mt-1 block w-full" required />
                    </div>
                    <div class="mb-4">
                        <label for="messenger" class="block text-sm font-medium text-gray-700">Messenger for communication</label>
                        <Dropdown>
                            <template #trigger>
                                <button class="w-full text-left bg-white border border-gray-300 rounded-md shadow-sm px-4 py-2 text-gray-700">
                                    {{ formData.messenger || 'Choose a messenger' }}
                                </button>
                            </template>
                            <template #content>
                                <div class="py-1 bg-white rounded-md shadow-xs">
                                    <a
                                        v-for="messenger in messengers"
                                        :key="messenger"
                                        href="#"
                                        @click.prevent="selectMessenger(messenger)"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        {{ messenger }}
                                    </a>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" v-model="formData.email" id="email" class="mt-1 block w-full" required />
                    </div>
                    <button type="submit" class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Leave a request</button>
                </form>
            </div>
        </template>
    </Modal>
</template>