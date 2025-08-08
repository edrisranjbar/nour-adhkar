<template>
  <div :class="[
    'backdrop-blur-sm rounded-3xl shadow-xl border p-6 lg:p-8 transition-all duration-300',
    isDarkMode 
      ? 'bg-gray-800/80 border-gray-700/50 shadow-black/40' 
      : 'bg-white/80 border-white/20'
  ]">
    <h2 :class="[
      'text-xl lg:text-2xl font-bold mb-6 flex items-center gap-3 transition-colors duration-300',
      isDarkMode ? 'text-gray-100' : 'text-gray-800'
    ]">
      <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
        <FontAwesomeIcon icon="fa-solid fa-chart-line" class="text-white text-lg" />
      </div>
      آمار من
    </h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
      <!-- Streak -->
      <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-orange-100">
        <div class="h-1 bg-gradient-to-r from-orange-500 to-red-500"></div>
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-orange-700 mb-1">روزهای متوالی</span>
              <div class="flex items-end gap-1">
                <span v-if="!loading" class="text-3xl font-bold text-orange-800">{{ streak }}</span>
                <div v-else class="h-8 w-16 bg-orange-200 rounded animate-pulse"></div>
                <span class="text-xs text-orange-600 mb-1">روز</span>
              </div>
            </div>
            <div class="p-3 bg-gradient-to-br from-orange-500 to-red-500 text-white rounded-xl shadow-lg">
              <FontAwesomeIcon icon="fa-solid fa-fire" class="text-xl" />
            </div>
          </div>
          <div class="mt-3 w-full bg-orange-200 rounded-full h-2">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 h-2 rounded-full transition-all duration-700" :style="{ width: `${Math.min(streak * 10, 100)}%` }"></div>
          </div>
        </div>
      </div>

             <!-- Heart Score -->
       <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-pink-100">
         <div class="h-1 bg-gradient-to-r from-pink-500 to-rose-500"></div>
         <div class="p-5">
           <div class="flex items-center justify-between">
             <div class="flex flex-col">
               <span class="text-sm font-medium text-pink-700 mb-1">امتیاز قلب</span>
               <div class="flex items-end gap-1">
                 <span v-if="!loading" class="text-3xl font-bold text-pink-800">{{ heartScore }}</span>
                 <div v-else class="h-8 w-16 bg-pink-200 rounded animate-pulse"></div>
                 <span class="text-xs text-pink-600 mb-1">امتیاز</span>
               </div>
             </div>
             <div class="p-3 bg-gradient-to-br from-pink-500 to-rose-500 text-white rounded-xl shadow-lg">
               <FontAwesomeIcon icon="fa-solid fa-heart" class="text-xl" />
             </div>
           </div>
           <div class="mt-3 w-full bg-pink-200 rounded-full h-2">
             <div class="bg-gradient-to-r from-pink-500 to-rose-500 h-2 rounded-full transition-all duration-700" :style="{ width: `${Math.min(heartScore, 100)}%` }"></div>
           </div>
         </div>
       </div>

             <!-- Global Score -->
       <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-blue-100">
         <div class="h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
         <div class="p-5">
           <div class="flex items-center justify-between">
             <div class="flex flex-col">
               <span class="text-sm font-medium text-blue-700 mb-1">امتیاز کل</span>
               <div class="flex items-end gap-1">
                 <span v-if="!loading" class="text-3xl font-bold text-blue-800">{{ score }}</span>
                 <div v-else class="h-8 w-16 bg-blue-200 rounded animate-pulse"></div>
                 <span class="text-xs text-blue-600 mb-1">امتیاز</span>
               </div>
             </div>
             <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-500 text-white rounded-xl shadow-lg">
               <FontAwesomeIcon icon="fa-solid fa-trophy" class="text-xl" />
             </div>
           </div>
           <div class="mt-3 w-full bg-blue-200 rounded-full h-2">
             <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-700" :style="{ width: `${Math.min(score / 10, 100)}%` }"></div>
           </div>
         </div>
       </div>

             <!-- League Progress -->
       <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-yellow-100">
         <div class="h-1 bg-gradient-to-r from-yellow-500 to-amber-500"></div>
         <div class="p-5">
           <div class="flex items-center justify-between">
             <div class="flex flex-col">
               <span class="text-sm font-medium text-yellow-700 mb-1">{{ league.name }}</span>
               <div class="flex items-end gap-1">
                 <span v-if="!loading" class="text-3xl font-bold text-yellow-800">{{ leagueScore }}</span>
                 <div v-else class="h-8 w-16 bg-yellow-200 rounded animate-pulse"></div>
                 <span class="text-xs text-yellow-600 mb-1">امتیاز</span>
               </div>
             </div>
             <div class="p-3 bg-gradient-to-br from-yellow-500 to-amber-500 text-white rounded-xl shadow-lg">
               <FontAwesomeIcon :icon="league.icon || 'fa-solid fa-trophy'" class="text-xl" />
             </div>
           </div>
           <div v-if="nextLeague" class="mt-1 text-xs text-gray-500">
             لیگ بعدی: <span class="font-semibold text-yellow-700">{{ nextLeague.name }}</span>
             <span class="text-yellow-600">({{ nextLeaguePoints - leagueScore }} امتیاز تا ارتقا)</span>
           </div>
           <div class="mt-3 w-full bg-yellow-200 rounded-full h-2">
             <div class="bg-gradient-to-r from-yellow-500 to-amber-500 h-2 rounded-full transition-all duration-700 ease-out" :style="{ width: `${Math.min(100, nextLeague ? (leagueScore / nextLeaguePoints) * 100 : 100)}%` }"></div>
           </div>
         </div>
       </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, onMounted, watch, inject } from 'vue'
import { useStore } from 'vuex'
import { useSettingsStore } from '@/stores/settings'
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
    const settingsStore = useSettingsStore()
    const loading = ref(true)
    const user = computed(() => store.state.user)
    const isDarkMode = computed(() => settingsStore.darkMode)

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
      nextLeaguePoints,
      isDarkMode
    }
  }
}
</script> 