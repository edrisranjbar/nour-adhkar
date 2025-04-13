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
          id: 'adhkar',
          title: 'مدیریت اذکار',
          icon: 'fa-solid fa-book-quran',
          items: [
            {
              path: '/admin/adhkar',
              title: 'اذکار',
              icon: 'fa-solid fa-book-quran'
            },
            {
              path: '/admin/collections',
              title: 'مجموعه‌ها',
              icon: 'fa-solid fa-layer-group'
            }
          ]
        },
        {
          id: 'content',
          title: 'مدیریت محتوا',
          icon: 'fa-solid fa-file-alt',
          items: [
            {
              path: '/admin/blog',
              title: 'مقالات',
              icon: 'fa-solid fa-newspaper'
            },
            {
              path: '/admin/categories',
              title: 'دسته‌بندی‌ها',
              icon: 'fa-solid fa-tags'
            },
            {
              path: '/admin/media',
              title: 'رسانه‌ها',
              icon: 'fa-solid fa-images'
            }
          ]
        },
        {
          id: 'users',
          title: 'مدیریت کاربران',
          icon: 'fa-solid fa-user-shield',
          items: [
            {
              path: '/admin/users',
              title: 'کاربران',
              icon: 'fa-solid fa-users'
            }
          ]
        },
        {
          id: 'system',
          title: 'تنظیمات سیستم',
          icon: 'fa-solid fa-cogs',
          items: [
            {
              path: '/admin/settings',
              title: 'تنظیمات عمومی',
              icon: 'fa-solid fa-sliders-h'
            },
          ]
        },
        {
          path: '/admin/logs',
          title: 'گزارش‌ها',
          icon: 'fa-solid fa-clipboard-list'
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