<script setup>
import { ref, defineProps, watch, onMounted } from 'vue';
import Lucide from '@/Components/Lucide.vue';
import Hammer from 'hammerjs';

const props = defineProps({
    mode: Object
})

const emits = defineEmits(['closeBottom']);

const isBottomSheetOpen = ref(1);

const calculateTranslateY = () => {
    if (isBottomSheetOpen.value == 0) return 90;
    if (isBottomSheetOpen.value == 1) return 60;
    if (isBottomSheetOpen.value == 2) return 5;
};

watch(() => props.mode, () => {
    isBottomSheetOpen.value = 1;
});

onMounted(() => {
    const hammer = new Hammer(document.getElementById('bottom-sheet'));

    hammer.get('swipe').set({ direction: Hammer.DIRECTION_ALL });

    hammer.on('swipeup', function () {
        if (isBottomSheetOpen.value < 2) isBottomSheetOpen.value += 1;
    });
    hammer.on('swipedown', function () {
        if (isBottomSheetOpen.value > 0) isBottomSheetOpen.value -= 1;
    });
});
</script>

<template>
    <div
      id="bottom-sheet"
      class="h-full fixed bottom-0 left-0 right-0 z-50 flex"
      :style="{ transform: `translateY(${calculateTranslateY()}%)` }"
    >
      <div class="relative flex flex-col">
        <div v-if="$slots.top" class="sticky top-0">
          <slot name="top" />
        </div>
  
        <div class="pt-2 mt-24 bg-gray-200 rounded-lg shadow-lg backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 shadow-lg">
          <div class="absolute -top-4 right-2 bg-gray-200 rounded-lg shadow-lg" @click="() => { emits('closeBottom') }">
            <Lucide class="text-gray-700 w-8 h-8" icon="X" />
          </div>
          <div class="h-2 w-1/3 mx-auto bg-gray-200 shadow-lg rounded-lg"></div>
          <slot name="body" class="overflow-x-hidden overflow-y-auto" />
        </div>
  
      </div>
    </div>
  </template>
  
  
<style scoped>
#bottom-sheet {
    transition: transform 0.3s ease-in-out;
    transform: translateY(100%);
}
</style>