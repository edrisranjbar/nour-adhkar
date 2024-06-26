import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import DhikrView from "@/views/DhikrView.vue";
import { morningCollection, nightCollection, istikharaCollection } from "@/assets/js/data.js";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
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
