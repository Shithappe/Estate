<script setup>
import { onMounted, ref, watch } from 'vue';
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import markerIcon from "@/assets/pin.png";

const props = defineProps({
  locations: {
    type: Array,
    required: true
  },
  mapId: {
    type: String,
    default: 'mapContainer'
  }
});

const map = ref(null);
const markers = ref([]);

const customIcon = L.icon({
  iconUrl: markerIcon,
  iconSize: [50, 50],
  iconAnchor: [25, 48],
});

const addMarkers = (locations) => {
  // Удаляем существующие маркеры
  markers.value.forEach(marker => map.value.removeLayer(marker));
  markers.value = [];

  // Добавляем новые маркеры
  locations.forEach((location) => {
    const marker = L.marker(location.location, { icon: customIcon });
    markers.value.push(marker);
    marker.addTo(map.value);
  });

  fitMapToMarkers();
};

const fitMapToMarkers = () => {
  if (markers.value.length === 1) {
    // Если только один маркер, устанавливаем зум вручную
    map.value.setView(markers.value[0].getLatLng(), 14);
  } else if (markers.value.length > 0) {
    const group = new L.featureGroup(markers.value);
    map.value.fitBounds(group.getBounds().pad(0.1));
  }
};

onMounted(() => {
  map.value = L.map(props.mapId).setView([0, 0], 2);
  L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map.value);

  addMarkers(props.locations);
});

watch(() => props.locations, (newLocations) => {
  addMarkers(newLocations);
});
</script>

<template>
    <div :id="mapId" class="w-full h-[500px] z-0"></div>
</template>