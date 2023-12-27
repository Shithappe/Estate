<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  booking_data: Array
})

const emits = defineEmits(['bookingClick']); // Определение события для передачи данных в родительский компонент

const handleBookingClick = (booking) => {
  emits('bookingClick', booking); // Вызов события с передачей данных (booking) в родительский компонент
}

</script>

<template>
  <div
    class="absolute right-0 z-10 w-1/5 h-screen shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-auto">
    <div class="flex flex-col gap-y-4 m-4">
      <div v-for="book in booking_data" @click="handleBookingClick(book)"
        class="text-md hover:drop-shadow-md hover:cursor-pointer transition ease-out duration-100">
        {{ book.title }}
      </div>
    </div>
  </div>
</template>

  