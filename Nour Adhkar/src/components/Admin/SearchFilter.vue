<template>
    <div class="filters">
      <div class="search-box">
        <font-awesome-icon icon="fa-solid fa-search" class="search-icon" />
        <input 
          type="text" 
          :value="searchQuery"
          @input="$emit('update:searchQuery', $event.target.value)"
          :placeholder="searchPlaceholder" 
          class="search-input"
        />
      </div>
      
      <div class="filter-actions">
        <slot name="filters"></slot>
        
        <select 
          :value="sortBy"
          @change="$emit('update:sortBy', $event.target.value)" 
          class="filter-select"
        >
          <option value="created_at_desc">جدیدترین</option>
          <option value="created_at_asc">قدیمی‌ترین</option>
          <option value="title_asc">عنوان (الف تا ی)</option>
          <option value="title_desc">عنوان (ی تا الف)</option>
        </select>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'SearchFilter',
    props: {
      searchQuery: {
        type: String,
        required: true
      },
      searchPlaceholder: {
        type: String,
        default: 'جستجو...'
      },
      sortBy: {
        type: String,
        required: true
      }
    }
  }
  </script>
  
  <style scoped>
  .filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
  }
  
  .search-box {
    flex: 1;
    position: relative;
  }
  
  .search-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
  }
  
  .search-input {
    width: 100%;
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
  }
  
  .filter-actions {
    display: flex;
    gap: 1rem;
  }
  
  .filter-select {
    padding: 0.75rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    min-width: 150px;
    font-family: inherit;
  }
  
  /* Dark mode styles */
  body.dark-mode {
    .search-input,
    .filter-select {
      background-color: #444;
      border-color: #555;
      color: #fff;
    }
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .filters {
      flex-direction: column;
    }
  
    .filter-actions {
      flex-direction: column;
    }
  
    .filter-select {
      width: 100%;
    }
  }
  </style>