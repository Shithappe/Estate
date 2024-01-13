<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  cities: Array,
  types: Array,
  facilities: Array,
  selectedCity: Array,
  selectedType: Array,
  selectedFacilities: Array,
});

const emits = defineEmits(['updateSelectedCity', 'updateSelectedTypes', 'updateSelectedFacilities', 'applyFilters']);

const selectedCity = ref(props.selectedCity);
const selectedTypes = ref(props.selectedType);
const selectedFacilities = ref(props.selectedFacilities);

const ar = ['1', 'asd', '2']

// Вызывать sortAZ после определения selectedFacilities

const selectCity = () => {
  emits('updateSelectedCity', selectedCity.value);
};

const selectTypes = () => {
  emits('updateSelectedTypes', selectedTypes.value);
};

const selectFacilities = () => {
  emits('updateSelectedFacilities', selectedFacilities.value);
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
  <div class="fixed lg:absolute z-10 bottom-0 lg:top-16 w-full lg:w-1/4 lg:h-screen flex flex-col gap-y-2 p-2 lg:border-r bg-white">
    <div>
      <label for="city">Select city</label>
      <v-select v-model="selectedCity" :options="clearData(props.cities)" multiple :searchable="true" @update:modelValue="selectCity" />
    </div>

    <div>
      <label for="type">Select type</label>
      <v-select v-model="selectedTypes" :options="clearData(props.types)" multiple :searchable="true" @update:modelValue="selectTypes" />
    </div>
    
    <div>
      <label for="facilities">Additional Properties</label>
      <v-select 
        v-model="selectedFacilities" 
        :reduce="(option) => option.id"
        :options="props.facilities" 
        label="title" multiple :searchable="true" 
        @update:modelValue="selectFacilities" 
      />
    </div>

    <button @click="applyFilters" class="mt-6 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Apply Filters</button>

  </div>
</template>
