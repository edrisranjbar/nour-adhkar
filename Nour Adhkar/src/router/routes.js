// Update the imports to include the admin dashboard
import { adminGuard } from './guards';

// Admin components
const AdminLayout = () => import('../views/admin/AdminLayout.vue');
const AdminDashboardView = () => import('../views/admin/AdminDashboardView.vue');
const BlogManageView = () => import('../views/admin/BlogManageView.vue');
const BlogEditorView = () => import('../views/admin/BlogEditorView.vue');

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
      }
    ]
  }
];