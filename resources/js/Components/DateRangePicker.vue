<script setup>
import { ref, onMounted, watch } from 'vue';
import VueTailwindDatePicker from "vue-tailwind-datepicker";
import moment from 'moment';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const dateValue = ref(props.modelValue);
const formatter = ref({
    date: 'DD MMM YYYY',
    month: 'MMM',
});

function dDate(date) {
    return date > new Date()
}

const formatDate = (date) => {
    return moment(date).format('DD MMM YYYY');
};

onMounted(() => {
    if (!dateValue.value) {
        const today = new Date();
        const lastMonth = new Date(today);
        lastMonth.setMonth(today.getMonth() - 1);

        const startDateStr = formatDate(lastMonth);
        const endDateStr = formatDate(today);

        dateValue.value = `${startDateStr} ~ ${endDateStr}`;
        emit('update:modelValue', dateValue.value);
    }
});

watch(dateValue, (newValue) => {
    console.log("DateRangePicker: Date changed to", newValue); // Лог для отладки
    emit('update:modelValue', newValue);
});
</script>

<template>
    <VueTailwindDatePicker 
        v-model="dateValue" 
        :formatter="formatter" 
        :disable-date="dDate"
    />
</template>