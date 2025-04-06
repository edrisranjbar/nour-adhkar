import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';
import { routes } from './routes';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // If the user uses back/forward navigation, restore their position
    if (savedPosition) {
      return savedPosition;
    }
    
    // Otherwise scroll to top when navigating to a new route
    return { top: 0 };
  }
})

router.beforeEach((to, from, next) => {
  const token = store.state.token;
  if (to.path == '/dashboard' && !token) {
    next('/login');
  } else {
    // Set document title based on meta
    if (to.meta.title) {
      document.title = to.meta.title;
    }
    next();
  }
});

export default router
