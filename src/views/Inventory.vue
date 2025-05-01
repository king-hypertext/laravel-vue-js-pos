<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPenToSquare, faSpinner, faTrashAlt } from '@fortawesome/free-solid-svg-icons'
import Alert from '@/utils/Notify'
import { storeToRefs } from 'pinia'
import { formatCurrency, formatDate, truncateText } from '@/utils'
import { useProductsStore } from '@/stores/products'
import DataTable from '@/components/DataTable.vue'
import DataTableSearchField from '@/components/DataTableSearchField.vue'
import FormModal from '@/components/FormModal.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import { useAuthStore } from '@/stores/auth'
import { isBefore, parseISO } from 'date-fns'

const theadData = [
  {
    name: '#',
    sortable: true,
  },
  {
    name: 'name',
    sortable: true,
  },
  {
    name: 'price',
    sortable: true,
  },
  {
    name: 'quantity',
    sortable: true,
  },
  {
    name: 'category',
    sortable: true,
  },
  {
    name: 'supplier',
    sortable: true,
  },
  {
    name: 'shelf no',
    sortable: true,
  },
  {
    name: 'expiry date',
    sortable: true,
  },
  {
    name: 'actions',
    sortable: false,
  },
];

const productStore = useProductsStore();

const { products, isLoading, errors, isUpdating, isAdding, isEditing, isDeleting } = storeToRefs(productStore);

const searchValue = ref('')

const itemsPerPage = ref(15);

const changeItemsPerPage = (page) => {
  itemsPerPage.value = parseInt(page)
}
const addItemFormData = reactive({
  name: '',
  price: null,
  cost_price: null,
  expiry_date: null,
  bar_code: '',
  supplier: '',
  brand: '',
  category: '',
  shell_location: ''
});

const resetForm = () => {
  Object.assign(addItemFormData, initialFormData);
};
const initialFormData = { ...addItemFormData }; // Store the initial state

const isAddItemModalOpen = ref(false);
const addItemModalTitle = ref('add new item');
const EditItemModalTitle = ref('edit new item');

const addNewItem = async () => {
  try {
    await productStore.addProduct(addItemFormData);
    resetForm();
    Alert.success('Item added successfully');
  } catch (error) {
    console.log(error);
  }
}

const docTitle = ref('sales');
onMounted(() => {
  productStore.fetchProducts();
});

const selectedItem = ref({
  id: null,
  name: '',
  price: '',
  cost_price: '',
  expiry_date: '',
  bar_code: '',
  supplier: '',
  brand: '',
  category: '',
  location: ''
});

const isEditProductModalOpen = ref(false);

const openEditProductModal = (item) => {
  EditItemModalTitle.value = 'edit: ' + item.name;
  isEditProductModalOpen.value = true;
  selectedItem.value.id = item.id;
  Object.assign(EditProductData, {
    name: item.name,
    price: item.current_price?.price,
    cost_price: item.current_price?.cost_price,
    expiry_date: formatDate(item.expiry_date, 'yyyy-MM-dd'),
    bar_code: item.bar_code,
    supplier: item.supplier,
    brand: item.brand,
    category: item.category,
    location: item.location,
  });
}
const EditProductData = reactive({
  name: '',
  price: '',
  cost_price: '',
  expiry_date: '',
  bar_code: '',
  supplier: '',
  brand: '',
  category: '',
  location: '',
});
const updateItem = async () => {
  try {
    if (!selectedItem.value.id) {
      console.log(selectedItem.value);
      return;
    }
    await productStore.updateProduct(selectedItem.value.id, EditProductData);
    Alert.success('Product updated successfully');
    selectedItem.value.id = null;
    isEditProductModalOpen.value = false; // Close modal on success
  } catch (error) {
    console.log(error);
  }
}

const authStore = useAuthStore();

const deleteItem = async (productId) => {
  const role = authStore.user.role;
  if (!role == 'manager') {
    Alert.error('You are not allowed to perform this action.');
    return;
  }
  if (!confirm('Delete Item?')) return;
  try {
    await productStore.deleteProduct(productId)
    Alert.success('Item removed successfuly')
  } catch (error) {
    console.log(error);
    Alert.error(error)
  }
  console.log(productId);

}

