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
  
  // Redirect logged-in users away from login and register pages
  if ((to.path === '/login' || to.path === '/register') && token) {
    next('/');
    return;
  }
  
  // Set document title based on meta
  if (to.meta.title) {
    document.title = to.meta.title;
  }
  
  next();
});

// Handle 404 status code
router.afterEach((to) => {
  // When the route is not-found, set proper HTTP status for SEO
  if (to.name === 'not-found') {
    // Only in production, set the HTTP status code
    if (import.meta.env.PROD) {
      // This works when using SSR or prerendering, otherwise it's ignored
      document.querySelector('html').setAttribute('http-equiv-status', '404');
    }
  }
  // Lightweight page visit tracking (skip admin routes)
  try {
    if (!to.path.startsWith('/admin')) {
      const path = to.fullPath || to.path
      const referrer = document.referrer || null
      const ua = navigator.userAgent
      // Use relative API path; axios baseURL is already set
      import('axios').then(({ default: axios }) => {
        axios.post('analytics/visit', { path, referrer, ua }).catch(() => {})
      })
    }
  } catch (_) {}
});

export default router
