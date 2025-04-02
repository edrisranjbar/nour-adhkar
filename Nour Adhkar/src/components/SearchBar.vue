<template>
  <div class="search-container" :class="{'search-results-active': isSearchFocused && searchResults.length > 0}">
    <div class="search-bar">
      <font-awesome-icon icon="fa-solid fa-search" class="search-icon" />
      <input 
        type="text" 
        v-model="searchQuery" 
        :placeholder="placeholder" 
        class="search-input"
        @focus="isSearchFocused = true"
        @blur="handleSearchBlur"
      />
    </div>
    <div v-if="isSearchFocused && searchResults.length > 0" class="search-results">
      <div 
        v-for="result in searchResults" 
        :key="result.id" 
        class="search-result-item"
        @click="navigateToResult(result)"
      >
        <span>{{ result.title }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SearchBar",
  props: {
    collections: {
      type: Array,
      required: true
    },
    placeholder: {
      type: String,
      default: "جستجوی اذکار..."
    }
  },
  data() {
    return {
      searchQuery: '',
      isSearchFocused: false
    };
  },
  computed: {
    searchResults() {
      const query = this.searchQuery.trim().toLowerCase();
      
      // Only search if query is 3 or more characters
      if (!query || query.length < 3) return [];
      
      const results = [];
      const addedCategories = new Set(); // Track added categories
      
      this.collections.forEach(collection => {
        // Add category itself if it matches and not already added
        if (collection.name.toLowerCase().includes(query) && !addedCategories.has(collection.path)) {
          results.push({
            id: collection.path,
            title: `اذکار ${collection.name}`,
            path: collection.path,
            type: 'category'
          });
          addedCategories.add(collection.path); // Mark this category as added
        }
        
        // Check individual adhkar
        if (collection.items) {
          collection.items.forEach(item => {
            // Skip empty or null items
            if (!item || !item.arabic && !item.translation) return;
            
            if (
              (item.arabic && item.arabic.toLowerCase().includes(query)) || 
              (item.translation && item.translation.toLowerCase().includes(query))
            ) {
              // Ensure we have valid title
              const title = item.title || (item.arabic ? `${item.arabic.substring(0, 20)}...` : null);
              
              // Only add if we have a valid title
              if (title) {
                results.push({
                  id: `${collection.path}-${item.id || (item.arabic ? item.arabic.substring(0, 10) : 'item')}`,
                  title: title,
                  path: collection.path,
                  itemId: item.id,
                  type: 'item'
                });
              }
            }
          });
        }
      });
      
      return results.slice(0, 5); // Limit to 5 results
    }
  },
  methods: {
    handleSearchBlur() {
      // Delay hiding to allow for clicking on results
      setTimeout(() => {
        this.isSearchFocused = false;
      }, 200);
    },
    navigateToResult(result) {
      if (result.type === 'category') {
        this.$router.push({ path: `/${result.path}` });
      } else {
        // Navigate to specific item in collection
        this.$router.push({ 
          path: `/${result.path}`,
          query: { highlight: result.itemId }
        });
      }
      this.searchQuery = '';
      this.isSearchFocused = false;
    }
  }
};
</script>

<style scoped>
.search-container {
  width: 100%;
  max-width: 1200px;
  margin: 1rem auto;
  padding: 0 16px;
  position: relative;
}

.search-bar {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  position: relative;
}

.search-results-active .search-bar {
  border-radius: 8px 8px 0 0;
}

body.dark-mode .search-bar {
  background: #2d2d2d;
}

.search-icon {
  color: #A79277;
  margin-left: 0.5rem;
}

.search-input {
  width: 100%;
  border: none;
  background: transparent;
  padding: 0.5rem;
  color: #333;
  direction: rtl;
  outline: none;
}

body.dark-mode .search-input {
  color: #eee;
}

.search-input::placeholder {
  color: #999;
}

.search-results {
  position: absolute;
  top: 100%;
  right: 16px;
  left: 16px;
  background: white;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 10;
  max-height: 300px;
  overflow-y: auto;
}

body.dark-mode .search-results {
  background: #2d2d2d;
}

.search-result-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #eee;
  transition: background 0.2s;
}

body.dark-mode .search-result-item {
  border-bottom: 1px solid #444;
}

.search-result-item:hover {
  background: #f5f5f5;
}

body.dark-mode .search-result-item:hover {
  background: #3a3a3a;
}
</style> 