<script setup>

const props = defineProps({
  booking_data: Array
})

const emits = defineEmits(['bookingClick', 'addToList']); // Определение события для передачи данных в родительский компонент

const handleBookingClick = (booking) => {
  emits('bookingClick', booking); // Вызов события с передачей данных (booking) в родительский компонент
}

const handleAddToList = (items) => { 
  emits('addToList', items.map(item => item.id));
}

</script>

<template>
  <div class="sm:block lg:absolute right-0 z-10 sm:w-full lg:w-1/5 h-screen shadow-lg md:backdrop-filter md:backdrop-blur-md md:bg-gray-400 md:bg-opacity-30 overflow-y-auto">
    <div class="relative flex flex-col gap-y-1 m-4">

      <button class="bg-black text-white px-2 py-1 rounded-lg" @click="handleAddToList(booking_data)" :disabled="booking_data.length === 0">
        Add to List {{ booking_data.length }} complex
      </button>

      <div
        v-for="book in booking_data"
        :key="book.id"
        @click="handleBookingClick(book)"
        class="text-md hover:drop-shadow-md hover:cursor-pointer hover:shadow px-2 py-1 rounded-lg transition ease-out duration-100"
      >
        {{ book.title }}
      </div>
    </div>
  </div>
</template>
