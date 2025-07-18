<template>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 py-8 px-4" dir="rtl">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="bg-white rounded-3xl shadow-lg p-6 border border-gray-100">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4 space-x-reverse">
            <div class="relative">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
                <img v-if="user.avatar" :src="user.avatar" alt="avatar" class="w-full h-full object-cover rounded-full" />
                <span v-else>{{ getInitial }}</span>
              </div>
              <div class="absolute -bottom-1 -left-1 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold text-white border-2 border-white">
                {{ user.streak }}
              </div>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-800">{{ user.name }}</h2>
              <div class="flex items-center space-x-2 space-x-reverse mt-1">
                <div class="flex items-center space-x-1 space-x-reverse bg-red-50 px-3 py-1 rounded-full">
                  <span class="text-sm font-semibold text-red-600">{{ user.heart_score }}</span>
                  <font-awesome-icon icon="heart" class="w-4 h-4 text-red-600 fill-current" />
                </div>
              </div>
            </div>
          </div>
          <div class="text-left">
            <div class="flex items-center space-x-1 space-x-reverse">
              <span class="text-sm font-semibold text-red-600">{{ user.heart_score }}</span>
              <font-awesome-icon icon="heart" class="w-4 h-4 text-red-600 fill-current" />
            </div>
            <div class="w-24 h-2 bg-gray-200 rounded-full mt-2">
              <div class="h-full bg-gradient-to-l from-red-400 to-red-500 rounded-full transition-all duration-500" :style="{ width: user.heart_score + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-medium text-gray-600">تداوم</div>
              <div class="text-2xl font-bold text-gray-800">{{ user.streak }}</div>
              <div class="text-xs text-orange-500 font-medium">روز متوالی</div>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
              <font-awesome-icon icon="fire" class="w-6 h-6 text-orange-500" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-medium text-gray-600">هدف روزانه</div>
              <div class="text-2xl font-bold text-gray-800">50</div>
              <div class="text-xs text-blue-500 font-medium">امتیاز</div>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <font-awesome-icon icon="bullseye" class="w-6 h-6 text-blue-500" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-medium text-gray-600">لیگ</div>
              <div class="text-2xl font-bold text-gray-800">{{ league ?? 'مبتدی' }}</div>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
              <font-awesome-icon icon="trophy" class="w-6 h-6 text-yellow-500" />
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly Activity -->
      <div class="bg-white rounded-3xl shadow-lg p-6 border border-gray-100">
        <div class="flex items-center space-x-3 space-x-reverse mb-6">
          <font-awesome-icon icon="calendar-check" class="w-6 h-6 text-blue-500" />
          <h3 class="text-xl font-bold text-gray-800">فعالیت هفتگی</h3>
          <div class="flex-1"></div>
          <div class="text-sm text-gray-500 font-medium">{{ weekProgress }}% تکمیل</div>
        </div>

        <div class="grid grid-cols-7 gap-2 mb-6">
          <div v-for="(day, index) in lastWeek" :key="index" class="flex flex-col">
            <div class="text-xs text-gray-500 font-medium mb-2">{{ day.label }}</div>
            <div
              class="w-12 h-12 rounded-xl flex items-center justify-center font-bold transition-all duration-300 transform hover:scale-110"
              :class="{
                'bg-gradient-to-br from-green-400 to-green-500 text-white shadow-md': day.completed,
                'bg-gradient-to-br from-blue-400 to-blue-500 text-white shadow-md animate-pulse': day.isToday,
                'bg-gray-100 text-gray-400 border-2 border-dashed border-gray-300': !day.completed && !day.isToday
              }"
            >
              <template v-if="day.completed">✓</template>
              <template v-else-if="day.isToday">
                <div class="w-3 h-3 bg-white rounded-full"></div>
              </template>
              <template v-else>
                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, inject, ref, onMounted } from 'vue'
import { useStore } from 'vuex'
import defaultAvatar from '@/assets/icons/back-button.svg' // Replace with a real default avatar
import axios from 'axios'
import jalaali from 'jalaali-js'

export default {
  name: 'DashboardView',
  
  setup() {
    const store = useStore()
    const user = computed(() => store.state.user)

    const getInitial = computed(() => {
      if (!user.value?.name) return '؟' // fallback
      return user.value.name.trim().charAt(0)
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

const lastWeek = computed(() => {
  const days = []
  const today = new Date()

  for (let i = 6; i >= 0; i--) {
    const date = new Date(today)
    date.setDate(date.getDate() - i)

    const jd = jalaali.toJalaali(date.getFullYear(), date.getMonth() + 1, date.getDate())
    const isoDate = date.toISOString().split('T')[0]

    days.push({
      label: date.toLocaleDateString('fa-IR', { weekday: 'long' }),
      dayNumber: jd.jd,
      isToday: i === 0,
      completed: user.value?.completed_dates?.includes(isoDate),
      isFuture: date > today,
      persianDate: `${jd.jd}/${jd.jm}/${jd.jy}`
    })
  }

  return days
})

    const weekProgress = computed(() => {
      const completed = lastWeek.value.filter(d => d.completed).length
      return Math.round((completed / 7) * 100)
    })
    return { user, lastWeek, weekProgress, defaultAvatar, getInitial }
  }
}
</script>

<style>
</style>