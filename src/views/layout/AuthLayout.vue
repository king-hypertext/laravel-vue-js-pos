  <script setup>
  import { RouterLink, RouterView } from 'vue-router'
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
  import {
    faChartBar,
    faCog,
    faCoins,
    faShoppingCart,
  } from '@fortawesome/free-solid-svg-icons'
  import { faWindows } from '@fortawesome/free-brands-svg-icons'

  import Cart from '@/views/Cart.vue'
  import { useAuthStore } from '@/stores/auth'
  import avatar from '/avatar.jpg'
  const user = useAuthStore();

</script>

<template>
  <div class="pos-menu">
    <li class="pos-menu-item" :class="{ active: $route.name == 'menu' }">
      <RouterLink class="nav-link" :to="{ name: 'menu' }">
        <FontAwesomeIcon size="2x" :icon="faWindows" />
        <span> menu </span>
      </RouterLink>
    </li>
    <li class="pos-menu-item" :class="{ active: $route.name == 'products' }">
      <RouterLink class="nav-link" :to="{ name: 'products' }">
        <FontAwesomeIcon size="2x" :icon="faShoppingCart" />
        <span> products </span>
      </RouterLink>
    </li>
    <li class="pos-menu-item" :class="{ active: $route.name == 'inventory-list' }">
      <RouterLink class="nav-link" :to="{ name: 'inventory-list' }">
        <FontAwesomeIcon size="2x" :icon="faChartBar" />
        <span> inventory </span>
      </RouterLink>
    </li>
    <li class="pos-menu-item" :class="{ active: $route.name == 'sales' }">
      <RouterLink class="nav-link" :to="{ name: 'sales' }">
        <FontAwesomeIcon size="2x" :icon="faCoins" />
        <span> sales </span>
      </RouterLink>
    </li>
    <!-- <li class="pos-menu-item" :class="{ active: $route.name == 'reports' }">
      <RouterLink class="nav-link" :to="{ name: 'reports' }">
        <FontAwesomeIcon size="2x" :icon="faChartBar" />
        <span> reports </span>
      </RouterLink>
    </li> -->
    <li class="pos-menu-item" :class="{ active: $route.name == 'settings' }">
      <RouterLink class="nav-link" :to="{ name: 'settings' }">
        <FontAwesomeIcon size="2x" :icon="faCog" />
        <span> settings </span>
      </RouterLink>
    </li>
  </div>
  <div class="pos-header">
    <div class="d-flex justify-content-between mx-3">
      <h6 class="h6 fs-5 text-uppercase mb-0 d-flex flex-row align-items-center">
        Q-POS /
        {{ $route.name.toString().replace('-', ' ') }}
      </h6>
      <div class="d-flex flex-row align-items-center">
        <img :src="avatar" alt="User Avatar" width="40" height="40" class="rounded-circle" loading="lazy">
        <h6 class="h6 text-uppercase fw-semibold ms-2 mb-0">
          {{ user.user?.username }}
        </h6>
      </div>
    </div>
  </div>
  <div class="pos-product-list">
    <!-- <div class="product-list-container"> -->
    <RouterView />
    <!-- <div class="developer-info">
        Developed by KING. <a
          href="https://api.whatsapp.com/send/?phone=%2B233549289243&text&type=phone_number&app_absent=0"
          target="_blank" rel="noopener noreferrer">Learn
          more
        </a>.
      </div> -->
    <!-- </div> -->
  </div>
  <div class="pos-cart">
    <Cart />
  </div>
</template>

<style scoped>
.developer-info {
  position: relative;
  bottom: 20px;
}

.developer-info a {
  color: #337ab7;
  /* Link color */
  text-decoration: none;
}

.developer-info a:hover {
  text-decoration: underline;
}
</style>
