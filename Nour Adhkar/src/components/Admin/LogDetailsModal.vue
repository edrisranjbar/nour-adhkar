<template>
  <div v-if="show" class="modal-overlay" @click.self="closeModal">
    <div class="log-details-modal">
      <div class="modal-header">
        <h2>جزئیات لاگ</h2>
        <button class="close-button" @click="closeModal">
          <font-awesome-icon icon="times" />
        </button>
      </div>

      <div class="modal-body" v-if="log">
        <div class="log-info">
          <div class="log-header">
            <div class="log-id">
              <span class="label">شناسه:</span>
              <span class="value">{{ log.id }}</span>
            </div>
            
            <div class="log-type">
              <span 
                class="log-type-badge" 
                :class="'log-type-' + log.type"
              >
                {{ getLogTypeText(log.type) }}
              </span>
            </div>
          </div>
          
          <div class="log-source">
            <span class="label">فایل منبع:</span>
            <span class="value">{{ log.source_file }}</span>
          </div>
          
          <div class="log-timestamps">
            <div class="timestamp">
              <span class="label">تاریخ و زمان:</span>
              <span class="value" dir="ltr">{{ formatDate(log.created_at) }}</span>
            </div>
          </div>
          
          <div class="log-metadata">
            <div class="metadata-item" v-if="log.ip_address">
              <span class="label">آدرس IP:</span>
              <span class="value">{{ log.ip_address }}</span>
            </div>
            
            <div class="metadata-item" v-if="log.url">
              <span class="label">URL:</span>
              <span class="value">{{ log.url }}</span>
            </div>
            
            <div class="metadata-item" v-if="log.user_agent">
              <span class="label">مرورگر:</span>
              <span class="value">{{ log.user_agent }}</span>
            </div>
          </div>
          
          <div class="log-message-container">
            <h3>پیام</h3>
            <div class="log-message">{{ log.message }}</div>
          </div>
          
          <div class="log-stack-container" v-if="log.stack_trace">
            <h3>Stack Trace</h3>
            <pre class="log-stack">{{ log.stack_trace }}</pre>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button class="primary-button" @click="closeModal">بستن</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LogDetailsModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    log: {
      type: Object,
      default: null
    }
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    
    getLogTypeText(type) {
      const types = {
        'info': 'اطلاعات',
        'warning': 'هشدار',
        'error': 'خطا',
        'debug': 'دیباگ'
      };
      return types[type] || type;
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      }).format(date);
    },
    
    formatJson(jsonData) {
      if (typeof jsonData === 'string') {
        try {
          return JSON.stringify(JSON.parse(jsonData), null, 2);
        } catch (e) {
          return jsonData;
        }
      }
      return JSON.stringify(jsonData, null, 2);
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.log-details-modal {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  border-bottom: 1px solid #eee;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #777;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.log-info {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.log-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.log-id {
  display: flex;
  gap: 0.5rem;
  font-size: 1.1rem;
}

.log-type-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.9rem;
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

.log-source {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.log-timestamps {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.timestamp {
  display: flex;
  gap: 0.5rem;
}

.log-metadata {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  background-color: #f9f9f9;
  padding: 1rem;
  border-radius: 6px;
}

.metadata-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.label {
  color: #666;
  font-size: 0.9rem;
}

.value {
  font-weight: 500;
}

.log-message-container,
.log-stack-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.log-message-container h3,
.log-stack-container h3 {
  font-size: 1.1rem;
  margin: 0;
  color: #333;
}

.log-message {
  background-color: #f9f9f9;
  padding: 1rem;
  border-radius: 6px;
  white-space: pre-wrap;
  word-break: break-word;
}

.log-stack {
  background-color: #f5f5f5;
  padding: 1rem;
  border-radius: 6px;
  font-family: monospace;
  white-space: pre-wrap;
  overflow-x: auto;
  font-size: 0.9rem;
  line-height: 1.5;
  color: #333;
  direction: ltr;
  text-align: left;
}

.modal-footer {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: flex-start;
  border-top: 1px solid #eee;
}

.primary-button {
  padding: 0.5rem 1.5rem;
  background-color: #A79277;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.primary-button:hover {
  background-color: #8a775e;
}

/* Dark mode styles */
body.dark-mode .log-details-modal {
  background-color: #333;
  color: #eee;
}

body.dark-mode .modal-header {
  border-bottom-color: #444;
}

body.dark-mode .modal-header h2 {
  color: #eee;
}

body.dark-mode .close-button {
  color: #aaa;
}

body.dark-mode .log-metadata {
  background-color: #444;
}

body.dark-mode .label {
  color: #aaa;
}

body.dark-mode .log-message-container h3,
body.dark-mode .log-stack-container h3 {
  color: #eee;
}

body.dark-mode .log-message,
body.dark-mode .log-stack {
  background-color: #444;
  color: #eee;
}

body.dark-mode .modal-footer {
  border-top-color: #444;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .log-metadata {
    grid-template-columns: 1fr;
  }
}
</style> 