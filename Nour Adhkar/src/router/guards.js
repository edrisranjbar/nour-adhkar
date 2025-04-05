import store from '../store';

// Check if user is authenticated
export const authGuard = (to, from, next) => {
  if (store.getters.isAuthenticated) {
    next();
  } else {
    next('/login');
  }
};

// Check if user is an admin
export const adminGuard = (to, from, next) => {
  if (store.getters.isAuthenticated) {
    // Check if user has admin role/permissions
    if (store.state.user && store.state.user.role === 'admin') {
      next();
    } else {
      // User is authenticated but not an admin
      next('/');
    }
  } else {
    // User is not authenticated
    next('/login');
  }
}; 