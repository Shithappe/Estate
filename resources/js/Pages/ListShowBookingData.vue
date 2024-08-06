<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Lucide from '@/Components/Lucide.vue';
import CardBookingData from '@/Components/CardBookingData.vue';

const props = defineProps({
    list: Object,
    auth: Object
});

onMounted(() => {
    console.log(props.list);

});
</script>

<template>
    <SimpleAppLayout title="List">

        <div class="mx-2 py-2 lg:py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

                <div class="flex aling-center gap-x-2 mb-4">
                    <span class="text-2xl font-medium">{{ props.list.name }}</span>
                </div>

                <div class="my-8 flex flex-wrap">
                    <CardBookingData v-for="item in props.list.hotels" :key="item.id" :item="item" :auth="true" class="col-span-1" />
                </div>

            </div>
        </div>
    </SimpleAppLayout>
</template>
