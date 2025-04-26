<template>
  <div class="min-h-screen bg-gray-50">
    <main class="container mx-auto px-4 py-8">
      <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex flex-col items-center gap-4">
          <h1 class="text-2xl font-bold text-gray-900">داشبورد</h1>
          <div class="flex items-center gap-4">
            <ProfilePhoto 
              :photo-url="user?.photo"
              @photo-change="handlePhotoUpload"
              @error="handlePhotoError"
            />
            <span class="text-xl font-medium text-gray-900">{{ user?.name || 'کاربر' }}</span>
          </div>
        </div>

        <UserStats 
          :streak="user?.streak || 0"
          :heart-score="user?.heartScore || 0"
        />

        <StreakCalendar />

        <BadgesList />
      </div>
    </main>
  </div>
</template>

<script>
import ProfilePhoto from '@/components/dashboard/ProfilePhoto.vue'
import UserStats from '@/components/dashboard/UserStats.vue'
import StreakCalendar from '@/components/dashboard/StreakCalendar.vue'
import BadgesList from '@/components/dashboard/BadgesList.vue'
import { computed, ref, watch, onMounted } from 'vue'
import { useStore } from 'vuex'
import axios from 'axios'
import { useToast } from 'vue-toastification'

export default {
  name: 'DashboardView',
  components: {
    ProfilePhoto,
    UserStats,
    StreakCalendar,
    BadgesList
  },
  setup() {
    const store = useStore()
    const toast = useToast()
    const loading = ref(true)
    const user = computed(() => store.state.user)

    const fetchUserData = async () => {
      try {
        loading.value = true
        const response = await store.dispatch('fetchUserStats')
        console.log('User stats response:', response) // Debug log
        await store.dispatch('fetchCompletedDays')
      } catch (error) {
        console.error('Error fetching user data:', error)
        toast.error('خطا در دریافت اطلاعات کاربر')
      } finally {
        loading.value = false
      }
    }

    // Watch for changes in the user state
    watch(() => store.state.user, (newUser) => {
      console.log('User state changed:', newUser) // Debug log
      if (newUser) {
        loading.value = false
      }
    }, { immediate: true })

    onMounted(() => {
      fetchUserData()
    })

    const handlePhotoUpload = async (file) => {
      if (!file) {
        toast.error('لطفاً یک فایل انتخاب کنید')
        return
      }

      try {
        const formData = new FormData()
        formData.append('photo', file)

        const response = await axios.post(`${import.meta.env.VITE_API_URL}/user/profile-photo`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json',
            'Authorization': `Bearer ${store.state.token}`
          },
          withCredentials: true
        })

        if (response.data.status === 'success') {
          // Update user photo in store
          store.commit('updateUserPhoto', response.data.photo_url)
          
          // Show success message
          toast.success(response.data.message)
        }
      } catch (error) {
        console.error('Upload error:', error.response?.data)
        const message = error.response?.data?.message || 'خطا در بروزرسانی تصویر پروفایل'
        toast.error(message)
      }
    }

    const handlePhotoError = (message) => {
      toast.error(message)
    }

    return {
      user,
      loading,
      handlePhotoUpload,
      handlePhotoError
    }
  }
}
</script>

<style>
/* Global styles for user panel pages */
:global(.user-panel) {
  padding: 0 !important;
  margin: 0 !important;
}

:global(.user-panel .container) {
  padding: 0 !important;
  margin: 0 !important;
}
</style> 