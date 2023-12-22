<script setup>
import { defineProps, onMounted } from 'vue';
import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from "leaflet";
import "leaflet.markercluster";
import customMarkerIcon from "@/assets/custom-marker-icon.png";


const props = defineProps({
  locations: Array
})

let map = null;

onMounted(() => {
  map = L.map("mapContainer").setView([-8.51479, 115.26382], 15);
  L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);

  const customIcon = L.icon({
    iconUrl: customMarkerIcon, // Путь к вашему изображению маркера
    iconSize: [40, 40], // Размер изображения маркера
    iconAnchor: [22, 94], // Якорь иконки
    popupAnchor: [-3, -76], // Позиция всплывающей подсказки
  });

  // Создание кластеризатора маркеров
  const markerCluster = L.markerClusterGroup();

  const markers = [];

  props.locations.forEach((location) => {
    const marker = L.marker(location, { icon: customIcon });
    markerCluster.addLayer(marker);
  });

  map.addLayer(markerCluster);
})
</script>

<template>
  <div id="mapContainer"></div>
</template>
  
  
<style scoped>
#mapContainer {
  width: 100vw;
  height: 100vh;
}
</style>

