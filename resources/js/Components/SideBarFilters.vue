<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  map: {
    type: Boolean,
    default: false,
  },
  cities: Array,
  types: Array,
  facilities: Array
});

const emits = defineEmits(['applyFilters']);

const selectedCity = ref(JSON.parse(localStorage.getItem('selectedCity')));
const selectedTypes = ref(JSON.parse(localStorage.getItem('selectedTypes')));
const selectedFacilities = ref(JSON.parse(localStorage.getItem('selectedFacilities')));
const selectedPrice = ref(JSON.parse(localStorage.getItem('selectedPrice')) ? JSON.parse(localStorage.getItem('selectedPrice')) : { min: 10, max: 20 });


const selectCity = () => {
  localStorage.setItem('selectedCity', JSON.stringify(selectedCity.value));
};

const selectTypes = () => {
  localStorage.setItem('selectedTypes', JSON.stringify(selectedTypes.value));
};

const selectFacilities = () => {
  localStorage.setItem('selectedFacilities', JSON.stringify(selectedFacilities.value));
};

const selectPrice = () => {
  console.log(selectedPrice.value);
  localStorage.setItem('selectedPrice', JSON.stringify(selectedPrice.value));
};

const applyFilters = () => {
  emits('applyFilters');
};

const clearData = (data) => {
  const cleanData = data.filter(element => element && element !== ' ');
  return cleanData;
};

</script>

<template>
  <div
    class="fixed lg:absolute z-10 bottom-0 lg:top-16 w-full lg:w-1/4 lg:h-screen flex flex-col gap-y-2 p-2 lg:border-r bg-white"
    :class="{ 'backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30': props.map }">
    <div>
      <label for="city">Select city</label>
      <v-select v-model="selectedCity" :options="clearData(props.cities)" multiple :searchable="true"
        @update:modelValue="selectCity" />
    </div>

    <div>
      <label for="type">Select type</label>
      <v-select v-model="selectedTypes" :options="clearData(props.types)" multiple :searchable="true"
        @update:modelValue="selectTypes" />
    </div>

    <div>
      <label for="facilities">Additional Properties</label>
      <v-select v-model="selectedFacilities" :reduce="(option) => option.id" :options="props.facilities" label="title"
        multiple :searchable="true" @update:modelValue="selectFacilities" />
    </div>

    <div>
      <label>Price</label>
      <div class="flex">
        <input type="number" placeholder="Min Price" min="0" :max="selectedPrice.max"
          v-model="selectedPrice.min" @change="selectPrice"
          class="border border-gray-300 rounded-l py-2 px-8 focus:outline-none focus:z-10 focus:ring focus:border-blue-300 bg-transparent block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />

        <input type="number" placeholder="Max Price" :min="selectedPrice.min" v-model="selectedPrice.max"
          @update:modelValue="selectPrice"
          class="border border-gray-300 rounded-r py-3 px-8 focus:outline-none focus:ring focus:border-blue-300 bg-transparent block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
      </div>
    </div>

    <button @click="applyFilters" class="mt-6 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Apply
      Filters</button>

  </div>
</template>
