<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  booking_data: Array
})

console.log(props.booking_data[0]);

const emits = defineEmits(['bookingClick']); // Определение события для передачи данных в родительский компонент

const handleBookingClick = (booking) => {
  emits('bookingClick', booking); // Вызов события с передачей данных (booking) в родительский компонент
}

</script>

<template>
  <div
    class="absolute right-0 z-10 w-1/5 h-screen shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-auto">
    <div class="flex flex-col gap-y-1 m-4">
      <span>{{ props.booking_data.length }} items in radius</span>
      <div v-for="book in booking_data" @click="handleBookingClick(book)"
        class="text-md hover:drop-shadow-md hover:cursor-pointer hover:shadow px-2 py-1 rounded-lg transition ease-out duration-100">
        {{ book.title }}
      </div>
    </div>
  </div>
</template>

  