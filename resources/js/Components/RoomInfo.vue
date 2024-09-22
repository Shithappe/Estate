<script setup>
import { ref } from 'vue';
import Lucide from '@/Components/Lucide.vue';
import AddToListModal from '@/Components/AddToListModal.vue';


const props = defineProps({
  rooms: {
    type: Array,
    required: true
  },
  lists: Object,
  listId: Number,
  auth: Object,
});


const showAddToListModal = ref(false);
const selectedRoomId = ref(0);
const closeAddToListModal = () => { 
  showAddToListModal.value = false; 
  selectedRoomId.value = 0; 
};

const emit = defineEmits(['updateLists']);

const openAddToListModal = (roomId) => {
  selectedRoomId.value = roomId;
  showAddToListModal.value = true;
};

const removeFromList = async (id) => {
  try {
      await axios.delete(`/api/list_item/${props.listId}/${id}`);
    } catch (error) {
        console.error(error);
    }
}
</script>

<template>
  <div>
    <h3 v-if="rooms[0].booking_title" class="text-xl font-semibold mt-4 mb-2">{{ rooms[0].booking_title }}</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-4 mt-2 mb-4">
      <div v-for="room in rooms" :key="room.room_id">
        <div class="relative flex justify-between shadow rounded-lg p-4 bg-gray-100 shadow rounded-md hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out">
          <button
            v-if="!props.lists"
            @click="removeFromList(room.room_id)"
            class="absolute -top-2 -right-2 bg-slate-400 text-white shadow-lg rounded-full w-5 h-5 flex items-center justify-center z-10"
            aria-label="Close"
          >
            &times;
          </button>
          <div>
            <div class="text-2xl">
              {{ room.occupancy > 0 ? Math.round(room.occupancy) + '%' : 'N/A' }}
            </div>
            <div>{{ room.room_type }}</div>
          </div>
          <div class="flex flex-col justify-between items-end">
            <div v-if="room.price" class="text-xl">${{ room.price }}</div>
            <div class="flex items-center gap-x-2">
              <div v-if="room.hasOwnProperty('active') && room.active == false" class="text-sm">
                *hidden by owner
              </div>
  
              <button v-if="props.lists" @click="openAddToListModal(room.room_id)">
                <Lucide class="w-5 h-5 mt-1.5" icon="ChevronDown" />
              </button>
            </div>

          </div>
        </div>
      </div>

      <AddToListModal
        v-if="props.lists"
        :lists="props.lists.unit"
        :itemId="selectedRoomId"
        type='unit'
        :auth="auth"
        :show="showAddToListModal"
        @close="closeAddToListModal"
        @updateLists="newList => {
            emit('updateLists', newList);
        }"
      />

    </div>
  </div>
</template>