<script setup>
import { Link } from '@inertiajs/vue3'
import { defineProps, defineEmits } from 'vue';
// import html2pdf from 'html2pdf.js';

const props = defineProps({
  booking_data: Array
})

const generatePDF = () => {
  const element = document.getElementById('styledContent');

  // Опции для html2pdf
  const options = {
    margin: 10,
    filename: 'styled_example.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  // Генерируем PDF из HTML элемента
  html2pdf(element, options).then(pdf => {
    // Скачиваем PDF
    pdf.save();
  });
}

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
        <div v-for="book in booking_data" :key="book.id" @click="handleBookingClick(book)"
          class="text-md hover:drop-shadow-md hover:cursor-pointer hover:shadow px-2 py-1 rounded-lg transition ease-out duration-100">
          {{ book.title }}
        </div>

      </div>
    </div>

    <Link href="/get-report" method="post" as="button" :data="{ id: props.booking_data.map(obj => obj.id) }" v-if="props.booking_data.length > 0"
      class="fixed bottom-2 right-2 z-20 w-72 p-4 rounded-lg text-center text-white bg-black">
      Get a report on {{ props.booking_data.length }} objects
    </Link>


  </div>
</template>

<style scoped>
/* Стилизуйте свой контент как HTML и CSS */
#styledContent {
  color: red;
  background: linear-gradient(to right, red, yellow);
  padding: 10px;
  border-radius: 5px;
}

.highlightedText {
  background-color: yellow;
}
</style>