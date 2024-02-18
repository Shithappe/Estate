<script setup>
import { ref } from 'vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import CardBookingData from '@/Components/CardBookingData.vue';
import PieChart from '@/Components/PieChart.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    data: Object,
});

const isChart = ref(false);
const changeMode = () => { isChart.value = !isChart.value; }

const strFilter = ref('');
const showItems = ref(props.data);

const viewOnlySelected = ref(false);
const viewOnly = () => {
    viewOnlySelected.value = !viewOnlySelected.value;

    if (viewOnlySelected.value) {
        showItems.value = props.data.filter(obj => obj.selected == true);
    }
    else {
        showItems.value = props.data;
    }
}

const types = [...new Set(props.data.map(obj => obj.type))]
const selectedTypes = ref();

const filterItems = () => {
    showItems.value = props.data.filter(item => item.title.toLowerCase().includes(strFilter.value));
}

const updateSelectedItems = () => {
    props.data.map(function (item) {
        selectedTypes.value.includes(item.type) ? item.selected = true : item.selected = false;
    })
}

function countTypes(data) {
    const typeCounts = {};

    for (const item of data) {
        const type = item.type;
        typeCounts[type] = (typeCounts[type] || 0) + 1;
    }

    return {
        title: 'Types',
        legend: Object.entries(typeCounts).map(([name]) => name),
        data: Object.entries(typeCounts).map(([name, value]) => ({
            name,
            value,
        }))
    };
}
const typesChart = countTypes(props.data);

function countStars(data) {
    const typeCounts = {};

    for (const item of data) {
        const type = item.star;
        typeCounts[type] = (typeCounts[type] || 0) + 1;
    }

    return {
        title: 'Stars',
        legend: Object.entries(typeCounts).map(([name]) => name),
        data: Object.entries(typeCounts).map(([name, value]) => ({
            name,
            value,
        }))
    };
}

console.log(countStars(props.data));
const startsChart = countStars(props.data);

</script>

<template>
    <SimpleAppLayout title="Get Report">
        <div class="flex justify-between px-4 py-1.5">
            <div class="flex gap-x-2 items-end">
                <div class="w-64">
                    <v-select v-model="selectedTypes" :options="types" multiple :searchable="true" placeholder="Select type"
                        @update:modelValue="updateSelectedItems" />
                </div>

                <input type="text" v-model="strFilter" @change="filterItems" placeholder="Search"
                    class="w-64 border border-gray-300 rounded px-2 focus:outline-none focus:z-10 focus:ring focus:border-blue-300 bg-transparent block appearance-none leading-5 transition duration-150 ease-in-out sm:text-sm sm:leading-5">

                <button
                    class="px-2 py-2 rounded-lg shadow hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out text-md"
                    :class="{ 'shadow-lg text-slate-100 bg-black': viewOnlySelected }" @click="viewOnly">
                    View Only Selected
                </button>
            </div>

            <div class="flex gap-x-2">
                <button
                    class="px-2 py-2 rounded-lg shadow hover:shadow-lg hover:text-slate-100 hover:bg-black appearance-none leading-5 transition duration-300 ease-in-out text-md"
                    @click="changeMode">
                    {{ isChart ? 'Additional selection' : 'View chart' }}
                </button>

                <button
                    class="px-2 py-2 rounded-lg shadow shadow-lg text-slate-100 bg-black appearance-none leading-5 transition duration-300 ease-in-out text-md"
                    @click="viewOnly">
                    Download Report
                </button>
            </div>
        </div>

        <div v-if="isChart" class="flex">
            <PieChart 
                class="w-1/2 h-1/2 mx-4" 
                :title=typesChart.title
                :legend=typesChart.legend
                :data="typesChart.data" 
            />
            <PieChart 
                class="w-1/2 h-1/2 mx-4" 
                :title=startsChart.title
                :legend=startsChart.legend
                :data="startsChart.data" 
            />

        </div>

        <div v-else class="my-8 flex flex-wrap justify-center">
            <CardBookingData v-for="(item, index) in showItems" :key="item.id" :item="item"
                @click="() => { props.data[index].selected = !props.data[index].selected }" />
        </div>


    </SimpleAppLayout>
</template>


<style>
.vs__dropdown-toggle {
    height: 38px;
    cursor: pointer;
}

.vs__dropdown-toggle::placeholder {
    color: rgb(255, 0, 0);
}
</style>