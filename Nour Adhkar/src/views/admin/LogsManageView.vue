<template>
  <div class="logs-manage">
    <LogsComponent @view-details="showLogDetails" />
    
    <LogDetailsModal 
      :show="showDetailsModal" 
      :log="selectedLog" 
      @close="closeDetailsModal"
    />
    
    <NotificationToast
      :notification="notification"
      @close="clearNotification"
    />
  </div>
</template>

<script>
import LogsComponent from '@/components/Admin/LogsComponent.vue';
import LogDetailsModal from '@/components/Admin/LogDetailsModal.vue';
import NotificationToast from '@/components/Admin/NotificationToast.vue';

export default {
  name: 'LogsManageView',
  components: {
    LogsComponent,
    LogDetailsModal,
    NotificationToast
  },
  data() {
    return {
      showDetailsModal: false,
      selectedLog: null,
      notification: {
        type: '',
        message: ''
      },
    };
  },
  methods: {
    showLogDetails(log) {
      this.selectedLog = log;
      this.showDetailsModal = true;
    },
    
    closeDetailsModal() {
      this.showDetailsModal = false;
      this.selectedLog = null;
    },
    
    showNotification(message, type = 'success') {
      this.notification = {
        message,
        type
      };
      
      // Auto-hide after 3 seconds
      setTimeout(() => {
        this.clearNotification();
      }, 3000);
    },
    
    clearNotification() {
      this.notification = {
        type: '',
        message: ''
      };
    }
  }
};
</script>

<style scoped>
.logs-manage {
  padding: 1.5rem;
}
</style> 