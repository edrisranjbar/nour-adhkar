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
.logs-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  color: var(--admin-text);
}

.logs-controls {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  background: var(--admin-surface);
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid var(--admin-border);
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.45);
}

.logs-controls label {
  color: var(--admin-muted);
  font-size: 0.9rem;
}

.logs-controls input,
.logs-controls select {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--admin-border);
  border-radius: 8px;
  padding: 0.6rem 0.8rem;
  color: var(--admin-text);
  font-family: inherit;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.logs-controls input:focus,
.logs-controls select:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.logs-controls select {
  min-width: 140px;
  direction: rtl;
}

.search-filter {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.search-input {
  flex: 1;
}

.clear-search {
  position: absolute;
  left: 0.65rem;
  background: none;
  border: none;
  color: var(--admin-muted);
  cursor: pointer;
  font-size: 1rem;
  padding: 0.25rem;
  transition: color 0.2s ease;
}

.clear-search:hover {
  color: var(--admin-accent);
}

.filter-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.logs-table-container {
  background-color: var(--admin-surface);
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--admin-border);
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.45);
}

.logs-table {
  width: 100%;
  border-collapse: collapse;
}

.logs-table th,
.logs-table td {
  padding: 0.85rem 1rem;
  text-align: right;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
}

.logs-table th {
  background: rgba(255, 255, 255, 0.05);
  font-weight: 600;
  color: var(--admin-muted);
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
}

.logs-table tr:last-child td {
  border-bottom: none;
}

.logs-table tr:hover {
  background-color: rgba(59, 130, 246, 0.08);
}

.log-message {
  max-width: 320px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.log-type-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.log-type-info {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
}

.log-type-warning {
  background: rgba(250, 204, 21, 0.15);
  color: #facc15;
}

.log-type-error {
  background: rgba(248, 113, 113, 0.15);
  color: #f87171;
}

.log-type-debug {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
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
  padding: 0.35rem;
  border-radius: 6px;
  transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
  color: var(--admin-muted);
}

.view-btn:hover {
  color: var(--admin-accent);
  background-color: rgba(59, 130, 246, 0.12);
  transform: translateY(-1px);
}

.delete-btn:hover {
  color: #f87171;
  background-color: rgba(248, 113, 113, 0.12);
  transform: translateY(-1px);
}

.logs-loading,
.logs-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--admin-muted);
  gap: 1rem;
}

.logs-pagination {
  display: flex;
  justify-content: center;
}

@media (max-width: 768px) {
  .filter-controls {
    flex-direction: column;
    align-items: stretch;
  }
}
</style> 