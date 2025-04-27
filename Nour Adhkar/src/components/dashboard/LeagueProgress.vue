<template>
  <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-gray-900">پیشرفت در لیگ</h3>
      <div class="flex items-center bg-primary/10 px-4 py-2 rounded-full">
        <i :class="['fas', currentLeague.icon, 'text-xl text-primary']"></i>
        <span class="mr-2 font-semibold text-primary">{{ currentLeague.name }}</span>
      </div>
    </div>

    <div class="mb-6">
      <div class="flex justify-between text-sm mb-2">
        <span class="text-gray-600">امتیاز فعلی: <span class="font-semibold text-primary">{{ currentScore }}</span></span>
        <span v-if="nextLeague" class="text-gray-600">امتیاز مورد نیاز: <span class="font-semibold text-primary">{{ nextLeaguePoints }}</span></span>
      </div>
      <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
        <div
          class="h-full rounded-full transition-all duration-700 ease-out"
          :style="{
            width: `${Math.max(5, nextLeague ? (currentScore / nextLeaguePoints) * 100 : 100)}%`,
            backgroundColor: currentLeague.color || '#4F46E5'
          }"
        ></div>
      </div>
    </div>

    <div v-if="nextLeague" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <i class="fas fa-trophy text-yellow-500 mr-2"></i>
          <span class="text-gray-700">لیگ بعدی: <span class="font-semibold">{{ nextLeague.name }}</span></span>
        </div>
        <span class="text-primary font-semibold">{{ nextLeaguePoints - currentScore }} امتیاز</span>
      </div>
      <p class="text-sm text-gray-500 mt-2">برای ارتقا به لیگ بعدی، {{ nextLeaguePoints - currentScore }} امتیاز دیگر نیاز دارید.</p>
    </div>
    <div v-else class="bg-green-50 rounded-lg p-4 border border-green-200">
      <div class="flex items-center justify-center">
        <i class="fas fa-crown text-yellow-500 mr-2"></i>
        <p class="text-green-700 font-semibold">
          تبریک، شما در حال پیشرفت در لیگ هستید!
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'

export default {
  name: 'LeagueProgress',
  setup() {
    const BASE_API_URL = inject('BASE_API_URL')
    const currentLeague = ref({
      name: 'لیگ مبتدی',
      icon: 'fa-star',
      color: '#4F46E5'
    })
    const currentScore = ref(0)
    const nextLeague = ref(null)
    const nextLeaguePoints = ref(0)

    const fetchLeagueProgress = async () => {
      try {
        const response = await axios.get(`${BASE_API_URL}/user/league-progress`)
        const data = response.data
        
        currentLeague.value = {
          ...currentLeague.value,
          ...data.current_league
        }
        currentScore.value = data.current_score
        nextLeague.value = data.next_league
        nextLeaguePoints.value = data.next_league_points
      } catch (error) {
        console.error('Error fetching league progress:', error)
      }
    }

    onMounted(fetchLeagueProgress)

    return {
      currentLeague,
      currentScore,
      nextLeague,
      nextLeaguePoints
    }
  }
}
</script> 