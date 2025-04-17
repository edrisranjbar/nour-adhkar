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
          <p class="arabic-text">{{ dhikr.arabic_text }}</p>
          <p class="translation">{{ dhikr.translation }}</p>
        </div>

        <div class="favorite-actions">
          <button class="action-button counter-button" @click="startCounter(dhikr)">
            <font-awesome-icon icon="fa-solid fa-play" />
            شروع ذکر
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'FavoriteAdhkarView',
  computed: {
    ...mapState(['favorites'])
  },
  methods: {
    async removeFavorite(id) {
      try {
        await this.$store.dispatch('toggleFavorite', id);
        this.$toast.success('ذکر از علاقه‌مندی‌ها حذف شد');
      } catch (error) {
        console.error('Error removing favorite:', error);
        this.$toast.error('خطا در حذف ذکر از علاقه‌مندی‌ها');
      }
    },
    startCounter(dhikr) {
      this.$router.push({
        name: 'counter',
        params: { id: dhikr.id }
      });
    }
  },
  async created() {
    try {
      await this.$store.dispatch('loadFavorites');
    } catch (error) {
      console.error('Error loading favorites:', error);
      this.$toast.error('خطا در بارگذاری علاقه‌مندی‌ها');
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
  font-size: 1.1rem;
  transition: color 0.2s ease;
}

.remove-button:hover {
  color: #c82333;
}

.favorite-content {
  margin-bottom: 1.5rem;
}

.arabic-text {
  font-size: 1.4rem;
  text-align: center;
  margin: 1rem 0;
  color: #333;
  line-height: 2;
}

.translation {
  font-size: 0.95rem;
  color: #666;
  text-align: justify;
  line-height: 1.6;
  margin-top: 1rem;
}

.favorite-actions {
  display: flex;
  gap: 0.75rem;
}

.action-button {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.counter-button {
  background-color: #A79277;
  color: white;
}

.counter-button:hover {
  background-color: #8a7660;
}

/* Dark mode styles */
body.dark-mode {
  .favorite-card,
  .empty-state {
    background-color: #333;
  }

  .favorite-header h3,
  .arabic-text {
    color: #fff;
  }

  .translation {
    color: #bbb;
  }
}

@media (max-width: 768px) {
  .favorites-list {
    grid-template-columns: 1fr;
  }
}
</style> 