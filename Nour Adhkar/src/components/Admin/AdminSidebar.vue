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
          icon: 'fa-solid fa-newspaper',
          items: [
            {
              title: 'مقالات',
              path: '/admin/blog',
              icon: 'fa-solid fa-file-lines'
            },
            {
              title: 'دسته‌بندی‌ها',
              path: '/admin/categories',
              icon: 'fa-solid fa-folder'
            },
            {
              title: 'نظرات',
              path: '/admin/comments',
              icon: 'fa-solid fa-comments'
            },
            {
              title: 'رسانه‌ها',
              path: '/admin/media',
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