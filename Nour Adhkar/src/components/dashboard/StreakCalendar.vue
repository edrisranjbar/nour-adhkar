<template>
  <div class="grid grid-cols-7 gap-2 sm:gap-4">
    <div v-for="(date, index) in lastWeekDates" :key="index" 
         class="flex flex-col items-center gap-2">
      <div class="p-2 sm:p-4 w-full aspect-square rounded-2xl flex flex-col items-center justify-center transition-all duration-300 hover:scale-105"
           :class="{
             'bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 shadow-lg hover:shadow-xl hover:bg-green-100': isCompletedDay(date),
             'bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-200 shadow-lg hover:shadow-xl hover:bg-red-100': isMissedDay(date),
             'bg-gradient-to-br from-gray-50 to-slate-50 border-2 border-gray-200 shadow-md hover:shadow-lg hover:bg-gray-100': !isCompletedDay(date) && !isMissedDay(date)
           }">
        <div class="relative flex items-center justify-center">
          <div v-if="isCompletedDay(date)" class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center shadow-lg">
            <FontAwesomeIcon 
              icon="fa-solid fa-check" 
              class="text-white text-sm sm:text-lg"
            />
          </div>
          <div v-else-if="isMissedDay(date)" class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-red-500 to-rose-500 rounded-full flex items-center justify-center shadow-lg">
            <FontAwesomeIcon 
              icon="fa-solid fa-xmark" 
              class="text-white text-sm sm:text-lg"
            />
          </div>
          <div v-else class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-gray-300 to-slate-400 rounded-full flex items-center justify-center">
            <span class="text-white text-sm sm:text-lg font-bold">-</span>
          </div>
        </div>
        <div class="mt-2 sm:mt-3 text-xs sm:text-sm font-bold"
             :class="{
               'text-green-800': isCompletedDay(date),
               'text-red-800': isMissedDay(date),
               'text-gray-600': !isCompletedDay(date) && !isMissedDay(date)
             }">
          {{ getJalaliDay(date) }}
        </div>
        <div class="text-xs sm:text-sm font-medium"
             :class="{
               'text-green-600': isCompletedDay(date),
               'text-red-600': isMissedDay(date),
               'text-gray-500': !isCompletedDay(date) && !isMissedDay(date)
             }">
          {{ getDayShortName(date) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { useSettingsStore } from '@/stores/settings'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { toJalaali, toGregorian } from 'jalaali-js'
import { library } from '@fortawesome/fontawesome-svg-core'
import { 
  faCheck, 
  faXmark
} from '@fortawesome/free-solid-svg-icons'

// Add icons to the library
library.add(faCheck, faXmark)

export default {
  name: 'StreakCalendar',
  components: {
    FontAwesomeIcon
  },
  setup() {
    const store = useStore()
    const settingsStore = useSettingsStore()
    const loading = ref(true)
    const completedDays = ref([])
    const isDarkMode = computed(() => settingsStore.darkMode)

    const lastWeekDates = ref([])
    const today = new Date()

    // Generate last 7 days dates in Jalali
    for (let i = 6; i >= 0; i--) {
      const date = new Date(today)
      date.setDate(date.getDate() - i)
      const jDate = toJalaali(date.getFullYear(), date.getMonth() + 1, date.getDate())
      lastWeekDates.value.push({
        date: date,
        gregorian: date.toISOString().split('T')[0],
        jalali: `${jDate.jy}/${jDate.jm}/${jDate.jd}`,
        dayIndex: date.getDay()
      })
    }

    const getDayName = (date) => {
      const days = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه']
      return days[date.dayIndex]
    }
    
    const getDayShortName = (date) => {
      const shortDays = ['یک', 'دو', 'سه', 'چهار', 'پنج', 'جمعه', 'شنبه']
      return shortDays[date.dayIndex]
    }
    
    const getJalaliDay = (date) => {
      const jDate = date.jalali.split('/')
      return jDate[2] // Return just the day part
    }

    const fetchCompletedDays = async () => {
      try {
        loading.value = true
        const days = await store.dispatch('fetchCompletedDays')
        completedDays.value = days || []
      } catch (error) {
        console.error('Error fetching completed days:', error)
        completedDays.value = []
      } finally {
        loading.value = false
      }
    }

    const isCompletedDay = (date) => {
      if (!completedDays.value.length) return false
      return completedDays.value.includes(date.gregorian)
    }

    const isMissedDay = (date) => {
      const todayObj = new Date()
      todayObj.setHours(0, 0, 0, 0)
      
      // Only mark as missed if:
      // 1. The day is in the past (not today or future)
      // 2. The day is not completed
      return date.date < todayObj && !isCompletedDay(date)
    }

    // Watch for changes in the store's completedDays
    watch(() => store.state.completedDays, (newDays) => {
      completedDays.value = newDays || []
    }, { immediate: true })

    onMounted(() => {
      fetchCompletedDays()
    })

    return {
      loading,
      lastWeekDates,
      getDayName,
      getDayShortName,
      getJalaliDay,
      isCompletedDay,
      isMissedDay
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