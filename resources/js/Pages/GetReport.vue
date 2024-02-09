<script setup>
import { ref } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import CardBookingData from '@/Components/CardBookingData.vue';

const props = defineProps({
    data: Object,
});

</script>

<template>
    <SimpleAppLayout title="Get Report">
        {{ props.data[0] }}

        <!-- <div class="w-full px-4 py-6 mx-auto"
            :class="{ 'lg:w-4/5 lg:float-right lg:pl-24': showFilters, 'lg:px-24 lg:max-w-8xl': !showFilters }">

            <div class="my-8 flex flex-wrap"
                :class="{ 'xl:pl-4 2xl:pl-10': showFilters, 'justify-center': !showFilters, 'opacity-50': load }">
                <CardBookingData v-for="item in data.data" :key="item.id" :item="item" class="col-span-1" />
            </div>

        </div> -->
    </SimpleAppLayout>
</template>

