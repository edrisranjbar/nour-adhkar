<template>
  <div class="bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen pb-12">
    <!-- Header -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg">
      <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <h1 class="text-2xl font-bold">داشبورد</h1>
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
              <span class="text-xl font-medium">{{ user?.name || 'کاربر' }}</span>
              <button 
                @click="showProfileSettings = true" 
                class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition-colors"
                title="ویرایش پروفایل"
              >
                <font-awesome-icon icon="fa-solid fa-user-pen" class="text-sm" />
                <span>ویرایش پروفایل</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Section: full width -->
    <div class="container mx-auto px-4 mt-8">
      <div class="w-full">
        <UserStats />
      </div>
    </div>

    <!-- Main Content: centered -->
    <div class="container mx-auto px-4 mt-8">
      <div class="max-w-6xl mx-auto space-y-8">
        <!-- Activity & Progress -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-3">
            <!-- Daily activity timeline -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border border-gray-100">
              <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-calendar-day" class="text-primary-600" />
                فعالیت روزانه
              </h2>
              <StreakCalendar />
            </div>
          </div>
        </div>

        <!-- Achievements & Badges -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border border-gray-100">
          <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <font-awesome-icon icon="fa-solid fa-award" class="text-primary-600" />
            دستاوردها
          </h2>
          <BadgesList />
        </div>
      </div>
    </div>

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
import UserStats from '@/components/dashboard/UserStats.vue'
import StreakCalendar from '@/components/dashboard/StreakCalendar.vue'
import BadgesList from '@/components/dashboard/BadgesList.vue'
import ProfileSettingsModal from '@/components/dashboard/ProfileSettingsModal.vue'
import { computed, ref, watch, onMounted } from 'vue'
import { useStore } from 'vuex'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { 
  faUserPen, 
  faCalendarDay, 
  faAward
} from '@fortawesome/free-solid-svg-icons'

// Add icons to the library
library.add(faUserPen, faCalendarDay, faAward)

export default {
  name: 'DashboardView',
  components: {
    UserStats,
    StreakCalendar,
    BadgesList,
    ProfileSettingsModal,
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