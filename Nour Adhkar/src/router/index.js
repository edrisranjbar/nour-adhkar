import { createRouter, createWebHistory } from 'vue-router';

import Login from '../views/LoginView.vue';
import Register from '../views/RegisterView.vue';
import Dashboard from '../views/DashboardView.vue';

import HomeView from '@/views/HomeView.vue'
import DhikrView from "@/views/DhikrView.vue";
import { morningCollection } from '@/assets/js/collections/morning';
import { nightCollection } from '@/assets/js/collections/night';
import { istikharaCollection } from '@/assets/js/collections/istikhara';
import CounterView from '@/views/CounterView.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/dashboard', component: Dashboard },
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/counter',
      name: 'counter',
      component: CounterView
    },
    {
      path: '/morning',
      name: 'morning',
      component: DhikrView,
      props: {
        title: 'اذکار صبحگاه',
        openedCollection: morningCollection
      }
    },
    {
      path: '/night',
      name: 'night',
      component: DhikrView,
      props: {
        title: 'اذکار شامگاه',
        openedCollection: nightCollection
      }
    },
    {
      path: '/istikhara',
      name: 'istikhara',
      component: DhikrView,
      props: {
        title: 'دعاء استخاره',
        openedCollection: istikharaCollection
      }
    }
  ]
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');

  // If user is logged in and tries to access login or register pages
  if ((to.path === '/login' || to.path === '/register') && token) {
    next('/dashboard');
    return;
  }

  // If user is not logged in and tries to access protected routes
  if (to.path !== '/login' && to.path !== '/register' && !token) {
    next('/login');
    return;
  }

  next();
});

export default router
