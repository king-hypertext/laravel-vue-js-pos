import { createRouter, createWebHistory } from 'vue-router'
import GuestLayout from '@/views/layout/GuestLayout.vue';
import AuthLayout from '@/views/layout/AuthLayout.vue';
import Menu from '@/views/Menu.vue';
import ProductList from '@/views/ProductList.vue';
import Sales from '@/views/Sales.vue';
import Inventory from '@/views/Inventory.vue';
import Settings from '@/views/Settings.vue';
import Login from '@/views/auth/Login.vue';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      meta: { requiresAuth: true },
      component: AuthLayout,
      children: [
        {
          path: '/',
          name: 'menu',
          component: Menu,
        },
        {
          path: '/products',
          name: 'products',
          component: ProductList,
        },
        {
          path: '/sales',
          name: 'sales',
          component: Sales,
        },
        {
          path: '/inventory',
          name: 'inventory-list',
          component: Inventory,
        },
        {
          path: '/settings',
          name: 'settings',
          component: Settings,
        },
        // {
        //   path: '/reports',
        //   name: 'reports',
        //   component: Reports
        // }
      ],
    },
    {
      path: '/',
      component: GuestLayout,
      meta: { requiresAuth: false },
      children: [
        {
          path: '/login',
          name: 'login',
          component: Login,
        }
      ]
    }
  ],
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.name !== 'login') {
    document.querySelector('#app').classList.remove('d-flex', 'justify-content-center', 'align-items-center', 'align-content-center', 'vh-100', 'auth');
  }
  if (authStore.isAuthenticated && to.name === 'login') next({ name: 'menu' })
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else {
    next();
  }
});
// router.afterEach((to, from) => {
//   document.title = to.meta.title ?? 'QQ - IMS'
// })

export default router
