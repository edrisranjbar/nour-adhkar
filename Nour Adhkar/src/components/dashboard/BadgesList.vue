<template>
  <div>
    <div v-if="loading" class="flex justify-center items-center py-6">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-500"></div>
    </div>
    <div v-else-if="error" class="text-red-500 text-center p-4 rounded-lg bg-red-50 border border-red-200">
      <font-awesome-icon icon="fa-solid fa-circle-exclamation" class="text-lg mr-2" />
      {{ error }}
    </div>
    <div v-else-if="!allBadges.length" class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200">
      <font-awesome-icon icon="fa-solid fa-medal" class="text-3xl text-gray-300 mb-2" />
      <p class="text-gray-500">هنوز هیچ نشانی کسب نکرده‌اید</p>
      <p class="text-gray-400 text-sm mt-2">با استفاده منظم از برنامه، نشان‌های مختلف را کسب کنید</p>
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
      <div 
        v-for="badge in allBadges" 
        :key="badge.id" 
        class="bg-white rounded-xl overflow-hidden border shadow-sm hover:shadow-md transition-all duration-300"
        :class="{ 
          'bg-gradient-to-br from-primary-50 to-primary-100 border-primary-200': badge.earned_at,
          'bg-gray-50 border-gray-200': !badge.earned_at
        }"
      >
        <div 
          class="h-1 sm:h-2 w-full"
          :class="{ 
            'bg-primary-500': badge.earned_at,
            'bg-gray-300': !badge.earned_at
          }"
        ></div>
        <div class="p-3 sm:p-4">
          <div class="flex items-start gap-2 sm:gap-3">
            <div 
              class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center text-xl sm:text-2xl flex-shrink-0"
              :class="{ 
                'bg-primary-500 text-white': badge.earned_at,
                'bg-gray-200 text-gray-400': !badge.earned_at
              }"
            >
              <font-awesome-icon :icon="badge.icon || 'fa-solid fa-medal'" />
            </div>
            <div class="flex-1">
              <h3 
                class="font-bold text-sm sm:text-base"
                :class="{ 
                  'text-primary-800': badge.earned_at,
                  'text-gray-500': !badge.earned_at
                }"
              >
                {{ badge.title }}
              </h3>
              <p 
                class="text-xs sm:text-sm mt-1"
                :class="{ 
                  'text-primary-700': badge.earned_at,
                  'text-gray-400': !badge.earned_at
                }"
              >
                {{ badge.description }}
              </p>
              <div v-if="badge.earned_at" class="mt-2 text-2xs sm:text-xs font-medium text-primary-600 flex items-center">
                <font-awesome-icon icon="fa-solid fa-calendar-check" class="ml-1" />
                کسب شده: {{ formatDate(badge.earned_at) }}
              </div>
              <div v-else class="mt-2 text-2xs sm:text-xs font-medium text-gray-400 flex items-center">
                <font-awesome-icon icon="fa-solid fa-lock" class="ml-1" />
                قفل شده
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import Badge from './Badge.vue'
import axios from 'axios'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { 
  faMedal, 
  faCalendarCheck, 
  faLock,
  faCircleExclamation
} from '@fortawesome/free-solid-svg-icons'

// Add icons to the library
library.add(faMedal, faCalendarCheck, faLock, faCircleExclamation)

export default {
  name: 'BadgesList',
  components: {
    Badge,
    FontAwesomeIcon
  },
  setup() {
    const allBadges = ref([])
    const availableBadges = ref([])
    const userBadges = ref([])
    const loading = ref(true)
    const error = ref(null)

    const fetchAllBadges = async () => {
      try {
        loading.value = true;
        error.value = null;
        
        // Get all available badges
        const badgesResponse = await axios.get(`${import.meta.env.VITE_API_URL}/badges`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          },
          withCredentials: true
        });
        
        availableBadges.value = badgesResponse.data.data || [];
        
        // Get user's earned badges
        const userBadgesResponse = await axios.get(`${import.meta.env.VITE_API_URL}/user/badges`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          },
          withCredentials: true
        });
        
        userBadges.value = userBadgesResponse.data.data || [];
        console.log('User Badges:', userBadges.value);
        
        // Combine the data - mark earned badges
        allBadges.value = availableBadges.value.map(badge => {
          const userBadge = userBadges.value.find(ub => ub.id === badge.id);
          if (userBadge) {
            return {
              ...badge,
              earned_at: userBadge.earned_at
            };
          }
          return badge;
        });
        
        console.log('Combined Badges:', allBadges.value);
      } catch (err) {
        console.error('Error fetching badges:', err);
        if (err.response && err.response.status === 401) {
          window.location.href = '/login';
        }
        error.value = 'خطا در دریافت نشان‌ها';
      } finally {
        loading.value = false;
      }
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      
      // Convert to Persian date
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    };

    onMounted(fetchAllBadges);

    return {
      allBadges,
      loading,
      error,
      formatDate
    }
  }
}
</script>

<style scoped>
.text-2xs {
  font-size: 0.65rem;
  line-height: 1rem;
}
</style> 