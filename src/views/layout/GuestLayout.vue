<script setup>
const authPaths = ['/login']
if (authPaths.includes(location.pathname)) {
  document.querySelector('#app').classList.add('d-flex', 'justify-content-center', 'align-items-center', 'align-content-center', 'vh-100', 'auth')
}
</script>
<template>
  <div class="card login-card shadow-0">
    <div class="card-body">
      <RouterView></RouterView>
    </div>
  </div>
</template>
<style scoped>
.login-card {
  width: 500px;
  background: linear-gradient(to right, rgb(0, 0, 0), rgb(0, 10, 19));
  border: 1px solid #5f5f5f;
}

@media screen and (max-width: 500px) {
  .login-card {
    width: 95%;
  }
}
</style>
