<script setup>
import api from '@/config/axios-config'
import { useCartStore } from '@/stores/cart'
import { useProductsStore } from '@/stores/products'
import { faMinus, faPlus } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { storeToRefs } from 'pinia'

import { computed, onMounted, onUnmounted, ref } from 'vue' // Adjust path as needed

const cartStore = useCartStore()
const productStore = useProductsStore()

const { products } = storeToRefs(productStore)

const filterText = ref('') // Use a ref to track the input value

const filteredProducts = computed(() => {
  const searchText = filterText.value.toLowerCase()
  if (!searchText) {
    return products.value // Return all products if the search input is empty
  }
  return products.value.filter((product) => product.name.toLowerCase().includes(searchText))
})

const searchInput = ref(null)

const handleKeyboardShortcut = (event) => {
  if (event.ctrlKey && event.key === 'k') {
    event.preventDefault()
    if (searchInput.value) {
      searchInput.value.focus();
      searchInput.value.select()
    }
  }
}


const isFetching = ref(false);

const fetchProducts = () => {
  isFetching.value = true;
  api.get('products').then((response) => {
    products.value = response.data.data;
  }).catch((error) => {
    console.log(error);
  }).finally(() => {
    isFetching.value = false;
  });
}
onMounted(() => {
  document.addEventListener('keydown', handleKeyboardShortcut)
  fetchProducts();
});

onUnmounted(() => {
  // Clean up the event listener when the component is unmounted to prevent memory leaks
  document.removeEventListener('keydown', handleKeyboardShortcut)
})
</script>

<template>
  <div class="card shadow-1 vh-100">
    <div class="mb-3">
      <label for="" class="form-label">Search Products</label>
      <input ref="searchInput" type="text" class="form-control" v-model="filterText" placeholder="Search (Ctrl + K)" />
      <small id="helpId" class="form-text text-muted">Filter Products</small>
    </div>
    <div class="product-list">
      <div v-if="isFetching" v-for="i in 10" :key="i" class="product-card shadow-2-strong placeholder-card">
        <div class="title placeholder-glow">
          <span class="placeholder col-6"></span>
        </div>
        <hr class="hr-blurry my-0" />
        <div class="price placeholder-glow d-flex align-items-center">
          <span class="placeholder col-3"></span>
          <span class="vr"></span>
          <span class="quantity placeholder col-4"></span>
        </div>
        <div class="d-flex flex-nowrap align-items-center justify-content-around placeholder-glow">
          <button class="btn btn-sm btn-warning shadow-0 placeholder placeholder-wave col-4"></button>
          <button class="btn btn-sm btn-warning shadow-0 placeholder placeholder-wave col-4"></button>
        </div>
        <span class="text-danger placeholder-glow">
          <span class="placeholder col-5"></span>
        </span>
      </div>
      <div v-else v-if="filteredProducts.length > 0" v-for="product in filteredProducts"
        class="product-card shadow-2-strong" :class="{ 'opacity0 low-stock': product.quantity === 0 }">
        <div class="title">
          {{ product.name }}
        </div>
        <hr class="hr-blurry my-0" />
        <div class="product-price">
          <span class="price fw-semibold"> {{ Number(product.current_price?.price).toFixed(2) }} </span>
          <span class="vr"></span>
          <span class="quantity" title="stock level"> {{ product.quantity }}
            <small>(available)</small>
          </span>
        </div>
        <div class="d-flex flex-nowrap align-items-center justify-content-around">
          <button class="btn btn-sm btn-warning shadow-0" title="add to cart"
            @click="cartStore.addItem(product.id, product.current_price?.price)" :disabled="product.quantity === 0">
            <FontAwesomeIcon :icon="faPlus" />
          </button>
          <button class="btn btn-sm btn-warning shadow-0" title="remove from cart"
            @click="cartStore.decreaseQuantity(product.id)" :disabled="product.quantity === 0">
            <FontAwesomeIcon :icon="faMinus" />
          </button>
        </div>
        <span v-if="product.quantity === 0" class="text-danger text-center text-capitalize">out of stock</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.product-list {
  display: flex;
  flex-grow: 1;
  flex-direction: row;
  flex-wrap: wrap;
}

.product-card {
  display: flex;
  flex-direction: column;
  /* align-items: space-around; */
  height: 180px;
  width: 250px;
  background-color: #fff;
  margin: 10px;
  border-radius: 5px;
  padding: 8px 8px 2px 8px;
  transition: all 0.3s ease-out;

  &.low-stock {
    background: linear-gradient(to right, rgba(184, 0, 0, 0.473), rgba(255, 18, 18, 0.486));
    /* opacity: 0.3; */
    color: #000000;
  }

  &:hover:not(.low-stock) {
    cursor: default;
    background: linear-gradient(to right, #000000, #344);
    transition: all 0.3s ease-out;
    color: #fff;
  }

  .title {
    text-align: left;
    text-transform: uppercase;
    /* background: linear-gradient(to right, red, rgb(107, 14, 14)); */
    border-radius: inherit;
    /* opacity: 0.5; */
  }

  .product-price {
    display: flex;
    align-items: baseline;
    justify-content: space-around;
    flex-grow: 1;
    font-weight: 500;
    font-size: 1.5rem;

    .quantity {
      font-size: 1.4rem;
      font-weight: 500;
    }
  }

  .vr {
    /* opacity: 1 !important; */
    background-color: #4b4b4b7a;
    margin: 0 5px;
    max-height: 35px;
  }

  >* {
    flex-grow: 1;
    color: inherit;
    -moz-user-select: none;
    -webkit-user-select: none;
    user-select: none;
  }
}
</style>
