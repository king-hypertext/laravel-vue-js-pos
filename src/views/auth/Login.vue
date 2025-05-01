<script setup>
import { reactive, ref } from 'vue'
import { MDBRow, MDBCheckbox, MDBCol } from 'mdb-vue-ui-kit'
import { storeToRefs } from 'pinia'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const { errors } = storeToRefs(useAuthStore)

const authStore = useAuthStore();
const loginFormData = reactive({
  username: '',
  password: '',
  remember_me: true,
});
const router = useRouter();
const loginError = ref(null);

const handleLogin = async () => {
  try {
    await authStore.login(loginFormData);
    router.push({ name: 'menu' });
  } catch (error) {
    loginError.value = authStore.errors;
  }
}; 
</script>
<template>
  <h5 class="h2 text-center text-white pt-3 mb-0">Q-POS</h5>
  <p class="text-warning text-start px-5">
    The Future of Your Front Counter. Focus on Your Customers, We'll Handle the Rest.
  </p>
  <form autocomplete="off" @submit.prevent="handleLogin" class="px-5 py-2">
    <div v-if="authStore.errors && authStore.errors.login" class=" mb-2 text-danger bg-error p-2 rounded-1 text-center">
      {{ authStore.errors.login[0] }}
    </div>
    <div class="form-group">
      <label for="username">Username</label>
      <input autofocus type="text" id="username" v-model.trim="loginFormData.username"
        :class="{ 'is-invalid border-danger': authStore.errors }" name="username" placeholder="Enter your username"
        required>
      <div v-if="authStore.errors && authStore.errors.username" class="invalid-feedback mt-0">{{
        authStore.errors.username[0] }}</div>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" v-model.trim="loginFormData.password"
        :class="{ 'is-invalid border-danger': authStore.errors }" name="password" placeholder="Enter your password"
        required>
      <div v-if="authStore.errors && authStore.errors.password" class="invalid-feedback mt-0">{{
        authStore.errors.password[0] }}</div>
    </div>
    <MDBCheckbox label="Remember me" id="form2LoginCheck" v-model="loginFormData.remember_me" wrapperClass="mb-3" />
    <div class="form-group text-center">
      <button class="btn btn-primary" type="submit" :disabled="authStore.isLoading">
        <span v-if="authStore.isLoading" class="d-inline-block">
          <FontAwesomeIcon spin :icon="faSpinner" />
          authenticating...
        </span>
        <span v-if="!authStore.isLoading">secure login</span>
      </button>
    </div>

  </form>
  <MDBRow center>
    <p class="text-center">
      made by <a target="_blank" href="https://wa.me/+233543093942">Q-WebConsortium</a>
    </p>
  </MDBRow>
</template>
<style scoped>
.bg-error {
  background-color: #9208081f;
}

.form-group {
  margin-bottom: 1.8rem;
}

label {
  display: block;
  margin-bottom: 5px;
  color: #bbbbbb;
  font-size: 0.9em;
}

input[type="text"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box;
  font-size: 1em;
  background: transparent;
  color: #ececec;
}

input::placeholder {
  color: #ebebeb;
  background: transparent !important;
}

.remember-me {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.remember-me input[type="checkbox"] {
  margin-right: 8px;
}

/* button {
  display: flex;
  justify-content: center;
  align-items: center;
  text-wrap: nowrap;
  width: 45%;
  background-color: #007bff;
  color: white;
  padding: 10px 25px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1em;
  user-select: none;
  transition: background-color 0.3s ease;

  &:hover {
    background-color: #0056b3;
  }
} */

input:-internal-autofill-selected,
input:-internal-autofill-selected::placeholder {
  background-color: transparent !important;
}
</style>
