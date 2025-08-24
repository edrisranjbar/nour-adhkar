import HomeView from '../views/HomeView.vue'
import DhikrView from '../views/DhikrView.vue'
import CounterView from '../views/CounterView.vue'
import Login from '../views/LoginView.vue';
import Register from '../views/RegisterView.vue';
import SettingsView from '../views/SettingsView.vue'
import DonationView from '../views/DonationView.vue'
import DonationSuccessView from '../views/DonationSuccessView.vue'
import DonationFailedView from '../views/DonationFailedView.vue'
import ContributionView from '../views/ContributionView.vue'
import AboutView from '../views/AboutView.vue'
import NotFoundView from '../views/NotFoundView.vue'
import { authGuard, adminGuard } from './guards';
import DashboardView from '../views/DashboardView.vue'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import ResetPasswordView from '../views/ResetPasswordView.vue'

// Lazy loading for blog components
const BlogView = () => import('../views/BlogView.vue');
const BlogPostView = () => import('../views/BlogPostView.vue');

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

// Public routes that everyone can access
export const publicRoutes = [
  { 
    path: '/login', 
    component: Login,
    meta: { 
      noindex: true // exclude from sitemap 
    }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordView,
    meta: {
      title: 'بازیابی رمز عبور | اذکار نور',
      noindex: true
    }
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: ResetPasswordView,
    meta: {
      title: 'تنظیم رمز عبور جدید | اذکار نور',
      noindex: true
    }
  },
  { 
    path: '/register', 
    component: Register,
    meta: { 
      noindex: true // exclude from sitemap 
    }
  },
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: {
      title: 'اذکار نور | مجموعه کامل اذکار و ادعیه',
      description: 'دسترسی آسان به اذکار صبحگاهی، شامگاهی و ادعیه مختلف در یک اپلیکیشن سبک و کاربرپسند',
      changefreq: 'daily',
      priority: '1.0'
    }
  },
  {
    path: '/counter',
    name: 'counter',
    component: CounterView,
    meta: {
      title: 'تسبیح شمار دیجیتال | اذکار نور',
      description: 'ذکر گفتن با تسبیح شمار دیجیتال، همراه با امکان ذخیره و آمار ذکرهای روزانه',
      changefreq: 'monthly',
      priority: '0.8'
    }
  },
  {
    path: '/collections/:slug',
    name: 'collection',
    component: DhikrView,
    meta: {
      title: 'مجموعه اذکار | اذکار نور',
      description: 'مجموعه اذکار و ادعیه با ترجمه فارسی و منبع',
      changefreq: 'weekly',
      priority: '0.8'
    }
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsView,
    meta: {
      title: 'تنظیمات | اذکار نور',
      description: 'تنظیمات شخصی‌سازی اپلیکیشن اذکار نور',
      changefreq: 'monthly',
      priority: '0.5'
    }
  },
  {
    path: '/donation',
    name: 'donation',
    component: DonationView,
    meta: {
      title: 'حمایت از ما | اذکار نور',
      description: 'حمایت مالی از پروژه منبع باز اذکار نور',
      changefreq: 'monthly',
      priority: '0.7'
    }
  },
  {
    path: '/donation/success',
    name: 'donation-success',
    component: DonationSuccessView,
    meta: {
      noindex: true // Don't include in search results or sitemap
    }
  },
  {
    path: '/donation/failed',
    name: 'donation-failed',
    component: DonationFailedView,
    meta: {
      noindex: true // Don't include in search results or sitemap
    }
  },
  {
    path: '/contribution',
    name: 'contribution',
    component: ContributionView,
    meta: {
      title: 'مشارکت در پروژه | اذکار نور',
      description: 'نحوه مشارکت در بهبود و توسعه پروژه منبع باز اذکار نور',
      changefreq: 'monthly',
      priority: '0.6'
    }
  },
  {
    path: '/about',
    name: 'about',
    component: AboutView,
    meta: {
      title: 'درباره ما | اذکار نور',
      description: 'اطلاعات درباره اذکار نور و تیم آن',
      changefreq: 'monthly',
      priority: '0.5'
    }
  },
  {
    path: '/blog',
    name: 'blog',
    component: BlogView,
    meta: {
      title: 'مقالات و نوشته‌ها | اذکار نور',
      description: 'مقالات و نوشته‌ها در مورد فضیلت ذکر و دعا',
      changefreq: 'weekly',
      priority: '0.7'
    }
  },
  {
    path: '/blog/:slug',
    name: 'blog-post',
    component: BlogPostView,
    meta: {
      changefreq: 'monthly',
      priority: '0.6'
    }
  },
];

// Protected routes that require authentication
export const protectedRoutes = [
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    beforeEnter: authGuard,
    meta: {
      title: 'داشبورد | اذکار نور',
      description: 'داشبورد کاربری اذکار نور',
      changefreq: 'daily',
      priority: '0.9'
    }
  }
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
      }
    ]
  }
];

// Combine all routes
export const routes = [
  ...publicRoutes,
  ...protectedRoutes,
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