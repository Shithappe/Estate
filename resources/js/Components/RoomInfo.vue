<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  rooms: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <div>
    <!-- <h3 v-if="rooms[0].booking_title" class="text-xl font-semibold mt-4 mb-2">{{ rooms[0].booking_title }}</h3> -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-4 mt-2 mb-4">
      <div v-for="room in rooms" :key="room">
        <div
          class="flex justify-between shadow rounded-lg p-4 bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out"
        >
          <div>
            <div class="text-2xl">
              {{ room.occupancy > 0 ? Math.round(room.occupancy) + '%' : 'N/A' }}
            </div>
            <div>{{ room.room_type }}</div>
          </div>
          <div class="flex flex-col justify-between items-end">
            <div v-if="room.price" class="text-xl">${{ room.price }}</div>
            <div v-if="room.hasOwnProperty('active') && room.active == false" class="text-sm">
              *hidden by owner
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>