const tableTitle = ref(null);

const currentPage = ref(1); // Current page

const isFiltered = ref(false);
const filteredValue = ref(null);

const displayData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const searchTerm = searchValue.value?.toLowerCase().trim();

  if (searchTerm) {
    const filteredData = products.value.filter((item) =>
      Object.values(item).some((value) =>
        String(value).toLowerCase().includes(searchTerm)
      )
    );
    return filteredData.slice(start, start + itemsPerPage.value);
  }

  if (isFiltered.value) {
    let filteredProducts;
    switch (filteredValue.value) {
      case 'out_of_stock':
        filteredProducts = products.value.filter((product) => product.quantity === 0);
        break;
      case 'low_stock':
        filteredProducts = products.value.filter((product) => product.quantity <= 5);
        break;
      case 'expired':
        filteredProducts = products.value.filter((product) => product.expiry_date && isBefore(parseISO(product.expiry_date), new Date()));
        break;
      default:
        filteredProducts = products.value; // Or an empty array, depending on desired behavior
        break;
    }
    return filteredProducts.slice(start, start + itemsPerPage.value);
  }

  return products.value.slice(start, start + itemsPerPage.value);
});

const filterTable = (e) => {
  searchValue.value = e.toString().toLowerCase();
}


const changePage = (newPage) => {
  currentPage.value = newPage; // Update the reactive currentPage value
};

const exportToPDF = () => {

  const doc = new jsPDF({
    orientation: 'landscape',
    unit: 'pt'
  });

  const title = "items list: ".toLocaleUpperCase() + formatDate(new Date(), 'dd-MM-yy');
  doc.setDisplayMode('fullpage', 'single');
  // Add a custom header
  const addHeader = () => {
    doc.setFontSize(14);
    doc.setTextColor(40);
    doc.text(title, doc.internal.pageSize.width / 2, 20, { align: "center" })
  };

  // Add a custom footer with page numbers
  const addFooter = () => {
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      doc.setFontSize(10);
      doc.text(`Page ${i} of ${pageCount}`, 10, doc.internal.pageSize.height - 20); // Align to the left
    }
  };

  // Add the header to the first page
  addHeader();

  const rows = displayData.value.map((item, i) => [
    i + 1,
    item.name.toLocaleString().toLocaleUpperCase(),
    '¢' + formatCurrency(item.current_price?.price),
    item.quantity,
    String(item.category ?? 'N/A').toLocaleUpperCase(),
    String(item.supplier ?? 'N/A').toLocaleUpperCase(),
    String(item.location ?? 'N/A').toString().toUpperCase(),
    new Date(item?.expiry_date).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  ]);
  const headers = theadData
    .filter(t => t.sortable == true)
    .map(t => t.name.toString().toUpperCase());
  autoTable(doc, {

    head: [headers],
    // headStyles: {
    //   fillColor: '#49505770',
    //   textColor: '#495057',
    // },
    body: rows,
    startY: 45, // Leave space for the header,
    margin: 15,
    bodyStyles: {
      fontSize: 10, // Adjust the font size for the body
      // font: 'Poppins', // Set the font family for the body
      cellPadding: 3, // Adjust cell padding
      // textColor: [0, 0, 0], // Set text color for the body
      rowPageBreak: 'avoid', // Avoid row page breaks
    },
    // foot: [[
    //   '', '', 'Total:', '¢' + formatCurrency(totalAmount.toFixed(2)), '', '', '', ''
    // ]], // Custom footer row
    footStyles: {
      fillColor: '#e0e0e0',
      textColor: 'black',
      fontStyle: 'bold',
    },
  });

  addFooter();

  doc.save(title + ".pdf");
};

const isItemQuantityModalOpen = ref(false);
const productToUpdateStockData = reactive({
  quantity: '',
});
const selectedProductToUpdateStock = ref({
  id: null,
  name: '',
  quantity: ''
});

const openItemQuantityModal = (item) => {
  isItemQuantityModalOpen.value = true;
  selectedProductToUpdateStock.value = item;
}

