<script setup>
import { ref, computed, onMounted, reactive } from 'vue'

import { storeToRefs } from 'pinia';
import { useSaleStore } from '@/stores/sales';
import DataTable from '@/components/DataTable.vue';
import { formatCurrency, formatDate, truncateText } from '@/utils';
import { faPrint, faSpinner } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import Modal from '@/components/Modal.vue';
import DataTableSearchField from '@/components/DataTableSearchField.vue';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import api from '@/config/axios-config';
import Alert from '@/utils/Notify';
import { useAuthStore } from '@/stores/auth';
import { isSameDay } from 'date-fns';

const theadData = [
  {
    name: '#',
    title: 'Sale ID',
  },
  {
    name: 'receipt no',
    title: 'receipt number'.toLocaleUpperCase(),
  },
  {
    name: 'items count',
    title: 'total items of the sale'.toLocaleUpperCase(),
  },
  {
    name: 'total',
    title: 'total amount of the sale'.toLocaleUpperCase(),
  },
  {
    name: 'payment mode',
    title: 'sale payment method'.toLocaleUpperCase(),
  },
  {
    name: 'customer',
    title: 'the person who purchased the items'.toLocaleUpperCase(),
  },
  {
    name: 'purchase date',
    title: 'transaction date'.toLocaleUpperCase(),
  },
  {
    name: 'sold by',
    title: 'the person who sold the item'.toLocaleUpperCase(),
  },
  {
    name: 'print',
  },
];
const saleStore = useSaleStore();

const { allSales, isLoading } = storeToRefs(saleStore);
const searchValue = ref(null)
const itemsPerPage = ref(15); // Number of items per page
const currentPage = ref(1); // Current page

const displayData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const searchTerm = searchValue.value?.toLowerCase().trim();

  if (searchTerm) {
    const filteredData = allSales.value.filter((item) => {
      return Object.values(item).some((value) => {
        return String(value).toLowerCase().includes(searchTerm);
      });
    });
    return filteredData.slice(start, start + itemsPerPage.value);
  }

  return allSales.value.slice(start, start + itemsPerPage.value);
});

const filterTable = (e) => {
  searchValue.value = e.toString().toLowerCase();
}

const changeItemsPerPage = (page) => {
  // console.log(page);
  itemsPerPage.value = parseInt(page);
}

const isPrintLoading = ref({});

const handlePrintSale = async (sale) => {
  isPrintLoading.value[sale.id] = true;
  try {
    const response = await api.post(`/print-reciept/${sale.id}`);
    Alert.success(response.data.message);
  } catch (error) {
    console.log(error);
  } finally {
    isPrintLoading.value[sale.id] = false
  }

}

const isSaleDetailModalOpen = ref(false);

const saleDetailModalTitle = ref('');

const saleItems = ref({});

const openSaleModal = (sale, number) => {
  isSaleDetailModalOpen.value = true;
  saleDetailModalTitle.value = 'sale #:'.toLocaleUpperCase() + number
  saleItems.value = sale;
}

const closeSaleModal = () => {
  isSaleDetailModalOpen.value = false;
  saleDetailModalTitle.value = '';
  saleItems.value = {}
}

