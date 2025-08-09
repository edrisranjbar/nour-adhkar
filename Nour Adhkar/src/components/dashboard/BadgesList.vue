<template>
  <div>
    <div v-if="loading" class="flex justify-center items-center py-6">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-500"></div>
    </div>
    <div v-else-if="error" class="text-red-500 text-center p-4 rounded-lg bg-red-50 border border-red-200">
      <font-awesome-icon icon="fa-solid fa-circle-exclamation" class="text-lg mr-2" />
      {{ error }}
    </div>
    <div v-else-if="!allBadges.length" class="text-center py-8 bg-gradient-to-br from-gray-50 to-slate-100 rounded-2xl border-2 border-dashed border-gray-300">
      <div class="w-16 h-16 bg-gradient-to-br from-gray-300 to-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
        <font-awesome-icon icon="fa-solid fa-medal" class="text-2xl text-white" />
      </div>
      <p class="text-gray-600 font-medium mb-2">هنوز هیچ نشانی کسب نکرده‌اید</p>
      <p class="text-gray-500 text-sm">با استفاده منظم از برنامه، نشان‌های مختلف را کسب کنید</p>
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
      <div 
        v-for="badge in allBadges" 
        :key="badge.id" 
        class="bg-white/80 backdrop-blur-sm rounded-2xl overflow-hidden border-2 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
        :class="{ 
          'bg-gradient-to-br from-yellow-50 to-amber-50 border-yellow-200': badge.earned_at,
          'bg-gradient-to-br from-gray-50 to-slate-50 border-gray-200': !badge.earned_at
        }"
      >
        <div 
          class="h-2 w-full"
          :class="{ 
            'bg-gradient-to-r from-yellow-500 to-amber-500': badge.earned_at,
            'bg-gradient-to-r from-gray-300 to-slate-400': !badge.earned_at
          }"
        ></div>
        <div class="p-4 lg:p-6">
          <div class="flex items-start gap-3 lg:gap-4">
            <div 
              class="w-12 h-12 lg:w-14 lg:h-14 rounded-2xl flex items-center justify-center text-xl lg:text-2xl flex-shrink-0 shadow-lg"
              :class="{ 
                'bg-gradient-to-br from-yellow-500 to-amber-500 text-white': badge.earned_at,
                'bg-gradient-to-br from-gray-300 to-slate-400 text-white': !badge.earned_at
              }"
            >
              <font-awesome-icon :icon="badge.icon || 'fa-solid fa-medal'" />
            </div>
            <div class="flex-1">
              <h3 
                class="font-bold text-base lg:text-lg"
                :class="{ 
                  'text-yellow-800': badge.earned_at,
                  'text-gray-600': !badge.earned_at
                }"
              >
                {{ badge.title }}
              </h3>
              <p 
                class="text-sm lg:text-base mt-2"
                :class="{ 
                  'text-yellow-700': badge.earned_at,
                  'text-gray-500': !badge.earned_at
                }"
              >
                {{ badge.description }}
              </p>
              <div v-if="badge.earned_at" class="mt-3 text-xs lg:text-sm font-medium text-yellow-600 flex items-center">
                <font-awesome-icon icon="fa-solid fa-calendar-check" class="ml-2" />
                کسب شده: {{ formatDate(badge.earned_at) }}
              </div>
              <div v-else class="mt-3 text-xs lg:text-sm font-medium text-gray-400 flex items-center">
                <font-awesome-icon icon="fa-solid fa-lock" class="ml-2" />
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