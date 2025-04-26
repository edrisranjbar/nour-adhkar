<template>
  <div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">نشان‌های شما</h2>
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    <div v-else-if="error" class="text-red-500 text-center py-4">
      {{ error }}
    </div>
    <div v-else-if="!allBadges.length" class="text-center py-8 text-gray-500">
      هیچ نشانی برای نمایش وجود ندارد
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <Badge v-for="badge in allBadges" :key="badge.id" :badge="badge" />
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import Badge from './Badge.vue'
import axios from 'axios'

export default {
  name: 'BadgesList',
  components: {
    Badge
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

    onMounted(fetchAllBadges);

    return {
      allBadges,
      loading,
      error
    }
  }
}
</script> 