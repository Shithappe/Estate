<script setup>
import { onMounted } from 'vue';
import { strToArray } from '@/Utils/strToArray.js';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';

import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
  booking_data: Object
});

const emits = defineEmits(['addToList']);

const images = [...new Set([...strToArray(props.booking_data.static_images, 500), ...strToArray(props.booking_data.images, 500)])];

const handleAddToList = (items) => { 
  emits('addToList', items);
}

// onMounted(() => {
//   console.log(props.booking_data);
// });

</script>

<template>
  <div class="sm:block lg:absolute z-10 sm:w-full lg:w-1/4 sm:h-max lg:h-screen shadow-x-lg md:backdrop-filter md:backdrop-blur-md md:bg-gray-400 md:bg-opacity-30 overflow-auto border-b-4 border-gray-500 border-dashed py-2">
    <div class="flex space-x-4">
    <!-- Контейнер для слайдера, занимающий 40% ширины -->
    <carousel id="gallery" :items-to-show="1" :wrap-around="false" class="w-2/5">
      <slide v-for="image in images" :key="image" class="w-full h-24 rounded-lg overflow-hidden">
        <img class="object-cover w-full rounded-lg" :src="image" alt="">
      </slide>
      <template #addons>
        <navigation />
      </template>
    </carousel>

    <!-- Контейнер для текста, занимающий оставшуюся ширину -->
    <div class="flex flex-col pr-2 w-3/5">
      <div class="flex items-start justify-between">
        <Link :href="'booking_data/' + booking_data.id" class="text-xl font-bold hover:text-blue-800">{{ booking_data.title }}</Link>
        <div class="text-2xl">{{ Math.round(booking_data.occupancy) }}%</div>
      </div>

      <div>{{ booking_data.min_price }}-{{ booking_data.max_price }}</div>
      <div class="flex items-center justify-end gap-x-2">
        <button @click="handleAddToList(booking_data.id)" class="pt-1 px-2 bg-black text-white rounded-lg">Add to List</button>
        <Link :href="'booking_data/' + booking_data.id">
          <button class="pt-1 px-2 bg-black text-white rounded-lg">Details</button>
        </Link>
      </div>
    </div>
  </div>

    <!-- <div class="flex flex-col gap-y-4 m-4 lg:m-0 lg:ml-2">
      <div class="text-2xl font-bold">
        <Link :href="'booking_data/' + booking_data.id" class="hover:text-blue-800">{{ booking_data.title }}</Link>
        <div class="text-xl font-medium rounded-lg">{{ booking_data.type }}</div>
      </div>
      
      <div class="mb-2 flex">
        <Lucide v-for="star in booking_data.star" :key="star" class="w-5 h-5 fill-black" icon="Star" />
      </div>
      <div class="flex flex-wrap gap-2">
        <span class="px-2 py-1 rounded-lg shadow hover:shadow-lg" v-for="facility in booking_data.facilities" :key="facility">
          {{ facility }}
        </span>
      </div>

      <div>{{ booking_data.description }}</div>
    </div> -->
  </div>
</template>


  