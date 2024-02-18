<template>
  <div>
    <v-chart class="chart pt-2 bg-gray-100 rounded-lg" :option="option" autoresize />
  </div>
</template>

<script setup>
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { PieChart, BarChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
} from 'echarts/components';
import VChart, { THEME_KEY } from 'vue-echarts';
import { ref, provide } from 'vue';

use([
  CanvasRenderer,
  PieChart,
  BarChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
]);

// provide(THEME_KEY, 'dark');

const props = defineProps({
    title: String,
    legend: Array,
    data: Object,
});

const option = ref({
  title: {
    text: props.title,
    left: 'center',
  },
  tooltip: {
    trigger: 'item',
    formatter: '{b} : {c} ({d}%)',
  },
  legend: {
    orient: 'horizontal',
    top: 'bottom',
    left: 'center',
    data: props.legend,
  },
  series: [
    {
      name: props.title,
      type: 'pie',
      radius: '80%',
      center: ['50%', '50%'],
      data: props.data,
    },
  ],
});
</script>

<style scoped>
.chart {
  height: 100vh;
  width: 100vh;
  /* padding: 1rem; */
}
</style>
