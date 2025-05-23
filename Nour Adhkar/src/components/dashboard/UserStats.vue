<template>
  <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border border-gray-100">
    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
      <FontAwesomeIcon icon="fa-solid fa-chart-line" class="text-primary-600" />
      آمار من
    </h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
      <!-- Streak -->
      <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
        <div class="h-1 bg-primary-500"></div>
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-primary-700 mb-1">روزهای متوالی</span>
              <div class="flex items-end gap-1">
                <span v-if="!loading" class="text-3xl font-bold text-primary-800">{{ streak }}</span>
                <div v-else class="h-8 w-16 bg-primary-200 rounded animate-pulse"></div>
                <span class="text-xs text-primary-600 mb-1">روز</span>
              </div>
            </div>
            <div class="p-3 bg-primary-500 text-white rounded-lg">
              <FontAwesomeIcon icon="fa-solid fa-fire" class="text-xl" />
            </div>
          </div>
          <div class="mt-3 w-full bg-primary-200 rounded-full h-1.5">
            <div class="bg-primary-500 h-1.5 rounded-full" :style="{ width: `${Math.min(streak * 10, 100)}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Heart Score -->
      <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
        <div class="h-1 bg-primary-500"></div>
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-primary-700 mb-1">امتیاز قلب</span>
              <div class="flex items-end gap-1">
                <span v-if="!loading" class="text-3xl font-bold text-primary-800">{{ heartScore }}</span>
                <div v-else class="h-8 w-16 bg-primary-200 rounded animate-pulse"></div>
                <span class="text-xs text-primary-600 mb-1">امتیاز</span>
              </div>
            </div>
            <div class="p-3 bg-primary-500 text-white rounded-lg">
              <FontAwesomeIcon icon="fa-solid fa-heart" class="text-xl" />
            </div>
          </div>
          <div class="mt-3 w-full bg-primary-200 rounded-full h-1.5">
            <div class="bg-primary-500 h-1.5 rounded-full" :style="{ width: `${Math.min(heartScore, 100)}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Global Score -->
      <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
        <div class="h-1 bg-primary-500"></div>
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-primary-700 mb-1">امتیاز کل</span>
              <div class="flex items-end gap-1">
                <span v-if="!loading" class="text-3xl font-bold text-primary-800">{{ score }}</span>
                <div v-else class="h-8 w-16 bg-primary-200 rounded animate-pulse"></div>
                <span class="text-xs text-primary-600 mb-1">امتیاز</span>
              </div>
            </div>
            <div class="p-3 bg-primary-500 text-white rounded-lg">
              <FontAwesomeIcon icon="fa-solid fa-trophy" class="text-xl" />
            </div>
          </div>
          <div class="mt-3 w-full bg-primary-200 rounded-full h-1.5">
            <div class="bg-primary-500 h-1.5 rounded-full" :style="{ width: `${Math.min(score / 10, 100)}%` }"></div>
          </div>
        </div>
      </div>

      <!-- League Progress -->
      <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
        <div class="h-1 bg-primary-500"></div>
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <FontAwesomeIcon :icon="league.icon || 'fa-solid fa-trophy'" class="text-xl text-primary-600" />
              <span class="text-sm font-medium text-primary-700 mb-1">{{ league.name }}</span>
            </div>
            <span v-if="!loading" class="text-3xl font-bold text-primary-800">{{ leagueScore }}</span>
          </div>
          <div v-if="nextLeague" class="mt-1 text-xs text-gray-500">
            لیگ بعدی: <span class="font-semibold text-primary-700">{{ nextLeague.name }}</span>
            <span class="text-primary-600">({{ nextLeaguePoints - leagueScore }} امتیاز تا ارتقا)</span>
          </div>
        </div>
        <div class="mt-3 w-full bg-primary-200 rounded-full h-1.5">
          <div class="bg-primary-500 h-1.5 rounded-full transition-all duration-700 ease-out"
          :style="{ width: `${Math.min(100, nextLeague ? (leagueScore / nextLeaguePoints) * 100 : 100)}%` }">
        </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, onMounted, watch, inject } from 'vue'
import { useStore } from 'vuex'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { 
  faFire, 
  faHeart, 
  faTrophy,
  faChartLine,
  faStar
} from '@fortawesome/free-solid-svg-icons'
import axios from 'axios'

// Add icons to the library
library.add(faFire, faHeart, faTrophy, faChartLine, faStar)

export default {
  name: 'UserStats',
  components: {
    FontAwesomeIcon
  },
  setup(props) {
    const store = useStore()
    const loading = ref(true)
    const user = computed(() => store.state.user)

    const streak = computed(() => user.value?.streak || 0)
    const heartScore = computed(() => user.value?.heart_score || 0)
    const score = computed(() => user.value?.score || 0)
    const userName = computed(() => {
      console.log('User state:', user.value) // Debug log
      return user.value?.name || 'کاربر'
    })

    // League progress state
    const BASE_API_URL = inject('BASE_API_URL')
    const league = ref({ name: 'لیگ مبتدی', icon: 'fa-solid fa-star' })
    const leagueScore = ref(0)
    const nextLeague = ref(null)
    const nextLeaguePoints = ref(0)

    const fetchLeagueProgress = async () => {
      try {
        const response = await axios.get(`${BASE_API_URL}/user/league-progress`)
        const data = response.data
        league.value = { ...league.value, ...data.current_league }
        leagueScore.value = data.current_score
        nextLeague.value = data.next_league
        nextLeaguePoints.value = data.next_league_points
      } catch (error) {
        console.error('Error fetching league progress:', error)
      }
    }

    onMounted(() => {
      fetchLeagueProgress()
    })

    // Watch for changes in the user state
    watch(() => store.state.user, (newUser) => {
      console.log('User state changed:', newUser) // Debug log
      if (newUser) {
        loading.value = false
      }
    }, { immediate: true })

    return {
      streak,
      heartScore,
      score,
      userName,
      loading,
      user,
      league,
      leagueScore,
      nextLeague,
      nextLeaguePoints
    }
  }
}
</script> 