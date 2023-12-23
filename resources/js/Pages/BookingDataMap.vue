<script setup>
import { defineProps, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from "leaflet";
import "leaflet.markercluster";
import markerIcon from "@/assets/marker.png";
// import markerIcon from "@/assets/custom-marker-icon.png";

import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';


const props = defineProps({
  locations: Array
})

const booking_data = ref(null);
const dataLoaded = ref(false);

let map = null;

onMounted(() => {
  map = L.map("mapContainer").setView([-8.51479, 115.26382], 15);
  L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);

  const customIcon = L.icon({
    iconUrl: markerIcon, // Путь к вашему изображению маркера
    iconSize: [25, 40], // Размер изображения маркера
    iconAnchor: [22, 94], // Якорь иконки
    popupAnchor: [-3, -76], // Позиция всплывающей подсказки
  });

  // Создание кластеризатора маркеров
  const markerCluster = L.markerClusterGroup();

  const markers = [];

  props.locations.forEach((item) => {
    const marker = L.marker(item.location, { icon: customIcon });
    marker.addTo(markerCluster);

    // Добавляем свойство данных 'id' к каждому маркеру
    marker.myId = item.id;

    marker.on('click', (e) => {
      const clickedMarker = e.target;
      const markerId = clickedMarker.myId;

      // Вывод id маркера в консоль (можно изменить на нужное действие)
      console.log('marker ID:', markerId);
      fetchData(markerId);

      const markerCoords = clickedMarker.getLatLng();

      // Плавное перемещение к маркеру с использованием метода flyTo
      map.flyTo(markerCoords, 18, {
        duration: 2,
        easeLinearity: 0.5
      });
    });
  });

  map.addLayer(markerCluster);
})


async function fetchData(markerId) {
  try {
    const response = await axios.get("/api/booking_data_map_card/" + markerId);

    response.data.images = response.data.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));
    response.data.description =  response.data.description.split('. ').slice(0, 2).join('. ');

    booking_data.value = response.data;
    dataLoaded.value = true;
    return response.data; // Возвращаем данные для дальнейшей обработки
  } catch (error) {
    console.error(error);
    throw error; // Вы можете выбросить ошибку или обработать её здесь
  }
}

</script>

<template>
  <div class="relative">
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="-translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100">
      <div v-if="booking_data && dataLoaded"
        class="absolute z-10 w-1/4 h-screen shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30">
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

          <div class="text-2xl font-bold hover:text-blue-800">
            <Link :href="'booking_data/' + booking_data.id">{{ booking_data.title }}</Link>
          </div>
          <div class="mb-4 flex">
            <Lucide v-for="star in booking_data.star" class="w-5 h-5 fill-black" icon="Star" />
          </div>
          <div>
            {{ booking_data.description }}.
          </div>
          <div v-for="(percentage, roomType) in booking_data.averageOccupancyPercentage" :key="roomType">
            <div>
              {{ roomType }} - {{ percentage }}
            </div>
          </div>
        </div>
      </div>

    </transition>
    <div id="mapContainer" class="w-full h-screen z-0"></div>
  </div>
</template>