const changePage = (newPage) => {
  currentPage.value = newPage; // Update the reactive currentPage value
};
const docTitle = ref('sales');
const exportToPDF = (title) => {
  const doc = new jsPDF();

  docTitle.value = title ? title : "all sales".toLocaleUpperCase();

  doc.setDisplayMode('fullpage', 'single');
  // Add a custom header
  const addHeader = () => {
    doc.setFontSize(14);
    doc.setTextColor(40);
    title ?
      doc.text(title, doc.internal.pageSize.width / 2, 15, { align: "center" }) :
      doc.text("all sales".toLocaleUpperCase(), doc.internal.pageSize.width / 2, 15, { align: "center" });
  };

  // Add a custom footer with page numbers
  const addFooter = () => {
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      doc.setFontSize(10);
      doc.text(`Page ${i} of ${pageCount}`, 10, doc.internal.pageSize.height - 10); // Align to the left
    }
  };

  // Add the header to the first page
  addHeader();

  const rows = displayData.value.map((sale, i) => [
    i + 1,
    sale.number,
    sale.sale_items_count,
    '¢' + formatCurrency(sale.total_amount),
    String(sale.payment_method).toLocaleUpperCase(),
    sale.customer?.name?.toLocaleUpperCase() || "N/A",
    formatDate(sale.created_at, 'dd-MMM-yy, h:mmaa'),
    sale.user?.username?.toLocaleUpperCase() || "N/A",
  ]);
  const totalAmount = displayData.value.reduce((sum, sale) => sum + parseFloat(sale.total_amount || 0), 0);
  const headers = theadData
    .filter(t => t.name !== 'print')
    .map(t => t.name.toLocaleString().toUpperCase());

  autoTable(doc, {
    head: [headers],
    // headStyles: {
    //   fillColor: '#49505770',
    //   textColor: '#495057',
    // },
    body: rows,
    startY: 20, // Leave space for the header,
    margin: {
      top: 15, left: 5, right: 5, bottom: 10
    },
    bodyStyles: {
      fontSize: 10, // Adjust the font size for the body
      // font: 'Poppins', // Set the font family for the body
      cellPadding: 3, // Adjust cell padding
      // textColor: [0, 0, 0], // Set text color for the body
      rowPageBreak: 'avoid', // Avoid row page breaks
    },
    foot: [[
      '', '', 'Total:', '¢' + formatCurrency(totalAmount.toFixed(2)), '', '', '', ''
    ]], // Custom footer row
    footStyles: {
      fillColor: '#e0e0e0',
      textColor: 'black',
      fontStyle: 'bold',
    },
  });

  addFooter();

  doc.save(docTitle.value + ".pdf");
};

const isOpenFilterModalOpen = ref(false)

const userStore = useAuthStore();

const { employees } = storeToRefs(userStore);

const filterFormData = reactive({
  start_date: '',
  end_date: formatDate(new Date(), 'yyyy-MM-dd'),
  customer: ''
});
const handleFilter = () => {

  const data = saleStore.filterSales(filterFormData.start_date, filterFormData.end_date, filterFormData.customer);

  if (filterFormData.customer && filterFormData.start_date && filterFormData.end_date && !isSameDay(filterFormData.start_date, filterFormData.end_date)) {
    docTitle.value = 'all sales from ('.toUpperCase() + formatDate(filterFormData.start_date, 'EEEE, MMMM dd, yyyy').toLocaleUpperCase() + ' and ' + formatDate(filterFormData.end_date, 'EEEE, MMMM dd, yyyy').toLocaleUpperCase() + ') by: ' + filterFormData.customer.toUpperCase()
  } else if (!filterFormData.customer && isSameDay(filterFormData.start_date, filterFormData.end_date)) {
    docTitle.value = 'all sales for ('.toUpperCase() + formatDate(filterFormData.end_date, 'EEEE, MMMM dd, yyyy').toLocaleUpperCase() + ')';
  } else if (filterFormData.customer && isSameDay(filterFormData.start_date, filterFormData.end_date)) {
    docTitle.value = 'all sales for ('.toUpperCase() + formatDate(filterFormData.end_date, 'EEEE, MMMM dd, yyyy').toLocaleUpperCase() + ') by: ' + filterFormData.customer.toUpperCase();
  }
  else {
    docTitle.value = 'all sales from ('.toUpperCase() + formatDate(filterFormData.start_date, 'EEEE, MMMM dd, yyyy').toLocaleUpperCase() + ' - ' + formatDate(filterFormData.end_date, 'EEEE, MMMM dd, yyyy').toLocaleUpperCase() + ')';
  }
  const newPerPage = Math.max(itemsPerPage.value, Math.ceil(data.length));
  switch (newPerPage) {
    case newPerPage > 15:
      itemsPerPage.value = 25
      break;
    case newPerPage > 25:
      itemsPerPage.value = 50
    case newPerPage > 50:
      itemsPerPage.value = 75
    case newPerPage > 75:
      itemsPerPage.value = 100
    case newPerPage > 100:
      itemsPerPage.value = 1000000
    default:
      itemsPerPage.value = 15
      break;
  }
  if (data.length !== 0) {
    saleStore.sales = data;
    Alert.success('Data filterd and exported successfully');
    exportToPDF(docTitle.value);
    saleStore.fetchSales();
    isOpenFilterModalOpen.value = false;
  } else {
    Alert.error('No sales found within the give date range ...')
  }
}
onMounted(() => {
  saleStore.fetchSales();
  userStore.fetchUsers();
});

