<template>
  <div class="confirm-dialog-backdrop" v-if="show" @click.self="cancel">
    <div class="confirm-dialog" @click.stop>
      <div class="confirm-dialog-header">
        <h3>{{ title }}</h3>
        <button class="close-btn" @click="cancel">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="confirm-dialog-body">
        <div class="confirm-dialog-icon" v-if="showIcon">
          <i :class="iconClass"></i>
        </div>
        <div class="confirm-dialog-message">
          {{ message }}
        </div>
      </div>
      
      <div class="confirm-dialog-footer">
        <button 
          @click="cancel" 
          class="cancel-btn"
        >
          {{ cancelText }}
        </button>
        <button 
          @click="confirm" 
          class="confirm-btn"
          :class="confirmButtonClass"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConfirmDialog',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'تأیید'
    },
    message: {
      type: String,
      default: 'آیا از انجام این عملیات اطمینان دارید؟'
    },
    confirmText: {
      type: String,
      default: 'تأیید'
    },
    cancelText: {
      type: String,
      default: 'لغو'
    },
    type: {
      type: String,
      default: 'warning', // warning, danger, info, success
      validator: (value) => ['warning', 'danger', 'info', 'success'].includes(value)
    },
    showIcon: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    iconClass() {
      const baseClass = 'fas';
      switch (this.type) {
        case 'warning':
          return `${baseClass} fa-exclamation-triangle`;
        case 'danger':
          return `${baseClass} fa-trash`;
        case 'info':
          return `${baseClass} fa-info-circle`;
        case 'success':
          return `${baseClass} fa-check-circle`;
        default:
          return `${baseClass} fa-question-circle`;
      }
    },
    confirmButtonClass() {
      switch (this.type) {
        case 'danger':
          return 'confirm-btn-danger';
        case 'success':
          return 'confirm-btn-success';
        case 'info':
          return 'confirm-btn-info';
        default:
          return 'confirm-btn-warning';
      }
    }
  },
  methods: {
    confirm() {
      this.$emit('confirm');
    },
    cancel() {
      this.$emit('cancel');
    }
  }
};
</script>

<style scoped>
.confirm-dialog-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.confirm-dialog {
  background-color: var(--admin-surface);
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.6);
  animation: fadeInScale 0.2s ease-out;
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
}

.confirm-dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.1rem 1.5rem;
  border-bottom: 1px solid var(--admin-border);
}

.confirm-dialog-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: var(--admin-text);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--admin-muted);
  cursor: pointer;
  padding: 0.25rem;
  transition: color 0.2s ease;
  font-family: inherit;
}

.close-btn:hover {
  color: var(--admin-text);
}

.confirm-dialog-body {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.confirm-dialog-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.confirm-dialog-icon .fa-exclamation-triangle {
  color: #facc15;
}

.confirm-dialog-icon .fa-trash {
  color: #f87171;
}

.confirm-dialog-icon .fa-info-circle {
  color: var(--admin-accent);
}

.confirm-dialog-icon .fa-check-circle {
  color: #4ade80;
}

.confirm-dialog-message {
  color: var(--admin-muted);
  line-height: 1.6;
}

.confirm-dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--admin-border);
}

.cancel-btn,
.confirm-btn {
  padding: 0.6rem 1.1rem;
  cursor: pointer;
  border-radius: 8px;
  font-weight: 600;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
  border: none;
  font-family: inherit;
}

.cancel-btn {
  background-color: rgba(148, 163, 184, 0.2);
  color: var(--admin-muted);
}

.cancel-btn:hover {
  background-color: rgba(148, 163, 184, 0.3);
  transform: translateY(-1px);
}

.confirm-btn {
  color: #fff;
  border: none;
}

.confirm-btn-warning {
  background-color: #facc15;
}

.confirm-btn-warning:hover {
  background-color: #eab308;
  transform: translateY(-1px);
}

.confirm-btn-danger {
  background-color: #f87171;
}

.confirm-btn-danger:hover {
  background-color: #ef4444;
  transform: translateY(-1px);
}

.confirm-btn-info {
  background-color: var(--admin-accent);
}

.confirm-btn-info:hover {
  background-color: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
}

.confirm-btn-success {
  background-color: #4ade80;
}

.confirm-btn-success:hover {
  background-color: #22c55e;
  transform: translateY(-1px);
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style> 