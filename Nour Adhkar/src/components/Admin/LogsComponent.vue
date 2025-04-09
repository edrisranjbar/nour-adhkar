<template>
  <div class="logs-container">
    <!-- Filters and controls -->
    <div class="logs-controls">
      <div class="search-filter">
        <label for="log-search" class="search-label">جستجو:</label>
        <input 
          id="log-search"
          type="text" 
          v-model="searchQuery" 
          @input="handleSearch" 
          placeholder="جستجو در لاگ‌ها..." 
          class="search-input"
        />
        <button class="clear-search" v-if="searchQuery" @click="clearSearch">
          <font-awesome-icon icon="times" />
        </button>
      </div>
      
      <div class="filter-controls">
        <div class="filter-item">
          <label for="log-type">نوع لاگ:</label>
          <select id="log-type" v-model="filters.type" @change="applyFilters">
            <option value="">همه</option>
            <option value="info">اطلاعات</option>
            <option value="warning">هشدار</option>
            <option value="error">خطا</option>
            <option value="debug">دیباگ</option>
          </select>
        </div>
        
        <div class="filter-item">
          <label for="date-range">بازه زمانی:</label>
          <select id="date-range" v-model="filters.dateRange" @change="applyFilters">
            <option value="all">همه</option>
            <option value="today">امروز</option>
            <option value="yesterday">دیروز</option>
            <option value="week">هفته اخیر</option>
            <option value="month">ماه اخیر</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Logs table -->
    <div class="logs-table-container">
      <table class="logs-table" v-if="!loading && logs.length > 0">
        <thead>
          <tr>
            <th>شناسه</th>
            <th>نوع</th>
            <th>پیام</th>
            <th>کاربر</th>
            <th>تاریخ</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs" :key="log.id" :class="getLogClass(log)">
            <td>{{ log.id }}</td>
            <td>
              <span class="log-type-badge" :class="'log-type-' + log.type">
                {{ getLogTypeText(log.type) }}
              </span>
            </td>
            <td class="log-message">{{ truncateText(log.message, 60) }}</td>
            <td>{{ log.user ? log.user.name : 'سیستم' }}</td>
            <td dir="ltr">{{ formatDate(log.created_at) }}</td>
            <td class="actions">
              <button class="view-btn" @click="viewLogDetails(log)">
                <font-awesome-icon icon="eye" />
              </button>
              <button class="delete-btn" @click="confirmDeleteLog(log)">
                <font-awesome-icon icon="trash" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-else-if="loading" class="logs-loading">
        <font-awesome-icon icon="spinner" spin />
        <p>در حال بارگذاری لاگ‌ها...</p>
      </div>
      
      <div v-else class="logs-empty">
        <font-awesome-icon icon="file-alt" size="2x" />
        <p>هیچ لاگی یافت نشد.</p>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="logs-pagination" v-if="!loading && logs.length > 0">
      <Pagination 
        :pagination="pagination" 
        @page-change="changePage" 
      />
    </div>
    
    <!-- Confirmation modal -->
    <ConfirmationModal
      v-if="showDeleteConfirmation"
      title="حذف لاگ"
      message="آیا از حذف این لاگ اطمینان دارید؟ این عمل قابل بازگشت نیست."
      confirmText="بله، حذف شود"
      cancelText="انصراف"
      @confirm="deleteLog"
      @close="showDeleteConfirmation = false"
    />
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useStore } from 'vuex';
import Pagination from './Pagination.vue';
import ConfirmationModal from './ConfirmationModal.vue';

const BASE_API_URL = import.meta.env.VITE_API_URL || '/api';

