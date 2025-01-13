<script setup>
import { ref, computed, onMounted } from 'vue';
import Lucide from '@/Components/Lucide.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  show: Boolean,
  map: {
    type: Boolean,
    default: false,
  },
  countries: Object,
  cities: Array,
  types: Array,
  facilities: Array
});


const emits = defineEmits(['applyFilters', 'closeFilters']);

const selectedCountry = ref(JSON.parse(localStorage.getItem('selectedCountry')));
const selectedCity = ref(JSON.parse(localStorage.getItem('selectedCity')));
const selectedTypes = ref(JSON.parse(localStorage.getItem('selectedTypes')));
const selectedFacilities = ref(JSON.parse(localStorage.getItem('selectedFacilities')));
const selectedPrice = ref(JSON.parse(localStorage.getItem('selectedPrice')) ? JSON.parse(localStorage.getItem('selectedPrice')) : 
  { 
    min_min: null, min_max: null, // min price
    max_min: null, max_max: null  // max price
  });
const selectedSort = ref(JSON.parse(localStorage.getItem('selectedSort')) || { value: null, orderBy: 'desc' });

const toggleSort = (value) => {
  if (!selectedSort.value) {
    selectedSort.value = { value: null, orderBy: 'desc' };
  }

  if (selectedSort.value.value === value) {
    if (selectedSort.value.orderBy === 'desc') {
      selectedSort.value.orderBy = 'asc';
    } else {
      selectedSort.value = { value: null, orderBy: 'desc' };
    }
  } else {
    selectedSort.value = { value, orderBy: 'desc' };
  }

  localStorage.setItem('selectedSort', JSON.stringify(selectedSort.value));
};

const selectCountry = () => {
  localStorage.setItem('selectedCountry', JSON.stringify(selectedCountry.value));
};

const selectCity = () => {
  localStorage.setItem('selectedCity', JSON.stringify(selectedCity.value));
};

const selectTypes = () => {
  localStorage.setItem('selectedTypes', JSON.stringify(selectedTypes.value));
};

const selectFacilities = () => {
  localStorage.setItem('selectedFacilities', JSON.stringify(selectedFacilities.value));
};

const validatePrice = () => {
      const { min_min, min_max, max_min, max_max } = selectedPrice.value;

      // Ensure logical order of ranges
      if (min_min < 0) selectedPrice.value.min_min = 0;
      if (min_max < min_min) selectedPrice.value.min_max = min_min;
      if (max_min < min_max) selectedPrice.value.max_min = min_max;
      if (max_max < max_min) selectedPrice.value.max_max = max_min;
    }
const selectPrice = () => {
  validatePrice();
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

const getCities = computed(() => {
  if (selectedCountry.value) return props.countries[selectedCountry.value];
  else return Object.values(Object.values(props.countries).flat());
});

const isDesktop = window.matchMedia('(min-width: 1024px)').matches;

const enterActiveClass = computed(() =>
      isDesktop ? 'transition ease-out duration-300' : 'transition ease-out duration-500'
    );
const enterFromClass = computed(() =>
    isDesktop ? 'transform -translate-x-full opacity-0' : 'transform translate-y-full opacity-0'
);
const enterToClass = computed(() =>
    isDesktop ? 'transform -translate-x-0 opacity-100' : 'transform translate-y-0 opacity-100'
);
const leaveActiveClass = computed(() =>
    isDesktop ? 'transition ease-in duration-300' : 'transition ease-in duration-500'
);
const leaveFromClass = computed(() =>
    isDesktop ? 'transform -translate-x-0 opacity-100' : 'transform translate-y-0 opacity-100'
);
const leaveToClass = computed(() =>
    isDesktop ? 'transform -translate-x-full opacity-0' : 'transform translate-y-full opacity-0'
);

let updatedCountries = Object.keys(props.countries).filter(country => country !== "").map(country => {
    if (country === "Thailand" || country === "Spain") {
        return country + " beta";
    }
    return country;
});

</script>

<template>
  <transition
            :enter-active-class="enterActiveClass"
            :enter-from-class="enterFromClass"
            :enter-to-class="enterToClass"
            :leave-active-class="leaveActiveClass"
            :leave-from-class="leaveFromClass"
            :leave-to-class="leaveToClass"
        >
  <div v-if="props.show"
    class="fixed lg:absolute z-10 bottom-0 w-full lg:w-1/4 min-w-96 lg:h-screen flex flex-col gap-y-2 p-2 pr-4 lg:border-r bg-white"
    :class="{ 'lg:top-0 backdrop-filter backdrop-blur-md bg-gray-400 bg-opacity-30': props.map, 'lg:top-16': isDesktop && !props.map }">
    <div v-if="!isDesktop" class="absolute -top-4 right-2 bg-gray-200 rounded-lg shadow-lg" @click="() => {emits('closeFilters')}">
      <Lucide class="text-gray-700 w-8 h-8" icon="X" />
    </div>

    <div>
      <label for="country">Select country</label>
      <v-select v-model="selectedCountry" :options="updatedCountries"
      @update:modelValue="selectCountry" />
    </div>

    <div>
      <label for="city">Select city</label>
      <v-select v-model="selectedCity" :options="getCities" multiple :searchable="true"
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
      <label>Min Price</label>
      <div class="flex">
        <input type="number" placeholder="Min" min="0" :max="selectedPrice.min_max"
          v-model="selectedPrice.min_min" @change="selectPrice"
          class="border border-gray-300 rounded-l py-2 px-8 focus:outline-none focus:z-10 focus:ring focus:border-blue-300 bg-transparent block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />

        <input type="number" placeholder="Max" :min="selectedPrice.min_min && 0" :max="selectedPrice.max_min"
          v-model="selectedPrice.min_max" @change="selectPrice"
          class="border border-gray-300 rounded-r py-3 px-8 focus:outline-none focus:ring focus:border-blue-300 bg-transparent block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
      </div>
    </div>
    <div>
      <label>Max Price</label>
      <div class="flex">
        <input type="number" placeholder="Min" :min="selectedPrice.min_max && selectedPrice.min_min && 0" :max="selectedPrice.max_max"
          v-model="selectedPrice.max_min" @change="selectPrice"
          class="border border-gray-300 rounded-l py-2 px-8 focus:outline-none focus:z-10 focus:ring focus:border-blue-300 bg-transparent block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />

        <input type="number" placeholder="Max" :min="selectedPrice.max_min && selectedPrice.min_max && selectedPrice.min_min && 0" 
          v-model="selectedPrice.max_max" @change="selectPrice"
          class="border border-gray-300 rounded-r py-3 px-8 focus:outline-none focus:ring focus:border-blue-300 bg-transparent block w-full appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
      </div>
    </div>

    <div>
    <label>Sort by</label>
    <div class="w-full flex flex-wrap gap-1 text-gray-600">
      <!-- ['occupancy', 'price', 'room_type', 'room_count', 'rate'] -->
      <span
        v-for="option in ['occupancy', 'price', 'room_type', 'room_count']"
        :key="option"
        class="px-3 py-1.5 border border-gray-300 rounded cursor-pointer flex items-center gap-2"
        @click="toggleSort(option)"
        :class="{
          'text-white bg-black': selectedSort.value === option,
        }"
      >
        {{ option.replace('_', ' ') }}
        <Lucide
          v-if="selectedSort.value === option"
          :icon="selectedSort.orderBy == 'asc' ? 'ArrowDown' : 'ArrowUp'"
          class="w-4 h-4"
        />
      </span>
    </div>
  </div>

    <button @click="applyFilters" class="mt-6 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Apply
      Filters</button>

  </div>
  </transition>
</template>
