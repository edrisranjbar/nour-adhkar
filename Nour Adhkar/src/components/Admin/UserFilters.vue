<template>
  <div class="users-filters">
    <div class="search-box">
      <font-awesome-icon icon="fa-solid fa-search" class="search-icon" />
      <input 
        type="text" 
        v-model="searchQuery" 
        placeholder="جستجو در کاربران..." 
        class="search-input"
        @input="handleSearch"
      />
    </div>
    
    <div class="filter-actions">
      <select v-model="roleFilter" class="filter-select" @change="applyFilters">
        <option value="">همه نقش‌ها</option>
        <option value="admin">مدیر</option>
        <option value="user">کاربر عادی</option>
      </select>
      
      <select v-model="sortBy" class="filter-select" @change="applyFilters">
        <option value="created_at_desc">جدیدترین</option>
        <option value="created_at_asc">قدیمی‌ترین</option>
        <option value="name_asc">نام (الف تا ی)</option>
        <option value="name_desc">نام (ی تا الف)</option>
      </select>
    </div>
  </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core';
import { faSearch } from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(faSearch);

export default {
  name: 'UserFilters',
  props: {
    initialSearchQuery: {
      type: String,
      default: ''
    },
    initialRoleFilter: {
      type: String,
      default: ''
    },
    initialSortBy: {
      type: String,
      default: 'created_at_desc'
    }
  },
  data() {
    return {
      searchQuery: this.initialSearchQuery,
      roleFilter: this.initialRoleFilter,
      sortBy: this.initialSortBy
    };
  },
  methods: {
    handleSearch() {
      this.$emit('search', this.searchQuery);
    },
    applyFilters() {
      this.$emit('filter-change', {
        searchQuery: this.searchQuery,
        roleFilter: this.roleFilter,
        sortBy: this.sortBy
      });
    }
  }
};
</script>

<style scoped>
.users-filters {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.search-box {
  position: relative;
  width: 100%;
}

.search-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #777;
}

.search-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.95rem;
}

.filter-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.95rem;
  background-color: white;
  min-width: 150px;
}

/* Dark mode styles */
body.dark-mode .search-input,
body.dark-mode .filter-select {
  background-color: #333;
  border-color: #444;
  color: #eee;
}

body.dark-mode .search-icon {
  color: #aaa;
}
</style> 