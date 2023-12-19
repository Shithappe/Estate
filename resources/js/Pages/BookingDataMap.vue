<script setup>
/* eslint-disable no-undef */
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';

import { Loader } from '@googlemaps/js-api-loader';

const props = defineProps({
    data: Object,
    cities: Object,
});

const loader = new Loader({ apiKey: 'AIzaSyCMenSGPO8e2i7EFsO456VZkMh30a310YE' })

const mapDiv = ref(null)
let map = ref(null)

onMounted(async () => {
    await loader.load()
    map.value = new google.maps.Map(mapDiv.value, {
        center: { lat: 50.123, lng: 49.334 },
        zoom: 7
    })
})

</script>

<template>
    <!-- <SimpleAppLayout title="Головна"> -->

        <div ref="mapDiv" style="width: 100vw; height: 100vh;"></div>
        <!-- <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        </div> -->
    <!-- </SimpleAppLayout> -->
</template>
