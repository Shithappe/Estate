<script setup>
import { Link } from '@inertiajs/vue3'
import { defineProps, defineEmits } from 'vue';
// import html2pdf from 'html2pdf.js';

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
    class="sm:block lg:absolute right-0 z-10 sm:w-full lg:w-1/5 h-screen shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-x-hidden">
    
    <div class="flex justify-center">
      <Link href="/get-report" method="post" as="button" :data="{ id: props.booking_data.map(obj => obj.id) }"
        v-if="props.booking_data.length > 0"
        class="sm:block lg:fixed bottom-2 right-1 z-20 w-72 p-4 rounded-lg text-center text-white bg-black">
      Get a report on {{ props.booking_data.length }} objects
      </Link>

    </div>

    <div class="relative flex flex-col gap-y-1 m-4">
      <div v-for="book in booking_data" :key="book.id" @click="handleBookingClick(book)"
        class="text-md hover:drop-shadow-md hover:cursor-pointer hover:shadow px-2 py-1 rounded-lg transition ease-out duration-100 sm:overflow-y-hidden lg:overflow-y-auto">
        {{ book.title }}
      </div>

    </div>
  </div>
</template>
