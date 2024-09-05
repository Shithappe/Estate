<script setup>
import { computed, watch, onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    lists: {
        type: Array,
        required: true,
    },
    type: {
        type: String,
        required: true,
    },
    itemId: {
        type: [String, Number],
        required: true
    },
    auth: {
        type: Object,
        required: true,
    },
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: Array,
        default: () => ['py-1', 'bg-white'],
    },
});

let open = ref(false);
let showInput = ref(false);
let newList = ref({
    name: '',
    type: 'complex', // Значение по умолчанию
});

watch(open, (newValue) => {
    if (!newValue) {
        showInput.value = false; // Сбрасываем showInput на false при закрытии дропдауна
    }
});

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => {
    console.log(props.lists);
    document.addEventListener('keydown', closeOnEscape)
});
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        '48': 'w-48',
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'origin-top-left left-0';
    }

    if (props.align === 'right') {
        return 'origin-top-right right-0';
    }

    return 'origin-top';
});

// Функция для создания нового списка
const createNewList = async () => {
    try {
        const response = await axios.post("/api/create_list", {
            user_id: props.auth.user.id,
            name: newList.value.name,
            type: props.type 
        });
        showInput.value = false;
        // Добавляем новый список в массив списков
        props.lists.push(response.data.list);
        newList.value = { name: '', type: 'complex' }; // Очистка полей
    } catch (error) {
        console.error('Error creating list:', error);
    }
};
const addToList = async (list_id) => {
    try {
        const response = await axios.post("/api/add_to_list/", {
            list_id, 
            booking_id: props.itemId
        });
        console.log(response);
        open.value = false;
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <div class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div v-show="open" class="fixed inset-0 z-40" @click="open = false" />

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="[widthClass, alignmentClasses]"
                style="display: none;"
                @click.stop="open = true"
            >
                <div class="rounded-md ring-1 ring-black ring-opacity-5" :class="contentClasses">
                    <ul>
                        <li v-for="list in props.lists" :key="list.id" @click="addToList(list.id)" class="px-4 py-2 text-black hover:bg-gray-100 cursor-pointer">
                            {{ list.name }}
                        </li>
                        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                            <button v-if="!showInput" @click="showInput = true" class="text-blue-500">
                                + New List
                            </button>

                            <div v-if="showInput" class="" @click.stop>
                                <input v-model="newList.name" placeholder="New List Name" class="w-full p-2 border rounded-md mb-2" />
                                <button @click="createNewList" class="w-full p-2 bg-blue-500 text-white rounded-md">
                                    Create List
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </transition>
    </div>
</template>
