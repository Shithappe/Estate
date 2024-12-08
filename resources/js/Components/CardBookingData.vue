<script setup>
import { onMounted, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import Lucide from '@/Components/Lucide.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Navigation } from 'vue3-carousel';
import AddToListModal from '@/Components/AddToListModal.vue';
import FormSubmissions from '@/Components/FormSubmissions.vue';
import { strToArray } from '@/Utils/strToArray.js';

const props = defineProps({
    item: Object,
    canOpenCart: Number,
    auth: Object,
    lists: {
        type: Object,
        default: null
    },
    listId: Number
});

const showModal = ref(false);
const showAddToListModal = ref(false);
const openModal = () => { showModal.value = true; };
const closeModal = () => { showModal.value = false; };
const closeAddToListModal = () => { showAddToListModal.value = false; };

const emit = defineEmits(['updateCanOpenCart', 'updateLists', 'removeItem']);

const loading = ref(props.auth?.user ? false : true);
const openCart = () => {
    if (!props.auth?.user) {
        if (props.canOpenCart > 0) {
            emit('updateCanOpenCart');
            loading.value = false;
        }
        else window.location.href = '/login';
    }
};

const images = [...new Set([...strToArray(props.item.static_images, 500), ...strToArray(props.item.images, 500)])];

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

const openAddToListModal = () => {
    showAddToListModal.value = true;
};

const removeFromList = async (id) => {
    try {
        await axios.delete(`/api/list_item/${props.listId}/${id}`);
        emit('removeItem', 'complex', id);
    } catch (error) {
        console.error(error);
    }
}

// onMounted(() => {

// });

</script>

<template>
    <div class="m-4 ms:w-96 lg:w-80 min-w-64 relative flex flex-col bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 hover:bg-gray-200 transition duration-300 ease-in-out"
        :class="{ 'bg-green-200 hover:bg-green-300': props.item.selected }" @click="openCart">

        <button
            v-if="!props.lists"
            @click="removeFromList(item.id)"
            class="absolute -top-2 -right-2 bg-slate-400 text-white shadow-lg rounded-full w-5 h-5 flex items-center justify-center z-10"
            aria-label="Close"
          >
            &times;
          </button>

        <carousel id="gallery" :items-to-show="1" :wrap-around="false">
            <slide v-for="image in images" :key="image" class="w-full h-36 rounded-lg overflow-hidden">
                <img class="object-cover w-full rounded-lg" :src="image" alt="">
            </slide>

            <template #addons>
                <navigation />
            </template>
        </carousel>

        <div class="relative col-span-3 h-96 mx-3 pt-2 pb-2">
            <div class="flex flex-col relative">
                <div class="flex items-center justify-between mb-1">
                    <Link class="text-xl font-semibold hover:text-blue-800 line-clamp-2" :href="'/booking_data/' + item.id">{{ item.title }}</Link>
                </div>
                
                <div class="flex gap-x-1">
                    <Lucide 
                        v-for="(star, index) in 5" :key="index" 
                        class="w-5 h-5"  
                        :class="{ 'fill-black': index < item.star }"
                        icon="Star" 
                    />
                    <div>{{ item.score }}</div>
                </div>
            </div>


            <div class="flex items-center justify-between text-md mb-1 pr-3">
                <div class="flex items-center gap-1">
                    <Lucide class="w-5 h-5" icon="MapPin" /> {{ item.city }}
                </div>
                <div class="flex items-center gap-1">
                    <Lucide class="w-5 h-5" icon="Hotel" /> {{ item.type }}
                </div>
            </div>

            <div class="flex flex-col mt-3 mb-2 gap-y-0.5 px-2">

                <div class="flex items-center justify-between">
                    <div class="flex gap-x-2">
                        <Lucide class="w-5 h-5" icon="Bed" />
                        <span>Units</span>
                    </div>
                    <div>{{ item.count_rooms }}</div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex gap-x-2">
                        <Lucide class="w-5 h-5" icon="Tags" />
                        <span>Types of units</span>
                    </div>
                    <div>{{ item.types_rooms }}</div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex gap-x-2">
                        <Lucide class="w-5 h-5" icon="Zap" />
                        <span>Average occupancy</span>
                    </div>
                    <div v-if="!loading" class="ml-auto">{{ Math.round(item.occupancy) >= 0 ? Math.round(item.occupancy) + '%' : 'N/A' }}</div>
                    <div v-else class="loading px-1 text-slate-500 ml-auto">Occupancy</div>
                </div>

                <div v-if="item.min_price && item.max_price" class="flex items-center justify-between">
                    <div class="flex gap-x-2">
                        <Lucide class="w-5 h-5" icon="DollarSign" />
                        <span>Prices per night</span>
                    </div>
                    <div v-if="!loading">${{ item.min_price }} - ${{ item.max_price }}</div>
                    <div v-else class="loading px-1 text-slate-500">Price</div>
                </div>

                <!-- если min === max отрисовуем только один -->
                <div v-if="(item.min_price || item.max_price) && item.count_rooms" class="flex items-center justify-between">
                    <div class="flex gap-x-2">
                        <Lucide class="w-5 h-5 mt-0.5" icon="TrendingUp" />
                        <span>Rental Income</span>
                    </div>
                    <div v-if="!loading">
                        <div v-if="item.min_price === item.max_price">${{ item.count_rooms * item.max_price }}</div>
                        <div v-else>${{ addDots(item.count_rooms * item.min_price) }} - ${{ addDots(item.count_rooms * item.max_price) }}</div>    
                    </div>
                    <div v-else class="loading px-1 text-slate-500">Price</div>
                </div>

                <div v-if="item.forecast_price" class="flex items-center justify-between">
                    <div class="flex gap-x-2">
                        <Lucide class="w-5 h-5" icon="Receipt" />
                        <span>Estimated price</span>
                    </div>
                    <div>${{ addDots(item.forecast_price) }}</div>
                </div>
                
            </div>

            <div class="absolute bottom-3 w-full flex justify-between gap-x-0.5">
                <Link :href="'booking_data/' + item.id" class="flex-auto bg-gray-900 text-white py-3 px-4 rounded-l-lg text-center hover:bg-black hover:shadow-lg transition duration-300">Details</Link>
                
                <button @click.stop="openAddToListModal" class="flex-auto bg-gray-900 text-white py-3 px-4 text-center hover:bg-black hover:shadow-lg transition duration-300" :class="{ 'rounded-r-lg':!props.lists }">Buy</button>
                
                <button v-if="props.auth?.user && props.lists" @click.stop="openAddToListModal" class="bg-gray-900 p-3 rounded-r-lg hover:bg-black hover:shadow-lg transition duration-300">
                    <Lucide class="w-5 h-5 text-white" icon="BookmarkPlus" />
                </button>
            </div>


            <FormSubmissions :booking_id="props.item.id" target="buy" title="Buy investment property in Bali with passive income" des="" :show="showModal" @close="closeModal" />
        </div>

        <AddToListModal
            v-if="props.lists"
            :lists="props.lists.complex"
            :itemId="props.item.id"
            type='complex'
            :auth="props.auth"
            :show="showAddToListModal"
            @close="closeAddToListModal"
            @updateLists="newList => {
                emit('updateLists', newList);
            }"
        />
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