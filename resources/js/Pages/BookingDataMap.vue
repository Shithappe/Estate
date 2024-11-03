<script setup>
import { defineProps, ref, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import SideLBarMap from '@/Components/SideLBarMap.vue';
import SideRBarMap from '@/Components/SideRBarMap.vue';
import SideBarFilters from '@/Components/SideBarFilters.vue';
import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from "leaflet";
import "leaflet.markercluster";
import markerIcon from "@/assets/pin.png";
import anotherCustomIcon from "@/assets/placeholder.png";

import BottomSheet from '@/Components/BottomSheet.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';


const props = defineProps({
  locations: Array,
  countries: Object,
  cities: Array,
  types: Array,
  facilities: Array
})

const isDesktop = window.matchMedia('(min-width: 1024px)').matches;

const data = ref(props.locations);
const locations = ref(null);
const selectCoord = ref(null);
const booking_data = ref(null);
const dataLoaded = ref(false);
const showFilters = ref(false);
const showBottomData = ref(false);
const radius = ref(1);

const markers = [];
let map = null;
let circle = null;

const applyFilters = async () => {
  try {
    const response = await axios.post("/api/booking_data-map", {
      'city': JSON.parse(localStorage.getItem('selectedCity')),
      'type': JSON.parse(localStorage.getItem('selectedTypes')),
      'facilities': JSON.parse(localStorage.getItem('selectedFacilities')),
      'price': JSON.parse(localStorage.getItem('selectedPrice'))
    });
    data.value = response.data;
    map.flyTo({ lat: data.value[0].location[0], lng: data.value[0].location[1] }, 12, {
      duration: 2,
      easeLinearity: 0.5
    });
  } catch (error) {
    console.error(error);
  }
};

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

  locations.value = filterCoordinatesByRadius(data.value, selectCoord.value, radius.value)
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

const addMarkers = (data) => {
  data.forEach((item) => {
    const marker = L.marker(item.location, {
      icon: customIcon,
      title: `${item.title}\n${item.price}$\n${item.occupancy}%`,
    });
    markers.push(marker);
    marker.addTo(markerCluster);
    marker.myId = item.id;

    marker.on('click', (e) => {
      const clickedMarker = e.target;
      const markerId = clickedMarker.myId;

      if (selectedMarker.value !== null) {
        // Вернуть предыдущий маркер в кластер и установить оригинальную иконку
        markerCluster.addLayer(selectedMarker.value);
        selectedMarker.value.setIcon(customIcon);
      }

      // Удалить текущий маркер из кластера
      markerCluster.removeLayer(clickedMarker);
      clickedMarker.addTo(map); // Добавить на карту отдельно

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

      showFilters.value = false;
      map.flyTo(markerCoords, 16, {
        duration: 2,
        easeLinearity: 0.5,
      });

      // Сохранить ссылку на выбранный маркер
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
};


watch(data, (newData) => {
  // Remove existing markers from the map
  markers.forEach(marker => map.removeLayer(marker));
  markers.length = 0; // Clear the markers array

  map.removeLayer(markerCluster);
  markerCluster.clearLayers();

  addMarkers(newData);
  map.addLayer(markerCluster);
});

const customIcon = L.icon({
  iconUrl: markerIcon, // Путь к вашему изображению маркера
  iconSize: [50, 50], // Размер изображения маркера
  iconAnchor: [25, 48], // Якорь иконки
});

// Создание кластеризатора маркеров
const markerCluster = L.markerClusterGroup({
  maxClusterRadius: 50, // Расстояние с которого маркеры объединяются 
});

let selectedMarker = ref(null);

onMounted(() => {
  map = L.map("mapContainer", { zoomControl: false }).setView([-8.51479, 115.26382], 15);
  L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png").addTo(map);

  map.on('click', (e) => {
    showBottomData.value = true;
    // Проверяем, был ли клик выполнен вне маркера
    if (e.originalEvent.target.classList.contains('leaflet-marker-icon')) {
      return; // Если клик выполнен на маркере, ничего не делаем
    }

    // Если клик выполнен вне маркера, выполните нужные действия здесь
    selectCoord.value = [e.latlng.lat, e.latlng.lng];
    reDrawCircle();

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

  addMarkers(data.value)
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
    showBottomData.value = true;
    const response = await axios.get("/api/booking_data_map_card/" + markerId);

    // response.data.images = response.data.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));
    response.data.description = response.data.description.split('. ').slice(0, 2).join('. ');

    booking_data.value = response.data;
    dataLoaded.value = true;
    return response.data;
  } catch (error) {
    console.error(error);
    throw error;
  }
}

const closeBottom = () => {
  showFilters.value = false;
  showBottomData.value = false;
}

</script>

<template>
  <SimpleAppLayout title="Map of units - ">

    <div class="relative w-full h-full">
      <transition v-if="isDesktop" enter-active-class="transition ease-out duration-300"
        enter-from-class="-translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transition ease-in duration-300" leave-from-class="translate-x-0 opacity-100"
        leave-to-class="-translate-x-full opacity-0">
        <SideLBarMap v-if="booking_data && dataLoaded" :booking_data="booking_data" />
      </transition>

      <transition v-if="isDesktop" enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transition ease-in duration-300" leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-full opacity-0">
        <SideRBarMap v-if="locations" :booking_data="locations" @bookingClick="handleBookingClick" />
      </transition>

        <BottomSheet v-if="(booking_data || locations) && !isDesktop && showBottomData" :mode="booking_data" @closeBottom="closeBottom">
          <template #top>
            <div v-if="locations"
              class="absolute z-10 top-4 w-1/2 left-1/2 transform -translate-x-1/2 px-4 py-3 rounded-lg flex flex-col shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-auto">
              <span class="mx-auto">{{ radius }} km</span>
              <input type="range" min="0.5" max="10" step="0.5" v-model="radius" @input="reDrawCircle">
            </div>
          </template>

          <template #body>
            <div>
              <SideLBarMap v-if="booking_data && dataLoaded" :booking_data="booking_data" />
              <SideRBarMap v-if="locations" :booking_data="locations" @bookingClick="handleBookingClick" />
            </div>
          </template>
        </BottomSheet>

      <SideBarFilters :show="showFilters" :map="true" :countries="props.countries" :types="props.types"
              :facilities="props.facilities" @applyFilters="applyFilters" @closeBottom="closeBottom" />

      <div class="absolute z-10 top-3 flex flex-col gap-y-2"
        :class="{ 'sm:left-0 lg:left-96': showFilters || (booking_data && dataLoaded) }"
      >
        <button
          class="px-2 py-2 rounded-lg shadow-lg hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out overflow-auto transform translate-x-4"
          :class="{ 'text-slate-100 bg-black': showFilters, 'backdrop-filter backdrop-blur-md bg-gray-100 bg-opacity-30': !showFilters }"
          @click="() => { closeBottom(); showFilters = !showFilters; }">
          <Lucide icon="Filter" />
        </button>

        <a href="/"
          class="px-2 py-2 rounded-lg shadow-lg backdrop-filter backdrop-blur-md bg-gray-100 cursor-pointer bg-opacity-30 hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out overflow-auto transform translate-x-4">
          <Lucide icon="ArrowLeftCircle" />
        </a>
      </div>


      <div v-if="locations && (!showBottomData || isDesktop)" class="absolute z-10 bottom-20 left-1/2 transform -translate-x-1/2 px-4 py-3 rounded-lg flex flex-col shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 overflow-auto">
        <span class="mx-auto">{{ radius }} km</span>
        <input type="range" min="0.5" max="10" step="0.5" v-model="radius" @input="reDrawCircle">
      </div>

      <div id="mapContainer" class="w-full h-screen z-0"></div>
    </div>
  </SimpleAppLayout>
</template>


<style scoped>
.custom-div-icon {
  background-color: rgba(0, 0, 0, 0.1);
  color: white;
  text-align: center;
  font-weight: bold;
  border-radius: 15px;
  padding: 5px;
}
</style>