<template>
  <div class="grid grid-cols-7 gap-1 sm:gap-3">
    <div v-for="(date, index) in lastWeekDates" :key="index" 
         class="flex flex-col items-center gap-1 sm:gap-2">
      <div class="p-1 sm:p-4 w-full aspect-square rounded-lg flex flex-col items-center justify-center transition-all duration-300"
           :class="{
             'bg-primary-50 border border-primary-200 shadow-sm hover:shadow-md hover:bg-primary-100': isCompletedDay(date),
             'bg-gray-50 border border-gray-200 shadow-sm hover:shadow-md hover:bg-gray-100': isMissedDay(date),
             'bg-gray-50 border border-gray-200': !isCompletedDay(date) && !isMissedDay(date)
           }">
        <div class="relative flex items-center justify-center">
          <FontAwesomeIcon 
            v-if="isCompletedDay(date)"
            icon="fa-solid fa-check" 
            class="text-primary-600 text-base sm:text-xl"
          />
          <FontAwesomeIcon 
            v-else-if="isMissedDay(date)"
            icon="fa-solid fa-xmark" 
            class="text-gray-500 text-base sm:text-xl"
          />
          <span v-else class="text-gray-400 text-base sm:text-lg">-</span>
        </div>
        <div class="mt-1 sm:mt-2 text-2xs sm:text-xs font-medium"
             :class="{
               'text-primary-800': isCompletedDay(date),
               'text-gray-600': isMissedDay(date),
               'text-gray-500': !isCompletedDay(date) && !isMissedDay(date)
             }">
          {{ getJalaliDay(date) }}
        </div>
        <div class="text-2xs sm:text-xs"
             :class="{
               'text-primary-600': isCompletedDay(date),
               'text-gray-500': isMissedDay(date),
               'text-gray-400': !isCompletedDay(date) && !isMissedDay(date)
             }">
          {{ getDayShortName(date) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
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
    const loading = ref(true)
    const completedDays = ref([])

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