export default {
  name: 'LogsComponent',
  components: {
    Pagination,
    ConfirmationModal
  },
  emits: ['view-details'],
  setup(props, { emit }) {
    const store = useStore();
    const token = computed(() => store.state.token);
    
    // State
    const logs = ref([]);
    const loading = ref(true);
    const searchQuery = ref('');
    const filters = ref({
      type: '',
      dateRange: 'all'
    });
    const pagination = ref({
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1
    });
    const showDeleteConfirmation = ref(false);
    const logToDelete = ref(null);
    
    // Methods
    const fetchLogs = async () => {
      loading.value = true;
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/logs`, {
          params: {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
            search: searchQuery.value,
            type: filters.value.type,
            date_range: filters.value.dateRange
          },
          headers: {
            Authorization: `Bearer ${token.value}`
          }
        });
        
        if (response.data.success) {
          logs.value = response.data.logs.data || [];
          pagination.value = {
            current_page: response.data.logs.current_page,
            last_page: response.data.logs.last_page,
            per_page: response.data.logs.per_page,
            total: response.data.logs.total
          };
        } else {
          console.error('Error fetching logs:', response.data.message);
        }
      } catch (error) {
        console.error('Error fetching logs:', error);
      } finally {
        loading.value = false;
      }
    };
    
    const handleSearch = () => {
      pagination.value.current_page = 1;
      fetchLogs();
    };
    
    const clearSearch = () => {
      searchQuery.value = '';
      handleSearch();
    };
    
    const applyFilters = () => {
      pagination.value.current_page = 1;
      fetchLogs();
    };
    
    const changePage = (page) => {
      pagination.value.current_page = page;
      fetchLogs();
    };
    
    const viewLogDetails = (log) => {
      emit('view-details', log);
    };
    
    const confirmDeleteLog = (log) => {
      logToDelete.value = log;
      showDeleteConfirmation.value = true;
    };
    
    const deleteLog = async () => {
      if (!logToDelete.value) return;
      
      try {
        const response = await axios.delete(`${BASE_API_URL}/admin/logs`, {
          params: {
            filename: logToDelete.value.source_file
          },
          headers: {
            Authorization: `Bearer ${token.value}`
          }
        });
        
        if (response.data.success) {
          // Refresh logs after deletion
          fetchLogs();
          showDeleteConfirmation.value = false;
          logToDelete.value = null;
        }
      } catch (error) {
        console.error('Error deleting log:', error);
      }
    };
    
    const getLogClass = (log) => {
      return {
        'log-info': log.type === 'info',
        'log-warning': log.type === 'warning',
        'log-error': log.type === 'error',
        'log-debug': log.type === 'debug'
      };
    };
    
    const getLogTypeText = (type) => {
      const types = {
        'info': 'اطلاعات',
        'warning': 'هشدار',
        'error': 'خطا',
        'debug': 'دیباگ'
      };
      return types[type] || type;
    };
    
    const truncateText = (text, length) => {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    };
    
    // Watch for changes in filters
    watch([() => filters.value.type, () => filters.value.dateRange], () => {
      applyFilters();
    });
    
    // Initial fetch
    fetchLogs();
    
    return {
      logs,
      loading,
      searchQuery,
      filters,
      pagination,
      showDeleteConfirmation,
      handleSearch,
      clearSearch,
      applyFilters,
      changePage,
      viewLogDetails,
      confirmDeleteLog,
      deleteLog,
      getLogClass,
      getLogTypeText,
      truncateText,
      formatDate
    };
  }
};
</script>

<style scoped>
input,select,textarea {
  font-family: inherit;
}

.logs-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  width: 100%;
}

.logs-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  background-color: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.search-filter {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  color: #666;
}

.search-input {
  width: 100%;
  padding: 0.6rem 2.5rem 0.6rem 1rem;
  font-size: 0.9rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  direction: rtl;
  height: 40px;
}

.clear-search {
  position: absolute;
  top: 33px;
  left: 0.75rem;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #999;
  cursor: pointer;
  padding: 0.25rem;
}

.filter-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.filter-item label {
  font-size: 0.85rem;
  color: #666;
}

.filter-item select {
  padding: 0.6rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: white;
  min-width: 120px;
  direction: rtl;
  height: 40px;
}

.logs-table-container {
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.logs-table {
  width: 100%;
  border-collapse: collapse;
}

.logs-table th,
.logs-table td {
  padding: 0.75rem 1rem;
  text-align: right;
  border-bottom: 1px solid #eee;
}

.logs-table th {
  background-color: #f5f5f5;
  font-weight: 600;
  color: #555;
}

.logs-table tr:last-child td {
  border-bottom: none;
}

.logs-table tr:hover {
  background-color: #f9f9f9;
}

.log-message {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.log-type-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.log-type-info {
  background-color: #e6f4ff;
  color: #0066cc;
}

.log-type-warning {
  background-color: #fff8e6;
  color: #cc7700;
}

.log-type-error {
  background-color: #ffebeb;
  color: #cc0000;
}

.log-type-debug {
  background-color: #e6ffee;
  color: #00994d;
}

.actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.view-btn,
.delete-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.view-btn {
  color: #0066cc;
}

.delete-btn {
  color: #cc0000;
}

.view-btn:hover {
  background-color: #e6f4ff;
}

.delete-btn:hover {
  background-color: #ffebeb;
}

.logs-loading,
.logs-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #666;
  gap: 1rem;
}

.logs-pagination {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

/* Dark mode styles */
body.dark-mode .logs-controls,
body.dark-mode .logs-table-container {
  background-color: #333;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

body.dark-mode .search-label {
  color: #aaa;
}

body.dark-mode .search-input,
body.dark-mode .filter-item select {
  background-color: #444;
  border-color: #555;
  color: #eee;
}

body.dark-mode .logs-table th {
  background-color: #444;
  color: #eee;
}

body.dark-mode .logs-table td {
  border-bottom-color: #444;
}

body.dark-mode .logs-table tr:hover {
  background-color: #3a3a3a;
}

body.dark-mode .logs-loading,
body.dark-mode .logs-empty {
  color: #aaa;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .search-filter {
    width: 100%;
  }
  
  .logs-table th,
  .logs-table td {
    padding: 0.5rem;
  }
  
  .log-message {
    max-width: 200px;
  }
}

@media (max-width: 768px) {
  .logs-table {
    display: block;
    overflow-x: auto;
  }
  
  .log-message {
    max-width: 150px;
  }
}
</style> 