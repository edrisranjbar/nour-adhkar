import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import DhikrView from "@/views/DhikrView.vue";
import { morningCollection } from '@/assets/js/collections/morning';
import { nightCollection } from '@/assets/js/collections/night';
import { istikharaCollection } from '@/assets/js/collections/istikhara';
import CounterView from '@/views/CounterView.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
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

export default router
