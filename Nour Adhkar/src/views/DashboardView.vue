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
            <div class="flex items-center">
              <span class="text-xl font-medium text-gray-900">{{ user?.name || 'کاربر' }}</span>
              <button 
                @click="showProfileSettings = true" 
                class="mr-2 p-1 text-gray-500 hover:text-gray-700 focus:outline-none"
                title="ویرایش پروفایل"
              >
                <font-awesome-icon icon="fa-solid fa-user-pen" class="text-sm" />
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <UserStats 
            :streak="user?.streak || 0"
            :heart-score="user?.heartScore || 0"
          />

          <LeagueProgress />
        </div>

        <StreakCalendar />

        <BadgesList />
      </div>
    </main>

    <!-- Profile Settings Modal -->
    <ProfileSettingsModal 
      :show="showProfileSettings"
      :user-name="user?.name"
      @close="showProfileSettings = false"
      @name-updated="handleNameUpdated"
    />
  </div>
</template>

<script>
import ProfilePhoto from '@/components/dashboard/ProfilePhoto.vue'
import UserStats from '@/components/dashboard/UserStats.vue'
import StreakCalendar from '@/components/dashboard/StreakCalendar.vue'
import BadgesList from '@/components/dashboard/BadgesList.vue'
import ProfileSettingsModal from '@/components/dashboard/ProfileSettingsModal.vue'
import LeagueProgress from '@/components/dashboard/LeagueProgress.vue'
import { computed, ref, watch, onMounted } from 'vue'
import { useStore } from 'vuex'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'DashboardView',
  components: {
    ProfilePhoto,
    UserStats,
    StreakCalendar,
    BadgesList,
    ProfileSettingsModal,
    LeagueProgress,
    FontAwesomeIcon
  },
  setup() {
    const store = useStore()
    const toast = useToast()
    const loading = ref(true)
    const user = computed(() => store.state.user)
    const showProfileSettings = ref(false)

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
        formData.append('avatar', file)

        const response = await axios.post(`${import.meta.env.VITE_API_URL}/avatar`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json',
            'Authorization': `Bearer ${store.state.token}`
          },
          withCredentials: true
        })

        if (response.data.avatar_url) {
          // Update user photo in store
          store.commit('updateUserPhoto', response.data.avatar_url)
          
          // Show success message
          toast.success(response.data.message || 'تصویر پروفایل با موفقیت به‌روزرسانی شد')
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

    const handleNameUpdated = (newName) => {
      // Update user name in store if needed
      if (user.value) {
        store.commit('updateUserName', newName)
      }
      toast.success('نام شما با موفقیت به‌روزرسانی شد')
    }

    return {
      user,
      loading,
      showProfileSettings,
      handlePhotoUpload,
      handlePhotoError,
      handleNameUpdated
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