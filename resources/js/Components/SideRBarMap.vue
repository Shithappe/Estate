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
  <div class="">
    <div
      class="absolute right-0 z-10 w-1/5 h-screen shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-x-hidden overflow-y-auto">
      <div class="relative flex flex-col gap-y-1 m-4">
        <div v-for="book in booking_data" @click="handleBookingClick(book)"
          class="text-md hover:drop-shadow-md hover:cursor-pointer hover:shadow px-2 py-1 rounded-lg transition ease-out duration-100">
          {{ book.title }}
        </div>

      </div>
    </div>

    <button v-if="props.booking_data.length > 0" class="fixed bottom-2 right-2 z-20 w-72 p-4 rounded-lg text-center text-white bg-black">
      <span>Get a report on {{ props.booking_data.length }} objects</span>
    </button>
  </div>
</template>

  