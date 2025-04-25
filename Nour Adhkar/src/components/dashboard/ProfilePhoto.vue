<template>
  <div class="relative group">
    <div class="relative w-16 h-16 rounded-full overflow-hidden">
      <img 
        v-if="photoUrl" 
        :src="photoUrl" 
        alt="Profile Photo" 
        class="w-full h-full object-cover"
        @error="handleImageError"
      />
      <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
        <FontAwesomeIcon :icon="['fas', 'user']" class="text-gray-400 text-2xl" />
      </div>
      
      <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
        <label class="cursor-pointer p-2 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 transition-colors">
          <FontAwesomeIcon :icon="['fas', 'camera']" class="text-white" />
          <input 
            type="file" 
            class="hidden" 
            accept="image/*"
            @change="handleFileChange"
          />
        </label>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'ProfilePhoto',
  components: {
    FontAwesomeIcon
  },
  props: {
    photoUrl: {
      type: String,
      default: ''
    }
  },
  emits: ['photo-change', 'error'],
  setup(props, { emit }) {
    const handleFileChange = (event) => {
      const file = event.target.files[0]
      if (!file) {
        emit('error', 'لطفاً یک فایل انتخاب کنید')
        return
      }

      // Validate file type
      if (!file.type.startsWith('image/')) {
        emit('error', 'لطفاً یک فایل تصویر انتخاب کنید')
        return
      }

      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        emit('error', 'حجم فایل نباید بیشتر از ۲ مگابایت باشد')
        return
      }

      // Emit the file directly
      emit('photo-change', file)
    }

    const handleImageError = () => {
      emit('error', 'خطا در بارگذاری تصویر')
    }

    return {
      handleFileChange,
      handleImageError
    }
  }
}
</script> 