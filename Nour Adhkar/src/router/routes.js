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
import ContributionView from '../views/ContributionView.vue'

import { morningCollection } from '@/assets/js/collections/morning';
import { nightCollection } from '@/assets/js/collections/night';
import { istikharaCollection } from '@/assets/js/collections/istikhara';
import { sleepCollection } from '@/assets/js/collections/sleep';
import { dailyCollection } from '@/assets/js/collections/daily';
import { ramadanCollection } from '@/assets/js/collections/ramadan';
import { specialCollection } from '@/assets/js/collections/special';

// Define routes with metadata for SEO and sitemap
export const routes = [
  { 
    path: '/login', 
    component: Login,
    meta: { 
      noindex: true // exclude from sitemap 
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
    path: '/dashboard', 
    component: Dashboard,
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
    path: '/morning',
    name: 'morning',
    component: DhikrView,
    props: {
      title: 'اذکار صبحگاه',
      openedCollection: morningCollection
    },
    meta: {
      title: 'اذکار صبحگاهی | اذکار نور',
      description: 'مجموعه کامل اذکار و دعاهای صبحگاهی با ترجمه فارسی و منبع',
      changefreq: 'weekly',
      priority: '0.9'
    }
  },
  {
    path: '/night',
    name: 'night',
    component: DhikrView,
    props: {
      title: 'اذکار شامگاه',
      openedCollection: nightCollection
    },
    meta: {
      title: 'اذکار شامگاهی | اذکار نور',
      description: 'مجموعه کامل اذکار و دعاهای شامگاهی با ترجمه فارسی و منبع',
      changefreq: 'weekly',
      priority: '0.9'
    }
  },
  {
    path: '/sleep',
    name: 'sleep',
    component: DhikrView,
    props: {
      title: 'دعای خواب',
      openedCollection: sleepCollection
    },
    meta: {
      title: 'دعای خواب | اذکار نور',
      description: 'اذکار و دعاهای هنگام خواب با ترجمه فارسی و منبع',
      changefreq: 'monthly',
      priority: '0.7'
    }
  },
  {
    path: '/istikhara',
    name: 'istikhara',
    component: DhikrView,
    props: {
      title: 'دعای استخاره',
      openedCollection: istikharaCollection
    },
    meta: {
      title: 'دعای استخاره | اذکار نور',
      description: 'دعای استخاره به همراه آموزش روش‌های صحیح انجام استخاره',
      changefreq: 'monthly',
      priority: '0.8'
    }
  },
  {
    path: '/daily',
    name: 'daily',
    component: DhikrView,
    props: {
      title: 'اذکار روزانه',
      openedCollection: dailyCollection
    },
    meta: {
      title: 'اذکار روزانه | اذکار نور',
      description: 'مجموعه اذکار روزانه برای استفاده در موقعیت‌های مختلف روزمره',
      changefreq: 'monthly',
      priority: '0.7'
    }
  },
  {
    path: '/ramadan',
    name: 'ramadan',
    component: DhikrView,
    props: {
      title: 'اذکار ماه رمضان',
      openedCollection: ramadanCollection
    },
    meta: {
      title: 'اذکار ماه رمضان | اذکار نور',
      description: 'دعاها و اذکار مخصوص ماه مبارک رمضان، شب‌های قدر و عید فطر',
      changefreq: 'yearly',
      priority: '0.6'
    }
  },
  {
    path: '/special',
    name: 'special',
    component: DhikrView,
    props: {
      title: 'اذکار مناسبت‌های خاص',
      openedCollection: specialCollection
    },
    meta: {
      title: 'اذکار مناسبت‌های خاص | اذکار نور',
      description: 'مجموعه اذکار و ادعیه برای مناسبت‌های خاص مانند عید، عرفه و ...',
      changefreq: 'yearly',
      priority: '0.6'
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
  }
]; 