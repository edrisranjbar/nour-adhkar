<template>
  <div class="categories-manage">
    <main class="container">

      <div class="admin-controls">
        <h2>مدیریت دسته‌بندی‌ها</h2>
        <RouterLink to="/admin/categories/new" class="new-category-button">
          <font-awesome-icon icon="fa-solid fa-plus" />
          ایجاد دسته‌بندی جدید
        </RouterLink>
      </div>

      <!-- Categories Grid Component -->
      <CategoriesGrid :categories="paginatedCategories" :loading="loading" :search-query="searchQuery"
        :pagination="pagination" @edit="openEditModal" @delete="openDeleteModal" @page-change="changePage" />
    </main>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import AppHeader from '@/components/Admin/AppHeader.vue'
import CategoriesGrid from '@/components/Admin/CategoriesGrid.vue'
import ConfirmationModal from '@/components/Admin/ConfirmationModal.vue'
import { BASE_API_URL } from '@/config'
import { useToast } from 'vue-toastification'

export default {
  name: 'CategoriesManageView',
  components: {
    AppHeader,
    CategoriesGrid,
    ConfirmationModal
  },
  setup() {
    const toast = useToast()
    const categories = ref([])
    const loading = ref(true)
    const showEditModal = ref(false)
    const showDeleteModal = ref(false)
    const editingCategory = ref(null)
    const categoryToDelete = ref(null)
    const searchQuery = ref('')
    const form = ref({
      name: '',
      description: '',
      parent_id: null
    })

    // Pagination state
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0
    })

    // Computed properties
    const mainCategories = computed(() => {
      return categories.value.filter(c => !c.parent_id)
    })

    const filteredCategories = computed(() => {
      if (!searchQuery.value) return categories.value
      const query = searchQuery.value.toLowerCase()
      return categories.value.filter(category => 
        category.name.toLowerCase().includes(query) ||
        category.description?.toLowerCase().includes(query) ||
        category.slug.toLowerCase().includes(query)
      )
    })

    const paginatedCategories = computed(() => {
      const filtered = filteredCategories.value
      const start = (pagination.value.current_page - 1) * pagination.value.per_page
      const end = start + pagination.value.per_page
      return filtered.slice(start, end)
    })

    // Watch for changes in filtered categories to update pagination
    watch(filteredCategories, (newFilteredCategories) => {
      const totalItems = newFilteredCategories.length
      const totalPages = Math.max(1, Math.ceil(totalItems / pagination.value.per_page))
      
      pagination.value = {
        ...pagination.value,
        total: totalItems,
        last_page: totalPages,
        current_page: Math.min(pagination.value.current_page, totalPages)
      }
    }, { immediate: true })

    // Watch for search query changes to reset pagination
    watch(searchQuery, () => {
      pagination.value.current_page = 1
    })

    const deleteMessage = computed(() => {
      if (!categoryToDelete.value) return ''
      return `آیا از حذف دسته‌بندی "${categoryToDelete.value.name}" اطمینان دارید؟`
    })

    // Methods
    const fetchCategories = async () => {
      try {
        loading.value = true
        const token = localStorage.getItem('token')
        const response = await axios.get(`${BASE_API_URL}/admin/categories`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        
        if (response.data && Array.isArray(response.data)) {
          categories.value = response.data
        } else if (response.data && Array.isArray(response.data.data)) {
          categories.value = response.data.data
        } else if (response.data && Array.isArray(response.data.categories)) {
          categories.value = response.data.categories
        } else {
          categories.value = []
          console.error('Unexpected API response structure:', response.data)
        }

        // Update pagination after setting categories
        const totalItems = categories.value.length
        pagination.value = {
          current_page: 1,
          last_page: Math.max(1, Math.ceil(totalItems / pagination.value.per_page)),
          per_page: 10,
          total: totalItems
        }
      } catch (error) {
        console.error('Error fetching categories:', error)
        toast.error('خطا در دریافت دسته‌بندی‌ها')
        categories.value = []
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        pagination.value.current_page = page
        window.scrollTo(0, 0)
      }
    }

    const openAddModal = () => {
      editingCategory.value = null
      form.value = {
        name: '',
        description: '',
        parent_id: null
      }
      showEditModal.value = true
    }

    const openEditModal = (category) => {
      editingCategory.value = category
      form.value = {
        name: category.name,
        description: category.description,
        parent_id: category.parent_id
      }
      showEditModal.value = true
    }

    const closeEditModal = () => {
      showEditModal.value = false
      editingCategory.value = null
      form.value = {
        name: '',
        description: '',
        parent_id: null
      }
    }

    const openDeleteModal = (category) => {
      categoryToDelete.value = category
      showDeleteModal.value = true
    }

    const closeDeleteModal = () => {
      showDeleteModal.value = false
      categoryToDelete.value = null
    }

    const handleSubmit = async () => {
      try {
        loading.value = true
        const token = localStorage.getItem('token')
        if (editingCategory.value) {
          await axios.patch(`${BASE_API_URL}/admin/categories/${editingCategory.value.id}`, form.value, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          })
          toast.success('دسته‌بندی با موفقیت ویرایش شد')
        } else {
          await axios.post(`${BASE_API_URL}/admin/categories`, form.value, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          })
          toast.success('دسته‌بندی با موفقیت ایجاد شد')
        }
        await fetchCategories()
        closeEditModal()
      } catch (error) {
        console.error('Error saving category:', error)
        toast.error(error.response?.data?.message || 'خطا در ذخیره دسته‌بندی')
      } finally {
        loading.value = false
      }
    }

    const handleDelete = async () => {
      if (!categoryToDelete.value) return

      try {
        loading.value = true
        const token = localStorage.getItem('token')
        await axios.delete(`${BASE_API_URL}/admin/categories/${categoryToDelete.value.id}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        toast.success('دسته‌بندی با موفقیت حذف شد')
        await fetchCategories()
        closeDeleteModal()
      } catch (error) {
        console.error('Error deleting category:', error)
        toast.error(error.response?.data?.message || 'خطا در حذف دسته‌بندی')
      } finally {
        loading.value = false
      }
    }

    // Initial fetch
    fetchCategories()

    return {
      categories,
      loading,
      showEditModal,
      showDeleteModal,
      editingCategory,
      form,
      mainCategories,
      deleteMessage,
      searchQuery,
      filteredCategories,
      paginatedCategories,
      pagination,
      openAddModal,
      openEditModal,
      closeEditModal,
      openDeleteModal,
      closeDeleteModal,
      handleSubmit,
      handleDelete,
      changePage
    }
  }
}
</script>

<style scoped>
.categories-manage {
  min-height: 100vh;
  background: var(--admin-bg);
  color: var(--admin-text);
}

.container {
  width: 100%;
  padding: 0 2rem 2.5rem;
}

.admin-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.admin-controls h2 {
  font-size: 1.5rem;
  color: var(--admin-text);
  margin: 0;
}

.new-category-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: var(--admin-accent);
  color: #fff;
  padding: 8px 16px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.new-category-button:hover {
  background-color: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

/* Override Tailwind form styles for RTL */
[dir="rtl"] input,
[dir="rtl"] textarea,
[dir="rtl"] select {
  text-align: right;
}

/* Fix select arrow position in RTL */
[dir="rtl"] select {
  background-position: left 0.5rem center;
  padding-right: 0.75rem;
  padding-left: 2rem;
}
</style>
