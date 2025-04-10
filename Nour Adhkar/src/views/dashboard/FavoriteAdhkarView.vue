<template>
  <div class="favorite-adhkar">
    <h1>اذکار مورد علاقه</h1>
    
    <div v-if="!favorites.length" class="empty-state">
      <font-awesome-icon icon="fa-solid fa-heart" class="empty-icon" />
      <p>هنوز هیچ ذکری به علاقه‌مندی‌ها اضافه نشده است</p>
      <RouterLink to="/" class="browse-button">
        مشاهده اذکار
        <font-awesome-icon icon="fa-solid fa-arrow-left" />
      </RouterLink>
    </div>

    <div v-else class="favorites-list">
      <div v-for="dhikr in favorites" :key="dhikr.id" class="favorite-card">
        <div class="favorite-header">
          <h3>{{ dhikr.title }}</h3>
          <button class="remove-button" @click="removeFavorite(dhikr.id)">
            <font-awesome-icon icon="fa-solid fa-heart-broken" />
          </button>
        </div>
        
        <div class="favorite-content">
          <p class="arabic-text">{{ dhikr.arabicText }}</p>
          <p class="translation">{{ dhikr.translation }}</p>
          <div class="favorite-meta">
            <span class="source">{{ dhikr.source }}</span>
            <span class="category">{{ dhikr.category }}</span>
          </div>
        </div>

        <div class="favorite-actions">
          <button class="action-button counter-button" @click="startCounter(dhikr)">
            <font-awesome-icon icon="fa-solid fa-play" />
            شروع ذکر
          </button>
          <button class="action-button share-button" @click="shareDhikr(dhikr)">
            <font-awesome-icon icon="fa-solid fa-share-alt" />
            اشتراک‌گذاری
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FavoriteAdhkarView',
  data() {
    return {
      favorites: [] // Will be populated from Vuex store
    }
  },
  methods: {
    async removeFavorite(id) {
      try {
        await this.$store.dispatch('removeFavorite', id);
        // Show success toast
      } catch (error) {
        // Show error toast
        console.error('Error removing favorite:', error);
      }
    },
    startCounter(dhikr) {
      // Navigate to counter view with pre-filled dhikr
      this.$router.push({
        name: 'counter',
        query: { dhikr: dhikr.id }
      });
    },
    shareDhikr(dhikr) {
      // Implement sharing functionality
      if (navigator.share) {
        navigator.share({
          title: dhikr.title,
          text: `${dhikr.arabicText}\n${dhikr.translation}`,
          url: window.location.href
        }).catch(console.error);
      }
    }
  },
  async created() {
    try {
      // Load favorites from store
      await this.$store.dispatch('loadFavorites');
      this.favorites = this.$store.state.favorites;
    } catch (error) {
      console.error('Error loading favorites:', error);
      // Show error toast
    }
  }
}
</script>

<style scoped>
.favorite-adhkar {
  padding: 1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  background: white;
  border-radius: 8px;
  margin: 2rem 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 3rem;
  color: #A79277;
  margin-bottom: 1rem;
}

.browse-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #A79277;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  margin-top: 1rem;
  transition: background-color 0.2s ease;
}

.browse-button:hover {
  background-color: #8a7660;
}

.favorites-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
  margin-top: 2rem;
}

.favorite-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.favorite-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.favorite-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.remove-button {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}

.remove-button:hover {
  background-color: rgba(220, 53, 69, 0.1);
}

.favorite-content {
  margin: 1rem 0;
}

.arabic-text {
  font-size: 1.5rem;
  text-align: center;
  margin: 1rem 0;
  color: #333;
}

.translation {
  color: #666;
  text-align: center;
  margin: 0.5rem 0;
}

.favorite-meta {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #666;
}

.favorite-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.action-button {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.counter-button {
  background-color: #A79277;
  color: white;
}

.counter-button:hover {
  background-color: #8a7660;
}

.share-button {
  background-color: #f8f9fa;
  color: #333;
  border: 1px solid #dee2e6;
}

.share-button:hover {
  background-color: #e9ecef;
}

body.dark-mode {
  .empty-state,
  .favorite-card {
    background-color: #333;
  }

  .favorite-header h3,
  .arabic-text {
    color: #fff;
  }

  .translation,
  .favorite-meta {
    color: #aaa;
  }

  .share-button {
    background-color: #444;
    color: #fff;
    border-color: #555;
  }

  .share-button:hover {
    background-color: #555;
  }
}

@media (max-width: 768px) {
  .favorites-list {
    grid-template-columns: 1fr;
  }
}
</style> 