import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';

import HomeView from '../views/HomeView.vue'
import DhikrView from '../views/DhikrView.vue'
import CounterView from '../views/CounterView.vue'
import Login from '../views/LoginView.vue';
import Register from '../views/RegisterView.vue';
import Dashboard from '../views/DashboardView.vue';
import SettingsView from '../views/SettingsView.vue'
import DonationView from '../views/DonationView.vue'
import DonationSuccessView from '../views/DonationSuccessView.vue'
import DonationFailedView from '../views/DonationFailedView.vue'

import { morningCollection } from '@/assets/js/collections/morning';
import { nightCollection } from '@/assets/js/collections/night';
import { istikharaCollection } from '@/assets/js/collections/istikhara';
import { sleepCollection } from '@/assets/js/collections/sleep';
import { dailyCollection } from '@/assets/js/collections/daily';
import { ramadanCollection } from '@/assets/js/collections/ramadan';
import { specialCollection } from '@/assets/js/collections/special';

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
      path: '/sleep',
      name: 'sleep',
      component: DhikrView,
      props: {
        title: 'دعای خواب',
        openedCollection: sleepCollection
      }
    },
    {
      path: '/istikhara',
      name: 'istikhara',
      component: DhikrView,
      props: {
        title: 'دعای استخاره',
        openedCollection: istikharaCollection
      }
    },
    {
      path: '/daily',
      name: 'daily',
      component: DhikrView,
      props: {
        title: 'اذکار روزانه',
        openedCollection: dailyCollection
      }
    },
    {
      path: '/ramadan',
      name: 'ramadan',
      component: DhikrView,
      props: {
        title: 'اذکار ماه رمضان',
        openedCollection: ramadanCollection
      }
    },
    {
      path: '/special',
      name: 'special',
      component: DhikrView,
      props: {
        title: 'اذکار مناسبت‌های خاص',
        openedCollection: specialCollection
      }
    },
    {
      path: '/settings',
      name: 'settings',
      component: SettingsView
    },
    {
      path: '/donation',
      name: 'donation',
      component: DonationView
    },
    {
      path: '/donation/success',
      name: 'donation-success',
      component: DonationSuccessView
    },
    {
      path: '/donation/failed',
      name: 'donation-failed',
      component: DonationFailedView
    }
  ]
})

router.beforeEach((to, from, next) => {
  const token = store.state.token;
  if (to.path == '/dashboard' && !token) {
    next('/login');
  } else {
    next();
  }
});

export default router