const closeItemQuantityModal = () => {
  isItemQuantityModalOpen.value = false;
  selectedProductToUpdateStock.value = {};
}

const updateProductQuantity = async () => {
  try {
    const updatedQuantity = productToUpdateStockData.quantity;
    const res = await productStore.increaseStock(selectedProductToUpdateStock.value.id, updatedQuantity);
    productToUpdateStockData.quantity = null;
    Alert.success(res.message);
  } catch (error) {
    console.log(error);
  }
}

const handleFilterItems = (e) => {
  const value = e.target.value;
  switch (value) {
    case 'out_of_stock':
    case 'low_stock':
    case 'expired':
      isFiltered.value = true;
      filteredValue.value = value;
      break;
    default:
      isFiltered.value = false;
      filteredValue.value = null; // It's good practice to reset filteredValue
      break;
  }
  console.log(filteredValue.value, isFiltered.value);
};
</script>

<template>
  <div class="card shadow-1 vh-100">
    <h6 class="f-5 fw-semibold text-center my-3 text-uppercase" v-if="tableTitle !== null">
      viewing {{ tableTitle }}
    </h6>
    <DataTable :is-loading="isLoading" :thead="theadData" :total-items="products.length" searchable paginated exportable
      :items-per-page="itemsPerPage" :current-page="currentPage" @update:currentPage="changePage"
      @update:itemsPerPage="changeItemsPerPage" @exportToPDF="exportToPDF">
      <template #actions>
        <div class="d-flex justify-content-end align-items-center me-2">
          <select class="form-select rounded-0" @change="handleFilterItems" name="filterable">
            <option value="">Filter items</option>
            <option value="out_of_stock">out of stock</option>
            <option value="low_stock">low stock</option>
            <option value="expired">expired items</option>
          </select>
        </div>
        <button class="btn btn-secondary" @click="isAddItemModalOpen = true">add item</button>
      </template>
      <template #search>
        <DataTableSearchField @search="filterTable" />
      </template>
      <template #tbody>
        <tr v-for="(item, index) in displayData" :key="index">
          <td>{{ index + 1 }}</td>
          <td :title="String(item.name).length > 20 ? String(item.name).toLocaleUpperCase() : ''">
            {{ truncateText(String(item?.name).toLocaleUpperCase(), 20) }}
          </td>
          <td class="price">
            {{ parseFloat(item?.current_price?.price).toFixed(2) }}
          </td>
          <td>
            <button type="button" class="btn shadow-0 py-1" @click.prevent="openItemQuantityModal(item)">
              {{ item?.quantity }}
            </button>
          </td>
          <td :title="String(item.category).length > 20 ? item.category : ''">
            {{ truncateText(String(item.category ?? '').toLocaleUpperCase()) }}
          </td>
          <td :title="String(item.supplier).length > 20 ? item.supplier : ''">
            {{ truncateText(String(item.supplier ?? '').toLocaleUpperCase()) }}
          </td>
          <td>
            {{ item.location }}
          </td>
          <td>
            {{
              new Date(item?.expiry_date).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
              })
            }}
          </td>
          <td>
            <div class="d-flex align-items-center">
              <button type="button" @click="openEditProductModal(item)" class="btn btn-warning btn-sm me-1">
                <FontAwesomeIcon :icon="faPenToSquare" />
              </button>
              <button type="button" class="btn btn-danger btn-sm" @click="deleteItem(item.id)">
                <FontAwesomeIcon v-if="isDeleting[item.id]" :icon="faSpinner" spin />
                <FontAwesomeIcon v-else :icon="faTrashAlt" />
              </button>
            </div>
          </td>
        </tr>
      </template>
    </DataTable>
  </div>

  <!-- edit item modal -->
  <FormModal :is-open="isEditProductModalOpen" :modal-title="EditItemModalTitle" has-reset-button
    @close="isEditProductModalOpen = false">
    <form class="mb-0" @submit.prevent="updateItem">
      <div class="mb-4 row">
        <label for="name" class="col-sm-3 col-form-label">Name
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model="EditProductData.name" :class="{ 'is-invalid border-danger': errors && errors.name }"
            type="text" class="form-control mb-0 mb-0" id="name" />
          <div v-if="errors && errors.name" class="invalid-feedback mt-0 mb-1">{{
            errors.name[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="price" class="col-sm-3 col-form-label">Price
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model.number="EditProductData.price" :step="0.01"
            :class="{ 'is-invalid border-danger': errors && errors.price }" type="number" id="price" step="1"
            class="form-control mb-0" />
          <div v-if="errors && errors.price" class="invalid-feedback mt-0 mb-1">{{
            errors.price[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="quantity" class="col-sm-3 col-form-label">Cost Price
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model.number="EditProductData.cost_price" :step="0.01"
            :class="{ 'is-invalid border-danger': errors && errors.cost_price }" type="number" id="quantity" step="0"
            class="form-control mb-0" />
          <div v-if="errors && errors.cost_price" class="invalid-feedback mt-0 mb-1">{{
            errors.cost_price[0] }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="expiry_date" class="col-sm-3 col-form-label">Expiry Date
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model="EditProductData.expiry_date"
            :class="{ 'is-invalid border-danger': errors && errors.expiry_date }" type="date" id="expiry_date"
            class="form-control mb-0" />
          <div v-if="errors && errors.expiry_date" class="invalid-feedback mt-0 mb-1">{{
            errors.expiry_date[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="supplier" class="col-sm-3 col-form-label">Supplier
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.supplier }" v-model="EditProductData.supplier"
            type="text" class="form-control mb-0" id="supplier" />
          <div v-if="errors && errors.supplier" class="invalid-feedback mt-0 mb-1">{{
            errors.supplier[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="brand" class="col-sm-3 col-form-label">Brand
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.brand }" v-model="EditProductData.brand"
            type="text" class="form-control mb-0" id="brand" />
          <div v-if="errors && errors.brand" class="invalid-feedback mt-0 mb-1">{{
            errors.brand[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="category" class="col-sm-3 col-form-label">Category
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.category }" v-model="EditProductData.category"
            type="text" class="form-control mb-0" id="category" />
          <div v-if="errors && errors.category" class="invalid-feedback mt-0 mb-1">{{
            errors.category[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="bar_code" class="col-sm-3 col-form-label">Barcode
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.bar_code }" v-model="EditProductData.bar_code"
            type="text" class="form-control mb-0" id="bar_code" />
          <div v-if="errors && errors.bar_code" class="invalid-feedback mt-0 mb-1">{{
            errors.bar_code[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="location" class="col-sm-3 col-form-label">Shell Location
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.location }" v-model="EditProductData.location"
            type="text" class="form-control mb-0" id="location" />
          <div v-if="errors && errors.location" class="invalid-feedback mt-0 mb-1">{{
            errors.location[0]
          }}
          </div>
        </div>
      </div>
      <button type="submit" :class="{ 'disabled': isEditing }" class="btn btn-primary">
        <FontAwesomeIcon v-if="isEditing" :icon="faSpinner" spin />
        <span v-else="isEditing">save</span>
      </button>
      <button type="reset" class="btn btn-secondary ms-1">reset</button>
    </form>
  </FormModal>
  <!-- add item modal -->
  <FormModal :is-open="isAddItemModalOpen" :modal-title="addItemModalTitle" has-reset-button
    @close="isAddItemModalOpen = false">
    <form class="mb-0" @submit.prevent="addNewItem">
      <div class="mb-4 row">
        <label for="name" class="col-sm-3 col-form-label">Name
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model="addItemFormData.name" :class="{ 'is-invalid border-danger': errors && errors.name }"
            type="text" class="form-control mb-0 mb-0" id="name" />
          <div v-if="errors && errors.name" class="invalid-feedback mt-0 mb-1">{{
            errors.name[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="price" class="col-sm-3 col-form-label">Price
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model.number="addItemFormData.price" :step="0.01"
            :class="{ 'is-invalid border-danger': errors && errors.price }" type="number" id="price" step="1"
            class="form-control mb-0" />
          <div v-if="errors && errors.price" class="invalid-feedback mt-0 mb-1">{{
            errors.price[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="quantity" class="col-sm-3 col-form-label">Cost Price
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model.number="addItemFormData.cost_price" :step="0.01"
            :class="{ 'is-invalid border-danger': errors && errors.cost_price }" type="number" id="quantity" step="0"
            class="form-control mb-0" />
          <div v-if="errors && errors.cost_price" class="invalid-feedback mt-0 mb-1">{{
            errors.cost_price[0] }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="expiry_date" class="col-sm-3 col-form-label">Expiry Date
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model="addItemFormData.expiry_date"
            :class="{ 'is-invalid border-danger': errors && errors.expiry_date }" type="date" id="expiry_date"
            class="form-control mb-0" />
          <div v-if="errors && errors.expiry_date" class="invalid-feedback mt-0 mb-1">{{
            errors.expiry_date[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="supplier" class="col-sm-3 col-form-label">Supplier
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.supplier }" v-model="addItemFormData.supplier"
            type="text" class="form-control mb-0" id="supplier" />
          <div v-if="errors && errors.supplier" class="invalid-feedback mt-0 mb-1">{{
            errors.supplier[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="brand" class="col-sm-3 col-form-label">Brand
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.brand }" v-model="addItemFormData.brand"
            type="text" class="form-control mb-0" id="brand" />
          <div v-if="errors && errors.brand" class="invalid-feedback mt-0 mb-1">{{
            errors.brand[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="category" class="col-sm-3 col-form-label">Category
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.category }" v-model="addItemFormData.category"
            type="text" class="form-control mb-0" id="category" />
          <div v-if="errors && errors.category" class="invalid-feedback mt-0 mb-1">{{
            errors.category[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="bar_code" class="col-sm-3 col-form-label">Barcode
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.bar_code }" v-model="addItemFormData.bar_code"
            type="text" class="form-control mb-0" id="bar_code" />
          <div v-if="errors && errors.bar_code" class="invalid-feedback mt-0 mb-1">{{
            errors.bar_code[0]
          }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="location" class="col-sm-3 col-form-label">Shell Location
        </label>
        <div class="col-sm-9">
          <input :class="{ 'is-invalid border-danger': errors && errors.location }" v-model="addItemFormData.location"
            type="text" class="form-control mb-0" id="location" />
          <div v-if="errors && errors.location" class="invalid-feedback mt-0 mb-1">{{
            errors.location[0]
          }}
          </div>
        </div>
      </div>
      <button type="submit" :class="{ 'disabled': isAdding }" class="btn btn-primary">
        <FontAwesomeIcon v-if="isAdding" :icon="faSpinner" spin />
        <span v-else="isAdding">save</span>
      </button>
      <button type="reset" class="btn btn-secondary ms-1">reset</button>
    </form>
  </FormModal>
  <!-- update item quantity/stock modal -->
  <FormModal :is-open="isItemQuantityModalOpen" modal-title="update stock" @close="closeItemQuantityModal">
    <form class="mb-0" @submit.prevent="updateProductQuantity">
      <div class="mb-3 row">
        <label for="name" class="col-sm-3 col-form-label">Item Name</label>
        <div class="col-sm-9">
          <input readonly v-model="selectedProductToUpdateStock.name" type="text" class="form-control" id="name" />
        </div>
      </div>
      <div class="mb-3 row">
        <label for="quantity" class="col-sm-3 col-form-label">Quantity</label>
        <div class="col-sm-9">
          <input required min="1" v-model.number="productToUpdateStockData.quantity" type="number" id="quantity"
            step="0" :class="{ 'is-invalid border-danger': errors }"
            :placeholder="selectedProductToUpdateStock.quantity + ' quantity available'" class="form-control" />
          <div v-if="errors && errors.quantity" class="invalid-feedback mt-0">{{ errors.quantity[0] }}
          </div>
          <small class="text-muted">
            the value you enter will be added to the available quantity
          </small>
        </div>
      </div>

      <button type="submit" class="btn btn-sm btn-primary me-2" :class="{ 'disabled': isUpdating }">
        <FontAwesomeIcon v-if="isUpdating" :icon="faSpinner" spin />
        <span v-else="isUpdating">update</span>
      </button>
    </form>
  </FormModal>
</template>

<style scoped></style>
