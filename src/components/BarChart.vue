<template>

  <div class="card bg-secondary-subtle shadow-2-strong" v-if="props.data && props.data.length">
    <div class="card-body px-2">
      <div class="chart-container">
        <canvas ref="barChartCanvas"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { Chart } from 'chart.js/auto';

const props = defineProps({
  data: {
    type: [Array, Object],
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'Bar Chart',
  },
  xAxisLabel: {
    type: String,
    default: 'X Axis',
  },
  yAxisLabel: {
    type: String,
    default: 'Y Axis',
  },
});

const barChartCanvas = ref(null);
let barChartInstance = null;

const renderChart = () => {
  if (!barChartCanvas.value) return;

  if (barChartInstance) {
    barChartInstance.destroy();
  }

  const ctx = barChartCanvas.value.getContext('2d');

  barChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: props.data.map(product => product.name.toString().toUpperCase()),
      datasets: [{
        label: 'total items sold',
        data: props.data.map(product => product.total_sold),
        backgroundColor: [
          '#FF6384', // Soft Red
          '#36A2EB', // Vibrant Blue
          '#FFCE56', // Sunny Yellow
          '#4BC0C0', // Aqua Green
          '#9966FF', // Lavender Purple
          '#FF9F40', // Orange
          '#C9CBCF', // Light Gray
          '#E91E63', // Bright Pink
          '#8BC34A', // Lime Green
          '#607D8B'  // Steel Gray
        ]
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: props.title !== '',
          text: props.title.toUpperCase(),
          font: {
            size: 16,
          },
        },
        legend: {
          // display: false,
          position: 'left',
        },
      },
      // scales: {
      //   y: {
      //     beginAtZero: true,
      //     title: {
      //       display: props.yAxisLabel !== '',
      //       text: props.yAxisLabel,
      //     },
      //   },
      //   x: {
      //     title: {
      //       display: props.xAxisLabel !== '',
      //       text: props.xAxisLabel,
      //     },
      //   },
      // },
    },
  });
};

watch(
  () => props.data,
  (newData) => {
    if (newData && Array.isArray(newData)) {
      renderChart();
    }
  },
  { deep: true }
);


onMounted(() => {
  renderChart();
});

onBeforeUnmount(() => {
  if (barChartInstance) {
    barChartInstance.destroy();
  }
});
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 300px;
}
</style>