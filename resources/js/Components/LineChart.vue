<template>
  <div>
    <v-chart class="chart pt-2 bg-gray-100 rounded-lg" :option="option" autoresize />
  </div>
</template>

<script setup>
import { ref, provide } from 'vue';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
} from 'echarts/components';
import VChart, { THEME_KEY } from 'vue-echarts';

import { GridComponent } from 'echarts/components'

use([GridComponent, LineChart, CanvasRenderer])

// use([
//   CanvasRenderer,
//   LineChart,
//   TitleComponent,
//   TooltipComponent,
//   LegendComponent,
//   XAxis,
//   // yAxis
// ]);

// provide(THEME_KEY, 'dark');

const props = defineProps({
    title: String,
    legend: Array,
    data: Object,
});

const option = ref({
  xAxis: {
    type: 'category',
    data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  },
  // yAxis: {
  //   type: 'value'
  // },
  series: [
    {
      data: [820, 932, 901, 934, 1290, 1330, 1320],
      type: 'line',
      smooth: true
    }
  ]
});
</script>

<style scoped>
.chart {
  height: 100vh;
  width: 100vh;
  /* padding: 1rem; */
}
</style>
