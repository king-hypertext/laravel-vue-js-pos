<template>
  <div class="card shadow-1 vh-100">
    <!-- <AddUserModal :is-visible="isUserModalOpen" @close="isUserModalOpen = false" /> -->

    <ul class="nav nav-tabs mb-3 flex-nowrap" id="ex1" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link" :class="{ active: activeTab === 'tab-1' }" id="ex1-tab-1" href="#ex1-tabs-1" role="tab"
          aria-controls="ex1-tabs-1" :aria-selected="activeTab === 'tab-1'" @click.prevent="activeTab = 'tab-1'">
          user profile
        </a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" :class="{ active: activeTab === 'tab-2' }" id="ex1-tab-2" href="#ex1-tabs-2" role="tab"
          aria-controls="ex1-tabs-2" :aria-selected="activeTab === 'tab-2'" @click.prevent="activeTab = 'tab-2'">
          employees management
        </a>
      </li>
      <!-- <li class="nav-item" role="presentation">
        <a class="nav-link" :class="{ active: activeTab === 'tab-3' }" id="ex1-tab-3" href="#ex1-tabs-3" role="tab"
          aria-controls="ex1-tabs-3" :aria-selected="activeTab === 'tab-3'" @click.prevent="activeTab = 'tab-3'">
          system settings
        </a>
      </li> -->
    </ul>
    <!-- user profile tab -->
    <div class="tab-content" id="ex1-content">
      <div class="tab-pane fade" :class="{ show: activeTab === 'tab-1', active: activeTab === 'tab-1' }" id="ex1-tabs-1"
        role="tabpanel" aria-labelledby="ex1-tab-1">
        <div class="container pb-5">
          <div class="row">
            <div class="col-md-8">
              <div class="card mb-4">
                <div class="card-body">
                  <form class="mb-0" @submit.prevent="updateUser">
                    <div class="mb-4 row">
                      <label for="name" class="col-sm-3 col-form-label">Username</label>
                      <div class="col-sm-9">
                        <input required v-model="authUserData.username" type="text" class="form-control mb-0"
                          :class="{ 'is-invalid border-danger': usersStore.errors && usersStore.errors.username }"
                          id="edit-name" />
                        <div v-if="usersStore.errors && usersStore.errors.username" class="invalid-feedback mt-0 mb-1">
                          {{ usersStore.errors.username[0] }}</div>
                      </div>
                    </div>
                    <div class="mb-4 row">
                      <label for="phone" class="col-sm-3 col-form-label">Phone Number</label>
                      <div class="col-sm-9">
                        <input required v-model.number="authUserData.phone_number"
                          :class="{ 'is-invalid border-danger': usersStore.errors && usersStore.errors.phone_number }"
                          type="number" id="edit-phone" step="1" class="form-control mb-0" />
                        <div v-if="usersStore.errors && usersStore.errors.name" class="invalid-feedback mt-0 mb-1">
                          {{
                            usersStore.errors.phone_number[0]
                          }}</div>
                      </div>
                    </div>
                    <div class="mb-4 row">
                      <label for="role" class="col-sm-3 col-form-label">Role</label>
                      <div class="col-sm-9">
                        <input readonly v-model="authUserData.role" type="text" id="edit-role" class="form-control" />
                      </div>
                    </div>
                    <div class="mb-4 row">
                      <label for="password" class="col-sm-3 col-form-label">Password</label>
                      <div class="col-sm-9">
                        <input v-model="authUserData.password" type="password"
                          :class="{ 'is-invalid border-danger': usersStore.errors && usersStore.errors.password }"
                          placeholder="********" id="edit-password" class="form-control mb-0" />
                        <div v-if="usersStore.errors && usersStore.errors.password" class="invalid-feedback mt-0 mb-1">
                          {{ usersStore.errors.password[0] }}</div>
                      </div>
                    </div>
                    <div class="d-flex justify-content-center mb-2">
                      <button type="submit" class="btn btn-primary me-2" @click="editProfile">
                        <span v-if="usersStore.isUpdating">
                          <FontAwesomeIcon spin :icon="faSpinner" /> updating...
                        </span>
                        <span v-else="!usersStore.isUpdating">update Profile</span>
                      </button>
                      <button type="button" class="btn btn-outline-primary" :disabled="isLoading" @click="logout">
                        Logout
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- employees management tab -->
      <div class="tab-pane fade" :class="{ show: activeTab === 'tab-2', active: activeTab === 'tab-2' }" id="ex1-tabs-2"
        role="tabpanel" aria-labelledby="ex1-tab-2">
        <div class="d-flex flex-wrap justify-content-center justify-content-lg-between mb-2">
          <div class="d-flex align-items-center">
            <DataTable :is-loading="isLoading" :thead="theadData" :current-page="1"
              :total-items="employees && employees.length">
              <template #actions>
                <button class="btn btn-secondary" @click="isAddUserModalOpen = true">add item</button>
              </template>
              <template #tbody>
                <tr v-for="(user, i) in employees">
                  <td>{{ i + 1 }}</td>
                  <td>{{ user.username.toString().toUpperCase() }}</td>
                  <td>{{ user.role.toString().replace('-', ' ').toUpperCase() }}</td>
                  <td>{{ user.phone_number }}</td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm" :disabled="isDeleting[user.id]"
                      @click="deleteUser(user)">
                      <FontAwesomeIcon v-if="isDeleting[user.id]" :icon="faSpinner" spin />
                      <FontAwesomeIcon v-else :icon="faTrashAlt" />
                    </button>
                  </td>
                </tr>
              </template>
            </DataTable>
          </div>
        </div>
      </div>
      <!-- <div class="tab-pane fade" :class="{ show: activeTab === 'tab-3', active: activeTab === 'tab-3' }" id="ex1-tabs-3"
        role="tabpanel" aria-labelledby="ex1-tab-3">
        Tab 3 content
      </div> -->
    </div>
  </div>
  <!-- add user modal -->
  <FormModal :is-open="isAddUserModalOpen" @close="isAddUserModalOpen = false" modal-title="add new user" lg>
    <form @submit.prevent="addNewUser" class="mb-0" autocomplete="off">
      <div class="mb-4 row">
        <label for="name" class="col-sm-3 col-form-label">Username
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model="userData.username" type="text" class="form-control mb-0"
            :class="{ 'is-invalid border-danger': addUserErrors && addUserErrors.username }" id="name" />
          <div v-if="addUserErrors && addUserErrors.username" class="invalid-feedback mt-0 mb-1">
            {{ addUserErrors.username[0] }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="phone" class="col-sm-3 col-form-label">Phone Number
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model.trim="userData.phone_number" maxlength="10"
            :class="{ 'is-invalid border-danger': addUserErrors && addUserErrors.phone_number }" type="tel" id="phone"
            class="form-control mb-0" />
          <div v-if="addUserErrors && addUserErrors.phone_number" class="invalid-feedback mt-0 mb-1">
            {{
              addUserErrors.phone_number[0]
            }}</div>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="role" class="col-sm-3 col-form-label">Role
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <select required v-model="userData.role" class="form-select" name="role" id="role">
            <option value="shop-attendant">Shop attendant</option>
            <option value="manager">Manager</option>
          </select>
        </div>
      </div>
      <div class="mb-4 row">
        <label for="password" class="col-sm-3 col-form-label">Password
          <span class="text-danger">*</span>
        </label>
        <div class="col-sm-9">
          <input required v-model="userData.password" type="text"
            :class="{ 'is-invalid border-danger': addUserErrors && addUserErrors.password }" placeholder="********"
            id="password" class="form-control mb-0" />
          <div v-if="addUserErrors && addUserErrors.password" class="invalid-feedback mt-0 mb-1">
            {{ addUserErrors.password[0] }}</div>
        </div>
      </div>
      <div class="d-flex justify-content-start">
        <button type="submit" class="btn btn-primary me-2" :disabled="isAddingUser">
          <FontAwesomeIcon v-if="isAddingUser" :icon="faSpinner" spin />
          <span v-else="isAddingUser">save</span>
        </button>
      </div>
    </form>
  </FormModal>
</template>

<script setup>
// import AddUserModal from '@/components/AddUserModal.vue';
import DataTable from '@/components/DataTable.vue';
import FormModal from '@/components/FormModal.vue';
import api from '@/config/axios-config';
import { useAuthStore } from '@/stores/auth';
import Alert from '@/utils/Notify';
import { faSpinner, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { storeToRefs } from 'pinia';
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router';



const theadData = [
  {
    name: 'id',
  },
  {
    name: 'username',
  },
  {
    name: 'role',
  },
  {
    name: 'phone number',
  },
  {
    name: 'actions',
  },
];
const usersStore = useAuthStore();

const { isLoading, user, isAddingUser, employees, errors, } = storeToRefs(usersStore);


const isAddUserModalOpen = ref(false);

const addUserErrors = ref(errors.value);

const authUserData = reactive({
  username: user.value.username,
  phone_number: user.value.phone_number,
  role: user.value.role.toString().replace('-', ' '),
  password: ''
});


const addNewUser = async () => {
  if (usersStore.user.role !== 'manager') {
    Alert.error('You are not allowed to perform this action');
    return;
  }
  try {
    await usersStore.addUser(userData);
    Object.assign(userData, {
      username: '',
      phone_number: '',
      role: 'shop-attendant',
      password: '123456'
    });
    Alert.success('User added successfully');
  } catch (error) {
    console.log(error);
    if (error.status === 403) {
      Alert.error('You are not allowed to perform this action');
    }
  }
}

const isDeleting = ref({});

const deleteUser = async (user) => {
  console.log(user);

  if (!confirm('Delete user')) return;
  if (usersStore.user.role !== 'manager') {
    Alert.error('You are not allowed to perform this action');
    return;
  }
  try {
    isDeleting.value[user.id] = true;
    const response = await api.delete(`/users/${user.id}`);
    if (response.status === 200 || response.status === 204) {
      employees.value = employees.value.filter(e => e.id !== user.id);
      Alert.success('User deleted successfully');
    } else {
      // Show error alert if the response is not successful
      Alert.error('Can\'t delete user');
    }
  } catch (error) {
    // Handle any API call or other errors
    Alert.error(`An error occurred: ${error.message}`);
  } finally {
    // Reset the user deletion status
    isDeleting.value[user.id] = false;
  }
};

const updateUser = async () => {
  try {
    const res = await usersStore.updateUser(authUserData);
    if (res) {
      Alert.info('You are required to login');
    }
    Alert.success('You have successfully updated you data');
  } catch (error) {
    console.log(error);

  }
}

const router = useRouter();
const logout = async () => {
  try {
    if (confirm('Logout ?')) {
      await usersStore.logout();
    }
    router.replace({ name: 'login' });
  } catch (error) {
    console.log(error);
  }
}

onMounted(async () => {
  await usersStore.fetchUsers();
});

const activeTab = ref('tab-1') // Set the initial active tab
</script>

<style scoped>
.nav-link {
  cursor: pointer;
}
</style>
