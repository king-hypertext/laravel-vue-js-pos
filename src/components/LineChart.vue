<template>

  <div class="card bg-secondary-subtle shadow-2-strong" v-if="props.data && Object.keys(props.data).length">
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
import { formatCurrency } from '@/utils';

const props = defineProps({
  data: {
    type: Object,
    required: true,
    default: () => { }
  },
  title: {
    type: String,
    default: 'Weekly Sales Trend'.toString().toUpperCase(),
  },
  xLabel: {
    type: String,
    default: 'days of the week'.toString().toUpperCase(),
  },
  yLabel: {
    type: String,
    default: 'quatity sold'.toString().toUpperCase(),
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
  const labels = Object.keys(props.data); // Extract days of the week as labels
  const salesItemsData = Object.values(props.data).map(day => day.sales_items_count); // Extract sales_items_count
  const totalAmountData = Object.values(props.data).map(day => day.total_amount); // Extract total_amount

  // Output for Chart.js
  const chartData = {
    labels: labels,
    datasets: [
      {
        label: 'Sales Items Count',
        data: salesItemsData, // [1, 4, 10, 0, 9, 1, 13]
        borderColor: '#36A2EB', // Blue line
        backgroundColor: 'rgba(54, 162, 235, 0.2)', // Light blue area
        tension: 0.4, // Smooth curves
        yAxisID: 'y1',
        xAzisID: 'x',
        
      },
      {
        label: 'Total Amount (¢)',
        data: totalAmountData,
        borderColor: '#FF6384', // Red line
        backgroundColor: 'rgba(255, 99, 132, 0.2)', // Light red area
        tension: 0.4, // Smooth curves,
        yAxisID: 'y2',
        
      },
    ],
  };

  barChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
    labels: labels,
    datasets: [
      {
        label: 'Sales Items Count',
        data: salesItemsData, // [1, 4, 10, 0, 9, 1, 13]
        borderColor: '#36A2EB', // Blue line
        backgroundColor: 'rgba(54, 162, 235, 0.2)', // Light blue area
        tension: 0.4, // Smooth curves
        yAxisID: 'y1',
        xAzisID: 'x',
        pointBackgroundColor: 'rgb(54, 162, 235)',
        pointBorderWidth: 10,
      },
      {
        label: 'Total Amount (¢)',
        data: totalAmountData,
        borderColor: '#FF6384', // Red line
        backgroundColor: 'rgba(255, 99, 132, 0.2)', // Light red area
        tension: 0.4, // Smooth curves,
        yAxisID: 'y2',
        pointBackgroundColor: 'rgb(255, 99, 132)',
        pointBorderWidth: 5,
      },
    ],
  },
    // data: {
    //   labels: Object.keys(props.data),
    //   datasets: [
    //     {
    //       label: 'Sales Items Count',
    //       data: Object.values(props.data).map(i => i.sales_items_count), // [1, 4, 10, 0, 9, 1, 13]
    //       borderColor: '#36A2EB', // Blue line
    //       backgroundColor: 'rgba(54, 162, 235, 0.2)', // Light blue area
    //       tension: 0.4, // Smooth curves
    //       pointBorderWidth: 5,
    //     },
    //     {
    //       label: 'Total Amount (¢)',
    //       data: Object.values(props.data).filter(i => i.total_amount), // [639.93, 856.72, 11478.8, 0, 11684.86, 704.1, 19393.73]
    //       borderColor: '#FF6384', // Red line
    //       backgroundColor: 'rgba(255, 99, 132, 0.2)', // Light red area
    //       tension: 0.4, // Smooth curves
    //       pointBorderWidth: 5,
    //     },
    //   ],
    // },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: props.title !== '',
          text: props.title,
          font: {
            size: 16,
          },
        },
        legend: {
          position: 'top',
          // labels: {
          //   filter: (legendItem, chartData) => {
          //     // Show only Sales Items Count in the legend, hide Total Amount
          //     return legendItem.text === 'Sales Items Count';
          //   },
          // },
        },
      },
      scales: {
        y1: {
        type: 'linear',
        position: 'left', // Primary axis for 'Sales Items Count'
        beginAtZero: true,
        title:{
          display: true,
          text: 'quantity of items sold'.toString().toUpperCase()
        }
      },
      y2: {
        type: 'linear',
        position: 'right', // Secondary axis for 'Total Amount ($)'
        beginAtZero: true,
        grid: {
          drawOnChartArea: false, // Optional: Prevent grid overlap
        },
        ticks:{
          sampleSize: 5
        },
        title:{
          display: false
        }
      },
        x: {
          title: {
            display: props.xLabel !== '',
            text: props.xLabel,
          },
        },
      },
    },
  });
};

watch(
  () => props.data,
  () => {
    renderChart();
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