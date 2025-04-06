<template>
  <div v-if="show" class="pagination">
    <button 
      class="pagination-button" 
      :disabled="currentPage === 1" 
      @click="$emit('change-page', currentPage - 1)"
    >
      <font-awesome-icon icon="fa-solid fa-chevron-right" />
    </button>
    
    <span class="page-info">
      صفحه {{ currentPage }} از {{ totalPages }}
    </span>
    
    <button 
      class="pagination-button" 
      :disabled="currentPage === totalPages" 
      @click="$emit('change-page', currentPage + 1)"
    >
      <font-awesome-icon icon="fa-solid fa-chevron-left" />
    </button>
  </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core';
import { faChevronRight, faChevronLeft } from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(faChevronRight, faChevronLeft);

export default {
  name: 'PaginationControls',
  props: {
    currentPage: {
      type: Number,
      required: true
    },
    totalPages: {
      type: Number,
      required: true
    },
    show: {
      type: Boolean,
      default: true
    }
  }
};
</script>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination-button {
  border: 1px solid #ddd;
  background-color: white;
  border-radius: 4px;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.95rem;
  color: #666;
}

/* Dark mode styles */
body.dark-mode .pagination-button {
  background-color: #333;
  border-color: #444;
  color: #eee;
}
</style> 