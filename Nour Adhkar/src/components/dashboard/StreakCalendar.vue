<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">روزهای متوالی</h2>
    <div class="flex justify-between items-center">
      <div v-for="(date, index) in lastWeekDates" :key="index" 
           class="flex flex-col items-center gap-2">
        <div class="w-10 h-10 rounded-full flex items-center justify-center"
             :class="{
               'bg-green-100': isCompletedDay(date),
               'bg-red-100': isMissedDay(date),
               'bg-gray-100': !isCompletedDay(date) && !isMissedDay(date)
             }">
          <FontAwesomeIcon 
            v-if="isCompletedDay(date)"
            :icon="['fas', 'check']" 
            class="text-green-600 text-sm"
          />
          <FontAwesomeIcon 
            v-else-if="isMissedDay(date)"
            :icon="['fas', 'xmark']" 
            class="text-red-600 text-sm"
          />
        </div>
        <span class="text-xs text-gray-500">{{ getDayName(date) }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { toJalaali, toGregorian } from 'jalaali-js'

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
    for (let i = 0; i < 7; i++) {
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
      isCompletedDay,
      isMissedDay
    }
  }
}
</script> 