</script>

<template>
  <div class="card shadow-1 vh-100">
    <DataTable :is-loading="isLoading" :thead="theadData" :total-items="allSales.length" searchable paginated exportable
      :items-per-page="itemsPerPage" :current-page="currentPage" @update:currentPage="changePage"
      @update:itemsPerPage="changeItemsPerPage" @exportToPDF="exportToPDF">
      <template #actions>
        <button class="btn btn-info" title="Perform deep filter" :disabled="isLoading"
          @click="isOpenFilterModalOpen = true">filter
          sales</button>
      </template>
      <template #search>
        <DataTableSearchField @search="filterTable" />
      </template>
      <template #tbody>
        <tr v-for="(sale, i) in displayData">
          <td>{{ i + 1 }}</td>
          <td>
            {{ sale.number }}
          </td>
          <td>
            <button class="btn btn-sm shadow-0" title="view items" @click="openSaleModal(sale.sale_items, sale.number)">
              {{ sale?.sale_items_count }}
            </button>
          </td>
          <td class="price">
            {{ formatCurrency(sale.total_amount) }}
          </td>
          <td>
            {{ sale?.payment_method.toString().toLocaleUpperCase() }}
          </td>
          <td class="text-capitalize" :title="String(sale.customer?.name).length > 15 ? sale.customer?.name : ''">
            {{ truncateText(sale.customer?.name) }}
          </td>
          <td>
            {{ formatDate(sale.created_at, 'dd-MMM-yy, h:mmaa') }}
          </td>
          <td class="text-capitalize">
            {{ truncateText(sale.user?.username) }}
          </td>
          <td>
            <button :disabled="isPrintLoading[sale.id]" title="print sale receipt" type="button"
              class="btn btn-primary btn-sm" @click="handlePrintSale(sale)">
              <FontAwesomeIcon v-if="!isPrintLoading[sale.id]" :icon="faPrint" />
              <FontAwesomeIcon v-else="isPrintLoading[sale.id]" :icon="faSpinner" spin />
            </button>
          </td>
        </tr>
      </template>
    </DataTable>
  </div>
  <Modal :is-open="isOpenFilterModalOpen" @close="isOpenFilterModalOpen = false"
    modal-title="Filter sales by dates range or user">
    <div class="container">
      <form @submit.prevent="handleFilter">
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="form-group">
              <label for="startDate">Start Date</label>
              <input required v-model="filterFormData.start_date" :max="formatDate(new Date(), 'yyyy-MM-dd')"
                type="date" id="startDate" class="form-control" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="endDate">End Date</label>
              <input required v-model="filterFormData.end_date" :max="formatDate(new Date(), 'yyyy-MM-dd')" type="date"
                id="endDate" class="form-control" />
            </div>
          </div>
        </div>
        <div class="mb-4">
          <select class="form-select" id="customer" aria-label="Select Customer" v-model="filterFormData.customer">
            <option value="" selected>Select User</option>
            <option v-for="customer in employees" :key="customer.id" :value="customer.username">
              {{ customer.username }}
            </option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" title="Filtered data will be saved as PDF">filter & save</button>
      </form>
    </div>
  </Modal>

  <Modal :is-open="isSaleDetailModalOpen" :modal-title="saleDetailModalTitle" @close="closeSaleModal">
    <table class="display table align-middle">
      <thead class="text-uppercase">
        <tr class="fw-semibold">
          <th>#</th>
          <th>Item</th>
          <th>Qty</th>
          <th>Price</th>
          <th>SubTotal</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(sale_item, index) in saleItems">
          <td>{{ index + 1 }}</td>
          <td>{{ sale_item.product?.name }}</td>
          <td>{{ sale_item.quantity }}</td>
          <td class="price">{{ formatCurrency(sale_item.price) }}</td>
          <td class="price">
            {{ formatCurrency(sale_item.price * sale_item.quantity * 1) }}
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4">Total:</th>
          <th style="text-align: left" class="price">
            {{formatCurrency(saleItems.reduce((sum, item) => sum + item.total, 0.00))}}</th>
        </tr>
      </tfoot>
    </table>
  </Modal>
</template>

<style scoped></style>
