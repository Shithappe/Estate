<script setup>
import { ref } from 'vue';
import Lucide from '@/Components/Lucide.vue';
import AddToListModal from '@/Components/AddToListModal.vue';


const props = defineProps({
  rooms: {
    type: Array,
    required: true
  },
  lists: {
    type: Object,
    default: null
  },
  listId: Number,
  listType: String,
  auth: Object,
});


const showAddToListModal = ref(false);
const selectedRoomId = ref(0);
const closeAddToListModal = () => { 
  showAddToListModal.value = false; 
  selectedRoomId.value = 0; 
};

const emit = defineEmits(['updateLists', 'removeItem']);

const openAddToListModal = (roomId) => {
  selectedRoomId.value = roomId;
  showAddToListModal.value = true;
};

const removeFromList = async (id) => {
  try {
    await axios.delete(`/api/list_item/${props.listId}/${id}`);
    emit('removeItem', 'unit', id);
    } catch (error) {
        console.error(error);
    }
}
</script>

<template>
 <div>
  <h3 v-if="rooms[0].booking_title" class="text-xl font-semibold mt-4 mb-2">{{ rooms[0].booking_title }}</h3>
  <div 
    class="grid gap-4 mt-2 mb-4"
    :class="{
      'grid-cols-1': true, /* 1 колонка по умолчанию */
      'md:grid-cols-2': true, /* 2 колонки для средних экранов */
      'lg:grid-cols-3': true /* 3 колонки для больших экранов */
    }"
  >

    <div v-for="room in rooms" :key="room.room_id">
      <div class="relative w-full shadow rounded-lg p-4 bg-gray-100 hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out">
        <button
          v-if="props.listType === 'unit'"
          @click="removeFromList(room.room_id)"
          class="absolute -top-2 -right-2 bg-slate-400 text-white shadow-lg rounded-full w-5 h-5 flex items-center justify-center z-10"
          aria-label="Close"
        >
          &times;
        </button>

        <div class="flex items-center justify-between gap-x-4">
          <div class="text-2xl line-clamp-1">{{ room.room_type }}</div>
          <button v-if="props.lists" @click="openAddToListModal(room.room_id)">
            <Lucide class="w-6 h-6 mb-1" icon="BookmarkPlus" />
          </button>
        </div>
        <div v-if="room.hasOwnProperty('active') && room.active == false" class="w-full text-right text-sm text-slate-500">hidden by owner</div>

        <div class="flex flex-col">
          <div class="flex items-center justify-between">
            <div class="flex gap-x-1">
              <Lucide class="w-5 h-5" icon="DollarSign" />
              <div>Price</div>
            </div>
            <div>${{ room.price }}</div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex gap-x-1">
              <Lucide class="w-5 h-5" icon="Zap" />
              <div>Occupancy</div>
            </div>
            <div>{{ room.occupancy >= 0 ? Math.round(room.occupancy) + '%' : 'N/A' }}</div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex gap-x-1">
              <Lucide class="w-5 h-5" icon="TrendingUp" />
              <div>Profit</div>
            </div>
            <div>${{ room.profit }}</div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
</template>