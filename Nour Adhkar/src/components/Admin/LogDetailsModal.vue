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
  inset: 0;
  background-color: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.log-details-modal {
  background-color: var(--admin-surface);
  border-radius: 14px;
  width: 92%;
  max-width: 820px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.6);
  display: flex;
  flex-direction: column;
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.75rem;
  border-bottom: 1px solid var(--admin-border);
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: var(--admin-text);
}

.close-button {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: var(--admin-muted);
  cursor: pointer;
  transition: color 0.2s ease;
}

.close-button:hover {
  color: var(--admin-text);
}

.modal-body {
  padding: 1.75rem;
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
  gap: 1rem;
}

.log-id,
.log-source,
.metadata-item,
.timestamp {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.label {
  font-weight: 600;
  color: var(--admin-muted);
}

.value {
  color: var(--admin-text);
}

.log-type-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.85rem;
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

.log-message-container,
.log-stack-container {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--admin-border);
  border-radius: 12px;
  padding: 1.25rem;
}

.log-message-container h3,
.log-stack-container h3 {
  margin-top: 0;
  margin-bottom: 0.75rem;
  color: var(--admin-text);
}

.log-message {
  white-space: pre-wrap;
  line-height: 1.7;
  color: var(--admin-text);
}

.log-stack {
  max-height: 320px;
  overflow-y: auto;
  background: rgba(15, 23, 42, 0.6);
  border-radius: 8px;
  padding: 1rem;
  color: #f87171;
  direction: ltr;
  text-align: left;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 1.25rem 1.75rem;
  border-top: 1px solid var(--admin-border);
}

.primary-button {
  background: var(--admin-accent);
  color: #fff;
  border: none;
  padding: 0.65rem 1.4rem;
  border-radius: 8px;
  cursor: pointer;
  font-family: inherit;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.primary-button:hover {
  background: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

@media (max-width: 768px) {
  .log-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style> 