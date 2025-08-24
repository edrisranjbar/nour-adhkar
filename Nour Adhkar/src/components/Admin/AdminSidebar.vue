<template>
  <Sidebar :items="menuItems" @sidebar-toggle="onSidebarToggle" />
</template>

<script>
import Sidebar from '@/components/common/Sidebar.vue';

export default {
  name: 'AdminSidebar',
  components: {
    Sidebar
  },
  data() {
    return {
      menuItems: [
        {
          path: '/admin',
          title: 'پیشخوان',
          icon: 'fa-solid fa-tachometer-alt',
          exact: true
        },
        {
          path: '/admin/analytics',
          title: 'آمار',
          icon: 'fa-solid fa-chart-line',
          exact: true
        },
        { path: '/admin/adhkar', title: 'اذکار', icon: 'fa-solid fa-book-quran', exact: true },
        { path: '/admin/collections', title: 'مجموعه‌ها', icon: 'fa-solid fa-layer-group', exact: true },
        { path: '/admin/blog', title: 'مقالات', icon: 'fa-solid fa-file-lines', exact: true },
        { path: '/admin/categories', title: 'دسته‌بندی‌ها', icon: 'fa-solid fa-folder', exact: true },
        { path: '/admin/comments', title: 'نظرات', icon: 'fa-solid fa-comments', exact: true },
        { path: '/admin/media', title: 'رسانه‌ها', icon: 'fa-solid fa-images', exact: true },
        { path: '/admin/users', title: 'کاربران', icon: 'fa-solid fa-users', exact: true },
        {
          path: '/admin/settings',
          title: 'تنظیمات سیستم',
          icon: 'fa-solid fa-cogs',
          exact: true
        },
        {
          path: '/admin/logs',
          title: 'گزارش‌ها',
          icon: 'fa-solid fa-clipboard-list',
          exact: true
        },
      ]
    }
  },
  methods: {
    onSidebarToggle(isCollapsed) {
      this.$emit('sidebar-toggle', isCollapsed);
    }
  }
}
</script>