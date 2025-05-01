<script setup>
import { faFilePdf } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed, watch } from 'vue'

const props = defineProps({
  totalItems: {
    type: Number,
    required: true,
    default: 0
  },
  currentPage: {
    type: Number,
    required: true,
    default: 1
  },
  thead: {
    type: Array,
    required: true,
    default: ['R1C1', 'R1C2', 'R1C3', 'R1C4', 'R1C5']
  },
  tfoot: {
    type: Object,
    required: false,
  },
  searchable: {
    type: Boolean,
    required: false,
  },
  exportable: {
    type: Boolean,
    required: false,
    default: false
  },
  hasActions: {
    type: Boolean,
    required: false,
    default: true
  },
  isLoading: {
    type: Boolean,
    required: false,
  },
  paginated: {
    type: Boolean,
    required: false,
  },
  itemsPerPage: {
    type: Number,
    required: false,
    default: 15,
  },
});


const totalPages = computed(() => Math.max(1, Math.ceil(props.totalItems / props.itemsPerPage)));

const emit = defineEmits(['itemsPerPage', 'update:itemsPerPage', 'search', 'update:currentPage', 'exportToPDF']); // Added update:itemsPerPage for two-way binding

const previousPage = () => {
  if (props.currentPage > 1) {
    emit('update:currentPage', props.currentPage - 1)
  }
}

const nextPage = () => {
  if (props.currentPage < totalPages.value) {
    emit('update:currentPage', props.currentPage + 1); // Emit the new value
  }
};


const changeItemsPerPage = (p) => {
  let value = p.target.value;
  emit('update:itemsPerPage', value);
}

watch(() => props.currentPage, (newCurrentPage) => {
  emit('update:currentPage', newCurrentPage); // Emit event when page changes
});

watch(() => props.itemsPerPage, (newItemsPerPage) => {
  emit('update:itemsPerPage', newItemsPerPage); // Emit event when itemsPerPage changes
  // props.currentPage = 1; // Reset to first page when itemsPerPage changes
});


</script>

<template>
  <div class="table-responsive">
    <div class="d-flex flex-wrap justify-content-between align-items-center my-2">
      <div class="d-flex align-items-center mb-md-0 me-2">
        <slot v-if="props.searchable" name="search">
        </slot>
        <select v-if="props.searchable" :disabled="props.isLoading" :value="props.itemsPerPage"
          @change="changeItemsPerPage" class="form-select per-page me-2">
          <option value="15">15</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="75">75</option>
          <option value="100">100</option>
          <!-- <option value="150">150</option> -->
          <option value="10000000000000000">All</option>
        </select>
        <!-- </div> -->
        <div v-if="props.exportable" class="d-flex align-items-center">
          <button v-if="props.exportToExcel" title="export table to excel" type="button"
            class="btn shadow-0 me-1 table-action-btn-excel" @click="$emit('exportToExcel')">
            excel
          </button>
          <button title="save data as PDF" type="button" :disabled="props.isLoading"
            class="btn btn-danger shadow-0 me-1 table-action-btn-pdf" @click="$emit('exportToPDF')">
            pdf
          </button>
        </div>
      </div>
      <div v-if="props.hasActions" class="d-flex align-items-center mb-md-0 me-2">
        <slot name="actions"></slot>
      </div>
    </div>
    <table class="table table-sm table-hover table-striped align-middle text-start">
      <thead class="">
        <tr class="text-uppercase">
          <th class="" v-for="(h, i) in props.thead" :key="i" :title="h.title">{{ h.name }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="props.isLoading" v-for="i in 3">
          <td :colspan="props.thead.length">
            <span class="col-12 placeholder-glow">
              <span class="placeholder col-2"></span>
              <span class="placeholder col-4"></span>
              <span class="placeholder col-3"></span>
              <span class="placeholder col-3"></span>
            </span>
          </td>
        </tr>
        <tr v-else-if="props.totalItems === 0">
          <td :colspan="!props.totalItems === 0 ? props.totalItems : props.thead.length" class="text-center">No data
            available</td>
        </tr>
        <slot v-else name="tbody">
        </slot>
      </tbody>
      <tfoot v-if="tfoot">
        <tr>
          <th v-for="th in props.tfoot" :key="th">{{ th }}</th>
        </tr>
      </tfoot>
    </table>
    <!-- optional pagination -->
    <div class="pagination-container" v-if="props.paginated">
      <p>showing {{ props.currentPage }} to {{ totalPages }} of {{ props.totalItems }} items</p>
      <nav aria-label="Page navigation">
        Page {{ props.currentPage }} of {{ totalPages }}
        <ul class="pagination d-inline-flex">
          <li class="page-item" :class="{ disabled: props.currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="previousPage">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
          <li class="page-item mx-1">
            <a class="page-link active" href="#">{{ props.currentPage }}</a>
          </li>
          <li class="page-item" :class="{ disabled: props.currentPage === totalPages }">
            <a class="page-link" href="#" @click.prevent="nextPage">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<style>
tr th:first-child,
tr td:first-child {
  padding: 0.575rem 0.35rem !important;
  width: calc(1.75em + 10px) !important;
  white-space: nowrap !important;
}

tr>td,
tr>th {
  padding: 0.575rem 0.35rem !important;
  text-align: left !important;
}

button.table-action-btn-excel {
  background: rgb(30, 167, 30);
  color: white;
  border: none;
}

button.table-action-btn-pdf {
  background: rgb(221, 60, 60);
  color: white;
  border: none;
}

.loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 60%;
  /* background: rgba(0, 0, 0, 0.15) !important; */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  transition:
    opacity 0.3s,
    visibility 0.3s;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.loader::after {
  content: '';
  width: 50px;
  height: 50px;
  border: 5px solid #ffffff;
  border-top-color: #082a68;
  border-radius: 50%;
  animation: loading 1s ease infinite;
}

@keyframes loading {
  from {
    transform: rotate(0turn);
  }

  to {
    transform: rotate(1turn);
  }
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

select.per-page {
  max-width: 80px;
}
</style>
