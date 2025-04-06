<template>
  <div v-if="shouldShowPagination" class="pagination">
    <button 
      :disabled="pagination.current_page === 1" 
      @click="$emit('page-change', pagination.current_page - 1)"
      class="pagination-button"
    >
      قبلی
    </button>
    
    <span class="page-info">صفحه {{ pagination.current_page }} از {{ pagination.last_page }}</span>
    
    <button 
      :disabled="pagination.current_page === pagination.last_page" 
      @click="$emit('page-change', pagination.current_page + 1)"
      class="pagination-button"
    >
      بعدی
    </button>
  </div>
</template>

<script>
export default {
  name: 'AdminPagination',
  props: {
    pagination: {
      type: Object,
      required: true,
      default: () => ({
        current_page: 1,
        last_page: 1,
        total: 0
      }),
      validator: (value) => {
        if (!value) return false
        return 'current_page' in value &&
               'last_page' in value &&
               'total' in value
      }
    }
  },
  computed: {
    shouldShowPagination() {
      return this.pagination && 
             this.pagination.last_page && 
             this.pagination.last_page > 1
    }
  },
  emits: ['page-change']
}
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  gap: 16px;
  padding: 16px;
}

.pagination-button {
  background-color: #A79277;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
  font-family: inherit;
}

.pagination-button:disabled {
  background-color: #d1d1d1;
  cursor: not-allowed;
}

.pagination-button:not(:disabled):hover {
  background-color: #8a7660;
}

.page-info {
  color: #666;
}

/* Dark mode */
:deep(body.dark-mode) .page-info {
  color: #ddd;
}
</style> 