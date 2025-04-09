<template>
  <Sidebar :items="menuItems" @sidebar-toggle="onSidebarToggle" />
</template>

<script>
import Sidebar from '@/components/common/Sidebar.vue';

export default {
  name: 'UserSidebar',
  components: {
    Sidebar
  },
  data() {
    return {
      menuItems: [
        {
          path: '/dashboard',
          title: 'داشبورد',
          icon: 'fa-solid fa-home',
          exact: true
        },
        {
          id: 'adhkar',
          title: 'اذکار',
          icon: 'fa-solid fa-pray',
          items: [
            {
              path: '/dashboard/adhkar/daily',
              title: 'اذکار روزانه',
              icon: 'fa-solid fa-sun'
            },
            {
              path: '/dashboard/adhkar/favorites',
              title: 'اذکار مورد علاقه',
              icon: 'fa-solid fa-heart'
            },
            {
              path: '/dashboard/adhkar/custom',
              title: 'اذکار شخصی',
              icon: 'fa-solid fa-edit'
            }
          ]
        },
        {
          id: 'profile',
          title: 'پروفایل',
          icon: 'fa-solid fa-user',
          items: [
            {
              path: '/dashboard/profile/settings',
              title: 'تنظیمات',
              icon: 'fa-solid fa-cog'
            },
            {
              path: '/dashboard/profile/stats',
              title: 'آمار و گزارشات',
              icon: 'fa-solid fa-chart-bar'
            }
          ]
        }
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