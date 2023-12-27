<script setup>
import { defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';

import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
    booking_data: Object
})

</script>

<template>
      <div class="absolute z-10 w-1/4 h-screen shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-auto">
        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
          <slide v-for="image in booking_data.images" :key="image" class="w-full h-56 overflow-hidden">
            <img class="object-cover w-full rounded-lg" :src="image" alt="">
          </slide>
          <template #addons>
            <navigation />
            <pagination />
          </template>
        </carousel>
        <div class="flex flex-col gap-y-4 m-4">
          <div class="text-2xl font-bold">
            <Link :href="'booking_data/' + booking_data.id" class="hover:text-blue-800">{{ booking_data.title }}</Link>
            <div class="text-xl font-medium rounded-lg">{{ booking_data.type }}</div>
          </div>
          <div class="mb-2 flex">
            <Lucide v-for="star in booking_data.star" :key="star" class="w-5 h-5 fill-black" icon="Star" />
          </div>
          <div>
            {{ booking_data.description }}
          </div>
          <div v-for="(percentage, roomType) in booking_data.averageOccupancyPercentage" :key="roomType">
            <div>
              {{ roomType }} - {{ percentage }}
            </div>
          </div>
        </div>
      </div>
  </template>

  