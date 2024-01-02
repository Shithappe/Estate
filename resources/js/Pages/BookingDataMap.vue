<script setup>
import { defineProps, ref, onMounted } from 'vue';
import SideLBarMap from '@/Components/SideLBarMap.vue';
import SideRBarMap from '@/Components/SideRBarMap.vue';
import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from "leaflet";
import "leaflet.markercluster";
import markerIcon from "@/assets/pin.png";
import anotherCustomIcon from "@/assets/placeholder.png";


const props = defineProps({
  locations: Array
})

const booking_data = ref(null);
const dataLoaded = ref(false);

const markers = [];
let map = null;
let circle = null;

onMounted(() => {
  map = L.map("mapContainer").setView([-8.51479, 115.26382], 15);
  L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);

  map.on('click', (e) => {
    // Проверяем, был ли клик выполнен вне маркера
    if (e.originalEvent.target.classList.contains('leaflet-marker-icon')) {
      return; // Если клик выполнен на маркере, ничего не делаем
    }

    // Если клик выполнен вне маркера, выполните нужные действия здесь
    console.log('Клик выполнен вне маркера. Координаты:', e.latlng);

    // Например, можно сбросить выбранный маркер, если таковой был сохранен
    if (selectedMarker.value !== null) {
      selectedMarker.value.setIcon(customIcon); // Изменить на изначальную иконку
      selectedMarker.value = null;
      dataLoaded.value = false;
    }

    if (circle) {
      map.removeLayer(circle);
    }
  });

  const customIcon = L.icon({
    iconUrl: markerIcon, // Путь к вашему изображению маркера
    iconSize: [50, 50], // Размер изображения маркера
    iconAnchor: [25, 48], // Якорь иконки
  });

  // Создание кластеризатора маркеров
  const markerCluster = L.markerClusterGroup();

  let selectedMarker = ref(null);

  props.locations.forEach((item) => {
    const marker = L.marker(item.location, { icon: customIcon });
    markers.push(marker);
    marker.addTo(markerCluster);

    // Добавляем свойство данных 'id' к каждому маркеру
    marker.myId = item.id;

    marker.on('click', (e) => {
      const clickedMarker = e.target;
      const markerId = clickedMarker.myId;

      if (selectedMarker.value !== null) {
        selectedMarker.value.setIcon(customIcon); // Изменить на изначальную иконку
      }

      // Вывод id маркера в консоль (можно изменить на нужное действие)
      console.log(markerId);
      fetchData(markerId);

      const markerCoords = clickedMarker.getLatLng();

      if (circle) {
        map.removeLayer(circle);
      }
      circle = L.circle(e.latlng, {
        radius: 2000,
        color: 'red', // Цвет окружности (можно выбрать нужный)
        fillColor: '#f03', // Цвет заливки окружности
        fillOpacity: 0.2, // Прозрачность заливки окружности
      }).addTo(map);

      // Плавное перемещение к маркеру с использованием метода flyTo
      map.flyTo(markerCoords, 18, {
        duration: 2,
        easeLinearity: 0.5
      });

      // Cсылка на старый маркер
      selectedMarker.value = clickedMarker;

      const anotherIcon = L.icon({
        iconUrl: anotherCustomIcon, // Путь к вашему изображению маркера
        iconSize: [60, 60], // Размер изображения маркера
        iconAnchor: [30, 30], // Якорь иконки
        popupAnchor: [-3, -76], // Позиция всплывающей подсказки
      });

      clickedMarker.setIcon(anotherIcon);
    });
  });

  map.addLayer(markerCluster);
})

const handleBookingClick = (selectedBooking) => {
  const markerWithId = markers.find(marker => marker.myId === selectedBooking.id);

  map.flyTo(markerWithId.getLatLng(), 18, {
    duration: 2,
    easeLinearity: 0.5
  });
}

async function fetchData(markerId) {
  try {
    const response = await axios.get("/api/booking_data_map_card/" + markerId);

    response.data.images = response.data.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));
    response.data.description = response.data.description.split('. ').slice(0, 2).join('. ');


    console.log(response.data);


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
      enter-to-class="translate-x-0 opacity-100" leave-active-class="transition ease-in duration-300"
      leave-from-class="translate-x-0 opacity-100" leave-to-class="-translate-x-full opacity-0">
      <SideLBarMap v-if="booking_data && dataLoaded" :booking_data="booking_data" />
    </transition>

    <transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100" leave-active-class="transition ease-in duration-300"
      leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-full opacity-0">
      <SideRBarMap v-if="booking_data && dataLoaded" :booking_data="booking_data.nearby_location"
        @bookingClick="handleBookingClick" />
    </transition>

    <div id="mapContainer" class="w-full h-screen z-0"></div>
  </div>
</template>

