<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    cities: Array,
    types: Array,
});

const emits = defineEmits(['updateSelectedCity', 'updateSelectedTypes']);



const selectedCity = ref([]);
const selectedTypes = ref([]);

const selectCity = () => {
  emits('updateSelectedCity', selectedCity.value); 
}
const selectTypes = () => {
  emits('updateSelectedTypes', selectedTypes.value); 
}

const clearData = (data) => {
  const cleanData = []
  data.forEach(element => {
    if (element && element != ' '){
      cleanData.push(element)
    }
  });

  return cleanData;
}

</script>

<template>
  <div class="absolute w-1/4 h-screen flex flex-col gap-y-2 p-2 border-r">
    <div>
      <label for="city">Select city</label>
      <v-select v-model="selectedCity" :options="clearData(props.cities)" label="city" multiple :searchable="true" @update:modelValue="selectCity" />
    </div>

    <div>
      <label for="type">Select type</label>
      <v-select v-model="selectedTypes" :options="clearData(props.types)" label="type" multiple :searchable="true" @update:modelValue="selectTypes" />
    </div>
    <button class="mt-6 p-3 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Apply Filters</button>
  </div>

</template>
