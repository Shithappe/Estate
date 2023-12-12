<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import Lucide from '@/Components/Lucide.vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
    booking: Object,
    rooms: Object,
});

const book = props.booking[0]
const images = book.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));


</script>

<template>
    <SimpleAppLayout title="Головна">
        <template #header>
            <h2 class="flex gap-x-2 font-semibold text-xl text-gray-800 leading-tight">
                <Link href="/">
                <Lucide icon="ArrowLeft" />
                </Link>
                <div>{{ book.title }}</div>
            </h2>
        </template>

        <div class="mx-2 py-2 lg:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="">
                    <carousel id="gallery" :items-to-show="1" :wrap-around="false">
                        <slide v-for="image in images" :key="image">
                            <img class="object-cover rounded-lg" :src="image" alt="">
                        </slide>

                        <template #addons>
                            <navigation />
                            <pagination />
                        </template>
                    </carousel>

                    <div class="mt-6">
                        <div class="text-2xl font-bold">{{ book.title }}</div>

                        <div class="flex gap-2 mt-2 mb-4">
                            <Lucide class="w-5 h-5" icon="MapPin" /> {{ book.city }}
                        </div>


                        <div>{{ book.description }}</div>

                        <!-- <div class="flex flex-col justify-center">
                            <div class="mt-4" v-for="(percentage, roomType) in rooms" :key="roomType">
                                <div class="w-full mt-1 mx-auto font-semibold">{{ roomType }} - {{ percentage }}</div>
                            </div>
                        </div> -->


                        <div class="mt-8 flex items-center justify-center">
                            <table class="w-1/2 border-collapse border">
                                <thead>
                                    <tr>
                                        <th class="border">Room type</th>
                                        <th class="border">Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(percentage, roomType) in rooms" :key="roomType">
                                        <td class="border">{{ roomType }}</td>
                                        <td class="border text-center">{{ percentage }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </SimpleAppLayout>
</template>

