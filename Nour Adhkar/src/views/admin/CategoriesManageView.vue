<template>
  <div class="categories-manage">
    <AppHeader 
      title="مدیریت دسته‌بندی‌ها" 
      description="در این بخش می‌توانید دسته‌بندی‌های سایت را مدیریت کنید."
    />

    <!-- Actions Bar -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <div class="relative">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="جستجو در دسته‌بندی‌ها..."
            class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-64"
          />
          <font-awesome-icon
            icon="search"
            class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500"
          />
        </div>
      </div>

      <button
        @click="openAddModal()"
        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        <font-awesome-icon icon="plus" class="ml-2" />
        افزودن دسته‌بندی جدید
      </button>
    </div>

    <!-- Categories Grid Component -->
    <CategoriesGrid
      :categories="paginatedCategories"
      :loading="loading"
      :search-query="searchQuery"
      :pagination="pagination"
      @edit="openEditModal"
      @delete="openDeleteModal"
      @page-change="changePage"
    />

    <!-- Add/Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeEditModal"></div>
        
        <div class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 p-6 text-right shadow-xl transition-all">
          <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
            {{ editingCategory ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی جدید' }}
          </h3>
          
          <form @submit.prevent="handleSubmit" class="rtl">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  نام دسته‌بندی
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  dir="rtl"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  توضیحات
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  dir="rtl"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                ></textarea>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  دسته‌بندی والد
                </label>
                <select
                  v-model="form.parent_id"
                  dir="rtl"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                  <option :value="null">بدون والد</option>
                  <option
                    v-for="category in mainCategories"
                    :key="category.id"
                    :value="category.id"
                    :disabled="editingCategory && category.id === editingCategory.id"
                  >
                    {{ category.name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="mt-6 flex justify-start gap-3">
              <button
                type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                :disabled="loading"
              >
                {{ editingCategory ? 'ذخیره تغییرات' : 'افزودن' }}
              </button>
              <button
                type="button"
                @click="closeEditModal"
                class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
              >
                انصراف
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      title="حذف دسته‌بندی"
      :message="deleteMessage"
      confirm-text="حذف"
      @confirm="handleDelete"
      @close="closeDeleteModal"
    />
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

<style>
.categories-manage {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
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
