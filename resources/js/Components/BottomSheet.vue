<script setup>
import { ref, defineProps, watch, onMounted } from 'vue';
import Hammer from 'hammerjs';

const props = defineProps({
    mode: Object
})

const isBottomSheetOpen = ref(1);

const calculateTranslateY = () => {
    if (isBottomSheetOpen.value == 0) return 95;
    if (isBottomSheetOpen.value == 1) return 60;
    if (isBottomSheetOpen.value == 2) return 5;
};

watch(() => props.mode, () => {
    isBottomSheetOpen.value = 1;
    });

onMounted(() => {
    const bottomSheet = document.getElementById('bottom-sheet');

    const hammer = new Hammer(bottomSheet);
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
    <div id="bottom-sheet"
        class="h-full fixed bottom-0 left-0 right-0 z-50 flex backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30 shadow-lg overflow-x-hidden overflow-y-auto"
        :style="{ transform: `translateY(${calculateTranslateY()}%)` }">
        <div class="relative flex-1">
            <!-- <div class="py-2">
                <div class="h-2 w-1/3 mx-auto bg-gray-200 shadow-lg rounded-lg"></div>
            </div> -->
            <slot />
        </div>
    </div>
</template>
  
<style scoped>
#bottom-sheet {
    transition: transform 0.3s ease-in-out;
    transform: translateY(100%);
}
</style>