<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
    <!-- Modern Header with Glass Effect -->
    <div class="relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 bg-black/50 ">
        <div class="absolute inset-0 opacity-10">
          <div class="absolute top-0 left-0 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-xl opacity-70 dark:opacity-30 animate-blob"></div>
          <div class="absolute top-0 right-0 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 dark:opacity-30 animate-blob animation-delay-2000"></div>
          <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 dark:opacity-30 animate-blob animation-delay-4000"></div>
        </div>
      </div>
      
             <!-- Header Content -->
       <div class="relative z-10 container mx-auto px-4 py-10">
         <div class="flex flex-col lg:flex-row justify-between items-center lg:items-center gap-6">
           <!-- Welcome Section -->
           <div class="flex-1 text-center lg:text-right">
             <h1 class="text-3xl lg:text-4xl font-bold text-white mb-2">
               سلام {{ user?.name || 'کاربر' }}
             </h1>
           </div>
           
           <!-- User Actions -->
            <div class="flex flex-col sm:flex-row items-center gap-4">
            <!-- Quick Stats Preview -->
             <div class="hidden sm:flex items-center gap-4 bg-white/10 dark:bg-black/20 backdrop-blur-sm rounded-2xl p-4 border border-white/20 dark:border-white/10">
              <div class="text-center">
                <div class="text-2xl font-bold text-white">{{ user?.streak || 0 }}</div>
                <div class="text-xs text-primary-100">روز متوالی</div>
              </div>
              <div class="w-px h-8 bg-white/20"></div>
              <div class="text-center">
                <div class="text-2xl font-bold text-white">{{ totalDhikrs }}</div>
                <div class="text-xs text-primary-100">ذکر انجام‌شده</div>
              </div>
            </div>
            
            <!-- Profile Button -->
            <button 
              @click="showProfileSettings = true" 
              class="flex items-center gap-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-xl px-6 py-3 font-medium transition-all duration-300 hover:scale-105 shadow-lg border border-white/10"
            >
              <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <font-awesome-icon icon="fa-solid fa-user" class="text-lg" />
              </div>
              <span>پروفایل</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 mt-9 relative z-20">
      <!-- Stats Cards Grid -->
      <div class="mb-8">
        <UserStats />
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Main Content Area -->
        <div class="xl:col-span-2 space-y-8">
          <!-- Daily Activity -->
          <div class="bg-white/80 dark:bg-slate-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/20 dark:border-white/10 p-6 lg:p-8">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                  <font-awesome-icon icon="fa-solid fa-calendar-day" class="text-white text-lg" />
                </div>
                فعالیت روزانه
              </h2>
              <div class="text-sm text-gray-500 dark:text-gray-300 bg-gray-100 dark:bg-slate-700 px-3 py-1 rounded-full">
                هفته گذشته
              </div>
            </div>
            <StreakCalendar />
          </div>
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
import ProfileSettingsModal from '@/components/dashboard/ProfileSettingsModal.vue'
import { computed, ref, watch, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { 
  faUserPen, 
  faCalendarDay, 
  faUser,
  faBolt,
  faPray,
  faBookOpen,
  faChartLine,
  faQuoteRight
} from '@fortawesome/free-solid-svg-icons'

library.add(
  faUserPen, 
  faCalendarDay, 
  faUser, 
  faBolt, 
  faPray, 
  faBookOpen, 
  faChartLine, 
  faQuoteRight
)

export default {
  name: 'DashboardView',
  components: {
    UserStats,
    StreakCalendar,
    ProfileSettingsModal,
    FontAwesomeIcon
  },
  setup() {
    const store = useStore()
    const router = useRouter()
    const toast = useToast()
    const loading = ref(true)
    const user = computed(() => store.state.user)
    const showProfileSettings = ref(false)

    const totalDhikrs = computed(() =>
      user.value?.total_adhkar_completed ?? user.value?.total_dhikrs ?? 0
    )

    const fetchUserData = async () => {
      try {
        loading.value = true
        await store.dispatch('fetchUserStats')
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
      console.log('User state changed:', newUser)
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
          store.commit('updateUserPhoto', response.data.avatar_url)
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
      if (user.value) {
        store.commit('updateUserName', newName)
      }
      toast.success('نام شما با موفقیت به‌روزرسانی شد')
    }

    const goToCounter = () => {
      router.push('/counter')
    }

    const goToHome = () => {
      router.push('/')
    }

    return {
      user,
      totalDhikrs,
      loading,
      showProfileSettings,
      handlePhotoUpload,
      handlePhotoError,
      handleNameUpdated,
      goToCounter,
      goToHome
    }
  }
}
</script>

<style scoped>
/* Custom animations */
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

/* Glass morphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Responsive improvements */
@media (max-width: 640px) {
  .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

/* Smooth transitions */
* {
  transition: all 0.3s ease;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>