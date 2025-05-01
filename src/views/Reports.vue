<script setup>
import { ref, computed } from 'vue';

const selectedReport = ref('sales');
const selectedDateRange = ref('today');
const startDate = ref('');
const endDate = ref('');
const reportData = ref([]);
const loading = ref(false);
const reportGenerated = ref(false);

const reportTitle = computed(() => {
    switch (selectedReport.value) {
        case 'sales':
            return 'Sales Report';
        case 'inventory':
            return 'Inventory Report';
        case 'transactions':
            return 'Transaction Report';
        case 'employee':
            return 'Employee Report';
        default:
            return 'Report';
    }
});

const reportHeaders = computed(() => {
    if (reportData.value.length > 0) {
        return Object.keys(reportData.value[0]);
    }
    return [];
});

const generateReport = async () => {
    loading.value = true;
    reportData.value = [];
    reportGenerated.value = true;

    // Simulate API call to fetch report data
    await new Promise(resolve => setTimeout(resolve, 1500));

    // Replace this with your actual API call based on selectedReport and date range
    let mockData = [];
    switch (selectedReport.value) {
        case 'sales':
            mockData = [
                { Product: 'Item A', Quantity: 10, Price: 2.50, Total: 25.00 },
                { Product: 'Item B', Quantity: 5, Price: 5.00, Total: 25.00 },
            ];
            break;
        case 'inventory':
            mockData = [
                { Product: 'Item A', Stock: 50, LowStock: false },
                { Product: 'Item C', Stock: 5, LowStock: true },
            ];
            break;
        case 'transactions':
            mockData = [
                { ID: 1, Date: '2025-04-29', Amount: 15.00, Payment: 'Cash' },
                { ID: 2, Date: '2025-04-28', Amount: 32.50, Payment: 'Card' },
            ];
            break;
        case 'employee':
            mockData = [
                { Name: 'John Doe', Sales: 120.00, Transactions: 15 },
                { Name: 'Jane Smith', Sales: 95.50, Transactions: 10 },
            ];
            break;
    }

    reportData.value = mockData;
    loading.value = false;
};

const exportReport = () => {
    if (reportData.value.length > 0) {
        // Implement your export functionality here
        alert('Exporting report data...');
        console.log('Report Data to Export:', reportData.value);
    }
};
</script>

<template>
    <div class="card shadow-1 vh-100">
        reports page
        <div class="report-page">
            <h1 class="mb-4">POS System Reports</h1>

            <div class="controls mb-4">
                <div class="filter form-group">
                    <label for="report-type" class="form-label">Report Type:</label>
                    <select id="report-type" v-model="selectedReport" class="form-select">
                        <option value="sales">Sales Report</option>
                        <option value="inventory">Inventory Report</option>
                        <option value="transactions">Transaction Report</option>
                        <option value="employee">Employee Report</option>
                    </select>
                </div>

                <div class="filter form-group">
                    <label for="date-range" class="form-label">Date Range:</label>
                    <select id="date-range" v-model="selectedDateRange" class="form-select">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this-week">This Week</option>
                        <option value="last-week">Last Week</option>
                        <option value="this-month">This Month</option>
                        <option value="custom">Custom</option>
                    </select>
                    <div v-if="selectedDateRange === 'custom'" class="custom-date-range mt-2">
                        <div class="form-group">
                            <label for="start-date" class="form-label">Start Date:</label>
                            <input type="date" id="start-date" v-model="startDate" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="end-date" class="form-label">End Date:</label>
                            <input type="date" id="end-date" v-model="endDate" class="form-control">
                        </div>
                    </div>
                </div>

                <button @click="generateReport" class="btn btn-primary">Generate Report</button>
                <button @click="exportReport" :disabled="!reportData.length" class="btn btn-info ms-2">Export</button>
            </div>

            <div v-if="loading" class="loading text-muted">
                Loading report data...
            </div>

            <div v-else-if="reportData.length > 0" class="report-data mt-4">
                <h2 class="mb-3">{{ reportTitle }}</h2>
                <table class="table table-striped table-bordered">
                    <thead class="bg-light">
                        <tr v-if="reportHeaders.length > 0">
                            <th v-for="header in reportHeaders" :key="header">{{ header }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in reportData" :key="index">
                            <td v-for="value in item" :key="value">{{ value }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else-if="reportGenerated && reportData.length === 0" class="no-data alert alert-warning mt-4"
                role="alert">
                No data available for the selected criteria.
            </div>
        </div>
    </div>
</template>

<style scoped>
.report-page {
    padding: 20px;
}

.controls {
    display: flex;
    gap: 20px;
    align-items: center;
}

.filter {
    flex-grow: 1;
    /* Allows filters to take up available space */
    max-width: 300px;
    /* Optional: set a maximum width for filters */
}

.custom-date-range {
    display: flex;
    gap: 10px;
}

.loading {
    font-style: italic;
}

.report-data {
    margin-top: 20px;
}
</style>