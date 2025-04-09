<template>
  <div class="custom-adhkar">
    <div class="header">
      <h1>اذکار شخصی</h1>
      <button class="add-button" @click="showAddForm = true">
        <font-awesome-icon icon="fa-solid fa-plus" />
        افزودن ذکر جدید
      </button>
    </div>

    <!-- Add/Edit Form -->
    <div v-if="showAddForm" class="form-overlay">
      <div class="form-container">
        <h2>{{ editingDhikr ? 'ویرایش ذکر' : 'افزودن ذکر جدید' }}</h2>
        <form @submit.prevent="saveDhikr">
          <div class="form-group">
            <label for="title">عنوان</label>
            <input
              id="title"
              v-model="formData.title"
              type="text"
              required
              placeholder="عنوان ذکر را وارد کنید"
            />
          </div>

          <div class="form-group">
            <label for="arabicText">متن عربی</label>
            <textarea
              id="arabicText"
              v-model="formData.arabicText"
              required
              placeholder="متن عربی ذکر را وارد کنید"
              dir="rtl"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="translation">ترجمه</label>
            <textarea
              id="translation"
              v-model="formData.translation"
              required
              placeholder="ترجمه ذکر را وارد کنید"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="category">دسته‌بندی</label>
            <input
              id="category"
              v-model="formData.category"
              type="text"
              placeholder="دسته‌بندی ذکر را وارد کنید"
            />
          </div>

          <div class="form-group">
            <label for="count">تعداد تکرار پیشنهادی</label>
            <input
              id="count"
              v-model.number="formData.recommendedCount"
              type="number"
              min="1"
              placeholder="تعداد تکرار را وارد کنید"
            />
          </div>

          <div class="form-actions">
            <button type="button" class="cancel-button" @click="closeForm">
              انصراف
            </button>
            <button type="submit" class="save-button">
              {{ editingDhikr ? 'ذخیره تغییرات' : 'افزودن ذکر' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!customAdhkar.length && !showAddForm" class="empty-state">
      <font-awesome-icon icon="fa-solid fa-book" class="empty-icon" />
      <p>شما هنوز هیچ ذکر شخصی ندارید</p>
      <p class="subtitle">برای افزودن ذکر جدید روی دکمه بالا کلیک کنید</p>
    </div>

    <!-- Custom Adhkar List -->
    <div v-else-if="!showAddForm" class="custom-list">
      <div v-for="dhikr in customAdhkar" :key="dhikr.id" class="custom-card">
        <div class="custom-header">
          <h3>{{ dhikr.title }}</h3>
          <div class="card-actions">
            <button class="edit-button" @click="editDhikr(dhikr)">
              <font-awesome-icon icon="fa-solid fa-edit" />
            </button>
            <button class="delete-button" @click="deleteDhikr(dhikr.id)">
              <font-awesome-icon icon="fa-solid fa-trash" />
            </button>
          </div>
        </div>
        
        <div class="custom-content">
          <p class="arabic-text">{{ dhikr.arabicText }}</p>
          <p class="translation">{{ dhikr.translation }}</p>
          <div class="custom-meta">
            <span class="category" v-if="dhikr.category">{{ dhikr.category }}</span>
            <span class="count" v-if="dhikr.recommendedCount">
              تعداد تکرار: {{ dhikr.recommendedCount }}
            </span>
          </div>
        </div>

        <div class="custom-actions">
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
  name: 'CustomAdhkarView',
  data() {
    return {
      customAdhkar: [],
      showAddForm: false,
      editingDhikr: null,
      formData: {
        title: '',
        arabicText: '',
        translation: '',
        category: '',
        recommendedCount: null
      }
    }
  },
  methods: {
    resetForm() {
      this.formData = {
        title: '',
        arabicText: '',
        translation: '',
        category: '',
        recommendedCount: null
      }
      this.editingDhikr = null
    },
    closeForm() {
      this.showAddForm = false
      this.resetForm()
    },
    editDhikr(dhikr) {
      this.editingDhikr = dhikr
      this.formData = { ...dhikr }
      this.showAddForm = true
    },
    async saveDhikr() {
      try {
        if (this.editingDhikr) {
          await this.$store.dispatch('updateCustomDhikr', {
            id: this.editingDhikr.id,
            ...this.formData
          })
        } else {
          await this.$store.dispatch('addCustomDhikr', this.formData)
        }
        this.closeForm()
        await this.loadCustomAdhkar()
      } catch (error) {
        console.error('Error saving dhikr:', error)
        // Show error toast
      }
    },
    async deleteDhikr(id) {
      if (confirm('آیا از حذف این ذکر اطمینان دارید؟')) {
        try {
          await this.$store.dispatch('deleteCustomDhikr', id)
          await this.loadCustomAdhkar()
        } catch (error) {
          console.error('Error deleting dhikr:', error)
          // Show error toast
        }
      }
    },
    startCounter(dhikr) {
      this.$router.push({
        name: 'counter',
        query: { dhikr: dhikr.id, type: 'custom' }
      })
    },
    shareDhikr(dhikr) {
      if (navigator.share) {
        navigator.share({
          title: dhikr.title,
          text: `${dhikr.arabicText}\n${dhikr.translation}`,
          url: window.location.href
        }).catch(console.error)
      }
    },
    async loadCustomAdhkar() {
      try {
        await this.$store.dispatch('loadCustomAdhkar')
        this.customAdhkar = this.$store.state.customAdhkar
      } catch (error) {
        console.error('Error loading custom adhkar:', error)
        // Show error toast
      }
    }
  },
  async created() {
    await this.loadCustomAdhkar()
  }
}
</script>

<style scoped>
.custom-adhkar {
  padding: 1rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.add-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #A79277;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.add-button:hover {
  background-color: #8a7660;
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

.subtitle {
  color: #666;
  margin-top: 0.5rem;
}

.custom-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.custom-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.custom-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.custom-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.edit-button,
.delete-button {
  background: none;
  border: none;
  padding: 0.5rem;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.edit-button {
  color: #2196f3;
}

.delete-button {
  color: #dc3545;
}

.edit-button:hover {
  background-color: rgba(33, 150, 243, 0.1);
}

.delete-button:hover {
  background-color: rgba(220, 53, 69, 0.1);
}

.custom-content {
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

.custom-meta {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #666;
}

.custom-actions {
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

/* Form Styles */
.form-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.form-container {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #333;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  font-size: 1rem;
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-button,
.save-button {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.cancel-button {
  background-color: #f8f9fa;
  color: #333;
  border: 1px solid #dee2e6;
}

.save-button {
  background-color: #A79277;
  color: white;
}

.cancel-button:hover {
  background-color: #e9ecef;
}

.save-button:hover {
  background-color: #8a7660;
}

body.dark-mode {
  .empty-state,
  .custom-card,
  .form-container {
    background-color: #333;
  }

  .custom-header h3,
  .arabic-text,
  .form-group label {
    color: #fff;
  }

  .translation,
  .custom-meta,
  .subtitle {
    color: #aaa;
  }

  .share-button,
  .cancel-button {
    background-color: #444;
    color: #fff;
    border-color: #555;
  }

  .share-button:hover,
  .cancel-button:hover {
    background-color: #555;
  }

  .form-group input,
  .form-group textarea {
    background-color: #444;
    border-color: #555;
    color: #fff;
  }
}

@media (max-width: 768px) {
  .custom-list {
    grid-template-columns: 1fr;
  }

  .form-container {
    margin: 1rem;
    max-height: calc(100vh - 2rem);
  }
}
</style> 