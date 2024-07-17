<script setup>
import { ref } from "vue";
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Navigation } from 'vue3-carousel'; // Pagination
import FormSubmissions from '@/Components/FormSubmissions.vue';


const props = defineProps({
    item: Object,
    canOpenCart: Number,
    auth: Boolean
});

const showModal = ref(false);
const openModal = () => { showModal.value = true; };
const closeModal = () => { showModal.value = false; };

const emit = defineEmits(['updateCanOpenCart']);

const loading = ref(props.auth ? false : true);
const openCart = () => {
    if (!props.auth) {
        if (props.canOpenCart > 0) {
            emit('updateCanOpenCart');
            loading.value = false;
        }
        else window.location.href = '/login';
    }
};

const images = props.item.images.slice(1, -1).split(', ').map(item => item.slice(1, -1));

const addDots = (str) => {
    str = String(str)
    // Преобразуем строку в массив символов и перевернем его
    let reversed = str.split('').reverse().join('');

    // Используем регулярное выражение для добавления точек через каждые три символа
    let withDots = reversed.replace(/(\d{3})/g, '$1.');

    // Удалим последнюю точку, если она есть, и перевернем строку обратно
    withDots = withDots.split('').reverse().join('');
    if (withDots.startsWith('.')) withDots = withDots.slice(1);

    return withDots;
}

</script>

<template>
    <div class="m-4 ms:w-96 lg:w-72 min-w-64 max-96 flex flex-col bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 hover:bg-gray-200 transition duration-300 ease-in-out"
        :class="{ 'bg-green-200 hover:bg-green-300': props.item.selected }" @click="openCart">

        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
            <slide v-for="image in images" :key="image" class="w-full h-36 rounded-lg overflow-hidden">
                <img class="object-cover w-full rounded-lg" :src="image" alt="">
            </slide>

            <template #addons>
                <navigation />
            </template>
        </carousel>


        <div class="relative col-span-3 h-80 mx-3 pt-2 pb-2">
            <div class="flex flex-col relative">
                <div class="text-xl font-semibold hover:text-blue-800">
                    <Link :href="'booking_data/' + item.id">{{ item.title }}</Link>
                </div>
                <div class="mt-1 flex">
                    <Lucide v-for="star in item.star" class="w-5 h-5 fill-black" icon="Star" />
                </div>
            </div>

            <div class="flex items-center text-md mb-1">
                {{ item.city }}
            </div>

            <div class="mt-2 mb-2 flex justify-between gap-y-2 px-2 font-medium">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Hotel" /> {{ item.type }}
                    </div>



                    <div class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Zap" />
                        <div v-if="!loading">{{ Math.round(item.occupancy_rate) >= 0 ? Math.round(item.occupancy_rate) +
                            '%' :
                            'N/A' }}</div>
                        <div v-else class="loading px-1 text-slate-500">Occupancy</div>
                    </div>


                    <div v-if="item.min_price && item.max_price" class="flex items-center gap-x-2 z-3">
                        <Lucide class="w-5 h-5" icon="DollarSign" />
                        <div v-if="!loading">{{ item.min_price }} - {{ item.max_price }}</div>
                        <div v-else class="loading px-1 text-slate-500">Price</div>
                    </div>
                    <div v-if="item.forecast_price" class="flex items-center gap-x-2">
                        <Lucide class="w-5 h-5" icon="Receipt" />
                        <div>${{ addDots(item.forecast_price) }}</div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <!-- <div v-if="item.price" class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="DollarSign" /> {{ item.price }}
                    </div> -->

                    <div class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Bed" /> {{ item.count_rooms }}
                    </div>
                    <div class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Tags" /> {{ item.types_rooms }}
                    </div>

                    <div v-if="item.score" class="flex items-center gap-2">
                        <Lucide class="w-5 h-5" icon="Star" /> {{ item.score }}
                    </div>
                </div>
            </div>

            <div class="absolute bottom-3 w-full flex flex-col gap-y-2">
                <button @click="openModal"
                    class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-900 bg-slate-100 rounded-lg">Buy</button>
                <Link :href="'booking_data/' + item.id" class="">
                <button
                    class="w-full flex justify-center gap-1 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">See
                    Details</button>
                </Link>
            </div>
            <FormSubmissions :booking_id="props.item.id" target="buy" title="Buy investment property in Bali with passive income" des="" :show="showModal" @close="closeModal" />
        </div>
    </div>
</template>


<style scoped>
.loading {
    z-index: 2;
    /* color: transparent; */
    min-width: 5vh;
    margin: 1px 0;
    height: 1.5em;
    border-radius: 6px;
    background: linear-gradient(100deg, #e8eaeb 30%, #d1d2d3 50%, #e8eaeb 70%);
    background-size: 400%;
    animation: loading 1.2s ease-in-out infinite;
}

@keyframes loading {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0 50%;
    }
}
</style>