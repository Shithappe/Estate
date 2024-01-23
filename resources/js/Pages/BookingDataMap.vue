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

const locations = ref(null);
const selectCoord = ref(null);
const booking_data = ref(null);
const dataLoaded = ref(false);
const radius = ref(2);

const markers = [];
let map = null;
let circle = null;

const reDrawCircle = () => {
  if (circle) {
    map.removeLayer(circle);
  }
  circle = L.circle({
    lat: selectCoord.value[0],
    lng: selectCoord.value[1]
  }, {
    radius: radius.value * 1000,
    color: 'red',
    fillColor: '#f03',
    fillOpacity: 0.2,
  }).addTo(map);

  locations.value = filterCoordinatesByRadius(props.locations, selectCoord.value, radius.value)
};

function filterCoordinatesByRadius(coordinatesArray, centerPoint, radius) {
  function calculateDistance(point1, point2) {
    const [lat1, lon1] = point1;
    const [lat2, lon2] = point2;

    const R = 6371; // Радиус Земли в километрах
    const dLat = degToRad(lat2 - lat1);
    const dLon = degToRad(lon2 - lon1);

    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(degToRad(lat1)) * Math.cos(degToRad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    const distance = R * c; // Расстояние в километрах

    return distance;
  }

  function degToRad(degrees) {
    return degrees * (Math.PI / 180);
  }

  return coordinatesArray.filter(item => {
    const location = item.location;
    if (location && location.length === 2) {
      return calculateDistance(location, centerPoint) <= radius;
    }
    return false;
  });
}

onMounted(() => {
  map = L.map("mapContainer").setView([-8.51479, 115.26382], 15);
  L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png").addTo(map);

  map.on('click', (e) => {
    // Проверяем, был ли клик выполнен вне маркера
    if (e.originalEvent.target.classList.contains('leaflet-marker-icon')) {
      return; // Если клик выполнен на маркере, ничего не делаем
    }

    // Если клик выполнен вне маркера, выполните нужные действия здесь
    selectCoord.value = [e.latlng.lat, e.latlng.lng];
    reDrawCircle()
    
    // Например, можно сбросить выбранный маркер, если таковой был сохранен
    if (selectedMarker.value !== null) {
      selectedMarker.value.setIcon(customIcon); // Изменить на изначальную иконку
      selectedMarker.value = null;
      dataLoaded.value = false;
    }
    
    if (circle) {
      map.removeLayer(circle);
    }

    circle = L.circle(e.latlng, {
        radius: radius.value * 1000,
        color: 'red', // Цвет окружности
        fillColor: '#f03', // Цвет заливки окружности
        fillOpacity: 0.2, // Прозрачность заливки окружности
      }).addTo(map);
  });

  const customIcon = L.icon({
    iconUrl: markerIcon, // Путь к вашему изображению маркера
    iconSize: [50, 50], // Размер изображения маркера
    iconAnchor: [25, 48], // Якорь иконки
  });

  // Создание кластеризатора маркеров
  const markerCluster = L.markerClusterGroup({
    maxClusterRadius: 40, // Расстояние с которого маркеры объединяются 
});

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
      selectCoord.value = [markerCoords.lat, markerCoords.lng];

      if (circle) {
        map.removeLayer(circle);
      }
      circle = L.circle(e.latlng, {
        radius: radius.value * 1000,
        color: 'red', // Цвет окружности
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
      reDrawCircle();
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

    booking_data.value = response.data;
    dataLoaded.value = true;
    return response.data;
  } catch (error) {
    console.error(error);
    throw error; 
  }
}
</script>

<template>
  <div class="relative w-full h-full">
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="-translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100" leave-active-class="transition ease-in duration-300"
      leave-from-class="translate-x-0 opacity-100" leave-to-class="-translate-x-full opacity-0">
      <SideLBarMap v-if="booking_data && dataLoaded" :booking_data="booking_data" />
    </transition>

    <transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100" leave-active-class="transition ease-in duration-300"
      leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-full opacity-0">
      <SideRBarMap v-if="locations" :booking_data="locations"
        @bookingClick="handleBookingClick" />
    </transition>


    <div v-if="locations" class="absolute z-10 bottom-4 left-1/2 transform -translate-x-1/2 px-4 py-3 rounded-lg flex flex-col shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-auto">
      <span class="mx-auto">{{ radius }} km</span>
      <input type="range" min="0.5" max="10" step="0.5" v-model="radius" @input="reDrawCircle">
    </div>

    <div id="mapContainer" class="w-full h-screen z-0"></div>
  </div>
</template>
