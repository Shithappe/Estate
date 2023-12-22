<template>
  <div id="mapContainer"></div>
</template>
  
<script setup>
import { defineProps, onMounted } from 'vue';
import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from "leaflet";
import "leaflet.markercluster";


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

    // Создание кластеризатора маркеров
    const markerCluster = L.markerClusterGroup();

    const markers = [];

    props.locations.forEach((location) => markers.push(L.marker(location)));

    markerCluster.addLayers(markers);
    map.addLayer(markerCluster);
  })
  // beforeUnmount() {
  //   if (map) {
  //     map.remove();
  //   }
  // },
</script>
  
<style scoped>
#mapContainer {
  width: 100vw;
  height: 100vh;
}
</style>

