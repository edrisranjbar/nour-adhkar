import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import DhikrView from "@/views/DhikrView.vue";

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
        title: 'اذکار صبحگاه'
      }
    },
    {
      path: '/night',
      name: 'night',
      component: DhikrView,
      props: {
        title: 'اذکار شامگاه'
      }
    },
  ]
})

export default router
