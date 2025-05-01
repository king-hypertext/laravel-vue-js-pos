<script setup>
import { faSearch } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { defineEmits } from 'vue'
const emit = defineEmits(['search'])
const search = (e) => {
  let value = e.target.value
  emit('search', value);
}
</script>

<template>
  <div class="d-flex align-items-center me-2">
    <div class="search-container">
      <input
        type="search"
        @input="search"
        inputmode="search"
        autocomplete="off"
        id="searchInput"
        placeholder="Search table ..."
        class="form-control form-control-lg"
      />
      <!-- <i class="fas fa-search"></i> -->
      <FontAwesomeIcon :icon="faSearch" />
    </div>
  </div>
</template>

<style scoped>
.search-container {
  position: relative;
  z-index: 9;
}

.search-container input {
  padding-left: 45px;
  min-width: 240px;
}

.search-container svg {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--sims-primary);
  font-size: 14px;
  cursor: pointer;
}
</style>
