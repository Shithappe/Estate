<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
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
    lists: {
        type: Array,
        default: () => [],
    }
});

let open = ref(false);
let newListName = ref('');

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
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

const addHotelToList = (listId) => {
    // Логика добавления отеля в список
    console.log(`Adding hotel to list: ${listId}`);
};

const createNewList = () => {
    // Логика создания нового списка
    if (newListName.value.trim()) {
        console.log(`Creating new list: ${newListName.value}`);
        newListName.value = '';
    }
};
</script>

<template>
    <div class="relative">
        <div @click="open = ! open">
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
                @click="open = false"
            >
                <div class="rounded-md ring-1 ring-black ring-opacity-5" :class="contentClasses" @click.stop>
                    <input v-model="newListName" placeholder="New List Name" class="w-full p-2 mb-2 border rounded-md" />
                    <button @click="createNewList" class="w-full p-2 bg-blue-500 text-white rounded-md">Create List</button>
                    <div v-for="list in lists" :key="list.id" class="mt-2">
                        <button @click="addHotelToList(list.id)" class="w-full p-2 bg-gray-200 rounded-md">{{ list.name }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
