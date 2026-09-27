import LandingView from '../views/LandingView.vue'
import Login from '../views/LoginView.vue';
import NotFoundView from '../views/NotFoundView.vue'
import { adminGuard } from './guards';
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import ResetPasswordView from '../views/ResetPasswordView.vue'

// Lazy loading for admin components
const AdminLayout = () => import('../views/admin/AdminLayout.vue');
const AdminDashboardView = () => import('../views/admin/AdminDashboardView.vue');
const BlogManageView = () => import('../views/admin/BlogManageView.vue');
const BlogEditorView = () => import('../views/admin/BlogEditorView.vue');
const CategoriesManageView = () => import('../views/admin/CategoriesManageView.vue');
const UsersManageView = () => import('../views/admin/UsersManageView.vue');
const MediaManageView = () => import('../views/admin/MediaManageView.vue');
const SettingsManageView = () => import('../views/admin/SettingsManageView.vue');
const LogsManageView = () => import('../views/admin/LogsManageView.vue');
import AdminAdhkarView from '@/views/admin/AdminAdhkarView.vue';
import AdminCollectionsView from '@/views/admin/AdminCollectionsView.vue';
const AdminAnalyticsView = () => import('../views/admin/AdminAnalyticsView.vue');

// Public routes: the landing page plus the auth pages the admin panel needs
export const publicRoutes = [
  {
    path: '/',
    name: 'home',
    component: LandingView,
    meta: {
      title: 'اذکار نور | اپلیکیشن اذکار، قرآن و اوقات شرعی',
      description: 'اذکار نور، همراه روزانه برای اذکار صبح و شام، قرآن کریم، اوقات شرعی و تسبیح. دریافت از کافه بازار.',
      changefreq: 'monthly',
      priority: '1.0'
    }
  },
  {
    path: '/login',
    component: Login,
    meta: { noindex: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordView,
    meta: { title: 'بازیابی رمز عبور | اذکار نور', noindex: true }
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: ResetPasswordView,
    meta: { title: 'تنظیم رمز عبور جدید | اذکار نور', noindex: true }
  },
];

// Admin routes that require admin privileges
export const adminRoutes = [
  {
    path: '/admin',
    component: AdminLayout,
    beforeEnter: adminGuard,
    meta: { 
      noindex: true 
    },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: AdminDashboardView,
        meta: {
          title: 'پیشخوان مدیریت | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'analytics',
        name: 'admin-analytics',
        component: AdminAnalyticsView,
        meta: { title: 'آمار و تحلیل | اذکار نور', noindex: true }
      },
      {
        path: 'adhkar',
        name: 'admin-adhkar',
        component: AdminAdhkarView,
        meta: {
          title: 'مدیریت اذکار | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'collections',
        name: 'admin-collections',
        component: AdminCollectionsView,
        meta: {
          title: 'مدیریت مجموعه ها | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'blog',
        name: 'admin-blog',
        component: BlogManageView,
        meta: {
          title: 'مدیریت مقالات | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'blog/new',
        name: 'admin-blog-new',
        component: BlogEditorView,
        meta: {
          title: 'ایجاد مقاله جدید | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'blog/edit/:id',
        name: 'admin-blog-edit',
        component: BlogEditorView,
        meta: {
          title: 'ویرایش مقاله | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'categories',
        name: 'admin-categories',
        component: CategoriesManageView,
        meta: {
          title: 'مدیریت دسته‌بندی‌ها | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'users',
        name: 'admin-users',
        component: UsersManageView,
        meta: {
          title: 'مدیریت کاربران | اذکار نور',
          noindex: true
        }
      },
      // New routes for the additional sections
      {
        path: 'media',
        name: 'admin-media',
        component: MediaManageView,
        meta: {
          title: 'مدیریت رسانه‌ها | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: SettingsManageView,
        meta: {
          title: 'تنظیمات سیستم | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'logs',
        name: 'admin-logs',
        component: LogsManageView,
        meta: {
          title: 'گزارش‌ها و لاگ‌ها | اذکار نور',
          noindex: true
        }
      },
      {
        path: 'comments',
        name: 'admin-comments',
        component: () => import('@/views/admin/CommentsManageView.vue'),
        meta: { title: 'مدیریت نظرات' }
      },
      { path: 'app-inbox', name: 'admin-app-inbox', component: () => import('@/views/admin/AppInboxView.vue'), meta: { title: 'پیام‌های برنامه' } }
    ]
  }
];

// Combine all routes
export const routes = [
  ...publicRoutes,
  ...adminRoutes,
  // 404 catch-all route must be the last one
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
    meta: {
      title: 'صفحه یافت نشد | اذکار نور',
      noindex: true
    }
  }
];
