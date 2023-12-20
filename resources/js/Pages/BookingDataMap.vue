<script setup>
import { ref, onMounted } from 'vue';
import { Loader } from '@googlemaps/js-api-loader';
import MarkerClusterer from '@googlemaps/markerclustererplus';

const props = defineProps({
    coordinates: Object,
});

const loader = new Loader({ apiKey: import.meta.env.VITE_GOOGLE_MAP_API_KEY });
const mapDiv = ref(null);
let map = ref(null);
let markers = [];
const minZoomLevel = 10;
const maxZoomLevel = 15;
let markerClusterer;

onMounted(async () => {
    await loader.load();

    
    map.value = new google.maps.Map(mapDiv.value, {
        center: { lat: -8.532436, lng: 115.260834 },
        zoom: 13
    });

    props.coordinates.forEach(coord => {
        const marker = new google.maps.Marker({
            position: { lat: Number(coord.lat), lng: Number(coord.lng) },
            map: map.value
        });

        markers.push(marker);
    });

    markerClusterer = new MarkerClusterer(map.value, markers, {
        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
        gridSize: 60, // Размер сетки (примерное расстояние между кластерами)
        maxZoom: maxZoomLevel,
    });

    // google.maps.event.addListener(map.value, 'idle', () => {

    //     markerClusterer.clearMarkers();
    //     markers.forEach(marker => {
    //         marker.setMap(map.value);
    //     });

    //     markerClusterer.addMarkers(markers);

    // });

    google.maps.event.addListener(map.value, 'zoom_changed', () => {
        if (map.value.getZoom() > minZoomLevel && map.value.getZoom() < maxZoomLevel) {
            markerClusterer.clearMarkers();
            markerClusterer.addMarkers(markers);
        }
    });
});
</script>

<template>
    <div ref="mapDiv" style="width: 100vw; height: 100vh;"></div>
</template>
