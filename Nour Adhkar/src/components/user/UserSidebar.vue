<template>
  <Sidebar :items="menuItems" />
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
          path: '/dashboard/adhkar/favorites',
          title: 'اذکار مورد علاقه',
          icon: 'fa-solid fa-heart'
        },
        {
          path: '/dashboard/profile/settings',
          title: 'تنظیمات پروفایل',
          icon: 'fa-solid fa-cog'
        }
      ]
    }
  }
}
</script> 