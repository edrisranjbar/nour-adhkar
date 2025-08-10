<template>
  <div class="bg-gradient-to-br from-primary-50 to-primary-100 dark:from-slate-800 dark:to-slate-900 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 border border-transparent dark:border-slate-700">
    <div class="h-1 bg-primary-500"></div>
    <div class="p-5 flex flex-col gap-2">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-primary-500 text-white rounded-lg">
          <i :class="['fas', currentLeague.icon, 'text-xl']"></i>
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-medium text-primary-700 dark:text-primary-300 mb-1">{{ currentLeague.name }}</span>
          <span class="text-xs text-primary-600 dark:text-primary-300">امتیاز فعلی: <span class="font-bold text-primary-800 dark:text-primary-200">{{ currentScore }}</span></span>
        </div>
      </div>
      <div class="mt-2 w-full bg-primary-200 dark:bg-slate-700 rounded-full h-1.5">
        <div
          class="bg-primary-500 dark:bg-primary-400 h-1.5 rounded-full transition-all duration-700 ease-out"
          :style="{
            width: `${Math.max(5, nextLeague ? (currentScore / nextLeaguePoints) * 100 : 100)}%`
          }"
        ></div>
      </div>
      <div v-if="nextLeague" class="flex items-center justify-between mt-2">
        <span class="text-xs text-gray-500 dark:text-gray-300">لیگ بعدی: <span class="font-semibold text-primary-700 dark:text-primary-300">{{ nextLeague.name }}</span></span>
        <span class="text-xs text-primary-600 dark:text-primary-300">{{ nextLeaguePoints - currentScore }} امتیاز تا ارتقا</span>
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