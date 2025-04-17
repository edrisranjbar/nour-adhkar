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
              path: '/dashboard/adhkar/favorites',
              title: 'اذکار مورد علاقه',
              icon: 'fa-solid fa-heart'
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